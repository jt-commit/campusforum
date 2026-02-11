<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    echo json_encode(['saved' => false]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$post_id = (int)($data['post_id'] ?? 0);

if (!$post_id) {
    echo json_encode(['saved' => false]);
    exit;
}

$mysqli = db_connect();
$uid = $_SESSION['user']['id'];

/* verifica se já existe */
$check = $mysqli->prepare("
    SELECT id FROM saved_posts
    WHERE user_id = ? AND post_id = ?
");
$check->bind_param("ii", $uid, $post_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    /* remover */
    $del = $mysqli->prepare("
        DELETE FROM saved_posts
        WHERE user_id = ? AND post_id = ?
    ");
    $del->bind_param("ii", $uid, $post_id);
    $del->execute();

    echo json_encode(['saved' => false]);
} else {
    /* salvar */
    $ins = $mysqli->prepare("
        INSERT INTO saved_posts (user_id, post_id)
        VALUES (?, ?)
    ");
    $ins->bind_param("ii", $uid, $post_id);
    $ins->execute();

    echo json_encode(['saved' => true]);
}
