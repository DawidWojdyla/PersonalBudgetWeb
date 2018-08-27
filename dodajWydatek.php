<?php

session_start();
	
if ( !isset($_SESSION['isLogged']) || !$_SESSION['isLogged'])
{
	header('Location: index.php');
	exit();
}

$todaysDate = date('Y-m-d');

require_once 'database.php';

$categoryQuery = $database->query("SELECT  * FROM expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']}");
$categories = $categoryQuery ->fetchAll();

$paymentMethodQuery = $database->query("SELECT  * FROM payment_methods_assigned_to_users WHERE user_id={$_SESSION['loggedId']}");
$paymentMethods = $paymentMethodQuery ->fetchAll();

if (isset($_POST['amount']))
{
	$amount = $_SESSION['amountSes'] = $_POST['amount'];
	
	$isAllOk = true;
	
	$isNumber = true;
	$amount = str_replace(",",".",$amount);
	$amountInArray = str_split($amount);
	$find = array('0','1','2','3','4','5','6','7','8','9','.');
	
	foreach ($amountInArray as $digit) 
	{
		if (!in_array($digit, $find)) 
		{
			$isNumber = false;
			break;
		}
	}

	if (substr_count($amount, ".") >1 || !$isNumber)
	{
		$isAllOk = false;
		$_SESSION ['amountError'] = "Wprowadź poprawną kwotę!";
	}
	
		if ($_POST['date'] =="")
	{
		$isAllOk = false;
		$_SESSION['dateError'] = "Musisz podać datę!";
	}
	else
	{
		
		$_SESSION['dateSes'] =$_POST['date'];
		
		$dateDifference = (strtotime($todaysDate) - strtotime($_POST['date'])) / (60*60*24);
		
		 if ($dateDifference > 90)
		{
			$isAllOk = false;
			$_SESSION['dateError'] = "Możesz dodać przychód maksymalnie sprzed 90 dni!";
		}  
		else if ($_POST['date'] > $todaysDate)
		{
			$isAllOk = false;
			$_SESSION['dateError'] = "Nie mów hop Panie Marty Mcfly! ;)";
		} 	
	}
	
		if (!isset($_POST['paymentMethod']))
	{
		$isAllOk = false;
		$_SESSION['paymentMethodError'] = "Zaznacz odpowiednią metodę płatności!";
	}
	else $_SESSION['paymentMethodSes'] = $_POST['paymentMethod'];
	
	if (!isset($_POST['category']))
	{
		$isAllOk = false;
		$_SESSION['categoryError'] = "Zaznacz odpowiednią kategorię!";
	}
	else $_SESSION['categorySes'] = $_POST['category'];
	
	
	if (isset ($_POST['comment']))
		$_SESSION['commentSes'] = $_POST ['comment'];
	
	if ($isAllOk)
	{
		$query = $database -> query ("INSERT INTO expenses (id, user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date, comment) VALUES (NULL, {$_SESSION['loggedId']}, {$_POST['category']}, {$_POST['paymentMethod']}, {$amount}, '{$_POST['date']}', '{$_POST['comment']}')");
		
		unset ($_SESSION['amountError']);
		unset ($_SESSION['dateError']);
		unset ($_SESSION['categoryError']);
		unset ($_SESSION['paymentMethodError']);
		
		unset ($_SESSION['amountSes']);
		unset ($_SESSION['dateSes']);
		unset ($_SESSION['paymentMethodSes']);
		unset($_SESSION['categorySes']);
		unset ($_SESSION['commentSes']);
		
		$_SESSION['upgrade'] = "Dodano nowy wydatek!";
		
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

	<body style="margin-top: -20px;">
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
				<div class="col-md-4" style="padding:5px;">
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
				<div class="col-md-8" style="padding:5px;">	
					<main>
					<?PHP
									if(isset($_SESSION['upgrade']))
									{
										echo '<div class="option error" style="margin-bottom:10px;">'.$_SESSION['upgrade'].'</div>';
										//echo '<script>alert("'.$_SESSION['changeInfo'].'");</script>';
										unset($_SESSION['upgrade']);
										echo "<script type=\"text/javascript\">window.setTimeout(\"window.location.replace('menu.php');\",1800);</script>"; 
									}
					?>
						<div id="tableContainer">
							<form method="post">
								<div class="tableHead">
									Dodaj wydatek
								</div>
								<table class="expenseTable">
									<tr>
										<td>
											<div class="attributes">
												Kwota:
											</div>
										</td>
										<td>
											<input class="amountGetting" name="amount" type="text" value="<?PHP 
												if (isset($_SESSION['amountSes']))
												{
													echo $_SESSION['amountSes'];
													unset ($_SESSION['amountSes']);
												}
													echo '">PLN';
												
													if (isset($_SESSION['amountError']))
													{
														echo '<div class="option error">'.$_SESSION['amountError'].'</div>';
														unset($_SESSION['amountError']);
													}
									?></td>
									</tr>
									<tr>
										<td>
											<div class="attributes">
												Data:
											</div>
										</td>
										<td>
											<input id="dateGetting" name="date" type="date" value="<?PHP
											
													if (isset($_SESSION['dateSes']))
														echo $_SESSION['dateSes'].'">';
													else echo $todaysDate.'">';
												
													if (isset($_SESSION['dateError']))
													{
														echo '<div class="option error">'.$_SESSION['dateError'].'</div>';
														unset($_SESSION['dateError']);
													}
									?></td>
									</tr>
									<tr>
										<td>
											<div class="attributes">
												Sposób płatności:
											</div>
										</td>
										<td><?PHP
													foreach ($paymentMethods as $paymentMethod) 
													{
														echo '<div class="option"><label><input type="radio" name="paymentMethod" value="'.$paymentMethod['id'].'"';
														if (isset($_SESSION['paymentMethodSes']) && $_SESSION['paymentMethodSes'] == $paymentMethod['id'])
														{
															echo ' checked="checked"';
															unset ($_SESSION['paymentMethodSes']);
														}
														echo '>'."{$paymentMethod['name']}</label></div>";
													}
													if (isset($_SESSION['paymentMethodError']))
													{
														echo '<div class="option error">'.$_SESSION['paymentMethodError'].'</div>';
														unset($_SESSION['paymentMethodError']);
													}
									?></td>
									</tr>
									<tr>
										<td>
											<div class="attributes">
											Kategoria:
											</div>
										</td>
										<td><?PHP
													foreach ($categories as $category) 
													{
														echo '<div class="option"><label><input type="radio" name="category" value="'.$category['id'].'"';
														if (isset($_SESSION['categorySes']) && $_SESSION['categorySes'] == $category['id'])
														{
															echo ' checked="checked"';
															unset ($_SESSION['categorySes']);
														}
														echo '>'."{$category['name']}</label></div>";
													}
													if (isset($_SESSION['categoryError']))
													{
														echo '<div class="option error">'.$_SESSION['categoryError'].'</div>';
														unset($_SESSION['categoryError']);
													}
									?></td>
									</tr>
									<tr>
										<td>
											<div class="attributes">
												Komentarz
												<div style="font-size:14px">
													(opcjonalnie)
												</div>
											</div>
										</td>
										<td>
											<input class="commentGetting" name="comment" type="text"<?PHP
												if (isset($_SESSION['commentSes']))
												{
													echo ' value="'.$_SESSION['commentSes'].'"';
													unset ($_SESSION['commentSes']);
												}
												?>>
										</td>
									</tr>
								</table>
								<div class="buttons">
									<input type="submit" class="add" value="Dodaj">
									<input class="cancel" value="Anuluj"  type="<?PHP 
										if (isset($isAllOk) && !$isAllOk)
										{
											echo 'button" onClick="window.location.href=window.location.href"';
										}
										else
										{
											echo 'reset"';
										}
									?>>
								</div>
							</form>
						</div>
					</main>
				</div>	
			</div>	
		</div>

	</body>

</html>