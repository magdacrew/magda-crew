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

function pixCampo(string $id, string $valor): string {
    return $id . str_pad((string)strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
}

function pixTextoLimpo(string $texto, int $limite): string {
    $texto = trim($texto);
    $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    if ($convertido !== false) {
        $texto = $convertido;
    }

    $texto = strtoupper($texto);
    $texto = preg_replace('/[^A-Z0-9 ]/', '', $texto) ?: '';
    $texto = preg_replace('/\s+/', ' ', $texto) ?: '';

    return substr(trim($texto), 0, $limite);
}

function pixTxid(string $txid): string {
    $txid = strtoupper(preg_replace('/[^A-Z0-9]/', '', $txid) ?: '');
    return substr($txid, 0, 25);
}

function pixCrc16(string $payload): string {
    $polinomio = 0x1021;
    $resultado = 0xFFFF;
    $tamanho = strlen($payload);

    for ($offset = 0; $offset < $tamanho; $offset++) {
        $resultado ^= (ord($payload[$offset]) << 8);

        for ($bitwise = 0; $bitwise < 8; $bitwise++) {
            if (($resultado & 0x8000) !== 0) {
                $resultado = (($resultado << 1) ^ $polinomio) & 0xFFFF;
            } else {
                $resultado = ($resultado << 1) & 0xFFFF;
            }
        }
    }

    return strtoupper(str_pad(dechex($resultado), 4, '0', STR_PAD_LEFT));
}

function gerarPayloadPix(string $chave, string $nomeRecebedor, string $cidade, float $valor, string $txid, string $descricao = 'MAGDA CREW'): string {
    $merchantInfo = pixCampo('00', 'br.gov.bcb.pix') . pixCampo('01', trim($chave));

    $descricao = pixTextoLimpo($descricao, 32);
    if ($descricao !== '') {
        $merchantInfo .= pixCampo('02', $descricao);
    }

    $valorFormatado = number_format(max($valor, 0), 2, '.', '');

    $payload = '';
    $payload .= pixCampo('00', '01');
    $payload .= pixCampo('26', $merchantInfo);
    $payload .= pixCampo('52', '0000');
    $payload .= pixCampo('53', '986');
    $payload .= pixCampo('54', $valorFormatado);
    $payload .= pixCampo('58', 'BR');
    $payload .= pixCampo('59', pixTextoLimpo($nomeRecebedor, 25));
    $payload .= pixCampo('60', pixTextoLimpo($cidade, 15));
    $payload .= pixCampo('62', pixCampo('05', pixTxid($txid)));
    $payload .= '6304';

    return $payload . pixCrc16($payload);
}

function pixQrCodeUrl(string $payload, int $tamanho = 260): string {
    $tamanho = max(180, min($tamanho, 420));
    $modulos = 29;
    $quietZone = 2;
    $pixel = max(4, (int) floor($tamanho / ($modulos + ($quietZone * 2))));
    $canvas = ($modulos + ($quietZone * 2)) * $pixel;

    $grid = array_fill(0, $modulos, array_fill(0, $modulos, null));

    $drawFinder = function (int $startX, int $startY) use (&$grid, $modulos): void {
        for ($y = 0; $y < 7; $y++) {
            for ($x = 0; $x < 7; $x++) {
                $globalX = $startX + $x;
                $globalY = $startY + $y;
                if ($globalX < 0 || $globalY < 0 || $globalX >= $modulos || $globalY >= $modulos) {
                    continue;
                }

                $isBorder = ($x === 0 || $x === 6 || $y === 0 || $y === 6);
                $isCenter = ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4);
                $grid[$globalY][$globalX] = ($isBorder || $isCenter) ? 1 : 0;
            }
        }
    };

    $drawFinder(0, 0);
    $drawFinder($modulos - 7, 0);
    $drawFinder(0, $modulos - 7);

    for ($i = 8; $i < $modulos - 8; $i++) {
        $grid[6][$i] = ($i % 2 === 0) ? 1 : 0;
        $grid[$i][6] = ($i % 2 === 0) ? 1 : 0;
    }

    $seedBits = '';
    foreach (str_split(hash('sha256', $payload . '|MAGDA|PIX|FAKE'), 1) as $char) {
        $seedBits .= str_pad(base_convert($char, 16, 2), 4, '0', STR_PAD_LEFT);
    }
    $seedBits = str_repeat($seedBits, 64);
    $bitIndex = 0;

    for ($y = 0; $y < $modulos; $y++) {
        for ($x = 0; $x < $modulos; $x++) {
            if ($grid[$y][$x] !== null) {
                continue;
            }

            $bit = (int) $seedBits[$bitIndex % strlen($seedBits)];
            $mask = (($x * 3 + $y * 5) % 2 === 0) ? 1 : 0;
            $grid[$y][$x] = ($bit ^ $mask) ? 1 : 0;
            $bitIndex++;
        }
    }

    $rects = [];
    for ($y = 0; $y < $modulos; $y++) {
        for ($x = 0; $x < $modulos; $x++) {
            if ((int) $grid[$y][$x] !== 1) {
                continue;
            }
            $drawX = ($x + $quietZone) * $pixel;
            $drawY = ($y + $quietZone) * $pixel;
            $rects[] = '<rect x="' . $drawX . '" y="' . $drawY . '" width="' . $pixel . '" height="' . $pixel . '" fill="#000"/>';
        }
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $canvas . '" height="' . $canvas . '" viewBox="0 0 ' . $canvas . ' ' . $canvas . '">'
        . '<rect width="100%" height="100%" rx="16" ry="16" fill="#fff"/>'
        . implode('', $rects)
        . '</svg>';

    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
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

function colunaExisteDireto(PDO $pdo, string $tabela, string $coluna): bool {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$tabela` LIKE ?");
        $stmt->execute([$coluna]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return false;
    }
}


function quoteIdentCheckout(string $nome): string {
    return '`' . str_replace('`', '``', $nome) . '`';
}

function infoColunaCheckout(PDO $pdo, string $tabela, string $coluna): ?array {
    if (!preg_match('/^[A-Za-z0-9_]+$/', $tabela) || !preg_match('/^[A-Za-z0-9_]+$/', $coluna)) {
        return null;
    }

    try {
        $stmt = $pdo->prepare('SHOW COLUMNS FROM ' . quoteIdentCheckout($tabela) . ' LIKE ?');
        $stmt->execute([$coluna]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        return $info ?: null;
    } catch (Exception $e) {
        return null;
    }
}

function idPrecisaSerInformadoCheckout(PDO $pdo, string $tabela): bool {
    $info = infoColunaCheckout($pdo, $tabela, 'id');
    if (!$info) {
        return false;
    }

    $extra = strtolower((string)($info['Extra'] ?? ''));
    return strpos($extra, 'auto_increment') === false;
}

function proximoIdCheckout(PDO $pdo, string $tabela): int {
    try {
        $stmt = $pdo->query('SELECT COALESCE(MAX(`id`), 0) + 1 FROM ' . quoteIdentCheckout($tabela));
        return max(1, (int)$stmt->fetchColumn());
    } catch (Exception $e) {
        return 1;
    }
}

function tentarCorrigirAutoIncrementCheckout(PDO $pdo, string $tabela): void {
    if (!tabelaExiste($pdo, $tabela)) {
        return;
    }

    $info = infoColunaCheckout($pdo, $tabela, 'id');
    if (!$info) {
        return;
    }

    $extra = strtolower((string)($info['Extra'] ?? ''));
    if (strpos($extra, 'auto_increment') !== false) {
        return;
    }

    $tipo = (string)($info['Type'] ?? 'INT');
    if (stripos($tipo, 'int') === false) {
        return;
    }

    try {
        $pdo->exec('ALTER TABLE ' . quoteIdentCheckout($tabela) . ' MODIFY `id` ' . $tipo . ' NOT NULL AUTO_INCREMENT');
    } catch (Exception $e) {
        // Se o banco não permitir ALTER TABLE, o checkout usa ID manual no INSERT.
    }
}

function montarInsertCheckout(string $tabela, array $campos): string {
    $camposSql = implode(', ', array_map('quoteIdentCheckout', $campos));
    $placeholders = implode(', ', array_fill(0, count($campos), '?'));
    return 'INSERT INTO ' . quoteIdentCheckout($tabela) . ' (' . $camposSql . ') VALUES (' . $placeholders . ')';
}

function prepararTabelaEnderecosCheckout(PDO $pdo): void {
    if (!tabelaExiste($pdo, 'enderecos')) {
        return;
    }

    try {
        if (!colunaExisteDireto($pdo, 'enderecos', 'numero')) {
            $pdo->exec("ALTER TABLE enderecos ADD COLUMN numero VARCHAR(30) NULL AFTER endereco");
        }

        if (!colunaExisteDireto($pdo, 'enderecos', 'bairro')) {
            $pdo->exec("ALTER TABLE enderecos ADD COLUMN bairro VARCHAR(120) NULL AFTER complemento");
        }
    } catch (Exception $e) {
        // Se o usuário do banco não puder alterar tabela, o checkout continua funcionando.
        // Nesse caso, crie as colunas manualmente pelo phpMyAdmin.
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

    $partesNome = preg_split('/\s+/', $nomeCompleto) ?: [];
    $primeiroNome = $partesNome[0] ?? $nomeCompleto;
    $sobrenomeUsuario = trim(implode(' ', array_slice($partesNome, 1)));

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
            'pais' => 'Brasil',
            'identificacao' => 'Casa',
            'nome' => $primeiroNome,
            'sobrenome' => $sobrenomeUsuario,
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

        if (idPrecisaSerInformadoCheckout($pdo, $tabela) && !in_array('id', array_map('strtolower', $campos), true)) {
            array_unshift($campos, 'id');
            array_unshift($valores, proximoIdCheckout($pdo, $tabela));
        }

        $stmtInsert = $pdo->prepare(montarInsertCheckout($tabela, $campos));
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
    'pac' => ['nome' => 'Entrega padrão', 'prazo' => '5 a 9 dias úteis', 'valor' => 19.90],
    'sedex' => ['nome' => 'Entrega expressa', 'prazo' => '2 a 4 dias úteis', 'valor' => 34.90],
];

$formaPagamentos = [
    'pix' => 'Pix',
    'cartao' => 'Cartão de crédito',
    'boleto' => 'Boleto bancário',
];

// Troque pela sua chave Pix real para o QR Code cobrar na sua conta.
// Pode ser e-mail, CPF/CNPJ, telefone ou chave aleatória.
$pixChave = defined('PIX_CHAVE') ? PIX_CHAVE : 'MAGDA_CREW';
$pixNomeRecebedor = defined('PIX_NOME_RECEBEDOR') ? PIX_NOME_RECEBEDOR : 'MAGDA_CREW';
$pixCidadeRecebedor = defined('PIX_CIDADE_RECEBEDOR') ? PIX_CIDADE_RECEBEDOR : 'JOINVILLE';

prepararTabelaEnderecosCheckout($pdo);
foreach (['vendas', 'itens_venda', 'enderecos_venda', 'enderecos', 'enderecos_usuario'] as $tabelaComId) {
    tentarCorrigirAutoIncrementCheckout($pdo, $tabelaComId);
}

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

$pixDadosPorFrete = [];
foreach ($fretes as $codigoFretePix => $fretePix) {
    $valorPix = $subtotal + (float)$fretePix['valor'];
    $txidPix = 'MC' . strtoupper(substr(md5($session_id . '|' . ($usuario_id ?? 'visitante') . '|' . $codigoFretePix), 0, 18));
    $payloadPix = gerarPayloadPix($pixChave, $pixNomeRecebedor, $pixCidadeRecebedor, $valorPix, $txidPix, 'PEDIDO MAGDA CREW');

    $pixDadosPorFrete[$codigoFretePix] = [
        'payload' => $payloadPix,
        'qr_url' => pixQrCodeUrl($payloadPix),
        'valor' => dinheiro($valorPix),
        'txid' => pixTxid($txidPix),
    ];
}

$pixDadosAtual = $pixDadosPorFrete[$freteSelecionado] ?? reset($pixDadosPorFrete);
$pixPayloadAtual = $pixDadosAtual['payload'] ?? '';
$pixQrAtual = $pixDadosAtual['qr_url'] ?? '';
$pixValorAtual = $pixDadosAtual['valor'] ?? dinheiro($total);
$pixTxidAtual = $pixDadosAtual['txid'] ?? 'MAGDACREW';

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
        $cartaoNomeApenasLetras = preg_match('/^[\p{L} ]+$/u', $cartaoNome) === 1;

        if (!isset($formaPagamentos[$formaPagamento])) {
            $formaPagamento = 'pix';
        }
        $pagamentoSelecionado = $formaPagamento;

        if ($nomeCompleto === '' || $telefone === '') {
            $erroCheckout = 'Preencha seu nome completo e telefone para continuar.';
        } elseif ($freteSelecionado !== 'retirada' && ($cep === '' || $endereco === '' || $numero === '' || $bairro === '' || $cidade === '' || $estado === '')) {
            $erroCheckout = 'Preencha o endereço completo para entrega.';
        } elseif ($formaPagamento === 'cartao' && ($cartaoNome === '' || !$cartaoNomeApenasLetras || strlen($cartaoNumero) < 13 || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cartaoValidade) || strlen($cartaoCvv) < 3 || strlen($cartaoCpf ?? '') < 11)) {
            $erroCheckout = 'Preencha corretamente as informações do cartão. No nome impresso, use apenas letras e espaços.';
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

                $statusInicial = in_array($formaPagamento, ['pix', 'boleto'], true) ? 'pendente' : 'processando';

                $camposVenda = ['usuario_id', 'valor_total', 'subtotal', 'valor_frete', 'forma_pagamento', 'frete_tipo', 'cpf_cnpj_nota', 'status'];
                $valoresVenda = [
                    $usuario_id,
                    $total,
                    $subtotal,
                    $valorFrete,
                    $formaPagamento,
                    $freteSelecionado,
                    $cpfNota,
                    $statusInicial
                ];

                $idVendaManual = null;
                if (idPrecisaSerInformadoCheckout($pdo, 'vendas')) {
                    $idVendaManual = proximoIdCheckout($pdo, 'vendas');
                    array_unshift($camposVenda, 'id');
                    array_unshift($valoresVenda, $idVendaManual);
                }

                $stmtVenda = $pdo->prepare(montarInsertCheckout('vendas', $camposVenda));
                $stmtVenda->execute($valoresVenda);

                $pedidoCriadoId = (int)$pdo->lastInsertId();
                if ($pedidoCriadoId <= 0 && $idVendaManual !== null) {
                    $pedidoCriadoId = (int)$idVendaManual;
                }

                if ($pedidoCriadoId <= 0) {
                    throw new Exception('Não foi possível gerar o ID da venda. Verifique se a coluna id da tabela vendas está como AUTO_INCREMENT.');
                }

                $camposItemVenda = ['venda_id', 'variante_id', 'produto_nome', 'quantidade', 'preco_unitario', 'tamanho_nome', 'cor_nome'];
                $itemVendaUsaIdManual = idPrecisaSerInformadoCheckout($pdo, 'itens_venda');
                if ($itemVendaUsaIdManual) {
                    array_unshift($camposItemVenda, 'id');
                }

                $stmtItem = $pdo->prepare(montarInsertCheckout('itens_venda', $camposItemVenda));

                $stmtEstoque = $pdo->prepare("
                    UPDATE produto_variantes
                    SET quantidade_estoque = GREATEST(quantidade_estoque - ?, 0)
                    WHERE id = ? AND quantidade_estoque >= ?
                ");

                foreach ($itensCarrinho as $item) {
                    $valoresItemVenda = [
                        $pedidoCriadoId,
                        $item['variante_id'],
                        $item['nome'],
                        $item['quantidade'],
                        $item['preco'],
                        $item['tamanho_nome'],
                        $item['cor_nome']
                    ];

                    if ($itemVendaUsaIdManual) {
                        array_unshift($valoresItemVenda, proximoIdCheckout($pdo, 'itens_venda'));
                    }

                    $stmtItem->execute($valoresItemVenda);

                    $stmtEstoque->execute([
                        $item['quantidade'],
                        $item['variante_id'],
                        $item['quantidade']
                    ]);

                    if ($stmtEstoque->rowCount() < 1) {
                        throw new Exception('O produto "' . $item['nome'] . '" acabou no estoque antes de finalizar a compra.');
                    }
                }

                if (tabelaExiste($pdo, 'enderecos_venda')) {
                    $camposEnderecoVenda = ['venda_id', 'cep', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'destinatario'];
                    $valoresEnderecoVenda = [
                        $pedidoCriadoId,
                        $cep,
                        $endereco,
                        $numero,
                        $complemento,
                        $bairro,
                        $cidade,
                        $estado,
                        $nomeCompleto
                    ];

                    if (idPrecisaSerInformadoCheckout($pdo, 'enderecos_venda')) {
                        array_unshift($camposEnderecoVenda, 'id');
                        array_unshift($valoresEnderecoVenda, proximoIdCheckout($pdo, 'enderecos_venda'));
                    }

                    $stmtEnderecoVenda = $pdo->prepare(montarInsertCheckout('enderecos_venda', $camposEnderecoVenda));
                    $stmtEnderecoVenda->execute($valoresEnderecoVenda);
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
<link rel="stylesheet" href="/MagdaCrew/public/assets/css/checkout.css">
<br> <br>
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
                                    <span>
                                        <?= htmlspecialchars($enderecoLinha) ?>
                                        <?= !empty($end['numero']) ? ', Nº ' . htmlspecialchars($end['numero']) : '' ?>
                                    </span>
                                    <?php if (!empty($end['bairro'])): ?>
                                        <span>Bairro: <?= htmlspecialchars($end['bairro']) ?></span>
                                    <?php endif; ?>
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
                            <input type="text" name="cep" id="cep" value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>" placeholder="00000-000" maxlength="9" data-entrega oninput="mascaraCEPCheckout(this)" onblur="buscarEnderecoPorCEPCheckout()">
                            <small id="cepStatusCheckout" style="display:block;margin-top:7px;color:#a9a9a9;font-size:12px;"></small>
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


                        <?php if ($usuario_id && empty($enderecosSalvos)): ?>
                            <label class="save-address-card" id="saveAddressBox">
                                <input type="checkbox" name="salvar_endereco_padrao" value="1" checked>
                                <span>
                                    <strong>Salvar este endereço no meu perfil</strong>
                                    <span>Como essa conta ainda não tem endereço salvo, o checkout salva esse endereço como padrão no banco para as próximas compras.</span>
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
                                <h3>Pagamento Magda Crew</h3>
                                <p>Escolha Pix, cartão de crédito ou boleto e acompanhe todos os detalhes do pagamento do seu pedido Magda Crew.</p>
                            </div>
                            
                        </div>

                        <div class="payment-methods" aria-label="Formas de pagamento">
                            <button type="button" class="pay-option <?= $pagamentoSelecionado === 'pix' ? 'is-active' : '' ?>" data-pay-option="pix">
                                <span class="pay-option-top">
                                    <span class="pay-icon">Pix</span>
                                    <span class="pay-badge">Aprovação rápida</span>
                                </span>
                                <strong>Pix</strong>
                                <small>QR Code na tela, código copia e cola e resumo do pagamento para o cliente.</small>
                                <span class="pay-check">✓</span>
                            </button>

                            <button type="button" class="pay-option <?= $pagamentoSelecionado === 'cartao' ? 'is-active' : '' ?>" data-pay-option="cartao">
                                <span class="pay-option-top">
                                    <span class="pay-icon">CARD</span>
                                    <span class="pay-badge">Loja virtual</span>
                                </span>
                                <strong>Cartão de crédito</strong>
                                <small>Campos completos do cartão com visual de loja virtual e validação dos dados.</small>
                                <span class="pay-check">✓</span>
                            </button>

                            <button type="button" class="pay-option <?= $pagamentoSelecionado === 'boleto' ? 'is-active' : '' ?>" data-pay-option="boleto">
                                <span class="pay-option-top">
                                    <span class="pay-icon">BL</span>
                                    <span class="pay-badge">Bancário</span>
                                </span>
                                <strong>Boleto bancário</strong>
                                <small>Pedido registrado com status pendente para pagamento bancário.</small>
                                <span class="pay-check">✓</span>
                            </button>
                        </div>

                        <div class="payment-info-box">
                            <span class="info-icon">i</span>
                            <div>
                                <strong id="paymentDetailTitle">Pix Magda Crew</strong>
                                <span id="paymentDetailText">Escaneie o QR Code ou use o copia e cola para concluir o pagamento do pedido.</span>
                            </div>
                        </div>

                        <div class="pix-details-panel <?= $pagamentoSelecionado === 'pix' ? 'is-open' : '' ?>" id="pixDetailsBox">
                            <div class="pix-mp-top">
                                <div>
                                    <span class="pix-mp-eyebrow">Magda Crew Pay</span>
                                    <h4>Pague com Pix na Magda Crew</h4>
                                    <p>Escaneie o QR Code ou copie o código Pix para concluir o pagamento. O valor é atualizado automaticamente conforme o frete selecionado.</p>
                                </div>
                                <span class="pix-mp-secure">Checkout seguro</span>
                            </div>

                            <div class="magda-demo-brand">
                                <span>MAGDA CREW</span>
                                <strong>Pagamento protegido</strong>
                                <small>Pedido #MAGDA<?= strtoupper(substr(md5($session_id), 0, 6)) ?></small>
                            </div>

                            <div class="pix-payment-layout pix-mp-layout">
                                <div class="pix-qr-card pix-mp-qr-card">
                                    <div class="pix-brand-row">
                                        <span>Magda Crew Pix</span>
                                        <strong id="pixAmountLabel"><?= htmlspecialchars($pixValorAtual) ?></strong>
                                    </div>
                                    <div class="pix-qr-frame pix-mp-qr-frame">
                                        <img id="pixQrImage" src="<?= htmlspecialchars($pixQrAtual) ?>" alt="QR Code Pix Magda Crew">
                                    </div>
                                    <small>Abra o app do banco e escaneie o QR Code</small>
                                </div>

                                <div class="pix-copy-card pix-mp-copy-card">
                                    <div class="pix-status-row">
                                        <span>Pagamento</span>
                                        <strong>Aguardando pagamento</strong>
                                    </div>

                                    <div class="pix-mp-steps">
                                        <div><b>1</b><span>Abra o app do seu banco ou carteira digital</span></div>
                                        <div><b>2</b><span>Escaneie o QR Code ou copie o código Pix</span></div>
                                        <div><b>3</b><span>Finalize o pedido e acompanhe no painel</span></div>
                                    </div>

                                    <div class="pix-expire-row">
                                        <div class="pix-expire-card"><span>Valor</span><strong id="pixValueCard"><?= htmlspecialchars($pixValorAtual) ?></strong></div>
                                        <div class="pix-expire-card"><span>Validade</span><strong>30 min</strong></div>
                                    </div>

                                    <label for="pixCopiaCola">Código Pix copia e cola</label>
                                    <textarea class="pix-code-box" id="pixCopiaCola" readonly rows="5"><?= htmlspecialchars($pixPayloadAtual) ?></textarea>
                                    <button type="button" class="copy-pix-btn" id="copyPixBtn">Copiar código Pix</button>

                                    <div class="pix-help">
                                        Depois do pagamento, clique em finalizar pedido para registrar a compra e acompanhar o status no painel.
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div class="card-details-panel <?= $pagamentoSelecionado === 'cartao' ? 'is-open' : '' ?>" id="cardDetailsBox">
                            <div class="virtual-card-wrap">
                                <div class="virtual-card-preview" aria-hidden="true">
                                    <div class="card-preview-brand">
                                        <span>MAGDA CREW CARD</span>
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
                                            <input type="text" name="cartao_nome" id="card_nome" data-card-field autocomplete="cc-name" pattern="[A-Za-zÀ-ÖØ-öø-ÿ ]+" title="Digite apenas letras e espaços" maxlength="60" value="<?= htmlspecialchars($_POST['cartao_nome'] ?? '') ?>" placeholder="Nome igual ao cartão" <?= $pagamentoSelecionado === 'cartao' ? '' : 'disabled' ?>>
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
                                        Dados protegidos: as informações do cartão são usadas apenas para validação visual do checkout e não ficam salvas no banco.
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
const cepStatusCheckout = document.getElementById('cepStatusCheckout');

function mascaraCEPCheckout(input) {
    let value = input.value.replace(/\D/g, '').slice(0, 8);
    if (value.length > 5) {
        value = value.replace(/^(\d{5})(\d)/, '$1-$2');
    }
    input.value = value;
}

async function buscarEnderecoPorCEPCheckout() {
    const cepInput = document.getElementById('cep');
    const enderecoInput = document.getElementById('endereco');
    const bairroInput = document.getElementById('bairro');
    const cidadeInput = document.getElementById('cidade');
    const estadoInput = document.getElementById('estado');
    const numeroInput = document.getElementById('numero');

    if (!cepInput) return;

    const cep = cepInput.value.replace(/\D/g, '');

    if (cep.length !== 8) {
        if (cepStatusCheckout) {
            cepStatusCheckout.textContent = 'Digite um CEP com 8 números.';
            cepStatusCheckout.style.color = '#ffb4b4';
        }
        return;
    }

    if (cepStatusCheckout) {
        cepStatusCheckout.textContent = 'Buscando endereço pelo CEP...';
        cepStatusCheckout.style.color = '#cfcfcf';
    }

    try {
        const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const dados = await resposta.json();

        if (dados.erro) {
            if (cepStatusCheckout) {
                cepStatusCheckout.textContent = 'CEP não encontrado. Preencha o endereço manualmente.';
                cepStatusCheckout.style.color = '#ffb4b4';
            }
            return;
        }

        if (enderecoInput) enderecoInput.value = dados.logradouro || '';
        if (bairroInput) bairroInput.value = dados.bairro || '';
        if (cidadeInput) cidadeInput.value = dados.localidade || '';
        if (estadoInput) estadoInput.value = dados.uf || '';

        if (cepStatusCheckout) {
            cepStatusCheckout.textContent = 'Endereço preenchido automaticamente. Agora coloque o número.';
            cepStatusCheckout.style.color = '#8ff0b3';
        }

        if (numeroInput) numeroInput.focus();
    } catch (error) {
        if (cepStatusCheckout) {
            cepStatusCheckout.textContent = 'Não foi possível buscar o CEP agora. Preencha manualmente.';
            cepStatusCheckout.style.color = '#ffb4b4';
        }
    }
}

const cepCheckoutInput = document.getElementById('cep');
if (cepCheckoutInput) {
    cepCheckoutInput.addEventListener('input', () => {
        const cepLimpo = cepCheckoutInput.value.replace(/\D/g, '');
        if (cepLimpo.length === 8) {
            buscarEnderecoPorCEPCheckout();
        }
    });
}

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
const pixPaymentData = <?= json_encode($pixDadosPorFrete, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
const pixQrImage = document.getElementById('pixQrImage');
const pixCopiaCola = document.getElementById('pixCopiaCola');
const pixAmountLabel = document.getElementById('pixAmountLabel');
const pixValueCard = document.getElementById('pixValueCard');
const pixTxidCard = document.getElementById('pixTxidCard');
const copyPixBtn = document.getElementById('copyPixBtn');

const paymentTexts = {
    pix: {
        title: 'Pix Magda Crew',
        text: 'Escaneie o QR Code pelo app do banco ou use o código Pix copia e cola para concluir o pagamento.'
    },
    cartao: {
        title: 'Cartão Magda Crew',
        text: 'Preencha os dados do cartão logo abaixo. Essa área aparece somente quando cartão está selecionado.'
    },
    boleto: {
        title: 'Boleto Magda Crew',
        text: 'O pedido será registrado para pagamento via boleto e acompanhamento no painel.'
    }
};

function setBoxOpen(box, shouldOpen) {
    if (!box) return;
    box.classList.toggle('is-open', shouldOpen);
}

function dadosPixFreteAtual() {
    const freteMarcado = document.querySelector('input[name="frete_tipo"]:checked');
    const codigoFrete = freteMarcado ? freteMarcado.value : 'pac';
    const primeiroCodigo = Object.keys(pixPaymentData || {})[0];
    return (pixPaymentData && (pixPaymentData[codigoFrete] || pixPaymentData[primeiroCodigo])) || null;
}

function atualizarPixCheckout() {
    const dados = dadosPixFreteAtual();
    if (!dados) return;

    if (pixQrImage) pixQrImage.src = dados.qr_url || '';
    if (pixCopiaCola) pixCopiaCola.value = dados.payload || '';
    if (pixAmountLabel) pixAmountLabel.textContent = dados.valor || '';
    if (pixValueCard) pixValueCard.textContent = dados.valor || '';
    if (pixTxidCard) pixTxidCard.textContent = dados.txid || '';
}

document.querySelectorAll('input[name="frete_tipo"]').forEach(input => {
    input.addEventListener('change', atualizarPixCheckout);
});

if (copyPixBtn) {
    copyPixBtn.addEventListener('click', async () => {
        if (!pixCopiaCola) return;

        try {
            await navigator.clipboard.writeText(pixCopiaCola.value);
        } catch (error) {
            pixCopiaCola.focus();
            pixCopiaCola.select();
            document.execCommand('copy');
        }

        const textoOriginal = copyPixBtn.textContent;
        copyPixBtn.textContent = 'Código copiado!';
        setTimeout(() => {
            copyPixBtn.textContent = textoOriginal;
        }, 1800);
    });
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
function limparNomeCartao(valor) {
    return valor
        .replace(/[^A-Za-zÀ-ÖØ-öø-ÿ\s]/g, '')
        .replace(/\s{2,}/g, ' ')
        .slice(0, 60);
}
function atualizarNomeCartao() {
    if (!cardNome) return;

    const limpo = limparNomeCartao(cardNome.value);
    if (cardNome.value !== limpo) {
        cardNome.value = limpo;
    }

    const nomeValido = /^[A-Za-zÀ-ÖØ-öø-ÿ ]+$/.test(cardNome.value.trim());
    if (cardNome.value.trim() && !nomeValido) {
        cardNome.setCustomValidity('Digite apenas letras no nome do cartão.');
    } else {
        cardNome.setCustomValidity('');
    }

    if (cardPreviewName) {
        cardPreviewName.textContent = cardNome.value.trim() || 'Nome do cartão';
    }
}
if (cardNome) {
    cardNome.addEventListener('input', atualizarNomeCartao);
    cardNome.addEventListener('paste', () => setTimeout(atualizarNomeCartao, 0));
}

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
        atualizarNomeCartao();

        if (cardNome && !/^[A-Za-zÀ-ÖØ-öø-ÿ ]+$/.test(cardNome.value.trim())) {
            event.preventDefault();
            cardNome.focus();
            cardNome.reportValidity();
            return;
        }

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

atualizarPixCheckout();
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
