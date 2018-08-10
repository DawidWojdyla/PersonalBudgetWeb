<?php
	
	session_start();
	
	if (!isset($_SESSION['successfulRegistration'])) 
	{
		header('Location: index.php');
		exit();
	}
	else
	{
		unset ($_SESSION['successfulRegistration']);
	}

	if (isset($_SESSION['nameSes'])) unset($_SESSION['nameSes']);
	if (isset($_SESSION['emailSes'])) unset($_SESSION['EmailSes']);
	if (isset($_SESSION['passwordSes'])) unset($_SESSION['passwordSes']);

	
	//Usuwanie błędów rejestracji
	if (isset($_SESSION['nameError'])) unset($_SESSION['nameError']);
	if (isset($_SESSION['emailError'])) unset($_SESSION['emailError']);
	if (isset($_SESSION['passwordError'])) unset($_SESSION['passwordError']);

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
		<div class="row">
			<div class="col-sm-5">
				<main>
					<div id="successfulRegistration">
						Rejestracja przebiegła pomyślnie, możesz się teraz zalogować na swoje konto! :)
					</div>
				</main>
			</div>
			<div class="col-sm-7" style="padding:0px;">	
				<aside>
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