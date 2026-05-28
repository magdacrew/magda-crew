<?php
/**
 * admin-logout.php — Encerra a sessão admin com segurança
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpa todos os dados da sessão
session_unset();

// Destrói a sessão no servidor
session_destroy();

// Apaga o cookie da sessão no browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

header('Location: /magda-crew/admin-login.php');
exit;
