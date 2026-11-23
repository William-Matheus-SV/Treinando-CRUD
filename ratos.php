<?php 
    session_start();

    // Só entra quem está logado
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit;
    }

    require_once 'conexao.php';

    $erro = '';
    $form = ['idratos' => '', 'quantidade' => '', 'especie' => ''];

    // EXCLUIR (via POST)
    if (isset($_POST['excluir'])) {
        $id = (int) $_POST['idratos'];

        if ($id === (int) $_SESSION['idratos']) {
            $erro = 'Você não pode excluir o seu próprio usuário.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM ratos WHERE idratos = :idratos");
            $stmt->execute(['idratos' => $id]);
            header('Location: ratos.php');
            exit;
        }
    }

    // SALVAR (cadastrar ou editar)
    if (isset($_POST['salvar'])) {
        $id    = (int) ($_POST['idratos'] ?? 0);
        $quantidade  = trim($_POST['quantidade'] ?? '');
        $especie = trim($_POST['especie'] ?? '');
        

        if ($quantidade === '' || $especie === '') {
            $erro = 'Preencha os dados corretamente.';
        } elseif ($quantidade === 0 && $especie === '') {
            $erro = 'Quantidade e Especie errados';
        } else {
            try {
                if ($id === 0) {
                    // Novo usuário
                    $stmt = $pdo->prepare(
                        "INSERT INTO ratos (quantidade, especie)
                         VALUES (:quantidade, :especie)"
                    );
                    $stmt->execute([
                        'quantidade'  => $quantidade,
                        'especie' => $especie,
                    ]);
                } elseif ($especie !== '') {
                    // Edição trocando a especie
                    $stmt = $pdo->prepare(
                        "UPDATE ratos
                         SET quantidade = :quantidade, especie = :especie
                         WHERE idratos = :idratos"
                    );
                    $stmt->execute([
                        'quantidade'  => $quantidade,
                        'especie' => $especie,
                        'idratos'    => $id
                    ]);
                } else {
                    // Edição mantendo a senha atual
                    $stmt = $pdo->prepare(
                        "UPDATE ratos SET quantidade = :quantidade, especie = :especie WHERE idratos = :idratos"
                    );
                    $stmt->execute([
                        'quantidade'  => $quantidade,
                        'especie' => $especie,
                        'idratos'    => $id
                    ]);
                }

                header('Location: ratos.php');
                exit;

            } catch (PDOException $e) {
                // 23000 = violação de UNIQUE (e-mail repetido)
                $erro = ($e->getCode() === '23000')
                    ? 'Esta especie já está cadastrada.'
                    : 'Erro ao salvar o rato.';
            }
        }

        // Em caso de erro, mantém o que a pessoa digitou (menos a senha)
        $form = ['idratos' => $id ?: '', 'quantidade' => $quantidade, 'especie' => $especie];

    // CARREGAR para edição (clicou em "Editar")
    } elseif (isset($_GET['editar'])) {
        $stmt = $pdo->prepare("SELECT idratos, quantidade, especie FROM ratos WHERE idratos = :idratos");
        $stmt->execute(['idratos' => (int) $_GET['editar']]);
        $encontrado = $stmt->fetch();

        if ($encontrado) {
            $form = $encontrado;
        }
    }

    // LISTAR
    $ratos = $pdo->query("SELECT idratos, quantidade, especie FROM ratos ORDER BY idratos ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar ratos</title>
</head>
<body>

<h1>Gerenciar ratos</h1>
<p><a href="index.php">Voltar ao menu</a></p>

<?php if ($erro !== ''): ?>
    <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<h2><?= $form['idratos'] ? 'Editar usuário' : 'Cadastrar rato' ?></h2>

<form action="ratos.php" method="POST">
    <input type="hidden" name="idratos" value="<?= htmlspecialchars($form['idratos']) ?>">

    <label>Quantidade:</label>
    <input type="text" name="quantidade" placeholder="Digite a quantidade"
           value="<?= htmlspecialchars($form['quantidade']) ?>" required>
    <br><br>

    <label>Especie:</label>
    <input type="especie" name="especie" placeholder="Digite a especie"
           value="<?= htmlspecialchars($form['especie']) ?>" required>
    <br><br>


    <button type="submit" name="salvar">Salvar</button>
    <a href="ratos.php">Limpar</a>
</form>

<h2>ratos cadastrados</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Quantidade</th>
        <th>Especie</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($ratos as $u): ?>
        <tr>
            <td><?= $u['idratos'] ?></td>
            <td><?= htmlspecialchars($u['quantidade']) ?></td>
            <td><?= htmlspecialchars($u['especie']) ?></td>
            <td>
                <a href="ratos.php?editar=<?= $u['idratos'] ?>">Editar</a>

                <form action="ratos.php" method="POST" style="display:inline;"
                      onsubmit="return confirm('Excluir este usuário?');">
                    <input type="hidden" name="idratos" value="<?= $u['idratos'] ?>">
                    <button type="submit" name="excluir">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>