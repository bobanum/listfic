<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Files extends Functionality {
	static public $name = "Fichiers";
	static public $fieldName = "fichiers";
	static public $label = "Fichiers";
	static public $description = 'Booléen. Y a-t-il des fichiers à télécharger?';

	static public function admin_process() {
		//Rendre les fichiers de départ visibles
		if (!isset($_GET['f'])) return false;
		$result = '';
		foreach($_GET['f'] as $directory=>$etat) {
			$directoryObject = new Directory($directory);
			$directoryObject->fichiers = ($etat == 'true');
			$directoryObject->ini_put(true);
			$result .= $directoryObject->html_projectLine(true);
		}
		return $result;
	}
	static public function html_button($directoryObject){
		$data = 'f['.urlencode($directoryObject->url).']';
		if ($directoryObject->fichiers) {
			$result = '<a class="fichiers toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($directoryObject->path_file())) {
			$result = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$result = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $result;
	}
	/**
	 * Retourne un lien HTML vers le zip des fichiers en vérifiant toutes les conditions
	 * @param Directory $directoryObject L'objet directory à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le lien pour l'admin
	 */
	static public function html_lien($directoryObject) {
		$path = $directoryObject->path_file(Directory::$files_suffix);

		$label = static::$label;
		$condition = $directoryObject->fichiers;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition===false) {
			return "";
		}
		$lien = Directory::link_download($label, array("fichiers", $directoryObject->url), 'fichiers');
		if ($condition===true) {
			return $lien;
		}
		if (($time=strtotime($condition))!==false) {
			//TODO Réviser l'affichage par date...
			if ($time<time()) {
				return $lien;
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
	static public function html_form($directoryObject) {
		$champ = static::html_select($directoryObject, array('Disponible'=>'true','Non disponible'=>'false',));
		return static::html_form_line($champ);
	}
	static public function getIni($directoryObject, $ini){
		parent::getIni($directoryObject, $ini);
		if ($directoryObject->fichiers == true) {
				$directoryObject->fichiers = $directoryObject->adjustZip();
		}
	}
}
