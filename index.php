<?php
	
	session_start();
	
	if ( isset($_SESSION['isLogged'])&&($_SESSION['isLogged'] == true) ) 
	{
		header('Location: menu.php');
		exit();
	}
	
	if (isset($_POST['email']))
	{
		
		$isAllOk = true;
		
		$name=$_POST['name'];
		
		if (strlen($name)<3 || strlen($name)>15)
		{
			$isAllOk = false;
			$_SESSION['nameError']= "Imię musi zawierać 3 - 15 znaków!";
		}
		
		
		$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
		
		if (empty($email))
		{
			$isAllOk = false;
			$_SESSION['emailError']= "Podaj poprawny adres e-mail!";
		}

		
		$password = $_POST['password'];
		
		if (strlen($password) < 8 || strlen($password) >20)
		{
			$isAllOk = false;
			$_SESSION['passwordError']= "Hasło musi zawierać 8-20 znaków!";
		}
		
		$hashPassword = password_hash($password, PASSWORD_DEFAULT);
		
		$_SESSION['nameSes'] = $name;
		$_SESSION['emailSes'] = $_POST['email'];
		$_SESSION['passwordSes'] = $password;

		if ($isAllOk)
		{
			require_once 'database.php';
	
			$query = $database -> prepare ('SELECT * FROM users WHERE email=:email');
			$query -> bindValue(':email', $email, PDO::PARAM_STR);
			$query -> execute();
				
				if ($query-> rowCount())
				{
					$_SESSION['emailError']= "Istnieje już konto przypisane do tego adresu e-mail";
				}
				else
				{
					$query = $database -> prepare ('INSERT INTO users VALUES (NULL, :name, :hashPassword, :email)');
					$query -> bindValue(':name', $name, PDO::PARAM_STR);
					$query -> bindValue(':hashPassword', $hashPassword, PDO::PARAM_STR);
					$query -> bindValue(':email', $email, PDO::PARAM_STR);
					$query -> execute();
					
					$query = $database -> prepare ('SELECT * FROM users WHERE email=:email');
					$query -> bindValue(':email', $email, PDO::PARAM_STR);
					$query -> execute();
					
					$user = $query -> fetch();
						
					$query = $database -> query ("ALTER TABLE expenses_category_assigned_to_users ALTER user_id SET DEFAULT {$user['id']}");
					$query = $database -> query ("ALTER TABLE incomes_category_assigned_to_users ALTER user_id SET DEFAULT {$user['id']}");
					$query = $database -> query ("ALTER TABLE payment_methods_assigned_to_users ALTER user_id SET DEFAULT {$user['id']}");
								
					$query = $database -> query ("INSERT INTO expenses_category_assigned_to_users (name, position) SELECT name, position FROM expenses_category_default");
					$query = $database -> query ("INSERT INTO incomes_category_assigned_to_users (name, position) SELECT name, position FROM incomes_category_default");
					$query = $database -> query ("INSERT INTO payment_methods_assigned_to_users (name, position) SELECT name, position FROM payment_methods_default");	

					if (isset($_SESSION['nameSes'])) unset($_SESSION['nameSes']);
					if (isset($_SESSION['emailSes'])) unset($_SESSION['EmailSes']);
					if (isset($_SESSION['passwordSes'])) unset($_SESSION['passwordSes']);
					
					if (isset($_SESSION['nameError'])) unset($_SESSION['nameError']);
					if (isset($_SESSION['emailError'])) unset($_SESSION['emailError']);
					if (isset($_SESSION['passwordError'])) unset($_SESSION['passwordError']);
					
					$_SESSION['successfulRegistration'] = "Rejestracja przebiegła pomyślnie, możesz się teraz zalogować na swoje konto! :)";
				}
		}
	}

?>

<!DOCTYPE HTML>
<html lang="pl">

<head>
	<meta charset="UTF-8">
	<meta name="description" content="Aplikacja internetowa do prowadzenia budżetu osobistego, która pomaga w zapanowaniu nad wydatkami. Dzięki niej zaoszczędzisz pieniądze, by następnie móc spełniać marzenia :)" />
	<meta name="keywords" content="budżet, pieniądze, wydatki, pieniądze, dochód, rozliczenie, kontrola budżetu, portfel, przychód, oszczędzanie, bilans, płatności, budżet osobisty" />
	<meta http-equiv=X-UA-Compatible content="IE=edge" />	
	
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
		<title>Budżet osobisty - rejestracja</title>
		
		<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	
	<link href="style.css"  type="text/css"  rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Lato:400,700&amp;subset=latin-ext" rel="stylesheet">
	<link rel="stylesheet"  href="css/fontello.css" type="text/css"/>

</head>

<body>

	<div class="container-fluid">
		<header>
			<div class="rectangle">
				<div class="row">
					<div class="col-sm-6" id="logo">
						BUDŻET<i class="icon-wallet"></i>osobisty
					</div>
					<div class="col-sm-6" id="login">
						<form action="login.php" method="post" class="form-inline">
							<div class="form-group">
								<input class="myLoginInputs" type="email" name="logEmail" placeholder="adres e-mail">
							</div>
							<div class="form-group">
								<input class="myLoginInputs" type="password" name="logPassword" placeholder="hasło">
							</div>
								<div class="form-group">
								<input class="myLoginInputs" type="submit" value="Zaloguj">
							</div>
						</form>
					</div>
				</div>
			</div>
		</header>
		
<?php
	
		if (isset($_SESSION['loginError']))
		{
			echo $_SESSION['loginError'];
			unset($_SESSION['loginError']);
		}
?>
		<div class="row">
			<div class="col-sm-5">
				<main>
					<div class="registerForm">
						<form  method="post">
							<h1>Rejestracja</h1>
							<div class="form-group">
								<input class="myRegisterInputs" type="text" name="name"  
								<?PHP
								if (isset($_SESSION['nameSes']))
								{
									echo 'value="'.$_SESSION['nameSes'].'"';
									unset($_SESSION['nameSes']);
								}
								?> placeholder="imię"> 
								
								<?php
								if (isset($_SESSION['nameError']))
								{
									echo '</br><div class="option error">'.$_SESSION['nameError'].'</div>';
									unset($_SESSION['nameError']);
								}
							?>
							</div>
							<div class="form-group">
								<input class="myRegisterInputs" type="email" name="email"  
								<?PHP
										if (isset($_SESSION['emailSes']))
											{
												echo 'value="'.$_SESSION['emailSes'].'"';
												unset($_SESSION['emailSes']);
											}
		
									?> placeholder="adres e-mail">
								<?php
								if (isset($_SESSION['emailError']))
								{
									echo '</br><div class="option error">'.$_SESSION['emailError'].'</div>';
									unset($_SESSION['emailError']);
								}
							?>
							</div>
							<div class="form-group">
								<input class="myRegisterInputs" type="password" name="password" 
								<?PHP
										if (isset($_SESSION['passwordSes']))
											{
												echo 'value="'.$_SESSION['passwordSes'].'"';
												unset($_SESSION['passwordSes']);
											}
									?>
								placeholder="hasło" >
								
								<?php
								if (isset($_SESSION['passwordError']))
								{
									echo '</br><div class="option error">'.$_SESSION['passwordError'].'</div>';
									unset($_SESSION['passwordError']);
								}
							?>
							</div>
							<div class="form-group">
								<input class="myRegisterInputs" type="submit" value="Zarejestruj">
							</div>
						</form>
					</div>
				</main>
			</div>
			<div class="col-sm-7" style="padding:0px;">	
				<aside>
					<?php
								
							if(isset($_SESSION['successfulRegistration'])) 
							{
								echo '<div class="option error" style="margin-bottom:10px;">'.$_SESSION['successfulRegistration'].'</div>';
								unset($_SESSION['successfulRegistration']);
							}
						?>
					<div id="sideLogo">
						<div class="icons">
							<i class="icon-dollar-1"></i>
						</div>
						<div class="icons">
							<i class="icon-calculator"></i>	
							<i class="icon-check-1"></i>
							<i class="icon-chart-pie"></i>
						</div>
						<div class="icons">
							<i class="icon-credit-card"></i>
						</div>		
					</div>
				</aside>
			</div>
		</div>
	</div>	

</body>

</html>