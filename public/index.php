<?php
error_reporting(E_ALL);
include_once "../src/autoload.php";
Listfic\Listfic::process();
$x = new Listfic\Listfic("projets");
// var_dump($x->directories[5]);
var_dump($x->directories[5]->toArray());
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