<?php
session_start();
require 'db.php';

$id = intval($_GET['id'] ?? 0);
$mysqli = db_connect();

/* ===== BUSCAR POST ===== */
$stmt = $mysqli->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();

if (!$post) {
    http_response_code(404);
    echo 'Post não encontrado';
    exit;
}

/* ===== PERMISSÃO ===== */
if (
    empty($_SESSION['user']) ||
    $_SESSION['user']['id'] !== $post['user_id']
) {
    http_response_code(403);
    echo 'Acesso negado';
    exit;
}

$errors = [];

/* ===== PROCESSAR EDIÇÃO ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        $errors[] = 'Preencha título e conteúdo';
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare(
            'UPDATE posts SET title = ?, content = ? WHERE id = ?'
        );
        $stmt->bind_param('ssi', $title, $content, $id);

        if ($stmt->execute()) {
            header('Location: pergunta.php?id=' . $id);
            exit;
        } else {
            $errors[] = 'Erro ao atualizar o post';
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<title>Editar Post</title>
<link rel="stylesheet" href="styles.css">
<style>
/* ======== ESTILO GERAL ======== */
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background-color: #1a0d2e;
    color: #e0d5f0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* ======== CONTAINER ======== */
.login-container {
    background: linear-gradient(180deg, #29004b, #3d0073);
    padding: 40px 50px;
    border-radius: 18px;
    width: 380px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.5);
    text-align: center;
}

/* ======== TÍTULO ======== */
.login-container h1 {
    margin-top: 0;
    color: #f4e9ff;
    font-size: 26px;
}

/* ======== ERROS ======== */
.error {
    background-color: rgba(255, 50, 50, 0.25);
    padding: 8px;
    border-radius: 6px;
    margin-bottom: 12px;
    color: #ff9a9a;
}

/* ======== INPUTS ======== */
.login-container input,
.login-container textarea {
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 18px;
    border: none;
    outline: none;
    background: #000;
    color: #fff;
    font-size: 15px;
    resize: vertical;
}

/* ======== BOTÃO ======== */
button {
    width: 100%;
    padding: 12px;
    border: none;
    background: linear-gradient(180deg, #7c3aed, #5b21b6);
    color: #ffffff;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: 0.3s ease;
    box-shadow: 0 3px 6px rgba(0,0,0,0.3);
}

button:hover {
    background: linear-gradient(180deg, #9d4edd, #6a0dad);
    transform: translateY(-2px);
}

/* ======== VOLTAR ======== */
.voltar {
    display: inline-block;
    margin-top: 15px;
    color: #d3aaff;
    text-decoration: none;
    font-size: 14px;
}

.voltar:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<div class="login-container">
    <h1>Editar Post</h1>

    <?php foreach ($errors as $e): ?>
        <div class="error"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input
            type="text"
            name="title"
            placeholder="Título"
            value="<?= htmlspecialchars($post['title']) ?>"
            required
        >

        <textarea
            name="content"
            rows="6"
            placeholder="Conteúdo"
            required
        ><?= htmlspecialchars($post['content']) ?></textarea>

        <button type="submit">Salvar</button>
    </form>

    <a class="voltar" href="pergunta.php?id=<?= $id ?>">← Voltar</a>
</div>

</body>
</html>

