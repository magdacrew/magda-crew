<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: /magda-crew/views/pages/login.php");
    exit;
}

$email = $_SESSION["email"] ?? "usuario@teste.com";
$usuario_id = $_SESSION["usuario_id"];

// ==========================================
// 1. CONFIGURAÇÃO DO BANCO DE DADOS
// ==========================================
$host = 'localhost';
$dbname = 'magda_crew'; // Coloque o nome do seu banco de dados
$user = 'root';         // Coloque o seu usuário do banco
$pass = '';             // Coloque a sua senha do banco

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// ==========================================
// 2. LÓGICA PARA SALVAR O ENDEREÇO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'salvar_endereco') {
    $pais        = $_POST['pais'] ?? '';
    $nome        = $_POST['nome'] ?? '';
    $sobrenome   = $_POST['sobrenome'] ?? '';
    $cep         = $_POST['cep'] ?? '';
    $endereco    = $_POST['endereco'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $cidade      = $_POST['cidade'] ?? '';
    $estado      = $_POST['estado'] ?? '';
    $telefone    = $_POST['telefone'] ?? '';
    $padrao      = isset($_POST['padrao']) ? 1 : 0;

    // Se marcou como padrão, remove o status de padrão dos outros endereços desse usuário
    if ($padrao === 1) {
        $stmt = $pdo->prepare("UPDATE enderecos SET padrao = 0 WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
    }

    // Insere no banco
    $sql = "INSERT INTO enderecos (usuario_id, pais, nome, sobrenome, cep, endereco, complemento, cidade, estado, telefone, padrao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $pais, $nome, $sobrenome, $cep, $endereco, $complemento, $cidade, $estado, $telefone, $padrao]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==========================================
// 3. LÓGICA PARA REMOVER O ENDEREÇO
// ==========================================
if (isset($_GET['remover'])) {
    $id_remover = (int)$_GET['remover'];
    $stmt = $pdo->prepare("DELETE FROM enderecos WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id_remover, $usuario_id]);
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==========================================
// 4. BUSCAR ENDEREÇOS NO BANCO
// ==========================================
$stmt = $pdo->prepare("SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY padrao DESC, id DESC");
$stmt->execute([$usuario_id]);
$meus_enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Listas para o select
$lista_paises = ["Brasil", "Estados Unidos", "Portugal", "Espanha", "França", "Argentina"]; // Adicione mais se precisar
$lista_estados = ["AC"=>"Acre", "AL"=>"Alagoas", "AP"=>"Amapá", "AM"=>"Amazonas", "BA"=>"Bahia", "CE"=>"Ceará", "DF"=>"Distrito Federal", "ES"=>"Espírito Santo", "GO"=>"Goiás", "MA"=>"Maranhão", "MT"=>"Mato Grosso", "MS"=>"Mato Grosso do Sul", "MG"=>"Minas Gerais", "PA"=>"Pará", "PB"=>"Paraíba", "PR"=>"Paraná", "PE"=>"Pernambuco", "PI"=>"Piauí", "RJ"=>"Rio de Janeiro", "RN"=>"Rio Grande do Norte", "RS"=>"Rio Grande do Sul", "RO"=>"Rondônia", "RR"=>"Roraima", "SC"=>"Santa Catarina", "SP"=>"São Paulo", "SE"=>"Sergipe", "TO"=>"Tocantins"];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="/magda-crew/public/assets/css/perfil.css">
<title>Perfil - Magda Crew</title>
</head>
<body>

<div class="topbar">
    <div class="top-content">
        <div class="menu">
            <a href="/magda-crew/public/index.php">
                <img src="/magda-crew/public/assets/images/MagdaWhiteLogo.png" class="logo" alt="Logo">
            </a>
            <a href="/magda-crew/views/pages/orders.php">Orders</a>
            <a href="/magda-crew/views/pages/Profile.php">Profile</a>
        </div>
            <a href="javascript:history.back()">
        <img src="/magda-crew/public/assets/images/X.png" alt="Voltar" class="profile-icon">
    </a>
    </div>
</div>

<div class="container">
    <h1>Perfil</h1>

    <div class="card">
        <div class="label">E-mail</div>
        <div class="value"><?= htmlspecialchars($email) ?></div>
    </div>

    <div class="card">
        <div class="endereco-header">
            <h3>Endereços</h3>
            <button type="button" class="btn-adicionar" id="btnAbrirModal">Adicionar endereço</button>
        </div>

        <?php if (empty($meus_enderecos)): ?>
            <p style="color:#777;">Nenhum endereço adicionado</p>
        <?php else: ?>
            <div class="lista-enderecos-container">
                <?php foreach ($meus_enderecos as $end): ?>
                    <div class="endereco-item <?= $end['padrao'] ? 'item-padrao' : '' ?>">
                        <div class="end-detalhes">
                            <strong>
                                <?= htmlspecialchars($end['nome'] . ' ' . $end['sobrenome']) ?>
                                <?php if ($end['padrao']): ?>
                                    <span class="badge-padrao">Padrão</span>
                                <?php endif; ?>
                            </strong>
                            <p><?= htmlspecialchars($end['endereco']) ?><?= !empty($end['complemento']) ? ', ' . htmlspecialchars($end['complemento']) : '' ?></p>
                            <p><?= htmlspecialchars($end['cidade']) ?> - <?= htmlspecialchars($end['estado']) ?>, <?= htmlspecialchars($end['cep']) ?></p>
                            <p><?= htmlspecialchars($end['pais']) ?></p>
                            <p> <?= htmlspecialchars($end['telefone']) ?></p>
                        </div>
                        <div class="end-acoes">
                            <a href="?remover=<?= $end['id'] ?>" class="btn-remover" onclick="return confirm('Tem certeza que deseja remover este endereço?')">Remover</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <form action="/magda-crew/views/pages/logout.php" method="POST">
        <button class="logout-btn">Sair</button>
    </form>
</div>

<div class="modal-overlay" id="modalEndereco">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Adicionar endereço</h2>
            <span class="close-btn" id="btnFecharModal">&times;</span>
        </div>

        <form class="form-endereco" action="" method="POST">
            <input type="hidden" name="acao" value="salvar_endereco">

            <div class="form-group">
                <label>País/região</label>
                <select name="pais" required>
                    <?php foreach ($lista_paises as $pais): ?>
                        <option value="<?= $pais ?>" <?= $pais === 'Brasil' ? 'selected' : '' ?>><?= $pais ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <input type="text" name="nome" placeholder="Nome" required>
                </div>
                <div class="form-group half">
                    <input type="text" name="sobrenome" placeholder="Sobrenome" required>
                </div>
            </div>

            <div class="form-group">
                <input type="text" name="cep" id="cepInput" placeholder="CEP" maxlength="9" required oninput="mascaraCEP(this)">
            </div>

            <div class="form-group">
                <input type="text" name="endereco" placeholder="Endereço e número" required>
            </div>

            <div class="form-group">
                <input type="text" name="complemento" placeholder="Apartamento, bloco etc.">
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <input type="text" name="cidade" placeholder="Cidade" required>
                </div>
                <div class="form-group half">
                    <select name="estado" required>
                        <option value="" disabled selected>Estado</option>
                        <?php foreach ($lista_estados as $sigla => $nome_estado): ?>
                            <option value="<?= $sigla ?>"><?= $nome_estado ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group phone-group">
                <div class="phone-prefix">
                    <span>+55</span>
                </div>
                <input type="text" name="telefone" id="telefoneInput" placeholder="Telefone" maxlength="15" required oninput="mascaraTelefone(this)">
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="enderecoPadrao" name="padrao" value="1">
                <label for="enderecoPadrao">Este é meu endereço padrão</label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" id="btnCancelarModal">Cancelar</button>
                <button type="submit" class="btn-salvar">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ==========================================
    // MÁSCARAS PARA FORÇAR NÚMEROS E TAMANHO
    // ==========================================
    function mascaraCEP(input) {
        let value = input.value.replace(/\D/g, ''); // Remove tudo que não for número
        if (value.length > 5) {
            value = value.replace(/^(\d{5})(\d)/, '$1-$2'); // Coloca o traço depois do 5º dígito
        }
        input.value = value;
    }

    function mascaraTelefone(input) {
        let value = input.value.replace(/\D/g, ''); // Remove tudo que não for número
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3'); // Formato: (11) 99999-9999
        } else if (value.length > 5) {
            value = value.replace(/^(\d{2})(\d{4,5})(\d{0,4}).*/, '($1) $2-$3'); // Formato: (11) 9999-9999
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2'); // Formato: (11) 9
        }
        input.value = value;
    }

    // ==========================================
    // CONTROLE DO MODAL
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalEndereco');
        const btnAbrir = document.getElementById('btnAbrirModal');
        const btnFechar = document.getElementById('btnFecharModal');
        const btnCancelar = document.getElementById('btnCancelarModal');

        if (btnAbrir && modal) {
            btnAbrir.addEventListener('click', function(e) {
                e.preventDefault(); 
                modal.style.display = 'flex';
            });
        }
        
        const fecharModal = function() { if (modal) modal.style.display = 'none'; };

        if (btnFechar) btnFechar.addEventListener('click', fecharModal);
        if (btnCancelar) btnCancelar.addEventListener('click', fecharModal);

        window.addEventListener('click', function(e) {
            if (e.target === modal) fecharModal();
        });
    });
</script>

</body>
</html>