<?php
/**
 * Script de Migração - Adiciona coluna 'image' à tabela 'posts'
 * Execute este arquivo uma única vez em seu navegador
 */

require 'db.php';

try {
    $mysqli = db_connect();
    
    // Verificar se a coluna já existe
    $result = $mysqli->query("SHOW COLUMNS FROM posts LIKE 'image'");
    
    if ($result && $result->num_rows === 0) {
        // Coluna não existe, vamos adicionar
        $query = "ALTER TABLE posts ADD COLUMN image VARCHAR(255) NULL DEFAULT NULL";
        
        if ($mysqli->query($query)) {
            echo "<div style='background:#28a745;color:white;padding:20px;border-radius:8px;margin:20px;'>";
            echo "<h2>✓ Migração Executada com Sucesso!</h2>";
            echo "<p>A coluna 'image' foi adicionada à tabela 'posts'.</p>";
            echo "<p><a href='create_pergunta.php' style='color:white;text-decoration:underline;'>Ir para criar post</a></p>";
            echo "</div>";
        } else {
            echo "<div style='background:#dc3545;color:white;padding:20px;border-radius:8px;margin:20px;'>";
            echo "<h2>✗ Erro na Migração</h2>";
            echo "<p>Erro: " . htmlspecialchars($mysqli->error) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background:#17a2b8;color:white;padding:20px;border-radius:8px;margin:20px;'>";
        echo "<h2>ℹ Coluna Já Existe</h2>";
        echo "<p>A coluna 'image' já foi adicionada à tabela 'posts'.</p>";
        echo "<p><a href='create_pergunta.php' style='color:white;text-decoration:underline;'>Ir para criar post</a></p>";
        echo "</div>";
    }
    
    $mysqli->close();
} catch (Exception $e) {
    echo "<div style='background:#dc3545;color:white;padding:20px;border-radius:8px;margin:20px;'>";
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
    <title>Migração do Banco de Dados</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1 style="text-align:center;color:#f4e9ff;">Migração do Campus Forum</h1>
        <!-- Resultado da migração aparece acima -->
    </div>
</body>
</html>
