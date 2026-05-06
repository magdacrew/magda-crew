<?php
// Arquivo: public/index.php

// 1. Inicia a sessão (necessário para login e carrinho)
session_start();

// 2. Configurações Iniciais
require_once __DIR__ . '/../src/Config/Database.php';

// 3. Captura a URL digitada pelo usuário (o .htaccess envia isso pra cá)
// Se não tiver nada na URL, o padrão será 'home'
$url = isset($_GET['url']) ? $_GET['url'] : 'home';

// 4. Divide a URL em partes. Ex: 'produto/detalhe/5' vira ['produto', 'detalhe', '5']
$urlParts = explode('/', rtrim($url, '/'));

// 5. Define quem é o Controller e qual Método (Ação) chamar
// Ex: se acessou 'home', procura por HomeController
$controllerName = ucfirst($urlParts[0]) . 'Controller'; 
$actionName = isset($urlParts[1]) ? $urlParts[1] : 'index'; 

// 6. Caminho físico do arquivo do Controller
$controllerFile = __DIR__ . '/../src/Controllers/' . $controllerName . '.php';

// 7. Lógica de Roteamento
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Instancia a classe do Controller (Ex: $controller = new HomeController();)
    $controller = new $controllerName();
    
    // Verifica se a função existe dentro do Controller
    if (method_exists($controller, $actionName)) {
        // Pega os parâmetros extras da URL (ex: o ID do produto)
        $params = array_slice($urlParts, 2);
        
        // Chama a função passando os parâmetros
        call_user_func_array([$controller, $actionName], $params);
    } else {
        echo "<h1>Erro 404 - Página não encontrada.</h1>";
        echo "<p>O método <strong>{$actionName}</strong> não existe em <strong>{$controllerName}</strong>.</p>";
    }
} else {
    echo "<h1>Erro 404 - Página não encontrada.</h1>";
    echo "<p>O controlador <strong>{$controllerName}</strong> não foi encontrado.</p>";
}