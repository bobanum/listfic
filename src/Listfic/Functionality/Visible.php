<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Visible extends Functionality {
	static public $name = "Visible";
	static public $fieldName = "visible";
	static public $label = "Visible";
	static public $description = 'Booléen. Le directory est-il visible dans la liste? Il reste tout de même accessible.';
	static public function admin_process() {
		//Rendre le projet visible
		if (!isset($_GET['v'])) return false;
		$result = '';
		foreach($_GET['v'] as $directory=>$etat) {
			//$directory = $this->domaine."/".$directory;
			$directoryObject = new Directory($directory);
			if ($etat == 'true') $directoryObject->visible = true;
			else $directoryObject->visible = false;
			$directoryObject->ini_put(true);
			$result .= $directoryObject->html_projectLine(true);
		}
		return $result;
	}
	static public function html_button($directoryObject){
		$data = 'v['.urlencode($directoryObject->url).']';
		if ($directoryObject->visible) {
			$result = '<a class="visibilite toggle on" href="?admin&'.$data.'=false">V</a>';
		} else {
			$result = '<a class="visibilite toggle off" href="?admin&'.$data.'=true">V</a>';
		}
		return $result;
	}
	static public function html_form($directoryObject) {
		$champ = static::html_select($directoryObject, array('Visible'=>'true','Caché'=>'false',));
		return static::html_form_line($champ);
	}
}
