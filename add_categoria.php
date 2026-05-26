<?php
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();
$erro_mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);

    // 1. VERIFICAÇÃO: Campo obrigatório
    if (empty($nome)) {
        $erro_mensagem = "Por favor, preencha o nome da categoria.";
    } else {
        
        // 2. VERIFICAÇÃO: Evitar categorias duplicadas
        $checkNome = $pdo->prepare("SELECT id FROM categorias WHERE nome = ?");
        $checkNome->execute([$nome]);
        
        if ($checkNome->rowCount() > 0) {
            $erro_mensagem = "A categoria '<strong>" . htmlspecialchars($nome) . "</strong>' já existe.";
        } else {
            
            // 3. CADASTRO: Insere no banco e volta para a lista
            $insert = $pdo->prepare("INSERT INTO categorias (nome) VALUES (?)");
            if ($insert->execute([$nome])) {
                header("Location: categorias.php");
                exit;
            } else {
                $erro_mensagem = "Erro ao cadastrar a categoria. Tente novamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Nova Categoria</title>
    
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/adicionar-produto.css">
    
    <style>
        /* Alerta de erro reaproveitado */
        .alerta-erro {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<main class="container-admin">
    <a href="categorias.php">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Nova Categoria</h1>
    <p class="subtitle">Adicione um novo departamento para seus produtos.</p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro">
            <?= $erro_mensagem ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        
        <div class="form-group">
            <input type="text" name="nome" placeholder="Nome da Categoria (ex: Camisetas)" value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>" required>
        </div>

        <button type="submit" class="btn-add">
            Cadastrar Categoria
        </button>

    </form>
</main>

</body>
</html>