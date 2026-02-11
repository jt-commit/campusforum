<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'campusforumatl');// ✔ mesmo nome do SQL atualizar o banco  de banco da aplicação
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306); // ✔ porta correta

function db_connect() {
    $mysqli = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        DB_PORT
    );

    if ($mysqli->connect_errno) {
        die(
            'Falha na conexão com MySQL (' .
            $mysqli->connect_errno . '): ' .
            $mysqli->connect_error
        );
    }

    $mysqli->set_charset('utf8mb4');
    
    // Criar tabela de likes em comentários se não existir
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS comment_likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (comment_id, user_id),
            FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Criar tabela de dislikes em comentários se não existir
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS comment_dislikes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_dislike (comment_id, user_id),
            FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    return $mysqli;
}
?>
