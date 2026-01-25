<?php
// ===============================
// CONFIGURAÇÃO DE ERROS (DEV)
// ===============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ===============================
// SESSÃO E BANCO
// ===============================
session_start();
require_once 'db.php';

$mysqli = db_connect();

// ===============================
// FAVORITAR / DESFAVORITAR
// ===============================
if (isset($_GET['favorite']) && !empty($_SESSION['user'])) {
    $postId = (int) $_GET['favorite'];
    $userId = $_SESSION['user']['id'];

    $stmt = $mysqli->prepare(
        'SELECT id FROM favorites WHERE user_id = ? AND post_id = ?'
    );
    $stmt->bind_param('ii', $userId, $postId);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();

    if ($exists) {
        $stmt = $mysqli->prepare(
            'DELETE FROM favorites WHERE user_id = ? AND post_id = ?'
        );
        $stmt->bind_param('ii', $userId, $postId);
        $stmt->execute();
    } else {
        $stmt = $mysqli->prepare(
            'INSERT INTO favorites (user_id, post_id) VALUES (?, ?)'
        );
        $stmt->bind_param('ii', $userId, $postId);
        $stmt->execute();
    }

    header('Location: index.php');
    exit;
}

// ===============================
// PESQUISA + LISTAGEM DE POSTS
// ===============================
$search = $_GET['q'] ?? '';
$userId = $_SESSION['user']['id'] ?? 0;

if (!empty($search)) {
    $stmt = $mysqli->prepare(
        'SELECT 
            p.*,
            u.username,
            IF(f.id IS NULL, 0, 1) AS is_favorite
         FROM posts p
         JOIN users u ON u.id = p.user_id
         LEFT JOIN favorites f 
            ON f.post_id = p.id AND f.user_id = ?
         WHERE p.title LIKE CONCAT("%", ?, "%")
            OR p.content LIKE CONCAT("%", ?, "%")
         ORDER BY p.created_at DESC'
    );
    $stmt->bind_param('iss', $userId, $search, $search);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $stmt = $mysqli->prepare(
        'SELECT 
            p.*,
            u.username,
            IF(f.id IS NULL, 0, 1) AS is_favorite
         FROM posts p
         JOIN users u ON u.id = p.user_id
         LEFT JOIN favorites f 
            ON f.post_id = p.id AND f.user_id = ?
         ORDER BY p.created_at DESC'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
}

$posts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Campus Forum - Home</title>

<link rel="stylesheet" href="styles.css">

<style>
.search-box input {
    width: 330px;
    padding: 12px 22px;
    background: #000;
    color: #fff;
    border-radius: 30px;
    border: none;
}

.favorite-btn {
    margin-left: 12px;
    text-decoration: none;
    font-weight: bold;
    color: #f9d76e;
}
.favorite-btn:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<header>
    <img src="campusforumlogox.png" width="150" alt="Campus Forum">

    <div class="search-box">
        <form method="GET">
            <input
                type="text"
                name="q"
                placeholder="Pesquisar no fórum..."
                value="<?= htmlspecialchars($search) ?>"
            >
        </form>
    </div>

    <nav class="right">
        <?php if (!empty($_SESSION['user'])): ?>
            <span>Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
            <a href="create_post.php" class="btn-novo">Criar Post</a>
            <a href="logout.php" class="logout">Sair</a>
        <?php else: ?>
            <a href="login.php" class="btn-novo">Entrar</a>
            <a href="register.php" class="btn-novo">Registrar</a>
        <?php endif; ?>
    </nav>
</header>

<main>
<section>

<?php if (empty($posts)): ?>
    <p style="margin-left:20px;color:white;">Nenhum post encontrado.</p>
<?php endif; ?>

<?php foreach ($posts as $p): ?>
<article class="topico">
    <h2>
        <a href="post.php?id=<?= $p['id'] ?>">
            <?= htmlspecialchars($p['title']) ?>
        </a>
    </h2>

    <p>
        <?= nl2br(htmlspecialchars(substr($p['content'], 0, 400))) ?>
        <?= strlen($p['content']) > 400 ? '...' : '' ?>
    </p>

    <div class="topico-footer">
        <span>por <?= htmlspecialchars($p['username']) ?></span>
        <span><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></span>

        <?php if (!empty($_SESSION['user'])): ?>
            <a class="favorite-btn"
               href="index.php?favorite=<?= $p['id'] ?>">
                <?= $p['is_favorite'] ? '⭐ Desfavoritar' : '☆ Favoritar' ?>
            </a>
        <?php endif; ?>
    </div>
</article>
<?php endforeach; ?>

</section>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Campus Forum IFPE - Igarassu</p>
</footer>

</body>
</html>
