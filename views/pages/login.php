<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

require_once __DIR__ . "/../../src/Config/Database.php";

$pdo = Database::getConnection();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = "Digite um e-mail válido.";

    } else {

        $codigo = random_int(100000, 999999);

        $expira = date(
            "Y-m-d H:i:s",
            strtotime("+10 minutes")
        );

        $stmt = $pdo->prepare("
            SELECT id 
            FROM usuarios 
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {

            $stmt = $pdo->prepare("
                UPDATE usuarios
                SET codigo_login = ?,
                    codigo_expira = ?
                WHERE email = ?
            ");

            $stmt->execute([
                $codigo,
                $expira,
                $email
            ]);

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO usuarios (
                    email,
                    codigo_login,
                    codigo_expira,
                    ativo,
                    email_verificado,
                    data_cadastro
                )
                VALUES (?, ?, ?, 1, 0, NOW())
            ");

            $stmt->execute([
                $email,
                $codigo,
                $expira
            ]);
        }

        $_SESSION["email_login"] = $email;

        $_SESSION["codigo_para_email"] = $codigo;

        $_SESSION["enviar_email"] = true;

header("Location: /MagdaCrew/views/pages/VerificarCodigo.php");
exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet"
href="/MagdaCrew/public/assets/css/Login.css">
<title>Login - Magda Crew</title>
</head>
<body>

<div class="login-container">

    <a href="javascript:history.back()">
        <img src="/MagdaCrew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <div class="logo">
        <a href="/MagdaCrew/public/index.php">
            <img
                src="/MagdaCrew/public/assets/images/MagdaWhiteLogo.png"
                class="logo-img"
            >
        </a>
    </div>

    <h2>Fazer login</h2>

    <p>
        Digite seu e-mail para receber
        um código de acesso
    </p>

    <form method="POST">

        <div class="input-box">

            <input
                type="email"
                name="email"
                placeholder="E-mail"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                autocomplete="email"
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
            Continuar
        </button>

    </form>

    <div class="links">
        <a href="#">
            Política de privacidade
        </a>
    </div>

</div>

</body>
</html>