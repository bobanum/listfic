<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Solution extends Functionality {
	public $name = "Solution";
	public $fieldName = "solution";
	public $label = "Solution";
	public $description = 'Booléen. Y a-t-il des files de solution?';
	protected $_value = false;
	private $choices = [
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
			$directoryObject->solution = ($etat === 'true');
			$directoryObject->ini_put(true);
			$result .= $directoryObject->html_projectLine(true);
		}
		return $result;
	}
	public function html_button() {
		$data = 's['.urlencode($this->directory->url).']';
		if ($this->value) {
			$result = '<a class="solution toggle on" href="?admin&'.$data.'=false">S</a>';
		} else if (file_exists($this->directory->path_file(Directory::$solution_suffix))) {
			$result = '<a class="solution toggle off" href="?admin&'.$data.'=true">S</a>';
		} else {
			$result = '<a class="solution toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $result;
	}
	/**
	 * Retourne un link HTML vers le zip de la solution en vérifiant toutes les conditions
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le link pour l'admin
	 */
	public function html_link() {
		$path = $this->directory->path_zip("_solution");
		$label = $this->label;
		$condition = $this->value;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition===false) {
			return "";
		}
		$link = Directory::link_download($label, ["solution", $this->directory->url], 'solution');
		if ($condition===true) {
			return $link;
		}
		if (($time = strtotime($condition)) !== false) {
			//TODO Réviser l'affichage par date...
			if ($time<time()) {
				return $link;
			}
			else return "";
		}
		//TODO Réviser l'utilisation d'une autre adresse
		$path = $this->directory->path.'/'.$condition;
		$url = $this->directory->url.'/'.$condition;
		if (file_exists($path)) {
			return '<a href="'.$url.'">'.$label.'</a>';
		}
		return "";
	}
	public function ini_get($ini){
		parent::ini_get($ini);
		if ($this->value === true) {
			$this->value = $this->directory->adjustZip(Directory::PATH_SOLUTION);
		}
	}
	public function html_form() {
		$result = $this->html_select($this->choices);
		$result = $this->html_form_line($result);
		return $result;
	}
}
