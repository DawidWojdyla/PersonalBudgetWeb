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

$lastIncomes = $database->query("SELECT incomes.id, incomes_category_assigned_to_users.name, incomes.amount, incomes.date FROM incomes, incomes_category_assigned_to_users WHERE incomes.user_id={$_SESSION['loggedId']} AND incomes_category_assigned_to_users.id=incomes.income_category_assigned_to_user_id ORDER BY incomes.id DESC LIMIT 5");

$lastExpenses = $database->query("SELECT expenses.id, expenses_category_assigned_to_users.name, expenses.amount, expenses.date FROM expenses, expenses_category_assigned_to_users WHERE expenses.user_id={$_SESSION['loggedId']} AND expenses_category_assigned_to_users.id=expenses.expense_category_assigned_to_user_id ORDER BY expenses.id DESC LIMIT 5");


//zmiana imienia
if(isset($_POST['newName']))
{
		if (strlen($_POST['newName'])<3 || strlen($_POST['newName'])>15)
		{
			$_SESSION['changeInfo'] = "Imię musi składać się z 3-15 znaków!";
		}
		else
		{
			$query = $database -> prepare ("UPDATE users SET username=:name WHERE id={$_SESSION['loggedId']}");
			$query -> bindValue(':name', $_POST['newName'], PDO::PARAM_STR);
			$query -> execute();
			$_SESSION['changeInfo'] = "Pomyślnie zmieniono imię!";
		}
}

else if(isset($_POST['newEmail']))
{
	$email = filter_input(INPUT_POST, 'newEmail', FILTER_VALIDATE_EMAIL);
	
		if (empty($email))
		{
			
			$_SESSION['changeInfo']= "Podałeś niepoprawny adres e-mail!";
		}
		else
		{
			$database->query("UPDATE users SET email='{$email}' WHERE id={$_SESSION['loggedId']}");
			$_SESSION['changeInfo'] = "Pomyślnie zmieniono adres e-mail!";
		}

}

else if (isset($_POST['newPassword']))
{
	$givenPassword = filter_input(INPUT_POST, 'currentPassword');
	$userQuery = $database -> query("SELECT password FROM users WHERE id={$_SESSION['loggedId']}");
	$user = $userQuery  -> fetch();
	
	if (password_verify($givenPassword, $user['password']))
	{
		if (strlen($_POST['newPassword']) < 8 || strlen($_POST['newPassword']) >20)
		{
			$_SESSION['changeInfo'] = "Hasło musi zawierać od 8 do 20 znaków!";
		}
		else
		{
			if($_POST['newPassword'] == $_POST['newPassword2'])
			{
			
				$newHashPassword = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
				
				$database -> query("UPDATE users SET password='{$newHashPassword}' WHERE id={$_SESSION['loggedId']}");
				
				$_SESSION['changeInfo'] = "Pomyślnie zmieniono hasło!";
			}
			else
			{
				$_SESSION['changeInfo'] = "Podane hasła muszą być identyczne!";
			}
		}
	}
	else
	{
		$_SESSION['changeInfo'] = "Podałeś błędne bieżące hasło!";
	}
		
}

//zmiana nazwy kategorii
else if(isset($_POST['newCategoryName']))
{
	if( $_POST['newCategoryName'] != '')
	{
		if($_POST['typeOfCategory'] == 'i')
		{
			$newCategoryName = mb_convert_case($_POST['newCategoryName'], MB_CASE_TITLE, "UTF-8");
			$query = $database->query("SELECT name from incomes_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='{$newCategoryName}'");
		
			if ($query-> rowCount())
			{
				$_SESSION['changeInfo'] = "Podana nazwa kategorii istnieje!";
			}
			else
			{
				$database->query("UPDATE incomes_category_assigned_to_users SET name='{$newCategoryName}' WHERE user_id={$_SESSION['loggedId']} AND id={$_POST['categoryId']}");
				$_SESSION['changeInfo'] = "Pomyślnie zmieniono nazwę kategorii!";
			}
		}
		else
		{
			$newCategoryName = mb_convert_case($_POST['newCategoryName'], MB_CASE_TITLE, "UTF-8");
			$query = $database->query("SELECT name from expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='{$newCategoryName}'");
		
			if ($query-> rowCount())
			{
				$_SESSION['changeInfo'] = "Podana nazwa kategorii istnieje!";
			}
			else
			{
				$database->query("UPDATE expenses_category_assigned_to_users SET name='{$newCategoryName}' WHERE user_id={$_SESSION['loggedId']} AND id={$_POST['categoryId']}");			$_SESSION['changeInfo'] = "Pomyślnie zmieniono nazwę kategorii!";
			}
		}
	}
	else
	{
		$_SESSION['changeInfo'] = "Nazwa kategorii nie może być pusta!";
	}
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
	
	$newCategoryName = mb_convert_case($_POST['newIncomeCategory'], MB_CASE_TITLE, "UTF-8");
	
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
	$newCategoryName = mb_convert_case($_POST['newExpenseCategory'], MB_CASE_TITLE, "UTF-8");
	$query = $database->query("SELECT name from expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='{$newCategoryName}'");
	if ($query-> rowCount())
	{
		$_SESSION['changeInfo'] = "Podana nazwa kategorii istnieje!";
	}
	else
	{
		$query = $database->query("SELECT position FROM expenses_category_assigned_to_users WHERE user_id={$_SESSION['loggedId']} AND name='Inne'");
		$otherCategoryData = $query ->fetch();
		
		$database->query("INSERT INTO expenses_category_assigned_to_users (id, user_id, name, position) VALUES (NULL, {$_SESSION['loggedId']}, '{$newCategoryName}', {$otherCategoryData['position']})");
		
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
else if(isset($_POST['lastIncomes']))
{
	foreach($_POST['lastIncomes'] as $lastIncome)
	{
		$database->query("DELETE FROM incomes WHERE id={$lastIncome}");
	}
	$_SESSION['changeInfo'] = "Pomyślnie usunięto wybrane przychody!";
}
else if(isset($_POST['lastExpenses']))
{
	foreach($_POST['lastExpenses'] as $lastExpense)
	{
		$database->query("DELETE FROM expenses WHERE id={$lastExpense}");
	}
	$_SESSION['changeInfo'] = "Pomyślnie usunięto wybrane wydatki!";
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
		
			var isDataEditShown = false;
			var isNameEditShown = false;
			var isEmailEditShown = false;
			var isPasswordEditShown = false;
		
			var areCategoriesShown = false;
			var areIncomeCategoriesShown = false;
			var areExpenseCategoriesShown = false;
			
			var areLastAddedIncomesShown = false;
			var areLastAddedExpensesShown = false;
			
			
			function showDataEdition()
			{
				if(!isDataEditShown)
				{
					document.getElementById("dataEdit").innerHTML ='<table class="expenseTable"><tr><td><div class="attributes editClick" onclick="nameEditing();">imię</div></td><td><div class="option"><?PHP echo $userData['username'];?></div><div class="edit"><div id="nameEdit"></div></div></td></tr><tr><td><div class="attributes editClick" onclick="emailEditing();">e-mail</div></td><td><div class="option"><?PHP echo $userData['email']; ?></div><div class="edit"><div id="emailEdit"></div></div></td></tr><tr><td><div class="attributes editClick" onclick="passwordEditing();">hasło</div></td><td><div class="option"><div id="passwordEdit"></div></div></td></tr></table><div style="margin-bottom: 40px;"></div>';
					document.getElementById("dataDownArrow").style.display="none";
					 isDataEditShown = true;
				}
				else
				{
					document.getElementById("dataEdit").innerHTML="";
					document.getElementById("dataDownArrow").style.display="inline";
					isDataEditShown = false;
					isNameEditShown = false;
					isEmailEditShown = false;
					isPasswordEditShown = false;
				}	
			}
				
				
			function nameEditing()
			{
				if(!isNameEditShown)
				{
					document.getElementById("nameEdit").innerHTML = '<form method="post"><input class="commentGetting" type="text" name="newName" placeholder="Podaj nowe imię"/><div class="buttons editButtons" style="margin-top: 5px;"><input type="submit" class="add" value="Zapisz"><input class="cancel" value="Anuluj" type="button" onclick="nameEditing();"></form><div style="margin-bottom: -20px;"></div>';
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
					document.getElementById("emailEdit").innerHTML = '<form method="post"><input class="commentGetting" type="email" name="newEmail" placeholder="Podaj nowy e-mail" /><div class="buttons editButtons" style="margin-top: 5px;"><input type="submit" class="add" value="Zapisz"><input class="cancel" value="Anuluj" type="button" onclick="emailEditing();"></div></form><div style="margin-bottom: -20px;"></div>';
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
					document.getElementById("passwordEdit").innerHTML = '<form class="noMargin" method="post"><div class="edit"><input class="amountGetting" pattern=".{8,20}" required title="Hasło musi zawierać od 8  20 znaków" name="currentPassword" style="margin-bottom: 8px;" type="password" placeholder="Bieżące hasło"/></div><div class="edit"><input class="commentGetting" type="password" pattern=".{8,20}" required title="Hasło musi zawierać od 8  20 znaków" name="newPassword" placeholder="Nowe hasło" /></div><div class="edit"><input class="commentGetting" type="password" pattern=".{8,20}" required title="Hasło musi zawierać od 8  20 znaków" name="newPassword2" placeholder="Powtórz hasło"/></div><div class="buttons editButtons"><input type="submit" class="add" value="Zapisz"><input class="cancel" value="Anuluj" type="button" onclick="passwordEditing();"></div></form><div style="margin-bottom: -20px;"></div>';
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
				document.getElementById(kindOfCategory).innerHTML =  '<form method="post"><input class="commentGetting" type="text" name="'+kindOfCategory+'" placeholder="Podaj nazwę"/><div class="buttons editButtons noMargin"><input type="submit" class="add" value="Dodaj"><input class="cancel" value="Anuluj" type="button" onclick=\'hideCategoryOptions("'+kindOfCategory+'");\'></form><div style="margin-bottom: -30px;"></div>';
			}
			
			
			function showCategoryOptions(category)
			{
				document.getElementById(category).innerHTML =  '<a class="editLink" href="#" onclick=\'categoryRename("'+category+'");return false;\'>Edytuj</a>/<a class="editLink" href="#" onclick=\'categoryDeleting("'+category+'");return false;\'>Usuń</a>/<a class="editLink" href="#" onclick=\'hideCategoryOptions("'+category+'");return false;\'>Anuluj</a>';
			}
			
			function categoryRename(category)
			{
				document.getElementById(category).innerHTML = '<form method="post"><input class="commentGetting" type="text" name="newCategoryName" placeholder="Podaj nową nazwę" ><input type="hidden" name="typeOfCategory" value="'+category.substr(0,1)+'"><input type="hidden" name="categoryId" value="'+category.substr(1)+'"><div class="buttons editButtons noMargin"><input type="submit" value="Zapisz"><input class="cancel" value="Anuluj" type="button" onclick=\'hideCategoryOptions("'+category+'");\'></div></form>';
			}
			
			function categoryDeleting(category)
			{
				document.getElementById(category).innerHTML =  '<form method="post" class="noMargin"><input type="hidden" name="typeOfCategory" value="'+category.substr(0,1)+'"><input type="hidden" name="categoryId" value="'+category.substr(1)+'"><div style="font-size:14px; margin-top: 3px;">Czy na pewno chcesz usunąć kategorię?</div><div class="buttons editButtons noMargin"><input type="submit" class="noMargin" class="add" value="Tak"><input class="cancel" class="noMargin" value="Anuluj" type="button" onclick=\'hideCategoryOptions("'+category+'");\'></form>';
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
			
			function showIncomeCategories()
			{
				
				if(!areIncomeCategoriesShown)
				{
					document.getElementById("incomeCategoryDiv").innerHTML ='<form class="noMargin" method="post"><?PHP
												foreach ($incomesCategories as $category) 
												{
														echo '<input type="number" min="1" max="'.$incomeCategoriesAmount.'" class="incomeCategoryPositions amountGetting position" style="display: none;" name="incomeCategories['.$category['id'].']" value="'.$category['position'].'"/><div class="option pointer" style="display: inline;" onclick=\\\'showCategoryOptions("i'.$category['id'].'");\\\'>'."{$category['name']}</div>";
														echo '<div id="i'.$category['id'].'"></div>';
												}
									?><div class="option pointer" style="font-size: 14px; margin-top:4px;  color: #ed5543;" onclick="showChangePositions(\'incomeCategoryPositions\');">&uarr;&darr;zamień kolejność</div><div class="incomeCategoryPositions position" style="display: none;" ><div class="buttons editButtons noMargin"><input type="submit" class="add" value="Zamień"><input class="cancel" class="noMargin" value="Anuluj" type="button" onclick="hideChangePositions(\'incomeCategoryPositions\');"></div></div></form><div class="option pointer" style="font-size: 14px; color: #ed5543;" onclick="addNewCategory(\'newIncomeCategory\');">+ nowa kategoria</div><div id="newIncomeCategory"></div>';
					areIncomeCategoriesShown = true;
				}
				else
				{
					document.getElementById("incomeCategoryDiv").innerHTML ="";
					areIncomeCategoriesShown = false;
				}
				
			}
			
			function showExpenseCategories()
			{
				
				if(!areExpenseCategoriesShown)
				{
					document.getElementById("expenseCategoryDiv").innerHTML ='<form class="noMargin" method="post"><?PHP
												foreach ($expenseCategories as $category) 
												{
														echo '<input type="number" min="1" max="'.($expenseCategoriesAmount).'" class="expenseCategoryPositions amountGetting position" style="display: none;" name="expenseCategories['.$category['id'].']" value="'.$category['position'].'"/><div class="option pointer" style="display: inline;" onclick=\\\'showCategoryOptions("e'.$category['id'].'");\\\'>'."{$category['name']}</div>";
														echo '<div id="e'.$category['id'].'"></div>';
												}
									?><div class="option pointer" style="font-size: 14px;  margin-top:4px; color: #ed5543;" onclick="showChangePositions(\'expenseCategoryPositions\');">&uarr;&darr;zamień kolejność</div><div class="expenseCategoryPositions position" style="display: none;"><div class="buttons editButtons noMargin"><input type="submit" class="add" value="Zamień"><input class="cancel" class="noMargin" value="Anuluj" type="button" onclick="hideChangePositions(\'expenseCategoryPositions\');"></div></div></form><div class="option pointer" style="font-size: 14px; color: #ed5543;" onclick="addNewCategory(\'newExpenseCategory\');">+ nowa kategoria</div><div id="newExpenseCategory"></div><div class="option"></div><div class="edit"><div id="emailEdit"></div></div>';
					areExpenseCategoriesShown = true;
				}
				else
				{
					document.getElementById("expenseCategoryDiv").innerHTML ="";
					areExpenseCategoriesShown = false;
				}
				
			}
			
			function showCategories()
			{
				if(!areCategoriesShown)
				{
					document.getElementById("categoryEdit").innerHTML ='<table class="expenseTable"><tr><td><div class="attributes editClick" onclick="showIncomeCategories();">przychody</div></td><td><div id="incomeCategoryDiv"></div></td></tr><tr><td><div class="attributes editClick" onclick="showExpenseCategories();">wydatki</div></td><td><div id="expenseCategoryDiv"></div></td></tr></table><div style="margin-bottom: 40px;"></div>';
					document.getElementById("categoriesDownArrow").style.display="none";
					areCategoriesShown = true;
				}
				else
				{
					document.getElementById("categoryEdit").innerHTML ='';
					document.getElementById("categoriesDownArrow").style.display="inline";
					areCategoriesShown = false;
					areIncomeCategoriesShown = false;
					areExpenseCategoriesShown = false;
				}
			}
			
			function showLastAddedIncomes()
			{
				if(!areLastAddedIncomesShown)
				{
					document.getElementById("lastAddedIncomesEdit").style.display="inline";
					document.getElementById("incomeDownArrow").style.display="none";
					areLastAddedIncomesShown = true;
				}
				else
				{
					document.getElementById("lastAddedIncomesEdit").style.display="none";
					document.getElementById("incomeDownArrow").style.display="inline";
					areLastAddedIncomesShown = false;
				}
			}

			function showLastAddedExpenses()
			{
				if(!areLastAddedExpensesShown)
				{
					document.getElementById("lastAddedExpensesEdit").style.display="inline";
					document.getElementById("expenseDownArrow").style.display="none";
					areLastAddedExpensesShown = true;
				}
				else
				{
					document.getElementById("lastAddedExpensesEdit").style.display="none";
					document.getElementById("expenseDownArrow").style.display="inline";
					areLastAddedExpensesShown = false;
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
									//echo '<script>alert("'.$_SESSION['changeInfo'].'");</script>';
									unset($_SESSION['changeInfo']);
									echo "<script type=\"text/javascript\">window.setTimeout(\"window.location.replace('ustawienia.php');\",1800);</script>"; 
								}
								?>
						<div id="tableContainer">
							<div class="editClick tableHead" onclick="showDataEdition();">
								Edycja danych
							</div>
							<div id="dataEdit"></div>
							<div class="noMargin" style="text-align: center; margin-top: -20px; margin-bottom: -2px;">
								<div id='dataDownArrow' class='editButtons arrow pointer' onclick="showDataEdition();">&dArr;</div>
							</div>
							<div class="editClick tableHead" onclick="showCategories();">
								Edycja kategorii
							</div>
							<div id="categoryEdit"></div>
							<div class="noMargin" style="text-align: center; margin-top: -20px; margin-bottom: -2px;">
								<div id='categoriesDownArrow' class='editButtons arrow pointer' onclick="showCategories();">&dArr;</div>
							</div>
							<div class="editClick tableHead" onclick="showLastAddedIncomes();">
								Ostatnie przychody
							</div>
							<div id="lastAddedIncomesEdit" style="display: none;">
								<form class="noMargin" method="post">
									<div class="lastDataTableContainer">
										<table class="lastDataEdit option">
											<?PHP
											foreach ($lastIncomes as $lastIncome)
											{
												echo "<tr><td><label for='li{$lastIncome['id']}'><input class='lastDataCheckbox pointer' id='li{$lastIncome['id']}' type='checkbox' value='{$lastIncome['id']}' name='lastIncomes[]'></label></td><td><label  class='pointer' for='li{$lastIncome['id']}'>{$lastIncome['date']}</label></td><td><label  class='pointer' for='li{$lastIncome['id']}'>{$lastIncome['name']}</label></td><td align='right'><label class='pointer' for='li{$lastIncome['id']}'>{$lastIncome['amount']}</label></td></tr>";
											}
											?>
										</table>
										<?PHP
												
												if ($lastIncomes->rowCount())
												{
													echo '<div class="buttons editButtons" style="text-align: center; margin-bottom: 30px;">
																<input type="submit" class="add" value="Usuń" style="margin-right:7%;">
																<input class="cancel" class="noMargin" value="Anuluj" type="button" onclick="showLastAddedIncomes();" style="margin-left:7%;" />
															</div>';
												}							
												else 
													echo '<div class="text-center option">Nie masz dodanych żadnych przychodów!</div>
																<div style="margin-bottom: 35px;"></div>';
											?>
									</div>
								</form>	
							</div>
							<div class="noMargin" style="text-align: center; margin-top: -20px; margin-bottom: -2px;">
								<div id='incomeDownArrow' class='editButtons arrow pointer' onclick="showLastAddedIncomes();">&dArr;</div>
							</div>
							<div class="editClick noMargin tableHead" onclick="showLastAddedExpenses();">
								Ostatnie wydatki
							</div>
							<div id="lastAddedExpensesEdit" style="display: none;">
								<form class="noMargin" method="post">
									<div class="lastDataTableContainer">
										<table class="option lastDataEdit">
											<?PHP
											foreach ($lastExpenses as $lastExpense)
											{
												echo "<tr><td><label for='le{$lastExpense['id']}'><input class='lastDataCheckbox pointer' type='checkbox' id='le{$lastExpense['id']}' value='{$lastExpense['id']}' name='lastExpenses[]'></label></td><td><label class='pointer' for='le{$lastExpense['id']}'>{$lastExpense['date']}</label></td><td><label class='pointer' for='le{$lastExpense['id']}'>{$lastExpense['name']}</label></td><td align='right'><label class='pointer' for='le{$lastExpense['id']}'>{$lastExpense['amount']}</label></td></tr>";
											}
											?>
										</table>
											<?PHP
												
												if ($lastExpenses->rowCount())
												{
													echo '<div class="buttons editButtons" style="text-align: center; margin-bottom: 15px;">
																<input type="submit" class="add" value="Usuń" style="margin-right:7%;">
																<input class="cancel" value="Anuluj" type="button" onclick="showLastAddedExpenses();" style="margin-left:7%;" />
															</div>';
												}							
												else 
													echo '<div class="text-center option">Nie masz dodanych żadnych wydatków!</div>';
											?>
									</div>
								</form>	
							</div>
							<div class="noMargin" style="text-align: center; margin-bottom: -7px;">
								<div id='expenseDownArrow' class='editButtons arrow pointer' onclick="showLastAddedExpenses();">&dArr;</div>
							</div>
						</div>
					</main>
				</div>
			</div>		
		</div>
	</body>
</html>