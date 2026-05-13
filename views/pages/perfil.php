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
<title>Perfil - Magda Crew</title>

<style>

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f5f5f5;
    color:#111;
}

.topbar{
    width:100%;
    background:#fff;
    border-bottom:1px solid #ddd;
}

.top-content{
    max-width:1200px;
    margin:auto;
    padding:20px;
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.logo{
    height:45px;
}

.menu{
    display:flex;
    gap:20px;
    align-items:center;
}

.menu a{
    text-decoration:none;
    color:#111;
    font-size:15px;
}

.profile-icon{
    width:38px;
    height:38px;
    border-radius:50%;
    border:1px solid #ccc;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    background:#fff;
}

.container{
    max-width:1100px;
    margin:40px auto;
    padding:0 20px;
}

h1{
    font-size:38px;
    margin-bottom:30px;
}

.card{
    background:#fff;
    border-radius:16px;
    padding:30px;
    margin-bottom:25px;
    border:1px solid #e5e5e5;
}

.label{
    color:#666;
    font-size:14px;
    margin-bottom:8px;
}

.value{
    font-size:18px;
}

.logout-btn{
    padding:14px 28px;
    border-radius:12px;
    border:none;
    background:#111;
    color:#fff;
    cursor:pointer;
    font-size:15px;
}

.logout-btn:hover{
    opacity:.9;
}

</style>
</head>
<body>

<div class="topbar">
    <div class="top-content">

        <div class="menu">
            <a href="/magda-crew/public/index.php">
                <img 
                    src="/magda-crew/public/assets/images/MagdaBlackLogo.png"
                    class="logo"
                >
            </a>

            <a href="#">Orders</a>
            <a href="/magda-crew/views/pages/perfil.php">Profile</a>
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