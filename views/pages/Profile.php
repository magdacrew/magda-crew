<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

if (!isset($_SESSION["usuario_id"])) {
    header("Location: /MagdaCrew/views/pages/login.php");
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

    // Garante os campos separados de número e bairro.
    function colunaExisteEndereco(PDO $pdo, string $coluna): bool {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM enderecos LIKE ?");
        $stmt->execute([$coluna]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!colunaExisteEndereco($pdo, 'numero')) {
        $pdo->exec("ALTER TABLE enderecos ADD COLUMN numero VARCHAR(30) NULL AFTER endereco");
    }

    if (!colunaExisteEndereco($pdo, 'bairro')) {
        $pdo->exec("ALTER TABLE enderecos ADD COLUMN bairro VARCHAR(120) NULL AFTER complemento");
    }
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
    $numero      = $_POST['numero'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $bairro      = $_POST['bairro'] ?? '';
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
    $sql = "INSERT INTO enderecos (usuario_id, pais, nome, sobrenome, cep, endereco, numero, complemento, bairro, cidade, estado, telefone, padrao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $pais, $nome, $sobrenome, $cep, $endereco, $numero, $complemento, $bairro, $cidade, $estado, $telefone, $padrao]);

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
$lista_paises = [
    "Afeganistão",
    "África do Sul",
    "Albânia",
    "Alemanha",
    "Andorra",
    "Angola",
    "Antígua e Barbuda",
    "Arábia Saudita",
    "Argélia",
    "Argentina",
    "Armênia",
    "Austrália",
    "Áustria",
    "Azerbaijão",
    "Bahamas",
    "Bahrein",
    "Bangladesh",
    "Barbados",
    "Bélgica",
    "Belize",
    "Benin",
    "Bielorrússia",
    "Bolívia",
    "Bósnia e Herzegovina",
    "Botsuana",
    "Brasil",
    "Brunei",
    "Bulgária",
    "Burkina Faso",
    "Burundi",
    "Butão",
    "Cabo Verde",
    "Camarões",
    "Camboja",
    "Canadá",
    "Catar",
    "Cazaquistão",
    "Chade",
    "Chile",
    "China",
    "Chipre",
    "Colômbia",
    "Comores",
    "Coreia do Norte",
    "Coreia do Sul",
    "Costa do Marfim",
    "Costa Rica",
    "Croácia",
    "Cuba",
    "Dinamarca",
    "Djibuti",
    "Dominica",
    "Egito",
    "El Salvador",
    "Emirados Árabes Unidos",
    "Equador",
    "Eritreia",
    "Eslováquia",
    "Eslovênia",
    "Espanha",
    "Estados Unidos",
    "Estônia",
    "Eswatini",
    "Etiópia",
    "Fiji",
    "Filipinas",
    "Finlândia",
    "França",
    "Gabão",
    "Gâmbia",
    "Gana",
    "Geórgia",
    "Granada",
    "Grécia",
    "Guatemala",
    "Guiana",
    "Guiné",
    "Guiné-Bissau",
    "Guiné Equatorial",
    "Haiti",
    "Honduras",
    "Hungria",
    "Iêmen",
    "Ilhas Marshall",
    "Ilhas Salomão",
    "Índia",
    "Indonésia",
    "Irã",
    "Iraque",
    "Irlanda",
    "Islândia",
    "Israel",
    "Itália",
    "Jamaica",
    "Japão",
    "Jordânia",
    "Kiribati",
    "Kosovo",
    "Kuwait",
    "Laos",
    "Lesoto",
    "Letônia",
    "Líbano",
    "Libéria",
    "Líbia",
    "Liechtenstein",
    "Lituânia",
    "Luxemburgo",
    "Macedônia do Norte",
    "Madagascar",
    "Malásia",
    "Malawi",
    "Maldivas",
    "Mali",
    "Malta",
    "Marrocos",
    "Maurício",
    "Mauritânia",
    "México",
    "Micronésia",
    "Moçambique",
    "Moldávia",
    "Mônaco",
    "Mongólia",
    "Montenegro",
    "Myanmar",
    "Namíbia",
    "Nauru",
    "Nepal",
    "Nicarágua",
    "Níger",
    "Nigéria",
    "Noruega",
    "Nova Zelândia",
    "Omã",
    "Países Baixos",
    "Palau",
    "Palestina",
    "Panamá",
    "Papua-Nova Guiné",
    "Paquistão",
    "Paraguai",
    "Peru",
    "Polônia",
    "Portugal",
    "Quênia",
    "Quirguistão",
    "Reino Unido",
    "República Centro-Africana",
    "República Democrática do Congo",
    "República do Congo",
    "República Dominicana",
    "Romênia",
    "Ruanda",
    "Rússia",
    "Samoa",
    "San Marino",
    "Santa Lúcia",
    "São Cristóvão e Nevis",
    "São Tomé e Príncipe",
    "São Vicente e Granadinas",
    "Seicheles",
    "Senegal",
    "Serra Leoa",
    "Sérvia",
    "Singapura",
    "Síria",
    "Somália",
    "Sri Lanka",
    "Sudão",
    "Sudão do Sul",
    "Suécia",
    "Suíça",
    "Suriname",
    "Tailândia",
    "Taiwan",
    "Tajiquistão",
    "Tanzânia",
    "Tchéquia",
    "Timor-Leste",
    "Togo",
    "Tonga",
    "Trinidad e Tobago",
    "Tunísia",
    "Turcomenistão",
    "Turquia",
    "Tuvalu",
    "Ucrânia",
    "Uganda",
    "Uruguai",
    "Uzbequistão",
    "Vanuatu",
    "Vaticano",
    "Venezuela",
    "Vietnã",
    "Zâmbia",
    "Zimbábue"
];

$lista_estados = [
    "AC" => "Acre",
    "AL" => "Alagoas",
    "AP" => "Amapá",
    "AM" => "Amazonas",
    "BA" => "Bahia",
    "CE" => "Ceará",
    "DF" => "Distrito Federal",
    "ES" => "Espírito Santo",
    "GO" => "Goiás",
    "MA" => "Maranhão",
    "MT" => "Mato Grosso",
    "MS" => "Mato Grosso do Sul",
    "MG" => "Minas Gerais",
    "PA" => "Pará",
    "PB" => "Paraíba",
    "PR" => "Paraná",
    "PE" => "Pernambuco",
    "PI" => "Piauí",
    "RJ" => "Rio de Janeiro",
    "RN" => "Rio Grande do Norte",
    "RS" => "Rio Grande do Sul",
    "RO" => "Rondônia",
    "RR" => "Roraima",
    "SC" => "Santa Catarina",
    "SP" => "São Paulo",
    "SE" => "Sergipe",
    "TO" => "Tocantins"
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="/MagdaCrew/public/assets/css/Profile.css">
<link rel="icon" type="image/png" href="/MagdaCrew/public/assets/images/MgdWhite.png">
<title>Perfil - Magda Crew</title>
</head>
<body>

<div class="topbar">
    <div class="top-content">
        <div class="menu">
            <a href="/MagdaCrew/public/index.php">
                <img src="/MagdaCrew/public/assets/images/MagdaWhiteLogo.png" class="logo" alt="Logo">
            </a>
            <a href="/MagdaCrew/views/pages/orders.php">Orders</a>
            <a href="/MagdaCrew/views/pages/Profile.php">Profile</a>
        </div>
            <a href="javascript:history.back()">
        <img src="/MagdaCrew/public/assets/images/X.png" alt="Voltar" class="profile-icon">
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
                            <p>
                                <?= htmlspecialchars($end['endereco']) ?>
                                <?= !empty($end['numero']) ? ', Nº ' . htmlspecialchars($end['numero']) : '' ?>
                                <?= !empty($end['complemento']) ? ', ' . htmlspecialchars($end['complemento']) : '' ?>
                            </p>
                            <?php if (!empty($end['bairro'])): ?>
                                <p>Bairro: <?= htmlspecialchars($end['bairro']) ?></p>
                            <?php endif; ?>
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

    <form action="/MagdaCrew/views/pages/logout.php" method="POST">
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
                <input type="text" name="cep" id="cepInput" placeholder="CEP" maxlength="9" required oninput="mascaraCEP(this)" onblur="buscarEnderecoPorCEP()">
                <small id="cepStatus" style="display:block;margin-top:6px;color:#777;font-size:12px;"></small>
            </div>

            <div class="form-group">
                <input type="text" name="endereco" id="enderecoInput" placeholder="Rua, avenida ou travessa" required>
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <input type="text" name="numero" id="numeroInput" placeholder="Número" required>
                </div>
                <div class="form-group half">
                    <input type="text" name="bairro" id="bairroInput" placeholder="Bairro" required>
                </div>
            </div>

            <div class="form-group">
                <input type="text" name="complemento" placeholder="Apartamento, bloco etc.">
            </div>

            <div class="form-row">
                <div class="form-group half">
                    <input type="text" name="cidade" id="cidadeInput" placeholder="Cidade" required>
                </div>
                <div class="form-group half">
                    <select name="estado" id="estadoInput" required>
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

    async function buscarEnderecoPorCEP() {
        const cepInput = document.getElementById('cepInput');
        const enderecoInput = document.getElementById('enderecoInput');
        const bairroInput = document.getElementById('bairroInput');
        const cidadeInput = document.getElementById('cidadeInput');
        const estadoInput = document.getElementById('estadoInput');
        const numeroInput = document.getElementById('numeroInput');
        const cepStatus = document.getElementById('cepStatus');

        if (!cepInput) return;

        const cep = cepInput.value.replace(/\D/g, '');

        if (cep.length !== 8) {
            if (cepStatus) {
                cepStatus.textContent = 'Digite um CEP com 8 números.';
                cepStatus.style.color = '#c77';
            }
            return;
        }

        if (cepStatus) {
            cepStatus.textContent = 'Buscando endereço...';
            cepStatus.style.color = '#777';
        }

        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const dados = await resposta.json();

            if (dados.erro) {
                if (cepStatus) {
                    cepStatus.textContent = 'CEP não encontrado.';
                    cepStatus.style.color = '#c77';
                }
                return;
            }

            if (enderecoInput) enderecoInput.value = dados.logradouro || '';
            if (bairroInput) bairroInput.value = dados.bairro || '';
            if (cidadeInput) cidadeInput.value = dados.localidade || '';
            if (estadoInput) estadoInput.value = dados.uf || '';

            if (cepStatus) {
                cepStatus.textContent = 'Endereço preenchido automaticamente.';
                cepStatus.style.color = '#39a96b';
            }

            if (numeroInput) {
                numeroInput.focus();
            }
        } catch (erro) {
            if (cepStatus) {
                cepStatus.textContent = 'Não foi possível buscar o CEP agora.';
                cepStatus.style.color = '#c77';
            }
        }
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'cepInput') {
            const cepLimpo = e.target.value.replace(/\D/g, '');
            if (cepLimpo.length === 8) {
                buscarEnderecoPorCEP();
            }
        }
    });

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