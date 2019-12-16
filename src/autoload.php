<?php
namespace Listfic;
spl_autoload_register(function ($name) {
	$name = explode("\\", $name);
	if ($name[0] !== "Listfic") {
		$name = implode("/", $name).".php";
		throw new \Exception("Mauvais namespace '$name'");
		return false;
	}
	array_shift($name);
	$name = implode("/", $name).".php";
//	exit ($name);
	require_once __DIR__."/".$name;
});
