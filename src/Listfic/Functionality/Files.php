<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Files extends Functionality {
	static public $name = "Files";
	static public $fieldName = "files";
	static public $label = "Files";
	static public $description = 'Booléen. Y a-t-il des files à télécharger?';
	static private $choices = [
		'Disponible'=>'true',
		'Non disponible'=>'false',
	];

	static public function admin_process() {
		//Rendre les files de départ visibles
		if (!isset($_GET['f'])) return false;
		$result = '';
		foreach($_GET['f'] as $directory=>$etat) {
			$directoryObject = new Directory($directory);
			$directoryObject->files = ($etat == 'true');
			$directoryObject->ini_put(true);
			$result .= $directoryObject->html_projectLine(true);
		}
		return $result;
	}
	static public function html_button($directoryObject){
		$data = 'f['.urlencode($directoryObject->url).']';
		if ($directoryObject->files) {
			$result = '<a class="files toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($directoryObject->path_file())) {
			$result = '<a class="files toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$result = '<a class="files toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $result;
	}
	/**
	 * Retourne un link HTML vers le zip des files en vérifiant toutes les conditions
	 * @param Directory $directoryObject L'objet directory à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le link pour l'admin
	 */
	static public function html_lien($directoryObject) {
		$path = $directoryObject->path_file(Directory::$files_suffix);

		$label = static::$label;
		$condition = $directoryObject->files;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition === false) {
			return "";
		}
		$link = Directory::link_download($label, ["files", $directoryObject->url], 'files');
		if ($condition === true) {
			return $link;
		}
		if (($time = strtotime($condition)) !== false) {
			//TODO Réviser l'affichage par date...
			if ($time < time()) {
				return $link;
			} else {
				return "";
			}
		}
		//TODO Réviser l'utilisation d'une autre adresse
		$path = $directoryObject->path.'/'.$condition;
		$url = $directoryObject->url.'/'.$condition;
		if (file_exists($path)) {
			return '<a href="'.$url.'">'.$label.'</a>';
		}
		return "";
	}
	static public function html_form($directoryObject) {
		$champ = static::html_select($directoryObject, static::$choices);
		return static::html_form_line($champ);
	}
	static public function ini_get($directoryObject, $ini){
		parent::ini_get($directoryObject, $ini);
		if ($directoryObject->files == true) {
				$directoryObject->files = $directoryObject->adjustZip();
		}
	}
}
