<?php
require_once __DIR__ . '/admin_guard.php';

require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

$erro_mensagem = "";
$sucesso_mensagem = "";

// Processa o formulário de adição dinâmica
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produto_id'] ?? '';
    $cor_id = $_POST['cor_id'] ?? '';
    $variantes = $_POST['variantes'] ?? [];

    if (empty($produto_id) || empty($cor_id) || empty($variantes)) {
        $erro_mensagem = "Por favor, selecione o Produto, a Cor e adicione ao menos um tamanho com quantidade.";
    } else {
        try {
            $pdo->beginTransaction();

            foreach ($variantes as $v) {
                $tamanho_id = (int)$v['tamanho_id'];
                $quantidade_estoque = (int)$v['quantidade'];

                if ($quantidade_estoque <= 0) continue;

                // Verifica se essa variante já existe para atualizar ou inserir nova
                $stmtCheck = $pdo->prepare("SELECT id, quantidade_estoque FROM produto_variantes WHERE produto_id = ? AND cor_id = ? AND tamanho_id = ?");
                $stmtCheck->execute([$produto_id, $cor_id, $tamanho_id]);
                $varianteExistente = $stmtCheck->fetch();

                if ($varianteExistente) {
                    $nova_quantidade = $varianteExistente['quantidade_estoque'] + $quantidade_estoque;
                    $stmtUpdate = $pdo->prepare("UPDATE produto_variantes SET quantidade_estoque = ? WHERE id = ?");
                    $stmtUpdate->execute([$nova_quantidade, $varianteExistente['id']]);
                } else {
                    $stmtInsert = $pdo->prepare("INSERT INTO produto_variantes (produto_id, cor_id, tamanho_id, quantidade_estoque) VALUES (?, ?, ?, ?)");
                    $stmtInsert->execute([$produto_id, $cor_id, $tamanho_id, $quantidade_estoque]);
                }
            }

            $pdo->commit();
            $sucesso_mensagem = "Entradas de estoque registradas com sucesso!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro_mensagem = "Erro ao processar estoque: " . $e->getMessage();
        }
    }
}

// 1. Busca os dados incluindo a imagem principal do produto (Tabela: produtos e produto_imagens)
$produtos = $pdo->query("
    SELECT p.id, p.nome, i.caminho_imagem 
    FROM produtos p
    LEFT JOIN produto_imagens i ON p.id = i.produto_id AND i.is_principal = 1
    ORDER BY p.nome
")->fetchAll(PDO::FETCH_ASSOC);

// 2. Busca as cores cadastradas
$coresDoBanco = $pdo->query("SELECT id, nome FROM cores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Mapeamento dinâmico de cores para renderizar a bolinha via CSS sem alterar seu banco
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

$cores = [];
foreach ($coresDoBanco as $cor) {
    $nomeCor = $cor['nome'];
    // Se a cor cadastrada não estiver no mapa, define um cinza escuro padrão
    $hex = isset($mapaCoresHex[$nomeCor]) ? $mapaCoresHex[$nomeCor] : '#333333';
    $cores[] = [
        'id' => $cor['id'],
        'nome' => $nomeCor,
        'hex' => $hex
    ];
}

// 3. Busca os tamanhos
$tamanhos = $pdo->query("SELECT id, nome FROM tamanhos ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link class="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Cadastrar Entrada - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/adicionar-estoque.css">
</head>
<body>

<main class="container-admin">
    <a href="estoque.php">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="botao-x">
    </a>

    <h1>Cadastrar Entrada</h1>
    <p class="subtitle">Insira a grade de tamanhos e quantidades para a cor selecionada.</p>

    <?php if (!empty($erro_mensagem)): ?>
        <div class="alerta-erro"><?= $erro_mensagem ?></div>
    <?php endif; ?>

    <?php if (!empty($sucesso_mensagem)): ?>
        <div class="alerta-sucesso"><?= $sucesso_mensagem ?></div>
    <?php endif; ?>

    <form method="POST" class="form-admin" autocomplete="off">
        
        <!-- DROPDOWN CUSTOMIZADO: PRODUTO -->
        <div class="form-group" style="position: relative;">
            <label>Produto</label>
            <input type="hidden" name="produto_id" id="produto_id" required>
            
            <div class="custom-select-trigger" id="productTrigger">
                <span>Selecione o Produto...</span>
            </div>
            
            <div class="custom-options-container" id="productOptions">
                <?php foreach ($produtos as $p): 
                    // Garante o caminho correto da imagem com base nos seus UPDATES do banco
                    $caminhoImg = !empty($p['caminho_imagem']) ? '/magda-crew/' . $p['caminho_imagem'] : '/magda-crew/public/assets/images/15.png';
                ?>
                    <div class="custom-option" data-value="<?= $p['id'] ?>" onclick="selectProduct(this, '<?= htmlspecialchars($p['nome']) ?>', '<?= $caminhoImg ?>')">
                        <img src="<?= $caminhoImg ?>" alt="Produto">
                        <span><?= htmlspecialchars($p['nome']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- DROPDOWN CUSTOMIZADO: COR -->
        <div class="form-group" style="position: relative;">
            <label>Cor Geral do Lote</label>
            <input type="hidden" name="cor_id" id="cor_id" required>
            
            <div class="custom-select-trigger" id="colorTrigger">
                <span>Selecione a Cor...</span>
            </div>
            
            <div class="custom-options-container" id="colorOptions">
                <?php foreach ($cores as $c): ?>
                    <div class="custom-option" data-value="<?= $c['id'] ?>" onclick="selectColor(this, '<?= htmlspecialchars($c['nome']) ?>', '<?= $c['hex'] ?>')">
                        <span class="color-circle" style="background-color: <?= $c['hex'] ?>;"></span>
                        <span><?= htmlspecialchars($c['nome']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <hr>

        <h3>Grade de Tamanhos / Quantidades</h3>
        
        <div id="containerGrade"></div>

        <button type="button" class="btn-secundario" onclick="adicionarLinhaGrade()">
            + Adicionar Tamanho na Grade
        </button>

        <button type="submit" class="btn-add">
            Salvar Estoque
        </button>
    </form>
</main>

<script>
// Passa os tamanhos do PHP para o JS de forma segura
const listaTamanhos = <?= json_encode($tamanhos) ?>;
const containerGrade = document.getElementById('containerGrade');
let contadorLinhas = 0;

function adicionarLinhaGrade() {
    const div = document.createElement('div');
    div.classList.add('linha-dinamica');
    div.id = `linha-${contadorLinhas}`;

    let optionsTamanhos = '<option value="">Tam...</option>';
    listaTamanhos.forEach(t => {
        optionsTamanhos += `<option value="${t.id}">${t.nome}</option>`;
    });

    div.innerHTML = `
        <div class="form-group">
            <select name="variantes[${contadorLinhas}][tamanho_id]" required>
                ${optionsTamanhos}
            </select>
        </div>
        <div class="form-group">
            <input type="number" name="variantes[${contadorLinhas}][quantidade]" placeholder="Qtd" min="1" required>
        </div>
        <button type="button" class="btn-remover-linha" onclick="removerLinhaGrade(${contadorLinhas})"></button>
    `;

    containerGrade.appendChild(div);
    contadorLinhas++;
}

function removerLinhaGrade(id) {
    const linha = document.getElementById(`linha-${id}`);
    if (linha) {
        linha.remove();
    }
}

// Gerenciamento dos Menus Customizados (Abre/Fecha)
document.getElementById('productTrigger').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('productOptions').classList.toggle('show');
    document.getElementById('colorOptions').classList.remove('show');
});

document.getElementById('colorTrigger').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('colorOptions').classList.toggle('show');
    document.getElementById('productOptions').classList.remove('show');
});

document.addEventListener('click', function() {
    document.getElementById('productOptions').classList.remove('show');
    document.getElementById('colorOptions').classList.remove('show');
});

// Funções de Seleção de Elementos
function selectProduct(element, name, imgPath) {
    document.getElementById('produto_id').value = element.getAttribute('data-value');
    document.getElementById('productTrigger').innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="${imgPath}" style="width: 32px; height: 32px; object-fit: cover; border-radius: 6px; border: 1px solid #333;">
            <span>${name}</span>
        </div>
    `;
    document.getElementById('productOptions').classList.remove('show');
}

function selectColor(element, name, hexColor) {
    document.getElementById('cor_id').value = element.getAttribute('data-value');
    // Adiciona borda sutil se a cor for branca para não sumir no fundo
    const borderStyle = hexColor.toUpperCase() === '#FFFFFF' ? '1px solid #888' : '1px solid #333';
    
    document.getElementById('colorTrigger').innerHTML = `
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="width: 16px; height: 16px; background-color: ${hexColor}; border-radius: 50%; display: inline-block; border: ${borderStyle};"></span>
            <span>${name}</span>
        </div>
    `;
    document.getElementById('colorOptions').classList.remove('show');
}

// Inicia com uma linha padrão de tamanho ao carregar
adicionarLinhaGrade();
</script>

</body>
</html>