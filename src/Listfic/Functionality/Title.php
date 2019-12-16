<?php
namespace Listfic\Functionality;
class Title extends Functionality {
	static public $name = "Titre";
	static public $fieldName = "titre";
	static public $label = "Titre";
	static public $description = 'Le titre qui s\'affiche dans la liste';
	static public function html($directoryObject) {
		return '<a class="titre" target="_blank" href="'.$directoryObject->url.'">'.$directoryObject->titre.'</a>';
	}
	static public function getIni($directoryObject, $ini){
		parent::getIni($directoryObject, $ini);
		if (!$directoryObject->titre) $directoryObject->titre = static::recupererTitre($directoryObject);
		return $directoryObject->titre;
	}
	/**
	 * Analyse le fichier index pour en extraire le titre
	 * @return string
	 */
	static public function recupererTitre($directoryObject){
		$path = $directoryObject->path;
		$titre = basename($path);
		if (count($files=glob($path."/index.*"))==0) return $titre;
		$html = file_get_contents($files[0]);
		preg_match("#<title>(.*)</title>#", $html, $temp);
		if (count($temp) == 0) return $titre;
		$temp = trim($temp[1]);
		if ($temp == "") return $titre;
		return $temp;
	}
}
