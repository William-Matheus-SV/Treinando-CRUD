<?php 
    session_start();

    if(!isset($_SESSION['id'])){
        header('Location: login.php');
        exit;
    }
    
    require_once 'conexao.php';

    $usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $ratos = $pdo->query("SELECT COUNT(*) FROM ratos")->fetchColumn();
    $camundongos = $pdo->query("SELECT COUNT(*) FROM camundongos")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biotério - UFPE</title>
</head>
<body>
    <section><h2> [Foto] </h2> </section>
    <section>
        <span class="user-pill"> Olá, <?= htmlspecialchars($_SESSION['nome'] ?? 'Usuario', ENT_QUOTES, 'UTF-8')?></span>
    </section>
    
    <div class = "layout">
        <aside class="sidebar">
            <nav class="nav" aria-label="Menu principal">
                <a class="active" href="index.php">Menu Principal</a>
                <a href="usuarios.php">Usuarios</a>
                <a href="ratos.php">Ratos</a>
                <a href="camundongos.php">Camundongos</a>
                <a href="logout.php">Sair</a>
            </nav>

            <h4>css são os amigos</h4>
        </aside>
    </div>
</body>
</html>