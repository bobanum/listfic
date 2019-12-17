<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Solution extends Functionality {
	static public $name = "Solution";
	static public $fieldName = "solution";
	static public $label = "Solution";
	static public $description = 'Booléen. Y a-t-il des files de solution?';
	static private $choices = [
		'Disponible' => 'true',
		'Non disponible' => 'false',
	];

	static public function admin_process() {
		//Rendre la solution visible
		if (!isset($_GET['s'])) {
			return false;
		}
		$result = '';
		foreach($_GET['s'] as $directory=>$etat) {
			$directoryObject = new Directory($directory);
			$directoryObject->solution = ($etat == 'true');
			$directoryObject->ini_put(true);
			$result .= $directoryObject->html_projectLine(true);
		}
		return $result;
	}
	static public function html_button($directoryObject){
		$data = 's['.urlencode($directoryObject->url).']';
		if ($directoryObject->solution) {
			$result = '<a class="solution toggle on" href="?admin&'.$data.'=false">S</a>';
		} else if (file_exists($directoryObject->path_file(Directory::$solution_suffix))) {
			$result = '<a class="solution toggle off" href="?admin&'.$data.'=true">S</a>';
		} else {
			$result = '<a class="solution toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $result;
	}
	/**
	 * Retourne un link HTML vers le zip de la solution en vérifiant toutes les conditions
	 * @param Directory $directoryObject L'objet directory à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le link pour l'admin
	 */
	static public function html_lien($directoryObject) {
		$path = $directoryObject->path_zip("_solution");
		$label = static::$label;
		$condition = $directoryObject->solution;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition===false) {
			return "";
		}
		$link = Directory::link_download($label, ["solution", $directoryObject->url], 'solution');
		if ($condition===true) {
			return $link;
		}
		if (($time=strtotime($condition))!==false) {
			//TODO Réviser l'affichage par date...
			if ($time<time()) {
				return $link;
			}
			else return "";
		}
		//TODO Réviser l'utilisation d'une autre adresse
		$path = $directoryObject->path.'/'.$condition;
		$url = $directoryObject->url.'/'.$condition;
		if (file_exists($path)) {
			return '<a href="'.$url.'">'.$label.'</a>';
		}
		return "";
	}
	static public function ini_get($directoryObject, $ini){
		parent::ini_get($directoryObject, $ini);
		if ($directoryObject->solution == true) {
			$directoryObject->solution = $directoryObject->adjustZip(Directory::PATH_SOLUTION);
		}
	}
	static public function html_form($directoryObject) {
		$result = static::html_select($directoryObject, static::$choices);
		$result = static::html_form_line($result);
		return $result;
	}
}
