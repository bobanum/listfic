<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Solution extends Functionality {
	static public $nom = "Solution";
	static public $nomChamp = "solution";
	static public $etiquette = "Solution";
	static public $description = 'Booléen. Y a-t-il des fichiers de solution?';
	static public function admin_gerer() {
		//Rendre la solution visible
		if (!isset($_GET['s'])) {
			return false;
		}
		$resultat = '';
		foreach($_GET['s'] as $directory=>$etat) {
			$objDirectory = new Directory($directory);
			$objDirectory->solution = ($etat == 'true');
			$objDirectory->mettreIni(true);
			$resultat .= $objDirectory->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDirectory){
		$data = 's['.urlencode($objDirectory->url).']';
		if ($objDirectory->solution) {
			$resultat = '<a class="solution toggle on" href="?admin&'.$data.'=false">S</a>';
		} else if (file_exists($objDirectory->pathFic(Directory::$suffixe_solution))) {
			$resultat = '<a class="solution toggle off" href="?admin&'.$data.'=true">S</a>';
		} else {
			$resultat = '<a class="solution toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $resultat;
	}
	/**
	 * Retourne un lien HTML vers le zip de la solution en vérifiant toutes les conditions
	 * @param Directory $objDirectory L'objet directory à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le lien pour l'admin
	 */
	static public function html_lien($objDirectory) {
		$path = $objDirectory->pathZip("_solution");
		$etiquette = static::$etiquette;
		$condition = $objDirectory->solution;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition===false) {
			return "";
		}
		$lien = Directory::lienTelecharger($etiquette, array("solution", $objDirectory->url), 'solution');
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
		$path = $objDirectory->path.'/'.$condition;
		$url = $objDirectory->url.'/'.$condition;
		if (file_exists($path)) {
			return '<a href="'.$url.'">'.$etiquette.'</a>';
		}
		return "";
	}
	static public function prendreIni($objDirectory, $ini){
		parent::prendreIni($objDirectory, $ini);
		if ($objDirectory->solution == true) {
			$objDirectory->solution = $objDirectory->ajusterZip(Directory::PATH_SOLUTION);
		}
	}
	static public function html_form($objDirectory) {
		$champ = static::html_select($objDirectory, array('Disponible'=>'true','Non disponible'=>'false',));
		return static::html_form_ligne($champ);
	}
}
