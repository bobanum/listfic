<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Visible extends Functionality {
	public $fieldName = "visible";
	public $label = "Visible";
	public $description = 'Booléen. Le directory est-il visible dans la liste? Il reste tout de même accessible.';
	protected $_value = false;
	private $choices = [
		'Visible'=>'true',
		'Caché'=>'false',
	];
	static public function admin_process() {
		//Rendre le projet visible
		if (!isset($_GET['v'])) {
			return false;
		}
		$result = '';
		foreach($_GET['v'] as $directory=>$etat) {
			//$directory = $this->domaine."/".$directory;
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
		$data = 'v['.urlencode($this->directory->url()).']';
		if ($this->directory->visible) {
			$result = '<a class="visibilite toggle on" href="?admin&'.$data.'=false">V</a>';
		} else {
			$result = '<a class="visibilite toggle off" href="?admin&'.$data.'=true">V</a>';
		}
		return $result;
	}
	public function html_form() {
		$champ = $this->html_select($this->choices);
		return $this->html_form_line($champ);
	}
}
