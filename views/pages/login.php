<?php
session_start();

$erro = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um e-mail válido.";
    } else {
        $_SESSION["email"] = $email;
        $_SESSION["novidades"] = $novidades;

        header("Location: /magda-crew/public/index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="/magda-crew/public/assets/css/login.css">
<link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
<title>Login - Magda Crew</title>


</head>

<body>

<div class="login-container">
  <a href="/magda-crew/public/index.php">
    <img src="/magda-crew/public/assets/images/x.png" class="botao-x" onclick="minhaFuncao()">
  </a>

  <div class="logo">
        <a href="/magda-crew/public/index.php">
            <img src="/MAGDA-CREW/public/assets/images/MagdaWhiteLogo.png" alt="Magda Crew" class="logo-img">
        </a>
  </div>

  <h2>Fazer login</h2>
  <p>Fazer login ou criar uma conta</p>

  <button class="shop-btn" type="button">
    Continuar com Shop
  </button>

  <div class="divider">ou</div>

  <form method="POST">

    <div class="input-box">
      <input 
        type="email" 
        name="email" 
        placeholder="E-mail"
        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        required
      >
    </div>

    <?php if (!empty($erro)): ?>
      <div class="error"><?= $erro ?></div>
    <?php endif; ?>

    <button class="continue-btn" type="submit">
      Continuar
    </button>

    <label class="check-area">

  </form>

  <div class="links">
    <a href="#">Política de privacidade</a>
  </div>

</div>

</body>
</html>