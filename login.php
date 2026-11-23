<?php 
    session_start();
    require_once 'conexao.php';
    
    $erro = '';

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email = trim($_POST['email']?? '');
        $senha = $_POST['senha']?? '';

        if($email === '' || $senha === ''){
            $erro = "Preencha email e senha";
        } else {
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
            $stmt->execute(['email' =>$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                session_regenerate_id(true);

                $_SESSION['id'] = $usuario['id'];
                $_SESSION['nome'] = $usuario['nome'];
                $_SESSION['email'] = $usuario['email'];
                
                header('Location: index.php');
                exit;
            }else {
                $erro = "Email ou senha inválidos";
            }
        }

    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biotério - UFPE Login</title>
</head>
<body>
    <h1>Login</h1>
    <form action="login.php" method="POST">
        <label>E-mail:</label>
        <input type="email" name="email" required><br><br>
        
        <label>Senha:</label>
        <input type="password" name="senha" required><br><br>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>