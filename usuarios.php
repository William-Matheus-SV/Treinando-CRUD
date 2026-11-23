<?php 
    session_start();

    // Só entra quem está logado
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit;
    }

    require_once 'conexao.php';

    $erro = '';
    $form = ['id' => '', 'nome' => '', 'email' => ''];

    // EXCLUIR (via POST)
    if (isset($_POST['excluir'])) {
        $id = (int) $_POST['id'];

        if ($id === (int) $_SESSION['id']) {
            $erro = 'Você não pode excluir o seu próprio usuário.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header('Location: usuarios.php');
            exit;
        }
    }

    // SALVAR (cadastrar ou editar)
    if (isset($_POST['salvar'])) {
        $id    = (int) ($_POST['id'] ?? 0);
        $nome  = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if ($nome === '' || $email === '') {
            $erro = 'Preencha nome e e-mail.';
        } elseif ($id === 0 && $senha === '') {
            $erro = 'Informe a senha para o novo usuário.';
        } else {
            try {
                if ($id === 0) {
                    // Novo usuário
                    $stmt = $pdo->prepare(
                        "INSERT INTO usuarios (nome, email, senha)
                         VALUES (:nome, :email, :senha)"
                    );
                    $stmt->execute([
                        'nome'  => $nome,
                        'email' => $email,
                        'senha' => password_hash($senha, PASSWORD_DEFAULT)
                    ]);
                } elseif ($senha !== '') {
                    // Edição trocando a senha
                    $stmt = $pdo->prepare(
                        "UPDATE usuarios
                         SET nome = :nome, email = :email, senha = :senha
                         WHERE id = :id"
                    );
                    $stmt->execute([
                        'nome'  => $nome,
                        'email' => $email,
                        'senha' => password_hash($senha, PASSWORD_DEFAULT),
                        'id'    => $id
                    ]);
                } else {
                    // Edição mantendo a senha atual
                    $stmt = $pdo->prepare(
                        "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :id"
                    );
                    $stmt->execute([
                        'nome'  => $nome,
                        'email' => $email,
                        'id'    => $id
                    ]);
                }

                header('Location: usuarios.php');
                exit;

            } catch (PDOException $e) {
                // 23000 = violação de UNIQUE (e-mail repetido)
                $erro = ($e->getCode() === '23000')
                    ? 'Este e-mail já está cadastrado.'
                    : 'Erro ao salvar o usuário.';
            }
        }

        // Em caso de erro, mantém o que a pessoa digitou (menos a senha)
        $form = ['id' => $id ?: '', 'nome' => $nome, 'email' => $email];

    // CARREGAR para edição (clicou em "Editar")
    } elseif (isset($_GET['editar'])) {
        $stmt = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => (int) $_GET['editar']]);
        $encontrado = $stmt->fetch();

        if ($encontrado) {
            $form = $encontrado;
        }
    }

    // LISTAR
    $usuarios = $pdo->query("SELECT id, nome, email FROM usuarios ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar usuários</title>
</head>
<body>

<h1>Gerenciar usuários</h1>
<p><a href="index.php">Voltar ao menu</a></p>

<?php if ($erro !== ''): ?>
    <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<h2><?= $form['id'] ? 'Editar usuário' : 'Cadastrar usuário' ?></h2>

<form action="usuarios.php" method="POST">
    <input type="hidden" name="id" value="<?= htmlspecialchars($form['id']) ?>">

    <label>Nome:</label>
    <input type="text" name="nome" placeholder="Digite o nome"
           value="<?= htmlspecialchars($form['nome']) ?>" required>
    <br><br>

    <label>E-mail:</label>
    <input type="email" name="email" placeholder="Digite o e-mail"
           value="<?= htmlspecialchars($form['email']) ?>" required>
    <br><br>

    <label>Senha:</label>
    <input type="password" name="senha" placeholder="Digite a senha">
    <?php if ($form['id']): ?>
        <small>(deixe em branco para manter a senha atual)</small>
    <?php endif; ?>
    <br><br>

    <button type="submit" name="salvar">Salvar</button>
    <a href="usuarios.php">Limpar</a>
</form>

<h2>Usuários cadastrados</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>E-mail</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['nome']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <a href="usuarios.php?editar=<?= $u['id'] ?>">Editar</a>

                <form action="usuarios.php" method="POST" style="display:inline;"
                      onsubmit="return confirm('Excluir este usuário?');">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" name="excluir">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>