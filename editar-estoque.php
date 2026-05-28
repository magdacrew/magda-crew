<?php
require_once __DIR__ . '/admin_guard.php';

require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: estoque.php");
    exit;
}

$erro_mensagem = "";
$sucesso_mensagem = "";

// Processa a atualização da quantidade
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_quantidade = isset($_POST['quantidade_estoque']) ? (int)$_POST['quantidade_estoque'] : -1;

    if ($nova_quantidade < 0) {
        $erro_mensagem = "A quantidade não pode ser um valor negativo.";
    } else {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE produto_variantes SET quantidade_estoque = ? WHERE id = ?");
            $stmtUpdate->execute([$nova_quantidade, $id]);
            $sucesso_mensagem = "Estoque atualizado com sucesso!";
        } catch (Exception $e) {
            $erro_mensagem = "Erro ao atualizar o estoque: " . $e->getMessage();
        }
    }
}

// Busca os dados atuais da variante para exibir no formulário
$query = "
    SELECT pv.id, p.nome as produto, c.nome as cor, t.nome as tamanho, pv.quantidade_estoque,
           (SELECT caminho_imagem FROM produto_imagens WHERE produto_id = p.id AND is_principal = 1 LIMIT 1) as caminho_imagem
    FROM produto_variantes pv
    INNER JOIN produtos p ON pv.produto_id = p.id
    INNER JOIN cores c ON pv.cor_id = c.id
    INNER JOIN tamanhos t ON pv.tamanho_id = t.id
    WHERE pv.id = ?
";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$variante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$variante) {
    header("Location: estoque.php");
    exit;
}

// Mapeamento de cores para a bolinha visual
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

$nomeCor = $variante['cor'];
$hexColor = isset($mapaCoresHex[$nomeCor]) ? $mapaCoresHex[$nomeCor] : '#333333';
$borderStyle = strtoupper($hexColor) === '#FFFFFF' ? '1px solid #888' : '1px solid #333';
$caminhoImg = !empty($variante['caminho_imagem']) ? '/magda-crew/' . $variante['caminho_imagem'] : '/magda-crew/public/assets/images/15.png';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link class="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Editar Estoque - Magda Crew</title>
    <link rel="stylesheet" href="/magda-crew/public/assets/css/editar-estoque.css">
</head>
<body>

<main class="container-admin">
    <a href="estoque.php">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Editar Estoque</h1>
    <p class="subtitle">Ajuste o saldo disponível para a variante selecionada.</p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro"><?= $erro_mensagem ?></div>
    <?php endif; ?>

    <?php if (!empty($sucesso_mensagem)): ?>
        <div class="alerta-sucesso"><?= $sucesso_mensagem ?></div>
    <?php endif; ?>

    <form method="POST" class="form-admin" autocomplete="off">
        
        <div class="card-detalhes-variante">
            <img src="<?= $caminhoImg ?>" alt="Produto" class="mini-foto-produto">
            <div class="detalhes-info">
                <h3><?= htmlspecialchars($variante['produto']) ?></h3>
                <div class="badges-container">
                    <span class="badge-item">
                        <span class="color-circle" style="background-color: <?= $hexColor ?>; border: <?= $borderStyle ?>;"></span>
                        <?= htmlspecialchars($nomeCor) ?>
                    </span>
                    <span class="badge-item tamanho-badge">Tam: <?= htmlspecialchars($variante['tamanho']) ?></span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="quantidade_estoque">Quantidade Atual em Estoque</label>
            <input 
                type="number" 
                name="quantidade_estoque" 
                id="quantidade_estoque" 
                value="<?= htmlspecialchars($variante['quantidade_estoque']) ?>" 
                min="0" 
                required
            >
        </div>

        <button type="submit" class="btn-salvar">
            Salvar Alterações
        </button>
    </form>
</main>

</body>
</html>