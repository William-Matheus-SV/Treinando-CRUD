<?php 
    session_start();

    // Só entra quem está logado
    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit;
    }

    require_once 'conexao.php';

    $erro = '';
    $form = ['idcamun' => '', 'quantidade' => '', 'especie' => ''];

    // EXCLUIR (via POST)
    if (isset($_POST['excluir'])) {
        $id = (int) $_POST['idcamun'];

        if ($id === (int) $_SESSION['idcamun']) {
            $erro = 'Você não pode excluir o seu próprio usuário.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM camundongos WHERE idcamun = :idcamun");
            $stmt->execute(['idcamun' => $id]);
            header('Location: camundongos.php');
            exit;
        }
    }

    // SALVAR (cadastrar ou editar)
    if (isset($_POST['salvar'])) {
        $id    = (int) ($_POST['idcamun'] ?? 0);
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
                        "INSERT INTO camundongos (quantidade, especie)
                         VALUES (:quantidade, :especie)"
                    );
                    $stmt->execute([
                        'quantidade'  => $quantidade,
                        'especie' => $especie,
                    ]);
                } elseif ($especie !== '') {
                    // Edição trocando a especie
                    $stmt = $pdo->prepare(
                        "UPDATE camundongos
                         SET quantidade = :quantidade, especie = :especie
                         WHERE idcamun = :idcamun"
                    );
                    $stmt->execute([
                        'quantidade'  => $quantidade,
                        'especie' => $especie,
                        'idcamun'    => $id
                    ]);
                } else {
                    // Edição mantendo a senha atual
                    $stmt = $pdo->prepare(
                        "UPDATE camundongos SET quantidade = :quantidade, especie = :especie WHERE idcamun = :idcamun"
                    );
                    $stmt->execute([
                        'quantidade'  => $quantidade,
                        'especie' => $especie,
                        'idcamun'    => $id
                    ]);
                }

                header('Location: camundongos.php');
                exit;

            } catch (PDOException $e) {
                // 23000 = violação de UNIQUE (e-mail repetido)
                $erro = ($e->getCode() === '23000')
                    ? 'Esta especie já está cadastrada.'
                    : 'Erro ao salvar o camundongo.';
            }
        }

        // Em caso de erro, mantém o que a pessoa digitou (menos a senha)
        $form = ['idcamun' => $id ?: '', 'quantidade' => $quantidade, 'especie' => $especie];

    // CARREGAR para edição (clicou em "Editar")
    } elseif (isset($_GET['editar'])) {
        $stmt = $pdo->prepare("SELECT idcamun, quantidade, especie FROM camundongos WHERE idcamun = :idcamun");
        $stmt->execute(['idcamun' => (int) $_GET['editar']]);
        $encontrado = $stmt->fetch();

        if ($encontrado) {
            $form = $encontrado;
        }
    }

    // LISTAR
    $camundongos = $pdo->query("SELECT idcamun, quantidade, especie FROM camundongos ORDER BY idcamun ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar camundongos</title>
</head>
<body>

<h1>Gerenciar camundongos</h1>
<p><a href="index.php">Voltar ao menu</a></p>

<?php if ($erro !== ''): ?>
    <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
<?php endif; ?>

<h2><?= $form['idcamun'] ? 'Editar usuário' : 'Cadastrar camundongo' ?></h2>

<form action="camundongos.php" method="POST">
    <input type="hidden" name="idcamun" value="<?= htmlspecialchars($form['idcamun']) ?>">

    <label>Quantidade:</label>
    <input type="text" name="quantidade" placeholder="Digite a quantidade"
           value="<?= htmlspecialchars($form['quantidade']) ?>" required>
    <br><br>

    <label>Especie:</label>
    <input type="especie" name="especie" placeholder="Digite a especie"
           value="<?= htmlspecialchars($form['especie']) ?>" required>
    <br><br>


    <button type="submit" name="salvar">Salvar</button>
    <a href="camundongos.php">Limpar</a>
</form>

<h2>Camundongos cadastrados</h2>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Quantidade</th>
        <th>Especie</th>
        <th>Ações</th>
    </tr>

    <?php foreach ($camundongos as $u): ?>
        <tr>
            <td><?= $u['idcamun'] ?></td>
            <td><?= htmlspecialchars($u['quantidade']) ?></td>
            <td><?= htmlspecialchars($u['especie']) ?></td>
            <td>
                <a href="camundongos.php?editar=<?= $u['idcamun'] ?>">Editar</a>

                <form action="camundongos.php" method="POST" style="display:inline;"
                      onsubmit="return confirm('Excluir este usuário?');">
                    <input type="hidden" name="idcamun" value="<?= $u['idcamun'] ?>">
                    <button type="submit" name="excluir">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>