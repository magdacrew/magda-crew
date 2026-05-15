<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: /magda-crew/views/pages/login.php");
    exit;
}

$email = $_SESSION["email"] ?? "";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="/magda-crew/public/assets/css/perfil.css">
<title>Perfil - Magda Crew</title>
</head>
<body>

<div class="topbar">
    <div class="top-content">

        <div class="menu">
            <a href="/magda-crew/public/index.php">
                <img 
                    src="/magda-crew/public/assets/images/MagdaWhiteLogo.png"
                    class="logo"
                >
            </a>

            <a href="/magda-crew/views/pages/orders.php">Orders</a>
            <a href="/magda-crew/views/pages/Profile.php">Profile</a>
        </div>

        <div class="profile-icon">
            👤
        </div>

    </div>
</div>

<div class="container">

    <h1>Perfil</h1>

    <div class="card">

        <div class="label">
            E-mail
        </div>

        <div class="value">
            <?= htmlspecialchars($email) ?>
        </div>

    </div>

    <div class="card">

        <h3>Endereços</h3>

        <p style="color:#777;">
            Nenhum endereço adicionado
        </p>

    </div>

    <form action="/magda-crew/views/pages/logout.php" method="POST">
        <button class="logout-btn">
            Sair
        </button>
    </form>

</div>

</body>
</html>