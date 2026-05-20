<?php
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

$mensagem = '';

// Processa o formulário de adição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'];
    $cor_id = $_POST['cor_id'];
    $tamanho_id = $_POST['tamanho_id'];
    $quantidade_estoque = $_POST['quantidade_estoque'];

    // Verifica se essa variante já existe para atualizar ou inserir nova
    $stmtCheck = $pdo->prepare("SELECT id, quantidade_estoque FROM produto_variantes WHERE produto_id = ? AND cor_id = ? AND tamanho_id = ?");
    $stmtCheck->execute([$produto_id, $cor_id, $tamanho_id]);
    $variante = $stmtCheck->fetch();

    if ($variante) {
        // Se já existe, soma a quantidade
        $nova_quantidade = $variante['quantidade_estoque'] + $quantidade_estoque;
        $stmtUpdate = $pdo->prepare("UPDATE produto_variantes SET quantidade_estoque = ? WHERE id = ?");
        if ($stmtUpdate->execute([$nova_quantidade, $variante['id']])) {
            $mensagem = "<p style='color: green; margin-bottom: 15px;'>Estoque atualizado com sucesso!</p>";
        }
    } else {
        // Se não existe, cria a nova variante (com sku como NULL, já que ele aceita na sua tabela)
        $stmtInsert = $pdo->prepare("INSERT INTO produto_variantes (produto_id, cor_id, tamanho_id, quantidade_estoque) VALUES (?, ?, ?, ?)");
        if ($stmtInsert->execute([$produto_id, $cor_id, $tamanho_id, $quantidade_estoque])) {
            $mensagem = "<p style='color: green; margin-bottom: 15px;'>Nova variante adicionada ao estoque!</p>";
        } else {
            $mensagem = "<p style='color: red; margin-bottom: 15px;'>Erro ao adicionar estoque.</p>";
        }
    }
}

// Busca os dados para preencher os <select> do formulário
$produtos = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$cores = $pdo->query("SELECT id, nome FROM cores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$tamanhos = $pdo->query("SELECT id, nome FROM tamanhos ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Estoque - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
    <style>
        .form-admin select {
            padding: 14px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
            background-color: white;
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <h2>Adicionar Estoque / Variante</h2>
            <div class="user-info">
                <span>Admin</span>
                <a href="logout.php">Sair</a>
            </div>
        </header>

        <section class="content">
            <div class="topo-produtos">
                <h1>Cadastrar Entrada</h1>
                <a href="estoque.php" class="btn-editar">Voltar para Estoque</a>
            </div>

            <?= $mensagem ?>

            <form action="" method="POST" class="form-admin">
                
                <label for="produto_id">Produto</label>
                <select name="produto_id" id="produto_id" required>
                    <option value="">Selecione o Produto...</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="cor_id">Cor</label>
                <select name="cor_id" id="cor_id" required>
                    <option value="">Selecione a Cor...</option>
                    <?php foreach ($cores as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="tamanho_id">Tamanho</label>
                <select name="tamanho_id" id="tamanho_id" required>
                    <option value="">Selecione o Tamanho...</option>
                    <?php foreach ($tamanhos as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="quantidade_estoque">Quantidade (Entrada)</label>
                <input type="number" name="quantidade_estoque" id="quantidade_estoque" placeholder="Ex: 10" min="1" required>

                <button type="submit" class="btn-add" style="margin-top: 10px; font-size: 16px; cursor: pointer; border: none;">Salvar Estoque</button>
            </form>

        </section>
    </main>

</body>
</html>