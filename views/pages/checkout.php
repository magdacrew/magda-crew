<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../src/Config/Database.php';

$pdo = Database::getConnection();
$session_id = session_id();
$usuario_id = $_SESSION['usuario_id'] ?? null;
$emailUsuario = $_SESSION['email'] ?? $_SESSION['usuario_email'] ?? '';
$erroCheckout = '';
$pedidoCriadoId = null;

function dinheiro($valor) {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function caminhoImagemProduto($caminho) {
    if (empty($caminho)) {
        return '';
    }

    $caminho = trim($caminho);

    if (preg_match('/^https?:\/\//i', $caminho)) {
        return $caminho;
    }

    $caminho = ltrim($caminho, '/');

    if (stripos($caminho, 'MagdaCrew/') === 0) {
        return '/' . $caminho;
    }

    return '/MagdaCrew/' . $caminho;
}

function tabelaExiste(PDO $pdo, string $tabela): bool {
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$tabela]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

function colunasTabela(PDO $pdo, string $tabela): array {
    static $cache = [];

    if (isset($cache[$tabela])) {
        return $cache[$tabela];
    }

    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM `$tabela`");
        $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $cache[$tabela] = array_map('strtolower', $colunas ?: []);
        return $cache[$tabela];
    } catch (Exception $e) {
        $cache[$tabela] = [];
        return [];
    }
}

function salvarEnderecoPadraoUsuario(PDO $pdo, int $usuarioId, array $dados): bool {
    if ($usuarioId <= 0) {
        return false;
    }

    $cep = trim($dados['cep'] ?? '');
    $logradouro = trim($dados['endereco'] ?? '');
    $numero = trim($dados['numero'] ?? '');
    $bairro = trim($dados['bairro'] ?? '');
    $cidade = trim($dados['cidade'] ?? '');
    $estado = strtoupper(trim($dados['estado'] ?? ''));

    if ($cep === '' || $logradouro === '' || $numero === '' || $bairro === '' || $cidade === '' || $estado === '') {
        return false;
    }

    $nomeCompleto = trim($dados['nome_completo'] ?? '');
    $telefone = trim($dados['telefone'] ?? '');
    $complemento = trim($dados['complemento'] ?? '');

    foreach (['enderecos', 'enderecos_usuario'] as $tabela) {
        if (!tabelaExiste($pdo, $tabela)) {
            continue;
        }

        $colunas = colunasTabela($pdo, $tabela);
        if (empty($colunas) || !in_array('usuario_id', $colunas, true)) {
            continue;
        }

        $colunaLogradouro = in_array('logradouro', $colunas, true) ? 'logradouro' : (in_array('endereco', $colunas, true) ? 'endereco' : null);
        if (!$colunaLogradouro) {
            continue;
        }

        if (in_array('padrao', $colunas, true)) {
            try {
                $stmtPadrao = $pdo->prepare("UPDATE `$tabela` SET padrao = 0 WHERE usuario_id = ?");
                $stmtPadrao->execute([$usuarioId]);
            } catch (Exception $e) {
                // Se a coluna padrao existir mas der erro, continua salvando o endereço.
            }
        }

        $campos = [];
        $valores = [];

        $mapa = [
            'usuario_id' => $usuarioId,
            'identificacao' => 'Casa',
            'nome' => $nomeCompleto,
            'nome_completo' => $nomeCompleto,
            'destinatario' => $nomeCompleto,
            'telefone' => $telefone,
            'cep' => $cep,
            $colunaLogradouro => $logradouro,
            'numero' => $numero,
            'complemento' => $complemento,
            'bairro' => $bairro,
            'cidade' => $cidade,
            'estado' => $estado,
            'padrao' => 1,
        ];

        foreach ($mapa as $coluna => $valor) {
            if ($coluna && in_array(strtolower($coluna), $colunas, true)) {
                $campos[] = $coluna;
                $valores[] = $valor;
            }
        }

        if (empty($campos)) {
            continue;
        }

        $placeholders = implode(', ', array_fill(0, count($campos), '?'));
        $camposSql = implode(', ', array_map(fn($campo) => "`$campo`", $campos));

        $stmtInsert = $pdo->prepare("INSERT INTO `$tabela` ($camposSql) VALUES ($placeholders)");
        return $stmtInsert->execute($valores);
    }

    return false;
}

function buscarItensCarrinho(PDO $pdo, ?int $usuario_id, string $session_id): array {
    $sql = "
        SELECT
            MIN(c.id) AS carrinho_id,
            c.variante_id,
            SUM(c.quantidade) AS quantidade,
            p.id AS produto_id,
            p.nome,
            p.preco,
            t.nome AS tamanho_nome,
            COALESCE(co.nome, 'Única') AS cor_nome,
            COALESCE(v.quantidade_estoque, 0) AS quantidade_estoque,
            p_img.caminho_imagem
        FROM carrinho c
        JOIN produto_variantes v ON c.variante_id = v.id
        JOIN tamanhos t ON v.tamanho_id = t.id
        LEFT JOIN cores co ON v.cor_id = co.id
        JOIN produtos p ON v.produto_id = p.id
        LEFT JOIN produto_imagens p_img ON p.id = p_img.produto_id AND p_img.is_principal = 1
        WHERE c.usuario_id = :usuario_id OR c.session_id = :session_id
        GROUP BY c.variante_id, p.id, p.nome, p.preco, t.nome, co.nome, v.quantidade_estoque, p_img.caminho_imagem
        ORDER BY carrinho_id DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuario_id' => $usuario_id ?? 0,
        ':session_id' => $session_id
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$fretes = [
    'retirada' => ['nome' => 'Retirar na flagship', 'prazo' => 'Combinar retirada', 'valor' => 0.00],
    'pac' => ['nome' => 'Entrega padrão', 'prazo' => '5 a 9 dias úteis', 'valor' => 19.90],
    'sedex' => ['nome' => 'Entrega expressa', 'prazo' => '2 a 4 dias úteis', 'valor' => 34.90],
];

$formaPagamentos = [
    'pix' => 'Pix',
    'cartao' => 'Cartão de crédito',
    'boleto' => 'Boleto bancário',
];

$enderecosSalvos = [];
try {
    if ($usuario_id && tabelaExiste($pdo, 'enderecos')) {
        $stmtEnd = $pdo->prepare("SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY padrao DESC, id DESC");
        $stmtEnd->execute([$usuario_id]);
        $enderecosSalvos = $stmtEnd->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($usuario_id && tabelaExiste($pdo, 'enderecos_usuario')) {
        $stmtEnd = $pdo->prepare("SELECT * FROM enderecos_usuario WHERE usuario_id = ? ORDER BY id DESC");
        $stmtEnd->execute([$usuario_id]);
        $enderecosSalvos = $stmtEnd->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $enderecosSalvos = [];
}

$itensCarrinho = buscarItensCarrinho($pdo, $usuario_id ? (int)$usuario_id : null, $session_id);
$subtotal = 0;
$quantidadeTotal = 0;
foreach ($itensCarrinho as $item) {
    $subtotal += ((float)$item['preco'] * (int)$item['quantidade']);
    $quantidadeTotal += (int)$item['quantidade'];
}

$freteSelecionado = $_POST['frete_tipo'] ?? 'pac';
if (!isset($fretes[$freteSelecionado])) {
    $freteSelecionado = 'pac';
}
$valorFrete = $fretes[$freteSelecionado]['valor'];
$total = $subtotal + $valorFrete;
$pagamentoSelecionado = $_POST['forma_pagamento'] ?? 'pix';
if (!isset($formaPagamentos[$pagamentoSelecionado])) {
    $pagamentoSelecionado = 'pix';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'finalizar_compra') {
    if (!$usuario_id) {
        $erroCheckout = 'Faça login para finalizar sua compra. Seus produtos continuam guardados no carrinho.';
    } elseif (empty($itensCarrinho)) {
        $erroCheckout = 'Seu carrinho está vazio.';
    } else {
        $nomeCompleto = trim($_POST['nome_completo'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $cep = trim($_POST['cep'] ?? '');
        $endereco = trim($_POST['endereco'] ?? '');
        $numero = trim($_POST['numero'] ?? '');
        $bairro = trim($_POST['bairro'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $cidade = trim($_POST['cidade'] ?? '');
        $estado = strtoupper(trim($_POST['estado'] ?? ''));
        $cpfNota = trim($_POST['cpf_cnpj_nota'] ?? '');
        $formaPagamento = $_POST['forma_pagamento'] ?? 'pix';
        $cartaoNome = trim($_POST['cartao_nome'] ?? '');
        $cartaoNumero = preg_replace('/\D+/', '', $_POST['cartao_numero'] ?? '');
        $cartaoValidade = trim($_POST['cartao_validade'] ?? '');
        $cartaoCvv = preg_replace('/\D+/', '', $_POST['cartao_cvv'] ?? '');
        $cartaoCpf = preg_replace('/\D+/', '', $_POST['cartao_cpf'] ?? '');
        $cartaoParcelas = trim($_POST['cartao_parcelas'] ?? '1');

        if (!isset($formaPagamentos[$formaPagamento])) {
            $formaPagamento = 'pix';
        }
        $pagamentoSelecionado = $formaPagamento;

        if ($nomeCompleto === '' || $telefone === '') {
            $erroCheckout = 'Preencha seu nome completo e telefone para continuar.';
        } elseif ($freteSelecionado !== 'retirada' && ($cep === '' || $endereco === '' || $numero === '' || $bairro === '' || $cidade === '' || $estado === '')) {
            $erroCheckout = 'Preencha o endereço completo para entrega.';
        } elseif ($formaPagamento === 'cartao' && ($cartaoNome === '' || strlen($cartaoNumero) < 13 || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cartaoValidade) || strlen($cartaoCvv) < 3 || strlen($cartaoCpf ?? '') < 11)) {
            $erroCheckout = 'Preencha corretamente as informações do cartão para continuar.';
        } else {
            foreach ($itensCarrinho as $item) {
                if ((int)$item['quantidade_estoque'] < (int)$item['quantidade']) {
                    $erroCheckout = 'O produto "' . $item['nome'] . '" não tem estoque suficiente para essa quantidade.';
                    break;
                }
            }
        }

        if ($erroCheckout === '') {
            try {
                $pdo->beginTransaction();

                $stmtVenda = $pdo->prepare("
                    INSERT INTO vendas
                        (usuario_id, valor_total, subtotal, valor_frete, forma_pagamento, frete_tipo, cpf_cnpj_nota, status)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $statusInicial = in_array($formaPagamento, ['pix', 'boleto'], true) ? 'pendente' : 'processando';
                $stmtVenda->execute([
                    $usuario_id,
                    $total,
                    $subtotal,
                    $valorFrete,
                    $formaPagamento,
                    $freteSelecionado,
                    $cpfNota,
                    $statusInicial
                ]);

                $pedidoCriadoId = (int)$pdo->lastInsertId();

                $stmtItem = $pdo->prepare("
                    INSERT INTO itens_venda
                        (venda_id, variante_id, produto_nome, quantidade, preco_unitario, tamanho_nome, cor_nome)
                    VALUES
                        (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmtEstoque = $pdo->prepare("
                    UPDATE produto_variantes
                    SET quantidade_estoque = GREATEST(quantidade_estoque - ?, 0)
                    WHERE id = ?
                ");

                foreach ($itensCarrinho as $item) {
                    $stmtItem->execute([
                        $pedidoCriadoId,
                        $item['variante_id'],
                        $item['nome'],
                        $item['quantidade'],
                        $item['preco'],
                        $item['tamanho_nome'],
                        $item['cor_nome']
                    ]);

                    $stmtEstoque->execute([
                        $item['quantidade'],
                        $item['variante_id']
                    ]);
                }

                if (tabelaExiste($pdo, 'enderecos_venda')) {
                    $stmtEnderecoVenda = $pdo->prepare("
                        INSERT INTO enderecos_venda
                            (venda_id, cep, logradouro, numero, complemento, bairro, cidade, estado, destinatario)
                        VALUES
                            (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmtEnderecoVenda->execute([
                        $pedidoCriadoId,
                        $cep,
                        $endereco,
                        $numero,
                        $complemento,
                        $bairro,
                        $cidade,
                        $estado,
                        $nomeCompleto
                    ]);
                }

                if (
                    $freteSelecionado !== 'retirada'
                    && empty($enderecosSalvos)
                    && (($_POST['salvar_endereco_padrao'] ?? '1') === '1')
                ) {
                    salvarEnderecoPadraoUsuario($pdo, (int)$usuario_id, [
                        'nome_completo' => $nomeCompleto,
                        'telefone' => $telefone,
                        'cep' => $cep,
                        'endereco' => $endereco,
                        'numero' => $numero,
                        'bairro' => $bairro,
                        'complemento' => $complemento,
                        'cidade' => $cidade,
                        'estado' => $estado,
                    ]);
                }

                $stmtLimpar = $pdo->prepare("DELETE FROM carrinho WHERE usuario_id = ? OR session_id = ?");
                $stmtLimpar->execute([$usuario_id, $session_id]);

                $pdo->commit();

                $itensCarrinho = [];
                $subtotal = 0;
                $quantidadeTotal = 0;
                $valorFrete = 0;
                $total = 0;
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $erroCheckout = 'Erro ao finalizar compra: ' . $e->getMessage();
                $pedidoCriadoId = null;
            }
        }
    }
}

$tituloDaPagina = 'Finalizar Compra - Magda Crew';
include_once __DIR__ . '/../components/Header.php';
?>

<style>
.checkout-page,
.checkout-page * {
    box-sizing: border-box;
}

.checkout-page {
    min-height: calc(100vh - 95px);
    background:
        radial-gradient(circle at 12% 12%, rgba(255,255,255,.08), transparent 28%),
        radial-gradient(circle at 90% 0%, rgba(255,255,255,.05), transparent 26%),
        #1c1d21;
    color: #f4f4f4;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    padding: 42px 55px 70px;
}

.checkout-shell {
    max-width: 1240px;
    margin: 0 auto;
}

.checkout-hero {
    display: grid;
    grid-template-columns: 1.4fr .8fr;
    gap: 24px;
    margin-bottom: 28px;
}

.checkout-title-card,
.checkout-security-card,
.checkout-panel,
.checkout-summary {
    border: 1px solid rgba(255,255,255,.10);
    background: linear-gradient(145deg, rgba(255,255,255,.075), rgba(255,255,255,.025));
    box-shadow: 0 24px 70px rgba(0,0,0,.25);
    backdrop-filter: blur(16px);
    border-radius: 28px;
}

.checkout-title-card {
    padding: 34px;
    position: relative;
    overflow: hidden;
}

.checkout-title-card::after {
    content: "";
    position: absolute;
    width: 240px;
    height: 240px;
    border-radius: 999px;
    background: rgba(255,255,255,.07);
    right: -80px;
    top: -100px;
}

.checkout-eyebrow {
    color: #bdbdbd;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 2.8px;
    margin-bottom: 12px;
}

.checkout-title-card h1 {
    font-size: clamp(32px, 5vw, 64px);
    line-height: .95;
    letter-spacing: -3px;
    margin: 0 0 18px;
}

.checkout-title-card p {
    color: #c9c9c9;
    max-width: 640px;
    font-size: 16px;
    line-height: 1.7;
}

.checkout-steps {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 22px;
}

.checkout-step {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid rgba(255,255,255,.13);
    background: rgba(0,0,0,.18);
    padding: 10px 14px;
    border-radius: 999px;
    color: #f3f3f3;
    font-size: 13px;
}

.checkout-step span {
    display: inline-grid;
    place-items: center;
    width: 22px;
    height: 22px;
    border-radius: 999px;
    background: #fff;
    color: #111;
    font-weight: 800;
    font-size: 12px;
}

.checkout-security-card {
    padding: 26px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 20px;
}

.security-icon {
    width: 54px;
    height: 54px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: #fff;
    color: #111;
    font-size: 24px;
    font-weight: 900;
}

.checkout-security-card h3 {
    font-size: 22px;
    margin: 0 0 8px;
}

.checkout-security-card p {
    margin: 0;
    color: #c7c7c7;
    line-height: 1.6;
}

.checkout-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 410px;
    gap: 26px;
    align-items: start;
}

.checkout-panel,
.checkout-summary {
    padding: 26px;
}

.panel-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 22px;
}

.panel-title h2 {
    margin: 0;
    font-size: 24px;
    letter-spacing: -.8px;
}

.panel-badge {
    border: 1px solid rgba(255,255,255,.12);
    color: #cfcfcf;
    border-radius: 999px;
    padding: 7px 12px;
    font-size: 12px;
}

.alert-box {
    border-radius: 20px;
    padding: 18px 20px;
    margin-bottom: 22px;
    border: 1px solid rgba(255,255,255,.14);
    background: rgba(255,255,255,.06);
    color: #eaeaea;
}

.alert-box.erro {
    border-color: rgba(255,90,90,.38);
    background: rgba(255,70,70,.11);
}

.alert-box.sucesso {
    border-color: rgba(120,255,180,.28);
    background: rgba(90,255,160,.10);
}

.address-list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 22px;
}

.address-card {
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,.18);
    border-radius: 20px;
    padding: 15px;
    cursor: pointer;
    transition: .25s;
}

.address-card:hover {
    border-color: rgba(255,255,255,.32);
    transform: translateY(-2px);
}

.address-card strong {
    display: block;
    margin-bottom: 7px;
    font-size: 14px;
}

.address-card span {
    display: block;
    color: #bdbdbd;
    font-size: 12px;
    line-height: 1.5;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label,
.option-label {
    display: block;
    color: #bdbdbd;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    margin-bottom: 8px;
}

.form-group input,
.form-group select {
    width: 100%;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,.28);
    color: #fff;
    outline: none;
    border-radius: 16px;
    padding: 15px 15px;
    font-size: 14px;
    transition: .25s;
}

.form-group input:focus,
.form-group select:focus {
    border-color: rgba(255,255,255,.45);
    box-shadow: 0 0 0 4px rgba(255,255,255,.06);
}

.checkout-options {
    display: grid;
    gap: 12px;
    margin: 22px 0;
}

.option-card {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    align-items: center;
    gap: 16px;
    width: 100%;
    max-width: 100%;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,.18);
    border-radius: 20px;
    padding: 16px;
    cursor: pointer;
    transition: .25s;
    overflow: hidden;
}

.option-card:hover,
.option-card:has(input:checked) {
    border-color: rgba(255,255,255,.45);
    background: rgba(255,255,255,.075);
}

.option-card-main {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.option-card input {
    accent-color: #fff;
    flex: 0 0 auto;
}

.option-info {
    min-width: 0;
}

.option-info strong {
    display: block;
    margin-bottom: 4px;
    line-height: 1.25;
}

.option-info small {
    display: block;
    color: #b7b7b7;
    line-height: 1.45;
}

.option-price {
    white-space: nowrap;
    font-weight: 800;
}


.save-address-card {
    grid-column: 1 / -1;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    border: 1px solid rgba(120,255,180,.24);
    background: rgba(90,255,160,.08);
    color: #e9fff2;
    border-radius: 18px;
    padding: 15px 16px;
    margin-top: 4px;
}

.save-address-card input {
    margin-top: 3px;
    accent-color: #fff;
    flex: 0 0 auto;
}

.save-address-card strong {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
}

.save-address-card span {
    display: block;
    color: #c9d7cf;
    font-size: 12px;
    line-height: 1.45;
}

.save-address-card.is-hidden {
    display: none;
}


.payment-section {
    position: relative;
    margin-top: 30px;
    border: 1px solid rgba(255,255,255,.14);
    background:
        radial-gradient(circle at 20% 0%, rgba(255,255,255,.13), transparent 34%),
        radial-gradient(circle at 100% 100%, rgba(255,255,255,.08), transparent 28%),
        linear-gradient(145deg, rgba(255,255,255,.075), rgba(0,0,0,.26));
    border-radius: 32px;
    padding: 26px;
    overflow: hidden;
}

.payment-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.05), transparent);
    pointer-events: none;
}

.payment-head {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 20px;
}

.payment-title h3 {
    margin: 3px 0 7px;
    font-size: 24px;
    letter-spacing: -.8px;
}

.payment-title p {
    margin: 0;
    color: #c2c2c2;
    line-height: 1.55;
    font-size: 13px;
}

.payment-lock {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid rgba(255,255,255,.16);
    background: rgba(0,0,0,.26);
    color: #ededed;
    border-radius: 999px;
    padding: 10px 13px;
    font-size: 12px;
    font-weight: 900;
    white-space: nowrap;
}

.payment-methods {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 16px;
}

.pay-option {
    width: 100%;
    min-height: 172px;
    text-align: left;
    border: 1px solid rgba(255,255,255,.13);
    background: rgba(0,0,0,.28);
    color: #fff;
    border-radius: 26px;
    padding: 18px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: transform .25s ease, border-color .25s ease, background .25s ease, box-shadow .25s ease;
}

.pay-option::before {
    content: "";
    position: absolute;
    width: 160px;
    height: 160px;
    right: -95px;
    bottom: -92px;
    border-radius: 999px;
    background: rgba(255,255,255,.075);
    transition: .25s ease;
}

.pay-option:hover,
.pay-option.is-active {
    border-color: rgba(255,255,255,.62);
    background: rgba(255,255,255,.085);
    transform: translateY(-3px);
}

.pay-option.is-active {
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.28), 0 18px 40px rgba(0,0,0,.28);
}

.pay-option.is-active::before {
    background: rgba(255,255,255,.18);
}

.pay-option-top {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.pay-icon {
    display: grid;
    place-items: center;
    width: 52px;
    height: 52px;
    border-radius: 18px;
    background: #fff;
    color: #111;
    font-weight: 950;
    font-size: 15px;
    box-shadow: 0 12px 26px rgba(0,0,0,.25);
}

.pay-badge {
    border: 1px solid rgba(255,255,255,.14);
    background: rgba(255,255,255,.08);
    color: #ececec;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 900;
    white-space: nowrap;
}

.pay-option strong {
    position: relative;
    z-index: 1;
    display: block;
    font-size: 17px;
    margin-bottom: 8px;
}

.pay-option small {
    position: relative;
    z-index: 1;
    display: block;
    color: #c8c8c8;
    font-size: 12px;
    line-height: 1.55;
    padding-right: 16px;
}

.pay-check {
    position: absolute;
    z-index: 2;
    right: 16px;
    bottom: 16px;
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.18);
    background: rgba(0,0,0,.25);
    color: transparent;
    font-weight: 950;
    transition: .2s ease;
}

.pay-option.is-active .pay-check {
    background: #fff;
    color: #111;
    border-color: #fff;
}

.payment-info-box {
    position: relative;
    z-index: 1;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 13px;
    align-items: start;
    border: 1px dashed rgba(255,255,255,.18);
    background: rgba(0,0,0,.23);
    border-radius: 20px;
    padding: 15px;
    color: #d9d9d9;
    margin-bottom: 16px;
}

.payment-info-box .info-icon {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 12px;
    background: rgba(255,255,255,.12);
    color: #fff;
    font-weight: 900;
}

.payment-info-box strong {
    display: block;
    color: #fff;
    margin-bottom: 4px;
    font-size: 13px;
}

.payment-info-box span {
    display: block;
    font-size: 13px;
    line-height: 1.5;
}

.card-details-panel,
.boleto-details-panel,
.pix-details-panel {
    position: relative;
    z-index: 1;
    display: none;
    border: 1px solid rgba(255,255,255,.13);
    background: linear-gradient(145deg, rgba(255,255,255,.07), rgba(0,0,0,.25));
    border-radius: 26px;
    padding: 20px;
    margin-top: 16px;
}

.card-details-panel.is-open,
.boleto-details-panel.is-open,
.pix-details-panel.is-open {
    display: block;
    animation: payOpen .22s ease both;
}

@keyframes payOpen {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

.virtual-card-wrap {
    display: grid;
    grid-template-columns: 285px minmax(0, 1fr);
    gap: 18px;
    align-items: start;
}

.virtual-card-preview {
    min-height: 178px;
    border-radius: 24px;
    padding: 20px;
    background:
        radial-gradient(circle at 15% 15%, rgba(255,255,255,.22), transparent 24%),
        linear-gradient(135deg, #292b31, #090a0d 58%, #34363d);
    border: 1px solid rgba(255,255,255,.16);
    box-shadow: 0 20px 44px rgba(0,0,0,.32);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
}

.card-preview-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #fff;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.8px;
    font-weight: 900;
}

.card-chip {
    width: 42px;
    height: 30px;
    border-radius: 9px;
    background: linear-gradient(135deg, #f0f0f0, #8d8d8d);
    opacity: .9;
}

.card-preview-number {
    color: #fff;
    font-size: 19px;
    letter-spacing: 2.5px;
    font-weight: 850;
    margin: 24px 0 18px;
}

.card-preview-bottom {
    display: flex;
    justify-content: space-between;
    gap: 14px;
    color: #d6d6d6;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.card-preview-bottom strong {
    display: block;
    color: #fff;
    font-size: 12px;
    margin-top: 5px;
    letter-spacing: .5px;
    max-width: 170px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.card-form-content h4,
.boleto-details-panel h4,
.pix-details-panel h4 {
    margin: 0 0 6px;
    font-size: 19px;
    letter-spacing: -.5px;
}

.card-form-content p,
.boleto-details-panel p,
.pix-details-panel p {
    margin: 0 0 16px;
    color: #c6c6c6;
    font-size: 13px;
    line-height: 1.55;
}

.card-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 13px;
}

.card-form-grid .form-group.full {
    grid-column: 1 / -1;
}

.card-security-note {
    margin-top: 14px;
    border-radius: 16px;
    padding: 12px 14px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.12);
    color: #cfcfcf;
    font-size: 12px;
    line-height: 1.5;
}

.ticket-lines {
    display: grid;
    gap: 10px;
    margin-top: 12px;
}

.ticket-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    border: 1px solid rgba(255,255,255,.12);
    background: rgba(0,0,0,.20);
    border-radius: 16px;
    padding: 13px 14px;
    color: #dedede;
    font-size: 13px;
}

.ticket-line strong {
    color: #fff;
}

.payment-mini-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.payment-mini-actions span {
    display: inline-flex;
    align-items: center;
    min-height: 34px;
    padding: 0 12px;
    border-radius: 999px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.12);
    color: #e5e5e5;
    font-size: 12px;
    font-weight: 800;
}

.checkout-submit {
    width: 100%;
    border: none;
    background: #fff;
    color: #111;
    border-radius: 18px;
    padding: 18px;
    font-size: 15px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: .8px;
    cursor: pointer;
    transition: .25s;
}

.checkout-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 35px rgba(255,255,255,.10);
}

.checkout-submit:disabled {
    opacity: .45;
    cursor: not-allowed;
    transform: none;
}

.checkout-summary {
    position: sticky;
    top: 120px;
}

.cart-item {
    display: grid;
    grid-template-columns: 82px 1fr;
    gap: 14px;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255,255,255,.09);
}

.cart-item:first-of-type {
    padding-top: 0;
}

.cart-thumb {
    width: 82px;
    height: 98px;
    border-radius: 16px;
    overflow: hidden;
    background: rgba(255,255,255,.08);
    display: grid;
    place-items: center;
    color: #777;
    font-size: 11px;
}

.cart-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cart-item h3 {
    margin: 0 0 7px;
    font-size: 14px;
    text-transform: uppercase;
    line-height: 1.35;
}

.cart-meta {
    color: #b8b8b8;
    font-size: 12px;
    line-height: 1.5;
}

.cart-price {
    margin-top: 10px;
    font-weight: 800;
}

.summary-lines {
    margin-top: 20px;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    color: #c9c9c9;
    padding: 9px 0;
    font-size: 14px;
}

.summary-line.total {
    margin-top: 10px;
    padding-top: 18px;
    border-top: 1px solid rgba(255,255,255,.13);
    color: #fff;
    font-size: 21px;
    font-weight: 900;
}

.empty-cart-state {
    text-align: center;
    padding: 54px 18px;
}

.empty-cart-state h2 {
    margin: 0 0 10px;
    font-size: 30px;
}

.empty-cart-state p {
    color: #bdbdbd;
    margin-bottom: 24px;
}

.checkout-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    padding: 0 22px;
    border-radius: 999px;
    color: #111;
    background: #fff;
    text-decoration: none;
    font-weight: 900;
}

.checkout-link.secondary {
    background: transparent;
    border: 1px solid rgba(255,255,255,.16);
    color: #fff;
}

.success-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 18px;
}

@media (max-width: 980px) {
    .checkout-page { padding: 26px 18px 50px; }
    .checkout-hero,
    .checkout-grid { grid-template-columns: 1fr; }
    .checkout-summary { position: static; }
    .payment-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .card-details-panel { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
    .checkout-title-card,
    .checkout-security-card,
    .checkout-panel,
    .checkout-summary { border-radius: 22px; padding: 20px; }
    .form-grid,
    .address-list,
    .payment-grid,
    .card-form-grid { grid-template-columns: 1fr; }
    .option-card { grid-template-columns: 1fr; }
    .option-price { justify-self: start; }
    .payment-section { padding: 16px; border-radius: 22px; }
    .payment-card { min-height: 130px; }
    .card-details-panel { grid-template-columns: 1fr; }
    .checkout-title-card h1 { letter-spacing: -1.5px; }
}

@media (max-width: 980px) {
    .payment-methods { grid-template-columns: 1fr; }
    .virtual-card-wrap { grid-template-columns: 1fr; }
    .virtual-card-preview { max-width: 360px; }
}

@media (max-width: 640px) {
    .payment-head { flex-direction: column; }
    .payment-lock { width: 100%; justify-content: center; }
    .card-form-grid { grid-template-columns: 1fr; }
    .card-preview-number { font-size: 16px; letter-spacing: 1.8px; }
}

</style>

<main class="checkout-page">
    <div class="checkout-shell">
        <section class="checkout-hero">
            <div class="checkout-title-card">
                <div class="checkout-eyebrow">Magda Crew checkout</div>
                <h1>Finalização simples, bonita e confiável.</h1>
                <p>Uma tela de compra com aparência de loja virtual profissional, feita para o cliente entender entrega, pagamento e resumo do pedido sem confusão.</p>
                <div class="checkout-steps">
                    <div class="checkout-step"><span>1</span> Sacola</div>
                    <div class="checkout-step"><span>2</span> Endereço</div>
                    <div class="checkout-step"><span>3</span> Pagamento</div>
                </div>
            </div>

            <div class="checkout-security-card">
                <div class="security-icon">✓</div>
                <div>
                    <h3>Compra organizada</h3>
                    <p>O pedido entra no painel de vendas, atualiza o estoque e mantém o cliente com uma experiência mais profissional.</p>
                </div>
            </div>
        </section>

        <?php if ($pedidoCriadoId): ?>
            <section class="checkout-panel">
                <div class="alert-box sucesso">
                    <strong>Compra criada com sucesso!</strong><br>
                    Pedido #<?= (int)$pedidoCriadoId ?> registrado. Agora o cliente pode acompanhar em Meus Pedidos.
                </div>
                <div class="success-actions">
                    <a class="checkout-link" href="/MagdaCrew/views/pages/orders.php">Ver meus pedidos</a>
                    <a class="checkout-link secondary" href="/MagdaCrew/views/pages/shop.php">Continuar comprando</a>
                </div>
            </section>
        <?php elseif (empty($itensCarrinho)): ?>
            <section class="checkout-panel empty-cart-state">
                <h2>Sua sacola está vazia</h2>
                <p>Adicione um produto na loja para aparecer aqui e finalizar a compra.</p>
                <a class="checkout-link" href="/MagdaCrew/views/pages/shop.php">Ir para a loja</a>
            </section>
        <?php else: ?>
            <section class="checkout-grid">
                <form class="checkout-panel" method="POST" id="checkoutForm">
                    <input type="hidden" name="acao" value="finalizar_compra">

                    <div class="panel-title">
                        <h2>Dados da compra</h2>
                        <span class="panel-badge"><?= (int)$quantidadeTotal ?> item(ns)</span>
                    </div>

                    <?php if (!empty($erroCheckout)): ?>
                        <div class="alert-box erro"><?= htmlspecialchars($erroCheckout) ?></div>
                    <?php endif; ?>

                    <?php if (!$usuario_id): ?>
                        <div class="alert-box">
                            Para finalizar a compra, faça login primeiro. O carrinho continua guardado para sua conta.
                            <div style="margin-top:14px;">
                                <a class="checkout-link" href="/MagdaCrew/views/pages/login.php">Entrar na conta</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($enderecosSalvos)): ?>
                        <label class="option-label">Usar endereço salvo</label>
                        <div class="address-list">
                            <?php foreach ($enderecosSalvos as $end):
                                $nomeEnd = trim(($end['nome'] ?? '') . ' ' . ($end['sobrenome'] ?? ''));
                                if ($nomeEnd === '') {
                                    $nomeEnd = $end['identificacao'] ?? 'Endereço salvo';
                                }
                                $enderecoLinha = $end['endereco'] ?? $end['logradouro'] ?? '';
                                $cidadeEnd = $end['cidade'] ?? '';
                                $estadoEnd = $end['estado'] ?? '';
                                $cepEnd = $end['cep'] ?? '';
                            ?>
                                <div class="address-card"
                                    data-nome="<?= htmlspecialchars($nomeEnd) ?>"
                                    data-telefone="<?= htmlspecialchars($end['telefone'] ?? '') ?>"
                                    data-cep="<?= htmlspecialchars($cepEnd) ?>"
                                    data-endereco="<?= htmlspecialchars($enderecoLinha) ?>"
                                    data-numero="<?= htmlspecialchars($end['numero'] ?? '') ?>"
                                    data-bairro="<?= htmlspecialchars($end['bairro'] ?? '') ?>"
                                    data-complemento="<?= htmlspecialchars($end['complemento'] ?? '') ?>"
                                    data-cidade="<?= htmlspecialchars($cidadeEnd) ?>"
                                    data-estado="<?= htmlspecialchars($estadoEnd) ?>">
                                    <strong><?= htmlspecialchars($nomeEnd) ?></strong>
                                    <span><?= htmlspecialchars($enderecoLinha) ?></span>
                                    <span><?= htmlspecialchars($cidadeEnd . ($estadoEnd ? ' - ' . $estadoEnd : '')) ?></span>
                                    <span><?= htmlspecialchars($cepEnd) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Nome completo</label>
                            <input type="text" name="nome_completo" id="nome_completo" value="<?= htmlspecialchars($_POST['nome_completo'] ?? '') ?>" placeholder="Nome de quem vai receber" required>
                        </div>

                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" value="<?= htmlspecialchars($emailUsuario) ?>" placeholder="cliente@email.com" readonly>
                        </div>

                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="telefone" id="telefone" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="(00) 00000-0000" required>
                        </div>

                        <div class="form-group">
                            <label>CEP</label>
                            <input type="text" name="cep" id="cep" value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>" placeholder="00000-000" data-entrega>
                        </div>

                        <div class="form-group">
                            <label>Estado</label>
                            <input type="text" name="estado" id="estado" maxlength="2" value="<?= htmlspecialchars($_POST['estado'] ?? '') ?>" placeholder="SC" data-entrega>
                        </div>

                        <div class="form-group full">
                            <label>Endereço</label>
                            <input type="text" name="endereco" id="endereco" value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>" placeholder="Rua, avenida, travessa..." data-entrega>
                        </div>

                        <div class="form-group">
                            <label>Número</label>
                            <input type="text" name="numero" id="numero" value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>" placeholder="123" data-entrega>
                        </div>

                        <div class="form-group">
                            <label>Bairro</label>
                            <input type="text" name="bairro" id="bairro" value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>" placeholder="Centro" data-entrega>
                        </div>

                        <div class="form-group">
                            <label>Cidade</label>
                            <input type="text" name="cidade" id="cidade" value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>" placeholder="Joinville" data-entrega>
                        </div>

                        <div class="form-group">
                            <label>Complemento</label>
                            <input type="text" name="complemento" id="complemento" value="<?= htmlspecialchars($_POST['complemento'] ?? '') ?>" placeholder="Apto, casa, bloco...">
                        </div>

                        <div class="form-group full">
                            <label>CPF/CNPJ para nota fiscal (opcional)</label>
                            <input type="text" name="cpf_cnpj_nota" value="<?= htmlspecialchars($_POST['cpf_cnpj_nota'] ?? '') ?>" placeholder="Digite se o cliente quiser colocar na nota">
                        </div>

                        <?php if ($usuario_id && empty($enderecosSalvos)): ?>
                            <label class="save-address-card" id="saveAddressBox">
                                <input type="checkbox" name="salvar_endereco_padrao" value="1" checked>
                                <span>
                                    <strong>Salvar este endereço como padrão</strong>
                                    <span>Como essa conta ainda não tem endereço salvo, este endereço será usado automaticamente nas próximas compras.</span>
                                </span>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div class="checkout-options">
                        <span class="option-label">Entrega</span>
                        <?php foreach ($fretes as $codigo => $frete): ?>
                            <label class="option-card">
                                <span class="option-card-main">
                                    <input type="radio" name="frete_tipo" value="<?= htmlspecialchars($codigo) ?>" data-frete="<?= number_format($frete['valor'], 2, '.', '') ?>" <?= $freteSelecionado === $codigo ? 'checked' : '' ?>>
                                    <span class="option-info">
                                        <strong><?= htmlspecialchars($frete['nome']) ?></strong>
                                        <small><?= htmlspecialchars($frete['prazo']) ?></small>
                                    </span>
                                </span>
                                <span class="option-price"><?= $frete['valor'] > 0 ? dinheiro($frete['valor']) : 'Grátis' ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="payment-section">
                        <input type="hidden" name="forma_pagamento" id="forma_pagamento_hidden" value="<?= htmlspecialchars($pagamentoSelecionado) ?>">

                        <div class="payment-head">
                            <div class="payment-title">
                                <span class="option-label">Forma de pagamento</span>
                                <h3>Escolha como deseja pagar</h3>
                                <p>Visual mais claro para o cliente: Pix, cartão de crédito ou boleto. Ao selecionar cartão, os dados aparecem logo abaixo como em loja virtual.</p>
                            </div>
                            <span class="payment-lock">✓ Ambiente seguro</span>
                        </div>

                        <div class="payment-methods" aria-label="Formas de pagamento">
                            <button type="button" class="pay-option <?= $pagamentoSelecionado === 'pix' ? 'is-active' : '' ?>" data-pay-option="pix">
                                <span class="pay-option-top">
                                    <span class="pay-icon">Pix</span>
                                    <span class="pay-badge">Mais rápido</span>
                                </span>
                                <strong>Pix</strong>
                                <small>Pedido fica pendente até você confirmar o pagamento no painel de vendas.</small>
                                <span class="pay-check">✓</span>
                            </button>

                            <button type="button" class="pay-option <?= $pagamentoSelecionado === 'cartao' ? 'is-active' : '' ?>" data-pay-option="cartao">
                                <span class="pay-option-top">
                                    <span class="pay-icon">CARD</span>
                                    <span class="pay-badge">Loja virtual</span>
                                </span>
                                <strong>Cartão de crédito</strong>
                                <small>Abre os campos do cartão para o cliente preencher os dados de pagamento.</small>
                                <span class="pay-check">✓</span>
                            </button>

                            <button type="button" class="pay-option <?= $pagamentoSelecionado === 'boleto' ? 'is-active' : '' ?>" data-pay-option="boleto">
                                <span class="pay-option-top">
                                    <span class="pay-icon">BL</span>
                                    <span class="pay-badge">Bancário</span>
                                </span>
                                <strong>Boleto bancário</strong>
                                <small>Gera o pedido como pendente para pagamento via boleto.</small>
                                <span class="pay-check">✓</span>
                            </button>
                        </div>

                        <div class="payment-info-box">
                            <span class="info-icon">i</span>
                            <div>
                                <strong id="paymentDetailTitle">Pix</strong>
                                <span id="paymentDetailText">Finalize o pedido e confirme o pagamento depois pelo painel de vendas.</span>
                            </div>
                        </div>

                        <div class="pix-details-panel <?= $pagamentoSelecionado === 'pix' ? 'is-open' : '' ?>" id="pixDetailsBox">
                            <h4>Pagamento por Pix</h4>
                            <p>Depois de finalizar, o pedido fica salvo como pendente. Você pode confirmar o Pix no painel e atualizar o status da venda.</p>
                            <div class="payment-mini-actions">
                                <span>Confirmação rápida</span>
                                <span>Pedido no painel</span>
                                <span>Estoque atualizado</span>
                            </div>
                        </div>

                        <div class="card-details-panel <?= $pagamentoSelecionado === 'cartao' ? 'is-open' : '' ?>" id="cardDetailsBox">
                            <div class="virtual-card-wrap">
                                <div class="virtual-card-preview" aria-hidden="true">
                                    <div class="card-preview-brand">
                                        <span>MAGDA CREW</span>
                                        <span class="card-chip"></span>
                                    </div>
                                    <div class="card-preview-number" id="cardPreviewNumber">•••• •••• •••• ••••</div>
                                    <div class="card-preview-bottom">
                                        <span>Titular <strong id="cardPreviewName">Nome do cartão</strong></span>
                                        <span>Validade <strong id="cardPreviewDate">MM/AA</strong></span>
                                    </div>
                                </div>

                                <div class="card-form-content">
                                    <h4>Dados do cartão</h4>
                                    <p>Preencha os dados para finalizar com cartão. Os campos só são obrigatórios quando essa opção está selecionada.</p>

                                    <div class="card-form-grid">
                                        <div class="form-group full">
                                            <label>Nome impresso no cartão</label>
                                            <input type="text" name="cartao_nome" id="card_nome" data-card-field autocomplete="cc-name" value="<?= htmlspecialchars($_POST['cartao_nome'] ?? '') ?>" placeholder="Nome igual ao cartão" <?= $pagamentoSelecionado === 'cartao' ? '' : 'disabled' ?>>
                                        </div>

                                        <div class="form-group full">
                                            <label>Número do cartão</label>
                                            <input type="text" name="cartao_numero" id="card_numero" data-card-field inputmode="numeric" autocomplete="cc-number" maxlength="19" value="<?= htmlspecialchars($_POST['cartao_numero'] ?? '') ?>" placeholder="0000 0000 0000 0000" <?= $pagamentoSelecionado === 'cartao' ? '' : 'disabled' ?>>
                                        </div>

                                        <div class="form-group">
                                            <label>Validade</label>
                                            <input type="text" name="cartao_validade" id="card_validade" data-card-field inputmode="numeric" autocomplete="cc-exp" maxlength="5" value="<?= htmlspecialchars($_POST['cartao_validade'] ?? '') ?>" placeholder="MM/AA" <?= $pagamentoSelecionado === 'cartao' ? '' : 'disabled' ?>>
                                        </div>

                                        <div class="form-group">
                                            <label>CVV</label>
                                            <input type="password" name="cartao_cvv" id="card_cvv" data-card-field inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="000" <?= $pagamentoSelecionado === 'cartao' ? '' : 'disabled' ?>>
                                        </div>

                                        <div class="form-group">
                                            <label>CPF do titular</label>
                                            <input type="text" name="cartao_cpf" id="card_cpf" data-card-field inputmode="numeric" maxlength="14" value="<?= htmlspecialchars($_POST['cartao_cpf'] ?? '') ?>" placeholder="000.000.000-00" <?= $pagamentoSelecionado === 'cartao' ? '' : 'disabled' ?>>
                                        </div>

                                        <div class="form-group">
                                            <label>Parcelamento</label>
                                            <select name="cartao_parcelas" id="card_parcelas" data-card-field <?= $pagamentoSelecionado === 'cartao' ? '' : 'disabled' ?>>
                                                <?php for ($parcela = 1; $parcela <= 6; $parcela++): ?>
                                                    <option value="<?= $parcela ?>" <?= (($_POST['cartao_parcelas'] ?? '1') == $parcela) ? 'selected' : '' ?>><?= $parcela ?>x <?= $parcela === 1 ? 'sem juros' : 'no cartão' ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="card-security-note">
                                        Observação: esses dados não devem ser guardados no banco. Para cobrar cartão de verdade, integre depois com Mercado Pago, AbacatePay ou outro gateway.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="boleto-details-panel <?= $pagamentoSelecionado === 'boleto' ? 'is-open' : '' ?>" id="boletoDetailsBox">
                            <h4>Boleto bancário</h4>
                            <p>O pedido será criado como pendente. Depois você pode gerar/enviar o boleto pelo sistema de pagamento e atualizar a venda no painel.</p>
                            <div class="ticket-lines">
                                <div class="ticket-line"><span>Status inicial</span><strong>Pendente</strong></div>
                                <div class="ticket-line"><span>Compensação comum</span><strong>1 a 3 dias úteis</strong></div>
                            </div>
                        </div>
                    </div>

                    <button class="checkout-submit" type="submit" <?= !$usuario_id ? 'disabled' : '' ?>>Finalizar pedido</button>
                </form>

                <aside class="checkout-summary">
                    <div class="panel-title">
                        <h2>Resumo</h2>
                        <span class="panel-badge">Sacola</span>
                    </div>

                    <?php foreach ($itensCarrinho as $item):
                        $img = caminhoImagemProduto($item['caminho_imagem'] ?? '');
                        $totalItem = (float)$item['preco'] * (int)$item['quantidade'];
                    ?>
                        <div class="cart-item">
                            <div class="cart-thumb">
                                <?php if ($img): ?>
                                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                                <?php else: ?>
                                    Sem imagem
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3><?= htmlspecialchars($item['nome']) ?></h3>
                                <div class="cart-meta">
                                    Tamanho: <?= htmlspecialchars($item['tamanho_nome']) ?><br>
                                    Quantidade: <?= (int)$item['quantidade'] ?>
                                </div>
                                <div class="cart-price"><?= dinheiro($totalItem) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="summary-lines">
                        <div class="summary-line">
                            <span>Subtotal</span>
                            <strong id="subtotalResumo" data-subtotal="<?= number_format($subtotal, 2, '.', '') ?>"><?= dinheiro($subtotal) ?></strong>
                        </div>
                        <div class="summary-line">
                            <span>Frete</span>
                            <strong id="freteResumo"><?= dinheiro($valorFrete) ?></strong>
                        </div>
                        <div class="summary-line total">
                            <span>Total</span>
                            <strong id="totalResumo"><?= dinheiro($total) ?></strong>
                        </div>
                    </div>
                </aside>
            </section>
        <?php endif; ?>
    </div>
</main>

<script>
function formatBRL(valor) {
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

const checkoutForm = document.getElementById('checkoutForm');
const subtotalEl = document.getElementById('subtotalResumo');
const freteEl = document.getElementById('freteResumo');
const totalEl = document.getElementById('totalResumo');
const entregaInputs = document.querySelectorAll('[data-entrega]');
const saveAddressBox = document.getElementById('saveAddressBox');

function atualizarResumoCheckout() {
    if (!subtotalEl || !freteEl || !totalEl) return;

    const subtotal = parseFloat(subtotalEl.dataset.subtotal || '0');
    const freteMarcado = document.querySelector('input[name="frete_tipo"]:checked');
    const frete = freteMarcado ? parseFloat(freteMarcado.dataset.frete || '0') : 0;
    const isRetirada = freteMarcado && freteMarcado.value === 'retirada';

    freteEl.textContent = formatBRL(frete);
    totalEl.textContent = formatBRL(subtotal + frete);

    entregaInputs.forEach(input => {
        input.required = !isRetirada;
        const group = input.closest('.form-group');
        if (group) group.style.opacity = isRetirada ? '.55' : '1';
    });

    if (saveAddressBox) {
        saveAddressBox.classList.toggle('is-hidden', isRetirada);
    }
}

document.querySelectorAll('input[name="frete_tipo"]').forEach(input => {
    input.addEventListener('change', atualizarResumoCheckout);
});
atualizarResumoCheckout();

const paymentTitleEl = document.getElementById('paymentDetailTitle');
const paymentTextEl = document.getElementById('paymentDetailText');
const paymentHidden = document.getElementById('forma_pagamento_hidden');
const payOptions = document.querySelectorAll('[data-pay-option]');
const pixDetailsBox = document.getElementById('pixDetailsBox');
const cardDetailsBox = document.getElementById('cardDetailsBox');
const boletoDetailsBox = document.getElementById('boletoDetailsBox');
const cardFields = document.querySelectorAll('[data-card-field]');

const paymentTexts = {
    pix: {
        title: 'Pix',
        text: 'Finalize o pedido e confirme o pagamento depois pelo painel de vendas.'
    },
    cartao: {
        title: 'Cartão de crédito',
        text: 'Preencha os dados do cartão logo abaixo. Essa área aparece somente quando cartão está selecionado.'
    },
    boleto: {
        title: 'Boleto bancário',
        text: 'O pedido fica pendente para pagamento via boleto e pode ser atualizado no painel.'
    }
};

function setBoxOpen(box, shouldOpen) {
    if (!box) return;
    box.classList.toggle('is-open', shouldOpen);
}

function selecionarPagamento(valor) {
    if (!['pix', 'cartao', 'boleto'].includes(valor)) {
        valor = 'pix';
    }

    if (paymentHidden) {
        paymentHidden.value = valor;
    }

    payOptions.forEach(option => {
        option.classList.toggle('is-active', option.dataset.payOption === valor);
    });

    setBoxOpen(pixDetailsBox, valor === 'pix');
    setBoxOpen(cardDetailsBox, valor === 'cartao');
    setBoxOpen(boletoDetailsBox, valor === 'boleto');

    cardFields.forEach(field => {
        const isCard = valor === 'cartao';
        field.disabled = !isCard;
        field.required = isCard;
        if (!isCard) {
            field.setCustomValidity('');
        }
    });

    const copy = paymentTexts[valor] || paymentTexts.pix;
    if (paymentTitleEl) paymentTitleEl.textContent = copy.title;
    if (paymentTextEl) paymentTextEl.textContent = copy.text;
}

payOptions.forEach(option => {
    option.addEventListener('click', () => selecionarPagamento(option.dataset.payOption));
});

const cardNumero = document.getElementById('card_numero');
const cardPreviewNumber = document.getElementById('cardPreviewNumber');
function atualizarNumeroCartao() {
    if (!cardNumero) return;
    cardNumero.value = cardNumero.value
        .replace(/\D/g, '')
        .slice(0, 16)
        .replace(/(\d{4})(?=\d)/g, '$1 ');
    if (cardPreviewNumber) {
        cardPreviewNumber.textContent = cardNumero.value || '•••• •••• •••• ••••';
    }
}
if (cardNumero) cardNumero.addEventListener('input', atualizarNumeroCartao);

const cardNome = document.getElementById('card_nome');
const cardPreviewName = document.getElementById('cardPreviewName');
function atualizarNomeCartao() {
    if (cardPreviewName && cardNome) {
        cardPreviewName.textContent = cardNome.value.trim() || 'Nome do cartão';
    }
}
if (cardNome) cardNome.addEventListener('input', atualizarNomeCartao);

const cardValidade = document.getElementById('card_validade');
const cardPreviewDate = document.getElementById('cardPreviewDate');
function atualizarValidadeCartao() {
    if (!cardValidade) return;
    let valor = cardValidade.value.replace(/\D/g, '').slice(0, 4);
    if (valor.length >= 3) valor = valor.slice(0, 2) + '/' + valor.slice(2);
    cardValidade.value = valor;
    if (cardPreviewDate) cardPreviewDate.textContent = valor || 'MM/AA';
}
if (cardValidade) cardValidade.addEventListener('input', atualizarValidadeCartao);

const cardCvv = document.getElementById('card_cvv');
if (cardCvv) {
    cardCvv.addEventListener('input', () => {
        cardCvv.value = cardCvv.value.replace(/\D/g, '').slice(0, 4);
    });
}

const cardCpf = document.getElementById('card_cpf');
if (cardCpf) {
    cardCpf.addEventListener('input', () => {
        let value = cardCpf.value.replace(/\D/g, '').slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        cardCpf.value = value;
    });
}

if (checkoutForm) {
    checkoutForm.addEventListener('submit', (event) => {
        const forma = paymentHidden ? paymentHidden.value : 'pix';
        if (forma !== 'cartao') return;

        selecionarPagamento('cartao');

        for (const field of cardFields) {
            if (!field.value.trim()) {
                event.preventDefault();
                field.disabled = false;
                field.required = true;
                field.focus();
                field.reportValidity();
                return;
            }
        }
    });
}

selecionarPagamento(paymentHidden ? paymentHidden.value : 'pix');
atualizarNumeroCartao();
atualizarNomeCartao();
atualizarValidadeCartao();

document.querySelectorAll('.address-card').forEach(card => {
    card.addEventListener('click', () => {
        const campos = ['nome', 'telefone', 'cep', 'endereco', 'numero', 'bairro', 'complemento', 'cidade', 'estado'];
        campos.forEach(campo => {
            const input = document.getElementById(campo === 'nome' ? 'nome_completo' : campo);
            if (input) input.value = card.dataset[campo] || '';
        });
        document.querySelectorAll('.address-card').forEach(item => item.style.borderColor = 'rgba(255,255,255,.12)');
        card.style.borderColor = 'rgba(255,255,255,.75)';
    });
});
</script>

<div style="padding: 15px 55px; background:#1c1d21;">
    <?php include __DIR__ . '/../components/footer.php'; ?>
</div>
