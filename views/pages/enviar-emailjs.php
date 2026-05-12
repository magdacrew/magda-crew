<?php
session_start();

if (!isset($_SESSION["email_login"]) || !isset($_SESSION["codigo_para_email"])) {
    header("Location: /magda-crew/views/pages/login.php");
    exit;
}

$email = $_SESSION["email_login"];
$codigo = $_SESSION["codigo_para_email"];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Enviando código...</title>
</head>
<body>

<p>Enviando código...</p>

<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

<script>
emailjs.init("8uchm0rcbi6BVoMsp");

emailjs.send("service_4qi1zs9", "template_8bqxgl3", {
    email: "<?= htmlspecialchars($email) ?>",
    codigo: "<?= htmlspecialchars($codigo) ?>"
}).then(function() {
    window.location.href = "/magda-crew/views/pages/verificar-codigo.php";
}).catch(function(error) {
    alert("Erro ao enviar o e-mail.");
    console.log(error);
});
</script>

</body>
</html>