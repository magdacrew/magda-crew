<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloDaPagina) ? htmlspecialchars($tituloDaPagina) : 'Magda Crew' ?></title>
    
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/global.css">
    
    <?= isset($cssExtra) ? $cssExtra : '' ?>
</head>
<body>

    <header>
        <h1>MAGDA CREW</h1>
        <p>A revolução do seu estilo.</p>
    </header>

    <nav>
        <ul class="nav-categorias">
            <li><a href="/MAGDA-CREW/public/">Início</a></li>
            <?php if (isset($categorias) && is_array($categorias)): ?>
                <?php foreach ($categorias as $cat): ?>
                    <li>
                        <a href="/MAGDA-CREW/public/produtos/categoria/<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['nome']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </nav>

    <main></main>