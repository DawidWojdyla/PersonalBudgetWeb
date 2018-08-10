<?php

session_start();
	
if ((!isset($_POST['logEmail'])) || (!isset($_POST['logPassword'])))
{
	header('Location: index.php');
	exit();
}

$email = filter_input(INPUT_POST, 'logEmail', FILTER_VALIDATE_EMAIL);
if (empty($email))
{
	$_SESSION['loginError'] = '<span style="color:red">Nieprawidłowy adres e-mail!</span>';
	header('Location: index.php');
	exit();
}
	

$password = filter_input(INPUT_POST, 'logPassword');

require_once 'database.php';

$userQuery = $database->prepare('SELECT  * FROM users WHERE email=:email');
$userQuery -> bindValue(':email', $email, PDO::PARAM_STR);
$userQuery -> execute();

$user = $userQuery -> fetch();

if ($user && password_verify($password, $user['password']))
{
	$_SESSION['isLogged'] = true;			
	$_SESSION['loggedId'] = $user['id'];
	//$_SESSION ["loggedName"] = $user['username'];	
	
	unset($_SESSION['loginError']);
	header('Location: menu.php');
}
else
{
	$_SESSION['loginError'] = '<span style="color:red">Nieprawidłowy login lub hasło!</span>';
	header('Location: index.php');
}
?>