<?php
session_start();
require 'db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Preencha todos os campos.';
    }

    if (empty($errors)) {

        $mysqli = db_connect();

        // Agora usando email, já que userusername NÃO existe na tabela nova
        $stmt = $mysqli->prepare("SELECT id, username, password FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {

            $stmt->bind_result($id, $username, $hash);
            $stmt->fetch();

            // Verificar se $hash não é vazio
            if (!$hash) {
                $errors[] = "Erro na verificação de senha. Contacte o suporte.";
            } elseif (password_verify($password, $hash)) {

                $_SESSION['user'] = [
                    'id' => $id,
                    'username' => $username,
                    'email' => $email
                ];

                header("Location: index.php");
                exit;

            } else {
                $errors[] = "Senha incorreta.";
            }

        } else {
            $errors[] = "E-mail não encontrado.";
        }

        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum - Login</title>

<style>
/* ======== ESTILO GERAL ======== */
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    /* Adicionada a imagem de fundo conforme solicitado */
    background-image: url('/campusforum/fundo1.avif');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: #111827;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* ======== CONTAINER DO LOGIN ======== */
.login-container {
    /* Mantive o gradiente, mas você pode ajustar a opacidade se quiser ver mais o fundo */
    background: linear-gradient(180deg, #29004b, #3d0073);
    padding: 40px 50px;
    border-radius: 18px;
    width: 350px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    text-align: center;
}

/* ======== TÍTULO ======== */
.login-container h1 {
    margin-top: 0;
    color: #f4e9ff;
    font-size: 26px;
}

/* ======== ERROS ======== */
.error {
    background-color: rgba(255, 50, 50, 0.25);
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 12px;
    color: #ff9a9a;
}

/* ======== INPUTS (E-mail e Senha) ======== */
.login-container input {
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 30px;
    border: 1px solid #ddd; /* Adicionada uma borda leve para destacar o branco */
    outline: none;
    background: #ffffff;    /* Mudado de #000 para branco */
    color: #333333;         /* Texto que o usuário digita agora é escuro */
    font-size: 15px;
    box-sizing: border-box; /* Garante que o padding não quebre a largura */
}

/* Texto de exemplo dentro da caixa (Placeholder) */
.login-container input::placeholder {
    color: #666666;         /* Cor cinza para os textos "E-mail" e "Senha" */
}

/* ======== BOTÃO ======== */
button {
    width: 100%;
    padding: 12px;
    border: none;
    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #ffffff;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s ease;
    box-shadow: 0 3px 6px rgba(0,0,0,0.3);
}

button:hover {
    background: linear-gradient(180deg, #9d4edd, #6a0dad);
    transform: translateY(-2px);
}

/* ======== LINK VOLTAR ======== */
.voltar {
    margin-top: 15px;
    display: inline-block;
    color: #d3aaff;
    text-decoration: none;
    font-size: 14px;
}

.voltar:hover {
    text-decoration: underline;
}


</style>
</head>
<body>

<div class="login-container">

    <h1>Login</h1>

    <?php foreach ($errors as $e): ?>
        <p class="error"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Digite seu e-mail" required>
        <input type="password" name="password" placeholder="Digite sua senha" required>

        <button type="submit">Entrar</button>
    </form>

    <a href="index.php" class="voltar">← Voltar</a>

</div>

</body>
</html>

