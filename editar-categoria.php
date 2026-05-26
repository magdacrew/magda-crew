<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();
$erro_mensagem = "";
$sucesso_mensagem = "";

// 1. Pega o ID da URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    die("Categoria inválida.");
}

// 2. Busca a categoria atual para preencher o campo
$stmt = $pdo->prepare("SELECT * FROM categorias WHERE id = ?");
$stmt->execute([$id]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    die("Categoria não encontrada.");
}

// 3. Atualiza os dados quando o formulário for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);

    if (empty($nome)) {
        $erro_mensagem = "O nome não pode ficar vazio.";
    } else {
        // Verifica se já existe outra categoria com esse nome (ignorando a atual)
        $check = $pdo->prepare("SELECT id FROM categorias WHERE nome = ? AND id != ?");
        $check->execute([$nome, $id]);
        
        if ($check->rowCount() > 0) {
            $erro_mensagem = "Já existe outra categoria com esse nome.";
        } else {
            $update = $pdo->prepare("UPDATE categorias SET nome = ? WHERE id = ?");
            if ($update->execute([$nome, $id])) {
                // Redireciona de volta após o sucesso
                header("Location: categorias.php");
                exit;
            } else {
                $erro_mensagem = "Erro ao atualizar.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Categoria</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/adicionar-produto.css">
    <style>
        .alerta-erro { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

<main class="container-admin">
    <a href="categorias.php">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Editar Categoria</h1>
    <p class="subtitle">Alterando: <strong><?= htmlspecialchars($categoria['nome']) ?></strong></p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro"><?= $erro_mensagem ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label style="color: #aaa; font-size: 14px; margin-bottom: 5px; display: block;">Nome da Categoria</label>
            <input type="text" name="nome" value="<?= htmlspecialchars(isset($_POST['nome']) ? $_POST['nome'] : $categoria['nome']) ?>" required>
        </div>

        <button type="submit" class="btn-add">Salvar Alterações</button>
    </form>
</main>

</body>
</html>