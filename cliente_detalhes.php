<?php
require_once __DIR__ . '/src/Config/Database.php';

$cliente_id = $_GET['id'] ?? null;

if (!$cliente_id) {
    die("ID do cliente não informado.");
}

$pdo = Database::getConnection();

// 1. Busca os dados de cadastro do cliente (tabela usuarios)
$stmtCliente = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtCliente->execute([$cliente_id]);
$cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("Cliente não encontrado.");
}

// 2. Busca o endereço na tabela 'enderecos' vinculada a este usuário
$stmtEndereco = $pdo->prepare("SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY padrao DESC LIMIT 1");
$stmtEndereco->execute([$cliente_id]);
$endereco = $stmtEndereco->fetch(PDO::FETCH_ASSOC);

// 3. Busca o histórico de compras desse cliente (tabela vendas)
$stmtVendas = $pdo->prepare("SELECT * FROM vendas WHERE usuario_id = ? ORDER BY id DESC");
$stmtVendas->execute([$cliente_id]);
$vendas = $stmtVendas->fetchAll(PDO::FETCH_ASSOC);

// Preparar nome para exibição e inicial do Avatar
$nomeExibicao = $endereco ? trim($endereco['nome'] . ' ' . $endereco['sobrenome']) : 'Cliente sem nome';
$inicialAvatar = strtoupper(substr($nomeExibicao !== 'Cliente sem nome' ? $nomeExibicao : $cliente['email'], 0, 1));

// Função simples para definir a cor do badge de status
function getStatusClass($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'confirmado': case 'entregue': return 'status-confirmado';
        case 'pendente': case 'processando': return 'status-pendente';
        case 'cancelado': return 'status-cancelado';
        default: return 'status-padrao';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="/magda-crew/public/assets/images/15.png">
    <title>Perfil do Cliente - Magda Crew</title>
    <link rel="stylesheet" href="/MAGDA-CREW/public/assets/css/gestao.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <section class="content">

        <div class="topo-acoes">
            <a href="clientes.php" class="btn-voltar">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Voltar para Clientes
            </a>
        </div>

        <div class="perfil-header-card">
            <div class="avatar-circle">
                <?= htmlspecialchars($inicialAvatar) ?>
            </div>
            <div class="perfil-titulo">
                <h1><?= htmlspecialchars($nomeExibicao) ?></h1>
                <p>ID do Cliente: #<?= htmlspecialchars($cliente['id']) ?></p>
            </div>
        </div>

        <div class="painel-perfil">
            <h2>Informações e Contato</h2>
            <div class="info-grid">
                
                <div class="info-box">
                    <span>Email Principal</span>
                    <strong><?= htmlspecialchars($cliente['email']) ?></strong>
                </div>

                <div class="info-box">
                    <span>Telefone de Contato</span>
                    <strong><?= htmlspecialchars($endereco['telefone'] ?? 'Não cadastrado') ?></strong>
                </div>

                <div class="info-box full-width">
                    <span>Endereço de Entrega Principal</span>
                    <strong>
                        <?php if ($endereco): ?>
                            <?= htmlspecialchars($endereco['endereco']) ?> 
                            <?= !empty($endereco['complemento']) ? ' - ' . htmlspecialchars($endereco['complemento']) : '' ?>
                            <br>
                            <span class="endereco-texto">
                                CEP: <?= htmlspecialchars($endereco['cep'] ?? 'N/A') ?> &bull; 
                                <?= htmlspecialchars($endereco['cidade']) ?> - <?= htmlspecialchars($endereco['estado']) ?> | <?= htmlspecialchars($endereco['pais']) ?><br>
                                <em>Recebedor: <?= htmlspecialchars($endereco['nome'] . ' ' . $endereco['sobrenome']) ?></em>
                            </span>
                        <?php else: ?>
                            <span style="color: #999; font-weight: normal;">Nenhum endereço cadastrado para este cliente.</span>
                        <?php endif; ?>
                    </strong>
                </div>

            </div>
        </div>

        <h2 class="section-title">Histórico de Pedidos</h2>
        <table class="tabela">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                    <th style="text-align: center;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($vendas)): ?>
                    <?php foreach($vendas as $venda): ?>
                    <tr>
                        <td><strong>#<?= $venda['id'] ?></strong></td>
                        <td style="font-weight: 600;">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                        <td>
                            <span class="status-badge <?= getStatusClass($venda['status'] ?? '') ?>">
                                <?= htmlspecialchars($venda['status'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <a href="venda_detalhes.php?id=<?= $venda['id'] ?>" class="btn-acao">Ver Detalhes</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 30px; color: #777;">Este cliente ainda não fez nenhuma compra na loja.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </section>
</main>

</body>
</html>