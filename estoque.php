<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar no banco de dados: " . $e->getMessage());
}

// Query atualizada para trazer a imagem principal do produto
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

// Mapeamento idêntico de cores para renderizar a bolinha via CSS
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
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
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
                <a href="adicionar-estoque.php" class="btn-add">Adicionar Estoque</a>
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
                        <?php foreach ($estoques as $item): 
                            // Define a cor da bolinha baseado no mapa ou cinza padrão
                            $nomeCor = $item['cor'];
                            $hex = isset($mapaCoresHex[$nomeCor]) ? $mapaCoresHex[$nomeCor] : '#333333';
                            $borderStyle = strtoupper($hex) === '#FFFFFF' ? '1px solid #888' : '1px solid #eee';
                        ?>
                            <tr>
                                <td><?= $item['id'] ?></td>
                                
                                <td>
                                    <div class="produto-info-cell">
                                        <?php if (!empty($item['caminho_imagem'])): ?>
                                            <img src="/magda-crew/<?= htmlspecialchars($item['caminho_imagem']) ?>" alt="<?= htmlspecialchars($item['produto']) ?>" class="thumb-produto">
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
                                
                                <td><strong><?= $item['quantidade_estoque'] ?> pcs</strong></td>
                                
                                <td style="text-align: center;">
                                    <div class="acoes">
                                        <a href="editar-estoque.php?id=<?= $item['id'] ?>" class="btn-editar-img" title="Editar Estoque">
                                            <img src="/magda-crew/public/assets/images/BlackPencil.png" alt="Editar" class="icon-editar">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999; padding: 40px 0;">Nenhum item em estoque encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>