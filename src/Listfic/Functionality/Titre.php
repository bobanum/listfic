<?php
namespace Listfic\Fonctionnalite;
class Titre extends Fonctionnalite {
	static public $nom = "Titre";
	static public $nomChamp = "titre";
	static public $etiquette = "Titre";
	static public $description = 'Le titre qui s\'affiche dans la liste';
	static public function html($objDossier) {
		return '<a class="titre" target="_blank" href="'.$objDossier->url.'">'.$objDossier->titre.'</a>';
	}
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if (!$objDossier->titre) $objDossier->titre = static::recupererTitre($objDossier);
		return $objDossier->titre;
	}
	/**
	 * Analyse le fichier index pour en extraire le titre
	 * @return string
	 */
	static public function recupererTitre($objDossier){
		$path = $objDossier->path;
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
