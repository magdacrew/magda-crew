<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    die("Erro ao conectar: " . $e->getMessage());
}

// Puxa também nome/e-mail do cliente para a busca funcionar melhor.
$temTabelaEnderecos = false;
try {
    $temTabelaEnderecos = $pdo->query("SHOW TABLES LIKE 'enderecos'")->rowCount() > 0;
} catch (Exception $e) {
    $temTabelaEnderecos = false;
}

if ($temTabelaEnderecos) {
    $stmt = $pdo->query("
        SELECT 
            v.*,
            u.email AS cliente_email,
            COALESCE(
                NULLIF(u.nome_completo, ''),
                (
                    SELECT ev.destinatario
                    FROM enderecos_venda ev
                    WHERE ev.venda_id = v.id
                    ORDER BY ev.id DESC
                    LIMIT 1
                ),
                (
                    SELECT TRIM(CONCAT(COALESCE(e.nome, ''), ' ', COALESCE(e.sobrenome, '')))
                    FROM enderecos e
                    WHERE e.usuario_id = v.usuario_id
                    ORDER BY e.padrao DESC, e.id DESC
                    LIMIT 1
                )
            ) AS cliente_nome
        FROM vendas v
        LEFT JOIN usuarios u ON u.id = v.usuario_id
        ORDER BY v.id DESC
    ");
} else {
    $stmt = $pdo->query("
        SELECT 
            v.*,
            u.email AS cliente_email,
            COALESCE(
                NULLIF(u.nome_completo, ''),
                (
                    SELECT ev.destinatario
                    FROM enderecos_venda ev
                    WHERE ev.venda_id = v.id
                    ORDER BY ev.id DESC
                    LIMIT 1
                )
            ) AS cliente_nome
        FROM vendas v
        LEFT JOIN usuarios u ON u.id = v.usuario_id
        ORDER BY v.id DESC
    ");
}

$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Vendas - Magda Crew</title>
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
                <h1>Vendas</h1>
                <p style="color: #666; margin-top: 5px;">Gerencie e acompanhe todos os pedidos da loja.</p>
            </div>
        </div>

        <div class="painel-busca-wrap">
            <label class="painel-busca-label" for="buscaVendas">Buscar venda</label>
            <input type="text" id="buscaVendas" class="painel-busca-input" placeholder="Digite cliente, status, valor ou ID..." data-busca-painel autocomplete="off">
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Cliente</th>
                    <th>Valor Total</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($vendas as $venda): ?>
                    <?php 
                        $statusClass = 'status-padrao';
                        $statusText = strtolower($venda['status'] ?? '');
                        if ($statusText === 'confirmado' || $statusText === 'pago') $statusClass = 'status-confirmado';
                        elseif ($statusText === 'pendente' || $statusText === 'processando') $statusClass = 'status-pendente';
                        elseif ($statusText === 'cancelado') $statusClass = 'status-cancelado';

                        $clienteNome = trim($venda['cliente_nome'] ?? '');
                        if ($clienteNome === '') {
                            $clienteNome = $venda['cliente_email'] ?? 'Cliente não identificado';
                        }

                        $textoBusca = implode(' ', [
                            $venda['id'] ?? '',
                            $clienteNome,
                            $venda['cliente_email'] ?? '',
                            $venda['valor_total'] ?? '',
                            number_format($venda['valor_total'] ?? 0, 2, ',', '.'),
                            $venda['status'] ?? '',
                            $venda['forma_pagamento'] ?? '',
                            $venda['data_venda'] ?? ''
                        ]);
                    ?>
                    <tr data-search="<?= htmlspecialchars($textoBusca, ENT_QUOTES, 'UTF-8') ?>">
                        <td><?= htmlspecialchars($venda['id']) ?></td>
                        
                        <td>
                            <strong><?= htmlspecialchars($clienteNome) ?></strong><br>
                            <small style="color: #777;"><?= htmlspecialchars($venda['cliente_email'] ?? '') ?></small>
                        </td>

                        <td>
                            R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?>
                        </td>

                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($venda['status'] ?? 'N/A') ?>
                            </span>
                        </td>

                        <td style="text-align: center;">
                            <div class="acoes">
                                <a href="VendaDetalhe.php?id=<?= $venda['id'] ?>" class="btn-acao">Ver Detalhes</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr data-sem-resultado style="display: none;">
                    <td colspan="5" style="text-align: center; color: #999; padding: 35px 0;">Nenhuma venda encontrada.</td>
                </tr>
            </tbody>
        </table>

    </section>
</main>

<script src="public/assets/js/main.js"></script>
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
