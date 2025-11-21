<?php

require_once 'db.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $email1 = trim($_POST['email1']);
    $email2 = trim($_POST['email2']);
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    // Verificação de emails
    if ($email1 !== $email2) {
        $error = "Os emails não coincidem!";
    }
    // Verificação de senhas
    elseif ($password1 !== $password2) {
        $error = "As senhas não coincidem!";
    }
    else {

        // Hash somente depois que elas são iguais
        $password_hash = password_hash($password1, PASSWORD_DEFAULT);

        // Verifica se email existe
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email1]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $error = "Email já cadastrado!";
        } else {
            // Insere usuário novo
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email1, $password_hash]);

            header("Location: login.php");
            exit;
        }
    }
}
?>

<form method="post">

    <p><input type="text" name="name" placeholder="Nome" required></p>

    <p><input type="email" name="email1" placeholder="Email" required></p>
    <p><input type="email" name="email2" placeholder="Confirmar Email" required></p>

    <p><input type="password" name="password1" placeholder="Senha" required></p>
    <p><input type="password" name="password2" placeholder="Confirmar Senha" required></p>

    <p><button type="submit" name="register">Cadastrar</button></p>
</form>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>