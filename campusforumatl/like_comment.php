<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (empty($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Você precisa estar logado']);
    exit;
}

$comment_id = (int)($_POST['comment_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($comment_id <= 0 || !in_array($action, ['like', 'unlike'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$mysqli = db_connect();
$user_id = $_SESSION['user']['id'];

if ($action === 'like') {
    $check = $mysqli->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $check->bind_param('ii', $comment_id, $user_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows === 0) {
        $ins = $mysqli->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
        $ins->bind_param('ii', $comment_id, $user_id);
        $ins->execute();
    }
} elseif ($action === 'unlike') {
    $del = $mysqli->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $del->bind_param('ii', $comment_id, $user_id);
    $del->execute();
}

// Contar likes
$count = $mysqli->prepare("SELECT COUNT(*) as likes FROM comment_likes WHERE comment_id = ?");
$count->bind_param('i', $comment_id);
$count->execute();
$result = $count->get_result()->fetch_assoc();

echo json_encode(['success' => true, 'likes' => $result['likes']]);
?>
