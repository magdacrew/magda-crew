<?php
session_start();

require_once __DIR__ . "/../../src/Config/Database.php";

$pdo = Database::getConnection();

$erro = "";

/*
|--------------------------------------------------------------------------
| VERIFICA SE VEIO DO LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["email_login"])) {

    header("Location: /magda-crew/views/pages/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| ENVIA EMAIL AUTOMATICAMENTE
|--------------------------------------------------------------------------
*/

if (isset($_SESSION["enviar_email"])) {

    unset($_SESSION["enviar_email"]);

    $emailEnviar = $_SESSION["email_login"];
    $codigoEnviar = $_SESSION["codigo_para_email"];
}

/*
|--------------------------------------------------------------------------
| VERIFICA CÓDIGO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $codigo = preg_replace(
        '/[^0-9]/',
        '',
        $_POST["codigo"] ?? ""
    );

    $email = $_SESSION["email_login"];

    if (strlen($codigo) !== 6) {

        $erro = "Digite o código de 6 dígitos.";

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM usuarios
            WHERE email = ?
            AND codigo_login = ?
            AND ativo = TRUE
            LIMIT 1
        ");

        $stmt->execute([
            $email,
            $codigo
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $usuario &&
            strtotime($usuario["codigo_expira"]) > time()
        ) {

            $_SESSION["usuario_id"] = $usuario["id"];
            $_SESSION["email"] = $usuario["email"];
            $_SESSION["usuario_email"] = $usuario["email"];
            $_SESSION["is_admin"] = $usuario["is_admin"];

            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET
                    codigo_login = NULL,
                    codigo_expira = NULL,
                    email_verificado = TRUE,
                    ultimo_login = NOW()
                WHERE id = ?
            ");

            $stmt->execute([
                $usuario["id"]
            ]);

            unset($_SESSION["email_login"]);
            unset($_SESSION["codigo_para_email"]);

            header("Location: /magda-crew/public/index.php");
            exit;

        } else {

            $erro = "Código inválido ou expirado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<link rel="stylesheet"
href="/magda-crew/public/assets/css/login.css">

<title>Verificar Código - Magda Crew</title>

</head>

<body>

<div class="login-container">

    <a href="/magda-crew/public/index.php" target="_blank">
        <img src="/magda-crew/public/assets/images/X.png" alt="Canto" class="botao-x">
    </a>

    <div class="logo">

        <a href="/magda-crew/public/index.php">

            <img
                src="/magda-crew/public/assets/images/MagdaWhiteLogo.png"
                class="logo-img"
            >

        </a>

    </div>

    <h2>Verificar código</h2>

    <p>
        Digite o código enviado para seu e-mail.
    </p>

    <form method="POST">

        <div class="input-box">

            <input
                type="text"
                name="codigo"
                placeholder="Código de 6 dígitos"
                maxlength="6"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
            >

        </div>

        <?php if (!empty($erro)): ?>

            <div class="error">

                <?= htmlspecialchars($erro) ?>

            </div>

        <?php endif; ?>

        <button
            class="continue-btn"
            type="submit"
        >
            Entrar
        </button>

    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

<script>

emailjs.init("8uchm0rcbi6BVoMsp");

<?php if (
    isset($emailEnviar) &&
    isset($codigoEnviar)
): ?>

emailjs.send(
    "service_4qi1zs9",
    "template_8bqxgl3",
    {
        email: "<?= $emailEnviar ?>",
        codigo: "<?= $codigoEnviar ?>",
        name: "MAGDA CREW"
    }
);

<?php endif; ?>

</script>

</body>
</html>