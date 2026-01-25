<?php
session_start();
require 'db.php';
$id = intval($_GET['id'] ?? 0);
$mysqli = db_connect();
$stmt = $mysqli->prepare('SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id=u.id WHERE p.id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();
if (!$post) { 
    http_response_code(404); 
    echo 'Post não encontrado'; 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['user'])) {
    $content = trim($_POST['content'] ?? '');
    if ($content !== '') {
        $ins = $mysqli->prepare('INSERT INTO comments(post_id, user_id, content) VALUES(?,?,?)');
        $ins->bind_param('iis', $id, $_SESSION['user']['id'], $content);
        $ins->execute();
        header('Location: post.php?id='.$id); 
        exit;
    }
}

$comments = $mysqli->query('SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id=u.id WHERE c.post_id='.$id.' ORDER BY c.created_at ASC')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Campus Forum</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* ======== ESTILO DO POST ======== */
        main {
            display: flex;
            justify-content: center;
            padding: 30px 20px;
        }

        article {
            background-color: #120028;
            border-radius: 12px;
            padding: 30px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            color: #f9d76e;
        }

        article h1 {
            font-size: 28px;
            margin-top: 0;
            color: #f4e9ff;
            margin-bottom: 10px;
        }

        article .meta {
            font-size: 13px;
            color: #bca7ff;
            margin-bottom: 20px;
            border-bottom: 1px solid #3c006d;
            padding-bottom: 15px;
        }

        article .content {
            font-size: 16px;
            line-height: 1.6;
            margin: 20px 0;
            color: #f9d76e;
        }

        article img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        article a {
            color: #b085ff;
            text-decoration: none;
            transition: 0.3s;
            margin-right: 15px;
        }

        article a:hover {
            color: #d3aaff;
            text-decoration: underline;
        }

        /* ======== SEÇÃO DE COMENTÁRIOS ======== */
        section {
            background-color: #120028;
            border-radius: 12px;
            padding: 30px;
            max-width: 800px;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        }

        section h2 {
            color: #d3aaff;
            font-size: 20px;
            margin-top: 0;
            border-bottom: 2px solid #3c006d;
            padding-bottom: 10px;
        }

        /* ======== COMENTÁRIO ======== */
        .comment {
            background: #1e003b;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 3px solid #7c3aed;
        }

        .comment strong {
            color: #d3aaff;
        }

        .comment-meta {
            font-size: 12px;
            color: #bca7ff;
        }

        .comment div {
            margin-top: 8px;
            color: #f9d76e;
            line-height: 1.5;
        }

        /* ======== FORMULÁRIO DE COMENTÁRIO ======== */
        section form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #3c006d;
        }

        section label {
            color: #d3aaff;
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
        }

        section textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #3c006d;
            border-radius: 8px;
            background-color: #000;
            color: #fff;
            font-family: "Poppins", sans-serif;
            resize: vertical;
            min-height: 120px;
            margin-bottom: 15px;
            outline: none;
            transition: 0.3s;
        }

        section textarea:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 8px rgba(124, 58, 237, 0.3);
        }

        section button {
            background: linear-gradient(180deg, #7c3aed, #5b21b6);
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        section button:hover {
            background: linear-gradient(180deg, #9d4edd, #6a0dad);
            transform: translateY(-2px);
        }

        /* ======== LINK VOLTAR ======== */
        .voltar-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }

        .voltar-link a {
            color: #d3aaff;
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .voltar-link a:hover {
            color: #f4e9ff;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<header>
  <img src="/campusforum/campusforumlogox.png" width="150" alt="Campus Forum">

  <nav class="right">
    <?php if (!empty($_SESSION['user'])): ?>
      <span class="user">Olá, <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></span>
      <a href="create_post.php" class="btn-novo">Criar Post</a>
      <a href="logout.php" class="logout">Sair</a>
    <?php else: ?>
      <a href="login.php" class="btn-novo">Entrar</a>
      <a href="register.php" class="btn-novo">Registrar</a>
    <?php endif; ?>
  </nav>
</header>

<main>
  <article>
    <h1><?=htmlspecialchars($post['title'])?></h1>
    <p class="meta">por <?=htmlspecialchars($post['username'])?> em <?= $post['created_at'] ?></p>
    
    <?php if (!empty($post['image'])): ?>
      <img src="<?= htmlspecialchars($post['image']) ?>" alt="Imagem de <?= htmlspecialchars($post['title']) ?>">
    <?php endif; ?>
    
    <div class="content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>

    <?php if(!empty($_SESSION['user']) && $_SESSION['user']['id'] === $post['user_id']): ?>
      <p>
        <a href="edit_post.php?id=<?= $post['id'] ?>">Editar</a> | 
        <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este post?')">Excluir</a>
      </p>
    <?php endif; ?>
  </article>
</main>

<main>
  <section>
    <h2>Comentários</h2>
    <?php foreach($comments as $c): ?>
      <div class="comment">
        <strong><?=htmlspecialchars($c['username'])?></strong> <span class="comment-meta">em <?= $c['created_at'] ?></span>
        <div><?= nl2br(htmlspecialchars($c['content'])) ?></div>
      </div>
    <?php endforeach; ?>

    <?php if(!empty($_SESSION['user'])): ?>
      <form method="post">
        <label>Seu comentário:</label>
        <textarea name="content" required></textarea>
        <button type="submit">Enviar Comentário</button>
      </form>
    <?php else: ?>
      <p><a href="login.php">Entrar</a> para comentar.</p>
    <?php endif; ?>
  </section>
</main>

<main class="voltar-link">
  <a href="index.php">← Voltar ao Fórum</a>
</main>

</body>
</html>
