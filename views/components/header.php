<!DOCTYPE html>
<html lang="pt-BR">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloDaPagina) ? htmlspecialchars($tituloDaPagina) : 'Magda Crew' ?></title>
    
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/index.css">
    
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
      <span>🔍</span>
    </div>

    <span class="icon">♙</span>
    <span class="icon">☼</span>
    <span class="icon">▱</span>
  </div>
</header>

    <main></main>