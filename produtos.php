<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

$stmt = $pdo->query("
    SELECT p.*, 
           (SELECT caminho_imagem FROM produto_imagens WHERE produto_id = p.id AND is_principal = 1 LIMIT 1) as caminho_imagem
    FROM produtos p 
    ORDER BY p.id DESC
");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Produtos - Magda Crew</title>
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/gestao.css">
    <link rel="stylesheet" href="/MagdaCrew/public/assets/css/produtos.css">
    <style>
        .painel-busca-wrap {
            width: 100%;
            display: block;
            text-align: left;
            margin: 0 0 18px 0;
        }

        .painel-busca-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #111;
            margin-bottom: 7px;
        }

        .painel-busca-input {
            width: 100%;
            max-width: 360px;
            display: block;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            color: #111;
            outline: none;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .painel-busca-input:focus {
            border-color: #111;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">

        <div class="topo-produtos">
            <div>
                <h1>Produtos</h1>
                <p style="margin-bottom: 20px; color: #666;">
                    Gerencie os produtos da loja.
                </p>
            </div>
            <a href="AdicionarProduto.php" class="btn-adicionar">
                + Novo Produto
            </a>
        </div>

        <div class="painel-busca-wrap">
            <label class="painel-busca-label" for="buscaProdutos">Buscar produto</label>
            <input type="text" id="buscaProdutos" class="painel-busca-input" placeholder="Digite nome, preço ou ID..." data-busca-painel autocomplete="off">
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Produto</th>
                    <th style="width: 150px;">Preço</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($produtos as $produto): ?>
                    <?php
                        $textoBusca = implode(' ', [
                            $produto['id'] ?? '',
                            $produto['nome'] ?? '',
                            $produto['descricao'] ?? '',
                            $produto['preco'] ?? '',
                            isset($produto['ativo']) && $produto['ativo'] ? 'ativo' : 'inativo'
                        ]);
                    ?>
                    <tr data-search="<?= htmlspecialchars($textoBusca, ENT_QUOTES, 'UTF-8') ?>">
                        <td><?= htmlspecialchars($produto['id']) ?></td>

                        <td>
                            <div class="produto-info-cell">
                                <?php if (!empty($produto['caminho_imagem'])): ?>
                                    <img src="/MagdaCrew/<?= htmlspecialchars($produto['caminho_imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>" class="thumb-produto">
                                <?php else: ?>
                                    <div class="thumb-produto placeholder">Sem Foto</div>
                                <?php endif; ?>
                                <span class="produto-nome-texto"><?= htmlspecialchars($produto['nome']) ?></span>
                            </div>
                        </td>

                        <td>
                            R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
                        </td>

                        <td style="text-align: center;">
                            <div class="acoes">
                                <a href="EditarProduto.php?id=<?= $produto['id'] ?>" class="btn-editar-img" title="Editar Produto">
                                    <img src="/MagdaCrew/public/assets/images/BlackPencil.png" alt="Editar" class="icon-editar">
                                </a>

                                <label class="switch" title="Ativar/Desativar">
                                    <input 
                                        type="checkbox" 
                                        <?= (isset($produto['ativo']) && $produto['ativo'] == 1) ? 'checked' : '' ?>
                                        onchange="window.location.href='ToggleProduto.php?id=<?= $produto['id'] ?>'"
                                    >
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr data-sem-resultado style="display: none;">
                    <td colspan="4" style="text-align: center; color: #999; padding: 35px 0;">Nenhum produto encontrado.</td>
                </tr>
            </tbody>
        </table>

    </section>
</main>

<script src="public/assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('[data-busca-painel]');
    const linhas = document.querySelectorAll('tbody tr[data-search]');
    const semResultado = document.querySelector('[data-sem-resultado]');

    if (!input) return;

    const normalizar = (texto) => String(texto || '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    function filtrarTabela() {
        const termo = normalizar(input.value);
        let totalVisivel = 0;

        linhas.forEach(function (linha) {
            const textoLinha = normalizar(linha.dataset.search || linha.innerText);
            const mostrar = textoLinha.includes(termo);
            linha.style.display = mostrar ? '' : 'none';
            if (mostrar) totalVisivel++;
        });

        if (semResultado) {
            semResultado.style.display = totalVisivel === 0 ? '' : 'none';
        }
    }

    input.addEventListener('input', filtrarTabela);
});
</script>

</body>
</html>
