<?php
/**
 * save_post.php - Salvar/remover post dos favoritos
 */
session_start();
require 'db.php';

header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Você precisa estar logado', 'status' => 'error']);
    exit;
}

$post_id = intval($_POST['post_id'] ?? 0);
$user_id = intval($_SESSION['user']['id']);

if ($post_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Post inválido', 'status' => 'error']);
    exit;
}

try {
    $mysqli = db_connect();
    
    // Verificar se a tabela existe
    $table_check = $mysqli->query("SHOW TABLES LIKE 'saved_posts'");
    if (!$table_check || $table_check->num_rows === 0) {
        // Criar tabela se não existir
        $create_table = "
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
        $mysqli->query($create_table);
    }
    
    // Verificar se o post existe
    $check = $mysqli->prepare('SELECT id FROM posts WHERE id = ?');
    if (!$check) {
        throw new Exception('Erro na query: ' . $mysqli->error);
    }
    $check->bind_param('i', $post_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows === 0) {
        $check->close();
        http_response_code(404);
        echo json_encode(['error' => 'Post não encontrado', 'status' => 'error']);
        exit;
    }
    $check->close();
    
    // Verificar se já está salvo
    $verify = $mysqli->prepare('SELECT id FROM saved_posts WHERE user_id = ? AND post_id = ?');
    if (!$verify) {
        throw new Exception('Erro na query verify: ' . $mysqli->error);
    }
    $verify->bind_param('ii', $user_id, $post_id);
    $verify->execute();
    $verify->store_result();
    $is_saved = $verify->num_rows > 0;
    $verify->close();
    
    // Toggle save/unsave
    if ($is_saved) {
        // Remover dos salvos
        $delete = $mysqli->prepare('DELETE FROM saved_posts WHERE user_id = ? AND post_id = ?');
        if (!$delete) {
            throw new Exception('Erro ao preparar delete: ' . $mysqli->error);
        }
        $delete->bind_param('ii', $user_id, $post_id);
        if (!$delete->execute()) {
            throw new Exception('Erro ao executar delete: ' . $delete->error);
        }
        $delete->close();
        echo json_encode(['status' => 'removed', 'saved' => false, 'message' => 'Post removido dos salvos']);
    } else {
        // Salvar post
        $insert = $mysqli->prepare('INSERT INTO saved_posts (user_id, post_id) VALUES (?, ?)');
        if (!$insert) {
            throw new Exception('Erro ao preparar insert: ' . $mysqli->error);
        }
        $insert->bind_param('ii', $user_id, $post_id);
        if (!$insert->execute()) {
            throw new Exception('Erro ao executar insert: ' . $insert->error);
        }
        $insert->close();
        echo json_encode(['status' => 'saved', 'saved' => true, 'message' => 'Post salvo']);
    }
    
    $mysqli->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro: ' . $e->getMessage(), 'status' => 'error']);
}
?>
