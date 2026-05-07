<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloDaPagina) ? htmlspecialchars($tituloDaPagina) : 'Magda Crew' ?></title>
    
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/header.css">
    
    <?= isset($cssExtra) ? $cssExtra : '' ?>
</head>
<body>

    <header>
  <nav>
    <a href="#">Shop</a>
    <a href="#">Archive</a>
    <a href="#">Flagship</a>
  </nav>

  <div class="logo">
        <a href="../public/index.php" class="logo-link">
            <img src="/MAGDA-CREW/public/assets/images/MagdaWhiteLogo.png" alt="Magda Crew" class="logo-img">
        </a>
  </div>

  <div class="actions">
    <div class="search">
      <input type="text" placeholder="Buscar">
      <img src="/MAGDA-CREW/public/assets/images/WhiteMagnifyingGlass.png" alt="Buscar" class="icon">
    </div>
    <a href="#">
      <img src="/MAGDA-CREW/public/assets/images/WhiteUser.png" alt="Usuário"  class="icon">
    </a>
    
    <a href="#">
      <img src="/MAGDA-CREW/public/assets/images/Sun.png" alt="Alternar tema para claro"  class="icon">
    </a>
    <a href="#">
      <img src="/MAGDA-CREW/public/assets/images/WhiteBag.png" alt="Sacola"  class="icon">
    </a>
  </div>
</header>
    <main></main>