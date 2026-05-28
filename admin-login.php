<?php
/**
 * admin-login.php — Login exclusivo do painel admin MAGDA CREW
 *
 * Fluxo:
 *   1. Admin digita o e-mail
 *   2. Sistema verifica se é um admin real (is_admin = 1 AND ativo = 1)
 *   3. Gera código de 6 dígitos com expiração de 10 minutos
 *   4. Envia por e-mail via EmailJS (mesmo serviço que o site já usa)
 *   5. Admin digita o código → sessão criada → redirect para painel.php
 *
 * Segurança aplicada:
 *   - Rate limiting por IP: máximo 5 tentativas em 15 minutos
 *   - Mensagem genérica: não revela se o e-mail existe ou não
 *   - Código invalidado após uso
 *   - Sessão com cookies httponly + samesite=strict
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false, // mude para TRUE com HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Se já está logado como admin, vai direto para o painel
if (!empty($_SESSION['usuario_id']) && !empty($_SESSION['is_admin'])) {
    header('Location: /magda-crew/painel.php');
    exit;
}

require_once __DIR__ . '/src/Config/Database.php';

$pdo   = Database::getConnection();
$etapa = $_SESSION['admin_login_etapa'] ?? 'email'; // 'email' ou 'codigo'
$erro  = '';
$info  = '';

// Motivo de redirecionamento vindo do guard
$motivo = $_GET['motivo'] ?? '';
if ($motivo === 'acesso_revogado') {
    $info = 'Seu acesso de administrador foi removido.';
}

// ── Rate Limiting simples por IP ──────────────────────────────────────────────
$ip             = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$chave_tentativas = 'admin_tentativas_' . md5($ip);
$chave_bloqueio   = 'admin_bloqueio_'   . md5($ip);

// Usa a sessão para rate limit (simples, suficiente para um painel admin)
if (!isset($_SESSION[$chave_tentativas])) {
    $_SESSION[$chave_tentativas] = 0;
    $_SESSION[$chave_bloqueio]   = 0;
}

$bloqueado = (
    $_SESSION[$chave_tentativas] >= 5 &&
    (time() - $_SESSION[$chave_bloqueio]) < 900 // 15 minutos
);

if ($bloqueado) {
    $segundos_restantes = 900 - (time() - $_SESSION[$chave_bloqueio]);
    $minutos = ceil($segundos_restantes / 60);
    $erro = "Muitas tentativas. Aguarde {$minutos} minuto(s).";
}

// ── POST: Etapa 1 — Receber e-mail ────────────────────────────────────────────
if (!$bloqueado && $_SERVER['REQUEST_METHOD'] === 'POST' && $etapa === 'email') {

    $email = trim(strtolower($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    } else {

        // Busca no banco — só admins ativos
        $stmt = $pdo->prepare("
            SELECT id FROM usuarios
            WHERE email = ? AND is_admin = 1 AND ativo = 1
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        // IMPORTANTE: mesma resposta independente de encontrar ou não
        // Isso impede enumerar quais e-mails são admin
        $codigo = random_int(100000, 999999);
        $expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        if ($usuario) {
            // Salva o código no banco
            $pdo->prepare("
                UPDATE usuarios
                SET codigo_login = ?, codigo_expira = ?
                WHERE id = ?
            ")->execute([$codigo, $expira, $usuario['id']]);

            // Guarda na sessão para enviar via EmailJS no HTML
            $_SESSION['admin_email_destino'] = $email;
            $_SESSION['admin_codigo_enviar'] = $codigo;
        }

        // Sempre avança para a etapa 2 (não revela se achou)
        $_SESSION['admin_login_email'] = $email;
        $_SESSION['admin_login_etapa'] = 'codigo';
        $_SESSION['admin_enviar_email'] = true;

        header('Location: /magda-crew/admin-login.php');
        exit;
    }
}

// ── POST: Etapa 2 — Verificar código ─────────────────────────────────────────
if (!$bloqueado && $_SERVER['REQUEST_METHOD'] === 'POST' && $etapa === 'codigo') {

    $codigo_digitado = preg_replace('/[^0-9]/', '', $_POST['codigo'] ?? '');
    $email           = $_SESSION['admin_login_email'] ?? '';

    if (strlen($codigo_digitado) !== 6) {
        $erro = 'Digite o código de 6 dígitos.';
    } else {

        $stmt = $pdo->prepare("
            SELECT * FROM usuarios
            WHERE email = ?
            AND codigo_login = ?
            AND is_admin = 1
            AND ativo = 1
            LIMIT 1
        ");
        $stmt->execute([$email, $codigo_digitado]);
        $usuario = $stmt->fetch();

        if ($usuario && strtotime($usuario['codigo_expira']) > time()) {

            // Sucesso — cria sessão admin
            session_regenerate_id(true);

            $_SESSION['usuario_id']        = $usuario['id'];
            $_SESSION['email']             = $usuario['email'];
            $_SESSION['is_admin']          = 1;
            $_SESSION['_admin_last_check'] = time();
            $_SESSION['_session_created']  = time();

            // Invalida o código e registra o login
            $pdo->prepare("
                UPDATE usuarios
                SET codigo_login = NULL,
                codigo_expira = NULL,
                email_verificado = 1,
                ultimo_login = NOW()
                WHERE id = ?
            ")->execute([$usuario['id']]);

            // Limpa variáveis de login
            unset(
                $_SESSION['admin_login_etapa'],
                $_SESSION['admin_login_email'],
                $_SESSION['admin_email_destino'],
                $_SESSION['admin_codigo_enviar'],
                $_SESSION[$chave_tentativas],
                $_SESSION[$chave_bloqueio]
            );

            header('Location: /magda-crew/painel.php');
            exit;

        } else {
            // Código errado — incrementa tentativas
            $_SESSION[$chave_tentativas]++;
            $_SESSION[$chave_bloqueio] = time();

            $restam = 5 - $_SESSION[$chave_tentativas];
            $erro   = $restam > 0
                ? "Código inválido ou expirado. {$restam} tentativa(s) restante(s)."
                : "Código inválido. Você foi bloqueado por 15 minutos.";
        }
    }
}

// Captura dados para envio via EmailJS
$emailParaEnviar  = null;
$codigoParaEnviar = null;
if (isset($_SESSION['admin_enviar_email'])) {
    unset($_SESSION['admin_enviar_email']);
    $emailParaEnviar  = $_SESSION['admin_email_destino'] ?? null;
    $codigoParaEnviar = $_SESSION['admin_codigo_enviar'] ?? null;
    unset($_SESSION['admin_email_destino'], $_SESSION['admin_codigo_enviar']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Magda Crew - Admin</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/admin-login.css">
</head>
<body>

<div class="login-container">

    <!-- Botão de Voltar (X) -->
    <?php if ($etapa === 'email'): ?>
        <a href="/magda-crew/public/index.php">
            <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
        </a>
    <?php else: ?>
        <a href="/magda-crew/admin-login.php?reset=1">
            <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
        </a>
    <?php endif; ?>

    <!-- Logo centralizada -->
    <div class="logo">
        <a href="/magda-crew/public/index.php">
            <img src="/magda-crew/public/assets/images/MagdaWhiteLogo.png" class="logo-img" alt="Magda Crew">
        </a>
    </div>

    <?php if ($etapa === 'email'): ?>
    <!-- ── ETAPA 1: E-MAIL ── -->
        <h2>Painel Administrativo</h2>
        <p>Digite o e-mail cadastrado como administrador para receber o código de acesso.</p>

        <?php if ($info): ?>
            <div class="alert-box alert-info">
                <?= htmlspecialchars($info) ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert-box alert-error">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="input-box">
                <input
                    type="email"
                    name="email"
                    placeholder="E-mail"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    autofocus
                    required
                    <?= $bloqueado ? 'disabled' : '' ?>
                >
            </div>
            
            <button class="continue-btn" type="submit" <?= $bloqueado ? 'disabled' : '' ?>>
                Enviar código
            </button>
        </form>

    <?php else: ?>
    <!-- ── ETAPA 2: CÓDIGO ── -->
        <h2>Verifique seu e-mail</h2>
        <p>Se o e-mail for de um administrador, um código de 6 dígitos foi enviado. Expira em 10 minutos.</p>

        <?php if ($erro): ?>
            <div class="alert-box alert-error">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="input-box input-code">
                <input
                    type="text"
                    name="codigo"
                    placeholder="Código de 6 dígitos"
                    maxlength="6"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    autofocus
                    required
                    <?= $bloqueado ? 'disabled' : '' ?>
                    oninput="this.value = this.value.replace(/[^0-9]/g,'')"
                >
            </div>

            <button class="continue-btn" type="submit" <?= $bloqueado ? 'disabled' : '' ?>>
                Entrar no painel
            </button>
        </form>

        <div class="links" style="margin-top: 20px;">
            <a href="/magda-crew/admin-login.php?reset=1">Usar outro e-mail</a>
        </div>
    <?php endif; ?>

</div>

<?php if ($emailParaEnviar && $codigoParaEnviar): ?>
<!-- Disparo do e-mail via EmailJS (mesmo serviço do site) -->
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
<script>
emailjs.init("8uchm0rcbi6BVoMsp");
emailjs.send("service_4qi1zs9", "template_8bqxgl3", {
    email:  "<?= htmlspecialchars($emailParaEnviar) ?>",
    codigo: "<?= (int) $codigoParaEnviar ?>",
    name:   "MAGDA CREW Admin"
});
</script>
<?php endif; ?>

<?php
// Limpa reset de etapa se solicitado
if (isset($_GET['reset'])) {
    session_unset();
    header('Location: /magda-crew/admin-login.php');
    exit;
}
?>
</body>
</html>