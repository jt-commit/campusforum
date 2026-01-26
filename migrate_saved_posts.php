<?php
/**
 * migrate_saved_posts.php - Criar tabela saved_posts
 */

require 'db.php';

try {
    $mysqli = db_connect();
    
    // Verificar se a tabela já existe
    $result = $mysqli->query("SHOW TABLES LIKE 'saved_posts'");
    
    if ($result && $result->num_rows === 0) {
        // Tabela não existe, vamos criar
        $query = "
        CREATE TABLE IF NOT EXISTS saved_posts (
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NOT NULL,
          post_id INT NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY unique_saved (user_id, post_id),
          FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
          FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
        ";
        
        if ($mysqli->query($query)) {
            echo "<div style='background:#28a745;color:white;padding:20px;border-radius:8px;margin:20px;text-align:center;'>";
            echo "<h2>✓ Migração Executada com Sucesso!</h2>";
            echo "<p>A tabela 'saved_posts' foi criada com sucesso.</p>";
            echo "<p><a href='index.php' style='color:white;text-decoration:underline;font-weight:bold;'>Ir para o Fórum</a></p>";
            echo "</div>";
        } else {
            echo "<div style='background:#dc3545;color:white;padding:20px;border-radius:8px;margin:20px;text-align:center;'>";
            echo "<h2>✗ Erro na Migração</h2>";
            echo "<p>Erro: " . htmlspecialchars($mysqli->error) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background:#17a2b8;color:white;padding:20px;border-radius:8px;margin:20px;text-align:center;'>";
        echo "<h2>ℹ Tabela Já Existe</h2>";
        echo "<p>A tabela 'saved_posts' já foi criada.</p>";
        echo "<p><a href='index.php' style='color:white;text-decoration:underline;font-weight:bold;'>Ir para o Fórum</a></p>";
        echo "</div>";
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "<div style='background:#dc3545;color:white;padding:20px;border-radius:8px;margin:20px;text-align:center;'>";
    echo "<h2>✗ Erro de Conexão</h2>";
    echo "<p>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migração - Campus Forum</title>
    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(180deg, #1a0033, #3d0073);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #f9d76e;
        }
        .container {
            max-width: 600px;
            padding: 20px;
        }
        h1 {
            text-align:center;
            color:#f4e9ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Migração do Campus Forum</h1>
        <!-- Resultado da migração aparece acima -->
    </div>
</body>
</html>
