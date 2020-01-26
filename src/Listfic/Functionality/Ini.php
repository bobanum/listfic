<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Ini extends Functionality {
	public $fieldName = "";
	
	public function html_form(){
		return "html_form";
	}
	public function html_button(){
		$result = '<a style="font-size: 150%; line-height: 0; position: relative; text-decoration: none; top: 0.21em;" href="?admin&a='.urlencode($this->directory->url()).'">&#x270D;</a>';
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
