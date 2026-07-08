<?php
require_once __DIR__ . '/AdminGuard.php';
require_once __DIR__ . '/src/Config/Database.php';

$pdo = Database::getConnection();

// Puxa o nome do cliente pelo cadastro e, se não tiver, pega do endereço padrão.
$temTabelaEnderecos = false;
try {
    $temTabelaEnderecos = $pdo->query("SHOW TABLES LIKE 'enderecos'")->rowCount() > 0;
} catch (Exception $e) {
    $temTabelaEnderecos = false;
}

if ($temTabelaEnderecos) {
    $stmt = $pdo->query("
        SELECT 
            u.*,
            (
                SELECT TRIM(CONCAT(COALESCE(e.nome, ''), ' ', COALESCE(e.sobrenome, '')))
                FROM enderecos e
                WHERE e.usuario_id = u.id
                ORDER BY e.padrao DESC, e.id DESC
                LIMIT 1
            ) AS nome_endereco,
            (SELECT COUNT(id) FROM vendas WHERE usuario_id = u.id) AS total_pedidos
        FROM usuarios u
        ORDER BY u.id DESC
    ");
} else {
    $stmt = $pdo->query("
        SELECT 
            u.*,
            u.nome_completo AS nome_endereco,
            (SELECT COUNT(id) FROM vendas WHERE usuario_id = u.id) AS total_pedidos
        FROM usuarios u
        ORDER BY u.id DESC
    ");
}

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
    <title>Clientes - Magda Crew</title>
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
                <h1>Clientes</h1>
                <p style="color: #666; margin-top: 5px;">Gerencie os usuários cadastrados na sua loja.</p>
            </div>
        </div>

        <div class="painel-busca-wrap">
            <label class="painel-busca-label" for="buscaClientes">Buscar cliente</label>
            <input type="text" id="buscaClientes" class="painel-busca-input" placeholder="Digite nome ou e-mail..." data-busca-painel autocomplete="off">
        </div>

        <table class="tabela">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th style="width: 120px; text-align: center;">Pedidos</th>
                    <th style="width: 150px; text-align: center;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $usuario): ?>
                    <?php
                        $nomeCliente = trim($usuario['nome_completo'] ?? '');
                        if ($nomeCliente === '') {
                            $nomeCliente = trim($usuario['nome_endereco'] ?? '');
                        }
                        if ($nomeCliente === '') {
                            $nomeCliente = 'Cliente sem nome';
                        }

                        $textoBusca = implode(' ', [
                            $usuario['id'] ?? '',
                            $nomeCliente,
                            $usuario['email'] ?? '',
                            $usuario['total_pedidos'] ?? ''
                        ]);
                    ?>
                    <tr data-search="<?= htmlspecialchars($textoBusca, ENT_QUOTES, 'UTF-8') ?>">
                        <td><?= htmlspecialchars($usuario['id']) ?></td>
                        <td><?= htmlspecialchars($nomeCliente) ?></td>
                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                        <td style="text-align: center;"><?= (int)($usuario['total_pedidos'] ?? 0) ?></td>
                        <td style="text-align: center;">
                            <div class="acoes">
                                <a href="ClienteDetalhe.php?id=<?= $usuario['id'] ?>" class="btn-acao">Ver Detalhes</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr data-sem-resultado style="display: none;">
                    <td colspan="5" style="text-align: center; color: #999; padding: 35px 0;">Nenhum cliente encontrado.</td>
                </tr>
            </tbody>
        </table>
    </section>
</main>

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
