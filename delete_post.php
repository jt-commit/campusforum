<?php
session_start();
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$mysqli = db_connect();

// Pegar o post
$stmt = $mysqli->prepare('SELECT * FROM posts WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();

if (!$post) {
    http_response_code(404);
    echo 'Post não encontrado';
    exit;
}

// Só o autor pode excluir
if (empty($_SESSION['user']) || $_SESSION['user']['id'] !== $post['user_id']) {
    http_response_code(403);
    echo 'Acesso negado';
    exit;
}

// Excluir comentários relacionados
$mysqli->query('DELETE FROM comments WHERE post_id=' . $id);

// Excluir post
$stmt = $mysqli->prepare('DELETE FROM posts WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();

header('Location: index.php');
exit;
?>
