<?php
session_start();
require_once 'db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (empty($_SESSION['user'])) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

$postId = intval($_GET['id'] ?? 0);

if ($postId <= 0) {
    http_response_code(400);
    echo "ID inválido.";
    exit;
}

$mysqli = db_connect();

/* =========================
   BUSCAR POST
========================= */
$stmt = $mysqli->prepare(
    "SELECT id, user_id FROM posts WHERE id = ?"
);
$stmt->bind_param("i", $postId);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    http_response_code(404);
    echo "Post não encontrado.";
    exit;
}

/* =========================
   VERIFICAR AUTOR
========================= */
if ($_SESSION['user']['id'] !== $post['user_id']) {
    http_response_code(403);
    echo "Você não tem permissão para excluir este post.";
    exit;
}

/* =========================
   EXCLUIR COMENTÁRIOS
========================= */
$stmt = $mysqli->prepare(
    "DELETE FROM comments WHERE post_id = ?"
);
$stmt->bind_param("i", $postId);
$stmt->execute();
$stmt->close();

/* =========================
   EXCLUIR POST
========================= */
$stmt = $mysqli->prepare(
    "DELETE FROM posts WHERE id = ?"
);
$stmt->bind_param("i", $postId);
$stmt->execute();
$stmt->close();

/* =========================
   REDIRECIONAR
========================= */
header("Location: index.php");
exit;
