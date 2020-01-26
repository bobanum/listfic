<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Visible extends Functionality {
	public $fieldName = "visiblity";
	protected $_value = false;
	private $choices = [
		'Visible'=>'true',
		'Caché'=>'false',
	];
	static public function admin_process() {
		//Rendre le projet visible
		if (!isset($_GET[$this->fieldName])) {
			return false;
		}
		$result = '';
		foreach($_GET[$this->fieldName] as $directory=>$etat) {
			$directoryObject = new Directory($directory);
			if ($etat === 'true') {
				$directoryObject->visible = true;
			} else {
				$directoryObject->visible = false;
			}
			$directoryObject->ini_put(true);
			$result .= $directoryObject->html_projectLine(true);
		}
		return $result;
	}
	public function html_button(){
		$data = $this->fieldName.'['.urlencode($this->directory->url()).']';
		if ($this->directory->visible) {
			$result = '<a class="'.$this->fieldName.' toggle on" href="?admin&'.$data.'=false">V</a>';
		} else {
			$result = '<a class="'.$this->fieldName.' toggle off" href="?admin&'.$data.'=true">V</a>';
		}
		return $result;
	}
	public function html_form() {
		$champ = $this->html_select($this->choices);
		return $this->html_form_line($champ);
	}
}
