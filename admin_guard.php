<?php
/**
 * admin_guard.php — Porteiro central do painel MAGDA CREW
 *
 * Inclua este arquivo como PRIMEIRA linha de cada página do painel:
 *   require_once __DIR__ . '/admin_guard.php';
 *
 * O que ele faz:
 *   1. Inicia a sessão (só uma vez por request)
 *   2. Verifica se existe sessão válida com is_admin = 1
 *   3. Verifica no banco se o usuário ainda está ativo e ainda é admin
 *      (impede que uma sessão antiga continue válida após rebaixamento)
 *   4. Regenera o ID da sessão periodicamente (proteção contra session fixation)
 *   5. Redireciona para o login do admin caso qualquer verificação falhe
 */

// ── 1. Iniciar sessão com cookies seguros ─────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,           // expira ao fechar o browser
        'path'     => '/',
        'secure'   => false,       // mude para TRUE quando tiver HTTPS
        'httponly' => true,        // JS nunca acessa o cookie
        'samesite' => 'Strict'     // proteção contra CSRF
    ]);
    session_start();
}

// ── 2. Verificação rápida na sessão ───────────────────────────────────────────
if (empty($_SESSION['usuario_id']) || empty($_SESSION['is_admin'])) {
    _redirecionar_login();
}

// ── 3. Verificação no banco (a cada 5 minutos) ────────────────────────────────
// Evita que uma sessão aberta continue válida se o admin for desativado/rebaixado
$agora = time();
$ultimaVerificacao = $_SESSION['_admin_last_check'] ?? 0;

if (($agora - $ultimaVerificacao) > 300) { // 300 segundos = 5 minutos

    require_once __DIR__ . '/src/Config/Database.php';

    try {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare("
            SELECT id FROM usuarios
            WHERE id = ? AND is_admin = 1 AND ativo = 1
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['usuario_id']]);

        if (!$stmt->fetch()) {
            // Usuário foi desativado ou perdeu admin — derruba a sessão
            session_unset();
            session_destroy();
            _redirecionar_login('acesso_revogado');
        }

        $_SESSION['_admin_last_check'] = $agora;

    } catch (Exception $e) {
        // Em caso de erro no banco, nega acesso por segurança
        _redirecionar_login();
    }
}

// ── 4. Regeneração periódica do session ID (anti session fixation) ────────────
if (empty($_SESSION['_session_created'])) {
    $_SESSION['_session_created'] = $agora;
} elseif (($agora - $_SESSION['_session_created']) > 1800) { // 30 minutos
    session_regenerate_id(true); // true = apaga a sessão antiga do servidor
    $_SESSION['_session_created'] = $agora;
}

// ── Helper: redirecionar para o login ─────────────────────────────────────────
function _redirecionar_login(string $motivo = '') {
    $url = '/magda-crew/admin-login.php';
    if ($motivo) {
        $url .= '?motivo=' . urlencode($motivo);
    }
    header('Location: ' . $url);
    exit;
}
