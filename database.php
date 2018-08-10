<?php

$host='localhost';
$dbUser = 'root'	;
$dbPassword = '';
$dbName = 'home_budget';

try {
		
		$database = new PDO ("mysql:host=$host; dbname=$dbName; charset=utf8", $dbUser, $dbPassword);
		$database-> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
} catch (PDOException $error){
	
			echo $error;//->getMessage();
			exit(' Database error');
}

?>