<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'campus_forum');
define('DB_USER', 'root');
define('DB_PASS', 'root');

function db_connect(){
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($mysqli->connect_errno) {
        die('Falha na conexão com MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
?>
