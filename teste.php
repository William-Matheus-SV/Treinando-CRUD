<?php
require_once 'conexao.php';

$email = 'natally@email.com';
$senha = '1234';

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$u = $stmt->fetch();

var_dump($u ? 'usuario encontrado' : 'usuario NAO encontrado');
var_dump($u ? strlen($u['senha']) : null);
var_dump($u ? substr($u['senha'], 0, 7) : null);
var_dump($u ? password_verify($senha, $u['senha']) : null);