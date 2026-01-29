<?php
session_start();
require_once 'db.php';

/* ===============================
   AUTENTICAÇÃO
================================ */
if (empty($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

/* ===============================
   VARIÁVEIS
================================ */
$errors = [];
$title = '';
$content = '';
$image_path = null;

/* ===============================
   UPLOAD DIR
================================ */
$uploads_dir = __DIR__ . '/uploads/posts';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

/* ===============================
   CSRF
================================ */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ===============================
   POST
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requisição inválida.';
    }

    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $errors[] = 'Título e conteúdo são obrigatórios.';
    }

    /* ===== UPLOAD ===== */
    if (empty($errors) && !empty($_FILES['image']['name'])) {

        $file = $_FILES['image'];
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];

        if ($file['error'] === UPLOAD_ERR_OK) {

            $mime = mime_content_type($file['tmp_name']);

            if (in_array($mime, $allowed)) {

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $name = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], $uploads_dir.'/'.$name)) {
                    $image_path = $name;
                } else {
                    $errors[] = 'Falha ao salvar imagem.';
                }

            } else {
                $errors[] = 'Formato de imagem inválido.';
            }
        }
    }

    /* ===== INSERT ===== */
    if (empty($errors)) {

        $db = db_connect();

        $stmt = $db->prepare(
            'INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)'
        );

        $stmt->bind_param(
            'isss',
            $_SESSION['user']['id'],
            $title,
            $content,
            $image_path
        );

        if ($stmt->execute()) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        }

        $errors[] = 'Erro ao salvar post.';
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Criar Pergunta - Campus Forum</title>

<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="index_style.css">

<style>

/* ===== REAPROVEITADO DO INDEX ===== */
.search-box input{
    width:330px;
    padding:12px 22px;
    background:#000;
    color:#fff;
    border-radius:30px;
    border:none;
}

.topico{
    max-width:900px;
    margin:150px auto;
    background: linear-gradient(180deg, #6e22c3, #3c006d);
    border-radius:16px;
    padding:40px;
    color:#ffffff;
    box-shadow: 0 8px 16px rgba(0,0,0,0.6), 0 4px 8px rgba(0,0,0,0.4);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.topico h1{
    margin-bottom:30px;
    text-align: center;
    color: #ffffff;
    font-size: 28px;
    font-weight: 600;
}

.topico label{
    display:block;
    margin-top:15px;
    margin-bottom:6px;
    color: #ffffff;
}

.topico input,
.topico textarea{
    width:100%;
    padding:12px;
    border-radius:10px;
    border:none;
    background:#ffffff;
    color:#333;
}

.topico button{
    margin-top:20px;
    padding:12px 22px;
    border-radius:10px;
    border:none;
    background:#6d28d9;
    color:#fff;
    cursor:pointer;
    width: 100%;
}

.topico button:hover{
    background:#7c3aed;
}

.error{
    background:#7f1d1d;
    padding:12px;
    border-radius:10px;
    margin-bottom:20px;
    color: #ffffff;
}

.error ul {
    margin: 0;
    padding-left: 20px;
}

.btn-logout{
    background:#b91c1c;        /* vermelho */
    color:#fff !important;
    padding:8px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    transition:.2s;
}

.btn-logout:hover{
    background:#dc2626;        /* vermelho mais claro */
}
</style>
</head>

<body>

<header>
    <a href="/campusforum/index.php">
        <img src="/campusforum/campusforumlogox.png" width="140" alt="Campus Forum">
    </a>

    <div class="search-box">
        <form method="get" action="index.php">
            <input type="text" name="q" placeholder="Pesquisar no fórum..."
                   value="">
        </form>
    </div>

    <nav>
        <?php if (!empty($_SESSION['user'])): ?>
            <span>Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
            <a href="index.php" class="btn-novo">Voltar</a>
           <a href="logout.php" class="btn-logout">Sair</a>
        <?php endif; ?>
    </nav>
</header>

<main>

<article class="topico">

<h1>Criar pergunta</h1>

<?php if ($errors): ?>
<div class="error">
<ul>
<?php foreach ($errors as $e): ?>
<li><?= htmlspecialchars($e) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<label>Título</label>
<input type="text" name="title" value="<?= htmlspecialchars($title) ?>">

<label>Conteúdo</label>
<textarea name="content" rows="8"><?= htmlspecialchars($content) ?></textarea>

<label>Imagem (opcional)</label>
<input type="file" name="image" accept="image/*">

<button type="submit">Publicar</button>

</form>

</article>

</main>

<footer style="text-align:center;color:#aaa;margin:40px 0;">
&copy; <?= date('Y') ?> Campus Forum
</footer>

</body>
</html>

