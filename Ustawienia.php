<?php

session_start();
	
if(!isset($_SESSION['isLogged']))
{
	header("Location: index.php");
	exit();
}
	
require_once 'database.php';

$userQuery = $database->query("SELECT * FROM users WHERE id={$_SESSION['loggedId']}");
$userData = $userQuery ->fetch();

$categoryQuery = $database->query("SELECT id, name, position FROM incomes_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name <>'Inne' ORDER BY position");
$incomeCategoriesAmount = $categoryQuery ->rowCount();
$incomesCategories = $categoryQuery ->fetchAll();

$categoryQuery = $database->query("SELECT id, name, position FROM expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name <>'Inne' ORDER BY position");
$expenseCategoriesAmount = $categoryQuery ->rowCount();
$expenseCategories = $categoryQuery ->fetchAll();


//zmiana nazwy kategorii
if(isset($_POST['newName']) && $_POST['newName'] != '')
{
	if($_POST['typeOfCategory'] == 'i')
	{
			$database->query("UPDATE  incomes_category_assigned_to_users SET name='{$_POST['newName']}' WHERE user_id={$_SESSION['loggedId']} AND id={$_POST['categoryId']}");
	}
	else
	{
		$database->query("UPDATE  expenses_category_assigned_to_users SET name='{$_POST['newName']}' WHERE user_id={$_SESSION['loggedId']} AND id={$_POST['categoryId']}");
	}
	$_SESSION['changeInfo'] = "Pomyślnie zmieniono nazwę kategorii!";
}
//usuwanie kategorii
else if(isset($_POST['typeOfCategory']))
{
	if($_POST['typeOfCategory'] == 'i')
	{
		
		//nazwa kategorii usuwanej
		$query = $database->query("SELECT name, position FROM incomes_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND id={$_POST['categoryId']}");
		$category = $query ->fetch();
		
		//wstawia nazwe do komentarza
		$database->query("UPDATE incomes SET comment='{$category['name']}' WHERE user_id={$_SESSION['loggedId']} AND income_category_assigned_to_user_id={$_POST['categoryId']}");
		
		//pobiera id kategorii 'inne'
		$query = $database->query("SELECT id FROM incomes_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='Inne'");
		$otherCategoryData = $query ->fetch();
		
		//zmienia id kategorii usuwanej na id kategorii 'inne'
		$database->query("UPDATE incomes SET income_category_assigned_to_user_id={$otherCategoryData['id']} WHERE user_id={$_SESSION['loggedId']} AND income_category_assigned_to_user_id={$_POST['categoryId']}");
		
		//zmienia wartość pozycji dla kategorii, które są na wyższej pozycji od usuwanej 
		$database->query("UPDATE incomes_category_assigned_to_users SET position=position-1 WHERE user_id={$_SESSION['loggedId']} AND  position>{$category['position']}");
		
		//usuwa kategorię
		$database->query("DELETE FROM incomes_category_assigned_to_users WHERE id={$_POST['categoryId']}");
	}
	else
	{
		$query = $database->query("SELECT name, position FROM expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND id={$_POST['categoryId']}");
		$category = $query ->fetch();
		
		$database->query("UPDATE expenses SET comment='{$category['name']}' WHERE user_id={$_SESSION['loggedId']} AND expense_category_assigned_to_user_id={$_POST['categoryId']}");
		
		$query = $database->query("SELECT id FROM expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='Inne'");
		$otherCategoryData = $query ->fetch();
		
		$database->query("UPDATE expenses SET expense_category_assigned_to_user_id={$otherCategoryData['id']} WHERE user_id={$_SESSION['loggedId']} AND expense_category_assigned_to_user_id={$_POST['categoryId']}");
		
		$database->query("UPDATE expenses_category_assigned_to_users SET position=position-1 WHERE  user_id={$_SESSION['loggedId']} AND position>{$category['position']}");

		$database->query("DELETE FROM expenses_category_assigned_to_users WHERE id={$_POST['categoryId']}");
	}
	
$_SESSION['changeInfo'] = "Pomyślnie usunięto wybraną kategorię!";
}

else if (isset($_POST['newIncomeCategory']) && $_POST['newIncomeCategory'] != '')
{
	$newCategoryName = ucfirst(strtolower($_POST['newIncomeCategory']));
	$query = $database->query("SELECT name from incomes_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='{$newCategoryName}'");
	
	if ($query-> rowCount())
	{
		$_SESSION['changeInfo'] = "Podana nazwa kategorii istnieje!";
	}
	else
	{
		$query = $database->query("SELECT position FROM incomes_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='Inne'");
		$otherCategoryData = $query ->fetch();
		
		$database->query("INSERT INTO incomes_category_assigned_to_users (id, user_id, name, position) VALUES (NULL, {$_SESSION['loggedId']}, '{$newCategoryName}', {$otherCategoryData['position']})");
		
		$query = $database->query("UPDATE incomes_category_assigned_to_users SET position=position+1  WHERE user_id={$_SESSION['loggedId']} AND name='Inne'");
		
		$_SESSION['changeInfo'] = "Dodano nową kategorię!";
	}
}
else if (isset($_POST['newExpenseCategory']) && $_POST['newExpenseCategory'] != '')
{
	$newCategoryName = ucfirst(strtolower($_POST['newExpenseCategory']));
	$query = $database->query("SELECT name from expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='{$newCategoryName}'");
	if ($query-> rowCount())
	{
		$_SESSION['changeInfo'] = "Podana nazwa kategorii istnieje!";
	}
	else
	{
		$query = $database->query("SELECT position FROM expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='Inne'");
		$otherCategoryData = $query ->fetch();
		
		$database->query("INSERT INTO incomes_category_assigned_to_users (id, user_id, name, position) VALUES (NULL, {$_SESSION['loggedId']}, '{$newCategoryName}', {$otherCategoryData['position']})");
		
		$query = $database->query("UPDATE expenses_category_assigned_to_users SET position=position+1 WHERE user_id={$_SESSION['loggedId']} AND name='Inne'");
		
		$_SESSION['changeInfo'] = "Dodano nową kategorię!";
	}
}
else if (isset($_POST['incomeCategories']))
{
	   if($_POST['incomeCategories'] === array_unique($_POST['incomeCategories']))
	{
		
		foreach($_POST['incomeCategories'] as $categoryId=>$incomeCategory)
		{
			$database->query("UPDATE incomes_category_assigned_to_users SET position=".$incomeCategory." WHERE id=".$categoryId." AND user_id={$_SESSION['loggedId']}");
		}
		$_SESSION['changeInfo'] = "Pomyślnie zmieniono kolejność!";		
	}
	else
	{
		$_SESSION['changeInfo'] = "Każda kategoria musi mieć inną pozycję!";
	}
		
}
else if (isset($_POST['expenseCategories']))
{
	   if($_POST['expenseCategories'] === array_unique($_POST['expenseCategories']))
	{
		foreach($_POST['expenseCategories'] as $categoryId=>$expenseCategory)
		{
			$database->query("UPDATE expenses_category_assigned_to_users SET position=".$expenseCategory." WHERE id=".$categoryId." AND user_id={$_SESSION['loggedId']}");
		}
		$_SESSION['changeInfo'] = "Pomyślnie zmieniono kolejność!";		
	}
	else
	{
		$_SESSION['changeInfo'] = "Każda kategoria musi mieć inną pozycję!";
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
		
		<script type="text/javascript">
			
			var isNameEditShown = false;
			var isEmailEditShown = false;
			var isPasswordEditShown = false;
			var isDataEditShown = false;
			var areIncomesShown = false;
			var areExpensesShown = false;
			
			function showDataEdition()
			{
				if(!isDataEditShown)
				{
					document.getElementById("dataEdit").innerHTML ='<table id="expenseTable"><tr><td><div class="attributes editClick" onclick="nameEditing();">imię</div></td><td><div class="option"><?PHP echo $userData['username'];?></div><div class="edit"><div id="nameEdit"></div></div></td></tr><tr><td><div class="attributes editClick" onclick="emailEditing();">e-mail</div></td><td><div class="option"><?PHP echo $userData['email']; ?></div><div class="edit"><div id="emailEdit"></div></div></td></tr><tr><td><div class="attributes editClick" onclick="passwordEditing();">hasło</div></td><td><div class="option"><div id="passwordEdit"></div></div></td></tr></table>';
					 isDataEditShown = true;
				}
				else
				{
					document.getElementById("dataEdit").innerHTML="";
					isDataEditShown = false;
				}	
			}
				
				
			function nameEditing()
			{
				if(!isNameEditShown)
				{
					document.getElementById("nameEdit").innerHTML = '<form method="post"><input class="commentGetting" type="text" placeholder="Podaj nowe imię"/><div class="buttons editButtons"><input type="submit" id="add" value="Zapisz"><input id="cancel" value="Anuluj" type="button" onclick="nameEditing();"></form>';
					 isNameEditShown = true;
				}
				else
				{
					document.getElementById("nameEdit").innerHTML="";
					isNameEditShown = false;
				}	
			}
			
				function emailEditing()
			{
				if(!isEmailEditShown)
				{
					document.getElementById("emailEdit").innerHTML = '<input class="commentGetting" type="text"  placeholder="Podaj nowy e-mail" />';
					 isEmailEditShown = true;
				}
				else
				{
					document.getElementById("emailEdit").innerHTML="";
					isEmailEditShown = false;
				}	
			}
			
				function passwordEditing()
			{
				if(!isPasswordEditShown)
				{
					document.getElementById("passwordEdit").innerHTML = '<div class="edit"><input class="amountGetting" style="margin-bottom: 10px;" type="password" placeholder="Bieżące hasło"/></div><div class="edit"><input class="commentGetting" type="password" placeholder="Nowe hasło" /></div><div class="edit"><input class="commentGetting" type="password"placeholder="Powtórz hasło"/></div>';
					 isPasswordEditShown = true;
				}
				else
				{
					document.getElementById("passwordEdit").innerHTML="";
					isPasswordEditShown = false;
				}	
			}
			
			function addNewCategory(kindOfCategory)
			{
				document.getElementById(kindOfCategory).innerHTML =  '<form method="post"><input class="commentGetting" type="text" name="'+kindOfCategory+'" placeholder="Podaj nazwę"/><div class="buttons editButtons noMargin"><input type="submit" id="add" value="Dodaj"><input id="cancel" value="Anuluj" type="button" onclick=\'hideCategoryOptions("'+kindOfCategory+'");\'></form>';
			}
			
			
			function showCategoryOptions(category)
			{
				document.getElementById(category).innerHTML =  '<a class="editLink" href="#" onclick=\'categoryRename("'+category+'");return false;\'>Edytuj</a>/<a class="editLink" href="#" onclick=\'categoryDeleting("'+category+'");return false;\'>Usuń</a>/<a class="editLink" href="#" onclick=\'hideCategoryOptions("'+category+'");return false;\'>Anuluj</a>';
			}
			
			function categoryRename(category)
			{
				document.getElementById(category).innerHTML = '<form method="post"><input class="commentGetting" type="text" name="newName" placeholder="Podaj nową nazwę"/><input type="hidden" name="typeOfCategory" value="'+category.substr(0,1)+'"><input type="hidden" name="categoryId" value="'+category.substr(1)+'"><div class="buttons editButtons"><input type="submit" id="add" value="Zapisz"><input id="cancel" value="Anuluj" type="button" onclick=\'hideCategoryOptions("'+category+'");\'></form>';
			}
			
			function categoryDeleting(category)
			{
				document.getElementById(category).innerHTML =  '<form method="post" class="noMargin"><input type="hidden" name="typeOfCategory" value="'+category.substr(0,1)+'"><input type="hidden" name="categoryId" value="'+category.substr(1)+'"><span style="font-size:14px; margin: 10px 0 0 0;">Czy na pewno chcesz usunąć kategorię?</span><div class="buttons editButtons noMargin"><input type="submit" class="noMargin" id="add" value="Tak"><input id="cancel" class="noMargin" value="Anuluj" type="button" onclick=\'hideCategoryOptions("'+category+'");\'></form>';
			}
			
			function hideCategoryOptions(category)
			{
				document.getElementById(category).innerHTML = '';
			}
			
			function showChangePositions(categoryPositions)
			{
				var positions = document.getElementsByClassName(categoryPositions);
				for (var i = 0; i < positions.length; ++i) 
				{
					positions[i].style.display= 'inline';
				}
			}
			
			function hideChangePositions(categoryPositions)
			{
				var positions = document.getElementsByClassName(categoryPositions);
				for (var i = 0; i < positions.length; ++i) 
				{
					positions[i].style.display= 'none';
				}
			}
				
		</script>


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
						<main><?PHP
									
									if(isset($_SESSION['changeInfo']))
									{
										echo '<div class="option error" style="margin-bottom:10px;">'.$_SESSION['changeInfo'].'</div>';
										unset($_SESSION['changeInfo']);
										echo "<script type=\"text/javascript\">window.setTimeout(\"window.location.replace('ustawienia.php');\",2000);</script>"; 
									}
									?>
						<div id="tableContainer">
								<div id="tableHead" class="editClick" onclick="showDataEdition();">
									Edycja danych
								</div>
								<div id="dataEdit"></div>
								<div id="tableHead" class="editClick" onclick="showIncomesCategories();">
									Edycja kategorii
								</div>
								<table id="expenseTable"><tr><td><div class="attributes editClick" onclick=';'>przychody</div></td><td><form class="noMargin" method="post"><?PHP
												foreach ($incomesCategories as $category) 
												{
														echo '<input type="number" min="1" max="'.$incomeCategoriesAmount.'" class="incomeCategoryPositions amountGetting position" style="display: none;" name="incomeCategories['.$category['id'].']" value="'.$category['position'].'"/><div class="option pointer" style="display: inline;" onclick=\'showCategoryOptions("i'.$category['id'].'");\'>'."{$category['name']}</div>";
														echo '<div id="i'.$category['id'].'"></div>';
												}
									?>
									<div class="option pointer" style="font-size: 14px; margin-top:4px;  color: #ed5543;" onclick="showChangePositions('incomeCategoryPositions');">&uarr;&darr;zamień kolejność</div>
									<div class="incomeCategoryPositions position" style="display: none;" ><div class="buttons editButtons noMargin"><input type="submit" id="add" value="Zamień"><input id="cancel" class="noMargin" value="Anuluj" type="button" onclick="hideChangePositions('incomeCategoryPositions');"></div></div>
									</form>
									<div class="option pointer" style="font-size: 14px; color: #ed5543;" onclick="addNewCategory('newIncomeCategory');">+ nową kategorię</div>
									<div id="newIncomeCategory"></div>
									</td></tr>
									<tr><td><div class="attributes editClick" onclick="ShowExpensesCategories();">wydatki</div></td><td><form class="noMargin" method="post"><?PHP
												foreach ($expenseCategories as $category) 
												{
														echo '<input type="number" min="1" max="'.($expenseCategoriesAmount).'" class="expenseCategoryPositions amountGetting position" style="display: none;" name="expenseCategories['.$category['id'].']" value="'.$category['position'].'"/><div class="option pointer" style="display: inline;" onclick=\'showCategoryOptions("e'.$category['id'].'");\'>'."{$category['name']}</div>";
														echo '<div id="e'.$category['id'].'"></div>';
												}
									?>
									<div class="option pointer" style="font-size: 14px;  margin-top:4px; color: #ed5543;" onclick="showChangePositions('expenseCategoryPositions');">&uarr;&darr;zamień kolejność</div>
									<div class="expenseCategoryPositions position" style="display: none;"><div class="buttons editButtons noMargin"><input type="submit" id="add" value="Zamień"><input id="cancel" class="noMargin" value="Anuluj" type="button" onclick="hideChangePositions('expenseCategoryPositions');"></div></div>
									</form>
									<div class="option pointer" style="font-size: 14px; color: #ed5543;" onclick="addNewCategory('newExpenseCategory');">+ nową kategorię</div>
									<div id="newExpenseCategory"></div>
									
									<div class="option"></div><div class="edit"><div id="emailEdit"></div></div></td></tr></table>
								<div id="tableHead" class="editClick">
									Edycja kategorii wydatków
								</div>
								<div id="categoryEdit"></div>
						</div>
					</main>
					</div>
			</div>		
		</div>
	</body>

</html>