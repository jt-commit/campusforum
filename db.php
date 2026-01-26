<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'campusforumfx'); // ✔ mesmo nome do SQL atualizar o banco  de banco da aplicação
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3308); // ✔ porta correta

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
    return $mysqli;
}
?>
