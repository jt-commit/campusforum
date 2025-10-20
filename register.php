<?php
session_start();
require 'db.php';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') {
        $errors[] = 'Preencha usuário e senha';
    }

    if (empty($errors)) {
        $mysqli = db_connect();
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE username=?');
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = 'Usuário já existe';
        } else {
            $hash = password_hash($p, PASSWORD_DEFAULT);
            $ins = $mysqli->prepare('INSERT INTO users(username, password) VALUES(?, ?)');
            $ins->bind_param('ss', $u, $hash);

            if ($ins->execute()) {
                $_SESSION['user'] = ['id' => $ins->insert_id, 'username' => $u];
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Erro ao registrar';
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Registrar</title>
<link rel="stylesheet" hre
