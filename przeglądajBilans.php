<?php

session_start();
	
if ( !isset($_SESSION['isLogged']) || !$_SESSION['isLogged'])
{
	header('Location: index.php');
	exit();
}



//$firstDayOfPreviousMonthDate = date('Y-m-d', mktime(0, 0, 0, date('m') -1 , 1 , date('Y'))); 
//$lastDayOfPreviousMonthDate = date('Y-m-d', strtotime('last day of previous month'));
								

if (isset($_POST['custom']))
{
	$_SESSION['isCustomSelected'] = true;
}
else if (isset($_POST['previousMonth']))
{
	$_SESSION['dateFrom'] = date('Y-m-d', strtotime('first day of previous month'));
	$_SESSION['dateTo'] = date('Y-m-d', strtotime('last day of previous month'));
	$_SESSION['whatPeriod'] = "Poprzedni miesiąc";
}
else if (isset($_POST['thisYear']))
{
	$_SESSION['dateFrom'] = date('Y-01-01');
	$_SESSION['dateTo'] = date('Y-m-d');
	$_SESSION['whatPeriod'] = "Bieżący rok";
}
else if ( isset($_POST['okay']))
{
	$_SESSION['dateFromSes'] = $_POST['dateFrom'];
	$_SESSION['dateToSes'] = $_POST['dateTo'];
	
	if ($_POST['dateFrom'] =="" || $_POST['dateTo']=="")
	{
		$_SESSION['dateError'] = "Musisz podać zakres dat!";
	}
	else
	{
		$_SESSION['dateFrom'] = $_POST['dateFrom'];
		$_SESSION['dateTo'] = $_POST['dateTo'];
	}
}
else if (isset($_POST['cancel']))
{
	if (isset($_SESSION['dateFromSes']) && $_SESSION['dateFromSes']!="" && $_SESSION['dateToSes']!=""  )
	{
	$_SESSION['dateTo'] = date('Y-m-d');
	$_SESSION['dateFrom'] = date('Y-m-01');
	}
	unset($_SESSION['isCustomSelected']);
	unset($_SESSION['dateFromSes']);
	unset($_SESSION['dateToSes']);
	unset($_SESSION['dateFromSes']);
	unset($_SESSION['dateToSes']);
}
else
{
	$_SESSION['dateTo'] = date('Y-m-d');
	$_SESSION['dateFrom'] = date('Y-m-01');
	$_SESSION['whatPeriod'] = "Bieżący miesiąc";
}


require_once 'database.php';

$incomesQuery = $database->query("SELECT SUM(incomes.amount) as categorySum, incomes_category_assigned_to_users.name FROM incomes, incomes_category_assigned_to_users WHERE incomes.user_id={$_SESSION['loggedId']}  AND incomes_category_assigned_to_users.id=incomes.income_category_assigned_to_user_id AND incomes.date BETWEEN '{$_SESSION['dateFrom']}' AND '{$_SESSION['dateTo']}' GROUP BY income_category_assigned_to_user_id");
									
$incomes = $incomesQuery->fetchAll();

$expensesQuery = $database->query("SELECT SUM(expenses.amount) as categorySum, expenses_category_assigned_to_users.name FROM expenses, expenses_category_assigned_to_users WHERE expenses.user_id={$_SESSION['loggedId']}  AND expenses_category_assigned_to_users.id=expenses.expense_category_assigned_to_user_id AND expenses.date BETWEEN '{$_SESSION['dateFrom']}' AND '{$_SESSION['dateTo']}' GROUP BY expense_category_assigned_to_user_id ORDER BY categorySum DESC");
									
$expenses = $expensesQuery->fetchAll();

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
		
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		
		<script type="text/javascript">
		  google.charts.load('current', {'packages':['corechart']});
		  google.charts.setOnLoadCallback(drawChart);

		  function drawChart() {

			var data = google.visualization.arrayToDataTable([
			  ['Wydatki', 'PLN per period']<?PHP
			  foreach ($expenses as $expense) 
			  {
				  echo ", ['{$expense['name']}', {$expense['categorySum']}]";
			  }
			?>]);

			var options = {
			  'backgroundColor': 'transparent', 'width': 340, 'height':150, 'forceIFrame':true, 'chartArea':{width:'100%',height:'100%'}, 'sliceVisibilityThreshold': 0, 'legend': {position: 'labeled'}, 'pieSliceText':'label'
			};

			var chart = new google.visualization.PieChart(document.getElementById('piechart'));

			chart.draw(data, options);
		  }
		  
		</script>
		
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
				<main>
					<div class="col-sm-8" style="padding:5px;">
						<div id="dropdownMenuContainer">
							
						<?PHP
						
							if (isset($_SESSION['isCustomSelected']) && $_SESSION['isCustomSelected'] == true)
							{
								echo '<div class="row">
												<div class="col-sm-8" style="margin-top:8px;">
													<form method="post">
														<div class="customPeriod">
															<input type="date" id="dateGetting" min="1999-01-01" name="dateFrom" ';
															if(isset($_SESSION['dateFromSes']))
															{
																echo 'value="'.$_SESSION['dateFromSes'].'" ';
															}
															echo '>-
															<input type="date" id="dateGetting" min="1999-01-01" name="dateTo" ';
															if(isset($_SESSION['dateToSes']))
															{
																echo 'value="'.$_SESSION['dateToSes'].'" ';
															}
															echo '>
														</div>';
												
													if (isset($_SESSION['dateError']))
													{
														echo '<div class="error text-center">'.$_SESSION['dateError']."</div>";
														unset($_SESSION['dateError']);
													}
								  echo '</div>
											<div class="col-sm-2 col-xs-6" style="padding-right:1;">
												<div class="customPeriod" style="text-align:right;">													
													<input type="submit" id="add" name="okay" value="Pokaż">  															
												</div>
											</div>
													</form>
												<div class="col-sm-2 col-xs-6" style="padding-left:1;">
													<form method="post">
														<div class="customPeriod" style="text-align:left;">	
															<input type="submit" id="cancel" name="cancel" value="Anuluj">
														</div>
													</form>
												</div>
											</div>';
							}
							else
							{
								echo '<div class="dropdownMenu">
												<button class="dropButton">'.$_SESSION['whatPeriod'].
												'</button>
												<form method="post">
													<div id="periods">
														<input type="submit" name="thisMonth" class="period" value="Bieżący miesiąc">
														<input type="submit" name="previousMonth" class="period" value="Poprzedni miesiąc">
														<input type="submit" name="thisYear" class="period" value="Bieżący rok">
														<input type="submit" name="custom" class="period" value="Niestandardowy">	
													</div>
												</form>
											</div>';
							}
						?>
							</form>
							<div style="clear:both"></div>
						</div>
						<div id="tableContainer">
							<section>
								<div id="tableHead">
									Przychody
								</div>
								<table class="balanceTable"><?PHP
										$allIncomesSum = 0;
									if ($incomesQuery-> rowCount())
										{
											foreach ($incomes as $income) 
											{
												$allIncomesSum+= $income['categorySum'];
												echo '<tr><td>'.$income['name'].'</td><td><div class="text-right">'.number_format($income['categorySum'], 2,'.',' ').'</div></td></tr>';
											}
											echo '<tr><td>Razem</td><td><div class="text-right"">'.number_format($allIncomesSum, 2,'.',' ').'</div></td></tr>';
										}
										else
										{
											echo '<div class="text-center option">Nie masz dodanych żadnych przychodów w tym okresie!</div>';
										}
									?>
								</table>
							</section>
							<section>
								<div id="tableHead">
									Wydatki
								</div>
								<table class="balanceTable"><?PHP
									
										$allExpensesSum = 0;	
										if ($expensesQuery-> rowCount())
										{
											foreach ($expenses as $expense) 
											{
												$allExpensesSum+= $expense['categorySum'];
												echo '<tr><td>'.$expense['name'].'</td><td><div class="text-right">'.number_format($expense['categorySum'], 2,'.',' ').'</div></td></tr>';
											}
											echo '<tr><td>Razem</td><td><div class="text-right"">'.number_format($allExpensesSum, 2,'.',' ').'</div></td></tr>';
											echo '</table><table class="balanceTable"><div class="text-center"><div id="piechart"></div></div>';
										}
										else
										{
											echo '<div class="text-center option">Nie masz dodanych żadnych wydatków w tym okresie!</div>';
										}
									?>
								</table
							</section>
							<section>
								<div id="tableHead">
									Bilans
								</div>
								<div class="balanceTable">
									<div class="balance">
										<?PHP
										$balance = $allIncomesSum - $allExpensesSum;
										echo '<div class="text-center">'.number_format($balance, 2,'.',' ').' PLN';
											if($balance > 0)
												echo '<div class="depiction">Gratulacje. Świetnie zarządzasz finansami!</div>';
											else if($balance < 0)
												echo '<div class="depiction">Uważaj, wpadasz w długi!</div>';
											
										?>
										</div>
									</div>
								</div>
							</section>
						</div>	
					</div>
				</main>
			</div>	
		</div>

	</body>

</html>