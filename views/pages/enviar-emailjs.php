<?php
session_start();

if (
    !isset($_SESSION["email_login"]) ||
    !isset($_SESSION["codigo_para_email"])
) {
    die("Sessão não encontrada.");
}

$email = $_SESSION["email_login"];
$codigo = $_SESSION["codigo_para_email"];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Enviando...</title>
</head>
<body>

<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>

<script>
emailjs.init("8uchm0rcbi6BVoMsp");

emailjs.send("service_4qi1zs9", "template_8bqxgl3", {
    email: "<?= $email ?>",
    codigo: "<?= $codigo ?>",
    name: "MAGDA CREW"
})
.then(function(response) {

    window.location.href =
    "/magda-crew/views/pages/verificar-codigo.php";

})
.catch(function(error) {

    document.body.innerHTML = `
        <h1>Erro ao enviar</h1>
        <pre>${JSON.stringify(error, null, 2)}</pre>
    `;

});
</script>

</body>
</html>