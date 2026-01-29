<?php
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Não autenticado']);
        exit;
    }
    header('Location: index.php');
    exit;
}

$mysqli = db_connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    $stmt = $mysqli->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $_SESSION['user']['id']);
    $ok = $stmt->execute();

    header('Content-Type: application/json');
    if ($ok && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível excluir (permissão ou já excluído)']);
    }
    exit;
}

// Fallback para GET (comportamento antigo)
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $mysqli->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $_SESSION['user']['id']);
    $stmt->execute();
}

header('Location: index.php?msg=deleted');
exit;
