<?php
namespace Listfic\Functionality;
class Titre extends Functionality {
	static public $nom = "Titre";
	static public $nomChamp = "titre";
	static public $etiquette = "Titre";
	static public $description = 'Le titre qui s\'affiche dans la liste';
	static public function html($objDirectory) {
		return '<a class="titre" target="_blank" href="'.$objDirectory->url.'">'.$objDirectory->titre.'</a>';
	}
	static public function prendreIni($objDirectory, $ini){
		parent::prendreIni($objDirectory, $ini);
		if (!$objDirectory->titre) $objDirectory->titre = static::recupererTitre($objDirectory);
		return $objDirectory->titre;
	}
	/**
	 * Analyse le fichier index pour en extraire le titre
	 * @return string
	 */
	static public function recupererTitre($objDirectory){
		$path = $objDirectory->path;
		$titre = basename($path);
		if (count($fics=glob($path."/index.*"))==0) return $titre;
		$html = file_get_contents($fics[0]);
		preg_match("#<title>(.*)</title>#", $html, $temp);
		if (count($temp) == 0) return $titre;
		$temp = trim($temp[1]);
		if ($temp == "") return $titre;
		return $temp;
	}
}
