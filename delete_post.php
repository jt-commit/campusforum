<?php
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$mysqli = db_connect();

$stmt = $mysqli->prepare(
    'DELETE FROM posts WHERE id = ? AND user_id = ?'
);
$stmt->bind_param('ii', $id, $_SESSION['user']['id']);
$stmt->execute();

header('Location: index.php?msg=deleted');
exit;
