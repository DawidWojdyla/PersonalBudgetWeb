<?php

	session_start();
	
	if(!isset($_SESSION['isLogged']))
	{
		header("Location: index.php");
		exit();
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
						<div class="col-sm-3 col-sm-offset-3" id="login">
							<a href="logout.php"><input class="form-group myLoginInputs" type="submit" value="Wyloguj"></a>
						</div>
					</div>
				</div>
				<?PHP
					if (isset($_SESSION['upgrade']))
					{
						echo '<div class="info">'.$_SESSION['upgrade'].'</div>';
						unset ($_SESSION['upgrade']);
					}
					?>
			</header>
			<div class="row">
				<div class="col-sm-4" style="padding:5px;">
					<nav>
						<div class="menu">
							<ul>
								<li><a href="dodajPrzychod.php">Dodaj przychód</a></li>
								<li><a href="dodajWydatek.php">Dodaj wydatek</a></li>
								<li><a href="przeglądajBilans.php">Przeglądaj bilans</a></li>
								<li><a href="ustawienia.php">Ustawienia</a></li>
							</ul>
						</div>
					</nav>
				</div>
				<aside>
					<div class="col-sm-8" style="padding:0px;">	
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
					</div>
				</aside>
			</div>		
		</div>
	</body>

</html>