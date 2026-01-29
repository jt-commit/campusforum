<?php
/**
 * Script de Verificação do Banco de Dados
 * Verifica dados de sessão e integridade do banco
 */

session_start();
require 'db.php';

$mysqli = db_connect();
$messages = [];

// 1. Verificar tabelas
$tables = ['users', 'posts', 'comments'];
foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows === 0) {
        $messages[] = "<span style='color:red;'>✗ Tabela '$table' não existe</span>";
    } else {
        $messages[] = "<span style='color:green;'>✓ Tabela '$table' existe</span>";
    }
}

// 2. Verificar coluna 'image' em posts
$result = $mysqli->query("SHOW COLUMNS FROM posts LIKE 'image'");
if ($result && $result->num_rows > 0) {
    $messages[] = "<span style='color:green;'>✓ Coluna 'image' existe em posts</span>";
} else {
    $messages[] = "<span style='color:red;'>✗ Coluna 'image' não existe em posts</span>";
}

// 3. Contar usuários
$result = $mysqli->query("SELECT COUNT(*) as total FROM users");
$row = $result->fetch_assoc();
$user_count = $row['total'];
$messages[] = "Total de usuários: <strong>$user_count</strong>";

// 4. Contar posts
$result = $mysqli->query("SELECT COUNT(*) as total FROM posts");
$row = $result->fetch_assoc();
$post_count = $row['total'];
$messages[] = "Total de posts: <strong>$post_count</strong>";

// 5. Informações da sessão atual
if (!empty($_SESSION['user'])) {
    $user_id = $_SESSION['user']['id'];
    $messages[] = "<span style='color:green;'>✓ Sessão ativa - ID do usuário: <strong>$user_id</strong></span>";
    
    // Verificar se esse user_id existe no banco
    $result = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
    $result->bind_param('i', $user_id);
    $result->execute();
    $result_set = $result->get_result();
    
    if ($result_set->num_rows > 0) {
        $user = $result_set->fetch_assoc();
        $messages[] = "<span style='color:green;'>✓ Usuário existe no banco: <strong>" . htmlspecialchars($user['username']) . "</strong></span>";
    } else {
        $messages[] = "<span style='color:red;'>✗ Usuário ID $user_id não encontrado no banco</span>";
    }
    $result->close();
} else {
    $messages[] = "<span style='color:orange;'>⚠ Nenhuma sessão ativa - <a href='login.php'>Faça login</a></span>";
}

// 6. Listar todos os usuários
$result = $mysqli->query("SELECT id, username, email FROM users LIMIT 10");
$messages[] = "<hr><h3>Usuários cadastrados:</h3>";
if ($result->num_rows > 0) {
    $messages[] = "<table style='border-collapse:collapse;'>";
    $messages[] = "<tr style='border-bottom:1px solid #999;'><th style='padding:8px;text-align:left;'>ID</th><th style='padding:8px;text-align:left;'>Username</th><th style='padding:8px;text-align:left;'>Email</th></tr>";
    while ($user = $result->fetch_assoc()) {
        $messages[] = "<tr style='border-bottom:1px solid #ccc;'><td style='padding:8px;'>" . htmlspecialchars($user['id']) . "</td><td style='padding:8px;'>" . htmlspecialchars($user['username']) . "</td><td style='padding:8px;'>" . htmlspecialchars($user['email']) . "</td></tr>";
    }
    $messages[] = "</table>";
} else {
    $messages[] = "<p style='color:red;'>Nenhum usuário cadastrado</p>";
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação do Banco de Dados</title>
    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(180deg, #1a0033, #3d0073);
            color: #f9d76e;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }
        h1 {
            color: #f4e9ff;
            margin-top: 0;
        }
        .message {
            margin: 10px 0;
            padding: 8px;
            border-radius: 4px;
        }
        a {
            color: #b085ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            margin-top: 10px;
        }
        th {
            background: rgba(124, 58, 237, 0.3);
            color: #f4e9ff;
        }
        .buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: 0.3s;
        }
        .btn-primary {
            background: linear-gradient(180deg, #7c3aed, #5b21b6);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #f4e9ff;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificação do Campus Forum</h1>
        
        <?php foreach ($messages as $msg): ?>
            <div class="message"><?php echo $msg; ?></div>
        <?php endforeach; ?>

        <div class="buttons">
            <a href="index.php" class="btn btn-primary">← Voltar ao Fórum</a>
            <?php if (empty($_SESSION['user'])): ?>
                <a href="login.php" class="btn btn-primary">Fazer Login</a>
                <a href="register.php" class="btn btn-secondary">Cadastro</a>
            <?php else: ?>
                <a href="create_pergunta.php" class="btn btn-primary">Criar Post</a>
                <a href="logout.php" class="btn btn-secondary">Sair</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
