<?php
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$mysqli = db_connect();
$uid = $_SESSION['user']['id'];

/* =======================
   BUSCAR DADOS DO USUÁRIO
======================= */
$stmt = $mysqli->prepare("SELECT username, email, avatar, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

$avatar = !empty($user['avatar'])
    ? "/campusforumatl/uploads/avatars/".$user['avatar']
    
    : "https://ui-avatars.com/api/?name=".urlencode($user['username'])."&background=7b3fe4&color=fff&size=256";
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Meu Perfil - Campus Forum</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="index_style.css">

<style>
body { font-family: 'Poppins', sans-serif; }

header {
    justify-content: space-between;
}

header a {
    order: 0;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 12px;
    order: 0;
}

.nav-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #7b3fe4;
    transition: 0.25s;
}

.nav-avatar:hover {
    transform: scale(1.1);
}

.profile-container {
    max-width: 900px;
    margin: 60px auto;
    padding: 40px;
    background: #1a0d2e;
    border-radius: 16px;
    box-shadow: 0 0 30px rgba(123,63,228,0.25);
    display: flex;
    gap: 40px;
    align-items: center;
}

.profile-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 4px solid #7b3fe4;
    overflow: hidden;
    flex-shrink: 0;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-info h2 {
    font-size: 28px;
    margin-bottom: 6px;
    color: #ffffff;
}

.profile-info p {
    color: #bbb;
    margin-bottom: 10px;
}

.profile-actions {
    margin-top: 20px;
}

.profile-actions a {
    display: inline-block;
    padding: 10px 22px;
    border-radius: 25px;
    background: linear-gradient(135deg, #7b3fe4, #9f6bff);
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: 0.25s;
}

.profile-actions a:hover {
    transform: scale(1.05);
}

.nav-avatar:hover {
    transform: scale(1.1);
}
</style>
</head>

<body>

<header>
    <a href="/campusforumatl/index.php">
        <img src="/campusforumatl/campusforumlogox.png" width="140">
    </a>

    <nav class="nav-user">
        <?php
        $uid = $_SESSION['user']['id'];
        $q = $mysqli->prepare("SELECT avatar FROM users WHERE id = ?");
        $q->bind_param("i", $uid);
        $q->execute();
        $u = $q->get_result()->fetch_assoc();

        $nav_avatar = !empty($u['avatar'])
            ? "/campusforumatl/uploads/avatars/".$u['avatar']
            : "https://ui-avatars.com/api/?name=".urlencode($_SESSION['user']['username'])."&background=7b3fe4&color=fff";
        ?>

        <a href="perfil.php" class="avatar-link">
            <img src="<?= htmlspecialchars($nav_avatar) ?>" class="nav-avatar">
        </a>

        <a href="salvos.php" class="btn-novo">⭐</a>
        <a href="create_pergunta.php" class="btn-novo">+</a>
        <a href="logout.php" class="btn-logout">Sair</a>
    </nav>
</header>

<main>

<div class="profile-container">
    <div class="profile-avatar">
        <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar">
    </div>

    <div class="profile-info">
        <h2><?= htmlspecialchars($user['username']) ?></h2>
        <p><?= htmlspecialchars($user['email']) ?></p>
        <p>Membro desde: <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>

        <div class="profile-actions">
            <a href="config_perfil.php">Editar Perfil</a>
        </div>
    </div>
</div>

</main>

</body>
</html>
