<?php
error_reporting(E_ALL);
include_once "../src/autoload.php";

$x = new Listfic\Listfic("projets");
var_dump($x->toArray());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Document</title>
</head>
<body>
	
</body>
</html>