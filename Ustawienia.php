<?php

session_start();
	
if(!isset($_SESSION['isLogged']))
{
	header("Location: index.php");
	exit();
}
	
require_once 'database.php';

$userQuery = $database->query("SELECT  * FROM users WHERE id={$_SESSION['loggedId']}");

$userData = $userQuery ->fetch();

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
					<div class="col-sm-8" style="padding:0px;">	
						<main>
						<div id="tableContainer">
							<form method="post">
								<div id="tableHead">
									Edycja danych
								</div>
								<table id="expenseTable">
									<tr>
										<td>
											<div class="attributes">imię</div>
										</td>
										<td>
											<div class="option">
												<a href='#'>
											<?PHP
												echo $userData['username'];
												?>
												</a>
											</div>
											<div class="edit"><input class="commentGetting" type="text" /></div>
						
										</td>
									</tr>
									<tr>
										<td>
											<div class="attributes">e-mail</div>
										</td>
										<td>
											<div class="option">
												<a href='#'>
												<?PHP
													echo $userData['email'];
													?>
												</a>
											</div>
											<div class="edit"><input class="commentGetting" type="text" /></div>
										</td>
									</tr>
										<tr>
										<td>
											<div class="attributes">hasło</div>
										</td>
										<td>
											<div class="option">
												<div class="edit"><input class="amountGetting" type="password" /></div>
												<div class="edit"><input class="commentGetting" type="password" /></div>
												<div class="edit"><input class="commentGetting" type="password" /></div>
											</div>
										</td>
									</tr>
										<tr>
										<td>
											<div class="attributes">kategorie</div>
										</td>
										<td>
											<div class="option">
											<?PHP
												
												?>
											</div>
										</td>
									</tr>
								</table>
							</form>
						</div>
					</main>
					</div>
			</div>		
		</div>
	</body>

</html>