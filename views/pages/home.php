<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloDaPagina) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f9f9f9; }
        header { background: #111; color: #fff; padding: 20px; text-align: center; }
        .categorias { display: flex; justify-content: center; gap: 15px; padding: 20px; list-style: none; }
        .categorias a { text-decoration: none; color: #333; background: #ddd; padding: 8px 16px; border-radius: 20px; font-weight: bold; }
        .vitrine { display: flex; justify-content: center; gap: 20px; padding: 20px; flex-wrap: wrap; }
        .card-produto { background: #fff; border: 1px solid #eee; padding: 20px; border-radius: 8px; width: 250px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .preco { color: #27ae60; font-size: 24px; font-weight: bold; margin: 10px 0; }
        .btn-comprar { display: inline-block; background: #111; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; width: 100%; box-sizing: border-box; }
    </style>
</head>
<body>

    <header>
        <h1>MAGDA CREW</h1>
        <p>A revolução do seu estilo.</p>
    </header>

    <ul class="categorias">
        <?php foreach ($categorias as $cat): ?>
            <li>
                <a href="/MAGDA-CREW/public/produtos/categoria/<?= $cat['id'] ?>">
                    <?= htmlspecialchars($cat['nome']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2 style="text-align: center; margin-top: 40px;">Destaques da Semana</h2>
    <div class="vitrine">
        <?php foreach ($produtosDestaque as $produto): ?>
            <div class="card-produto">
                <div style="width: 100%; height: 200px; background: #eee; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; color: #aaa;">
                    Sem Imagem
                </div>
                
                <h3 style="font-size: 18px; margin: 0;"><?= htmlspecialchars($produto['nome']) ?></h3>
                <p style="font-size: 12px; color: #666;"><?= htmlspecialchars($produto['categoria_nome']) ?></p>
                
                <div class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                
                <a href="/MAGDA-CREW/public/produto/detalhes/<?= $produto['id'] ?>" class="btn-comprar">
                    Ver Detalhes
                </a>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>