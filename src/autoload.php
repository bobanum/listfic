<?php
namespace Listfic;
spl_autoload_register(function ($name) {
	$name = str_replace("\\", "/", $name);
	$name = realpath(__DIR__."/{$name}.php");
	if (!file_exists($name)) {
		return false;
	}
	require_once $name;
});
