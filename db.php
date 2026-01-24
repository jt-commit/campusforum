<?php

define('DB_HOST', 'localhost');
define('DB_PORT', 3308);
define('DB_NAME', 'campusforumfx');
define('DB_USER', 'root');
define('DB_PASS', '');

function db_connect(): mysqli
{
    $mysqli = new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        DB_PORT
    );

    if ($mysqli->connect_errno) {
        die(
            'Falha na conexão MySQL (' .
            $mysqli->connect_errno .
            '): ' .
            $mysqli->connect_error
        );
    }

    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}
