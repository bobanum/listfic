<?php
include_once "src/autoload.php";
$listfic = new Listfic\Listfic("..");
header("content-type: application/json");
echo $listfic->toJson();