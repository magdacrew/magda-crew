<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar no banco de dados: " . $e->getMessage());
}

$query = "
    SELECT pv.id, 
           p.nome as produto, 
           c.nome as cor, 
           t.nome as tamanho, 
           pv.quantidade_estoque,
           (SELECT caminho_imagem FROM produto_imagens WHERE produto_id = p.id AND is_principal = 1 LIMIT 1) as caminho_imagem
    FROM produto_variantes pv
    INNER JOIN produtos p ON pv.produto_id = p.id
    INNER JOIN cores c ON pv.cor_id = c.id
    INNER JOIN tamanhos t ON pv.tamanho_id = t.id
    ORDER BY p.nome ASC, c.nome ASC, t.nome ASC
";
$stmt = $pdo->query($query);
$estoques = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapaCoresHex = [
    'Preto'    => '#000000',
    'Branco'   => '#FFFFFF',
    'Cinza'    => '#808080',
    'Vermelho' => '#E60000',
    'Azul'     => '#0044CC',
    'Verde'    => '#008822',
    'Amarelo'  => '#FFCC00',
    'Rosa'     => '#FF66B2',
    'Roxo'     => '#660099',
    'Bege'     => '#F5F5DC',
    'Marrom'   => '#663300',
    'Laranja'  => '#FF6600'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Magda Crew</title>
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
                <h1>Estoque Atual</h1>
                <p style="margin-bottom: 20px; color: #666;">
                    Gerencie a quantidade disponível de variantes em tempo real.
                </p>
            </div>
            <a href="AdicionarEstoque.php" class="btn-add">Adicionar Estoque</a>
        </div>

        <div class="painel-busca-wrap">
            <label class="painel-busca-label" for="buscaEstoque">Buscar estoque</label>
            <input type="text" id="buscaEstoque" class="painel-busca-input" placeholder="Digite produto, cor, tamanho ou quantidade..." data-busca-painel autocomplete="off">
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Produto</th>
                    <th style="width: 150px;">Cor</th>
                    <th style="width: 120px;">Tamanho</th>
                    <th style="width: 120px;">Quantidade</th>
                    <th style="width: 120px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($estoques) > 0): ?>
                    <?php foreach ($estoques as $item): ?>
                        <?php
                            $nomeCor = $item['cor'];
                            $hex = isset($mapaCoresHex[$nomeCor]) ? $mapaCoresHex[$nomeCor] : '#333333';
                            $borderStyle = strtoupper($hex) === '#FFFFFF' ? '1px solid #888' : '1px solid #eee';
                            $textoBusca = implode(' ', [
                                $item['id'] ?? '',
                                $item['produto'] ?? '',
                                $item['cor'] ?? '',
                                $item['tamanho'] ?? '',
                                $item['quantidade_estoque'] ?? ''
                            ]);
                        ?>
                        <tr data-search="<?= htmlspecialchars($textoBusca, ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars($item['id']) ?></td>
                            
                            <td>
                                <div class="produto-info-cell">
                                    <?php if (!empty($item['caminho_imagem'])): ?>
                                        <img src="/MagdaCrew/<?= htmlspecialchars($item['caminho_imagem']) ?>" alt="<?= htmlspecialchars($item['produto']) ?>" class="thumb-produto">
                                    <?php else: ?>
                                        <div class="thumb-produto placeholder">Sem Foto</div>
                                    <?php endif; ?>
                                    <span class="produto-nome-texto"><?= htmlspecialchars($item['produto']) ?></span>
                                </div>
                            </td>
                            
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span class="color-circle" style="background-color: <?= $hex ?>; border: <?= $borderStyle ?>; width: 14px; height: 14px; border-radius: 50%; display: inline-block;"></span>
                                    <span><?= htmlspecialchars($nomeCor) ?></span>
                                </div>
                            </td>
                            
                            <td>
                                <span style="font-weight: 600; color: #444; background: #eef2f1; padding: 4px 10px; border-radius: 6px; font-size: 13px;">
                                    <?= htmlspecialchars($item['tamanho']) ?>
                                </span>
                            </td>
                            
                            <td><strong><?= htmlspecialchars($item['quantidade_estoque']) ?> pcs</strong></td>
                            
                            <td style="text-align: center;">
                                <div class="acoes">
                                    <a href="EditarEstoque.php?id=<?= $item['id'] ?>" class="btn-editar-img" title="Editar Estoque">
                                        <img src="/MagdaCrew/public/assets/images/BlackPencil.png" alt="Editar" class="icon-editar">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr data-sem-resultado style="display: none;">
                        <td colspan="6" style="text-align: center; color: #999; padding: 35px 0;">Nenhum item de estoque encontrado.</td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #999; padding: 40px 0;">Nenhum item em estoque encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>

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
