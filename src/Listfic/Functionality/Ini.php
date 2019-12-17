<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Ini extends Functionality {
	static public $name = "Ini";
	static public $fieldName = "";
	static public $label = "Fichier INI";
	static public $description = 'Le fichier ini au complet';
	
	static public function html_form($directoryObject){
		return "";
	}
	static public function html_button($directoryObject){
		$result = '<a style="font-size: 150%; line-height: 0; position: relative; text-decoration: none; top: 0.21em;" href="?admin&a='.urlencode($directoryObject->url).'">&#x270D;</a>';
		return $result;
	}
	static public function admin_process() {
		if (!isset($_GET['a'])) {
			return "";
		}
		$directory = array_keys($_GET['a']);
		$directoryObject = new Directory($directory[0]);
		$result = $directoryObject->html_updateForm();
		return $result;
	}
}
