<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Fichiers extends Functionality {
	static public $nom = "Fichiers";
	static public $nomChamp = "fichiers";
	static public $etiquette = "Fichiers";
	static public $description = 'Booléen. Y a-t-il des fichiers à télécharger?';

	static public function admin_gerer() {
		//Rendre les fichiers de départ visibles
		if (!isset($_GET['f'])) return false;
		$resultat = '';
		foreach($_GET['f'] as $directory=>$etat) {
			$objDirectory = new Directory($directory);
			$objDirectory->fichiers = ($etat == 'true');
			$objDirectory->mettreIni(true);
			$resultat .= $objDirectory->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDirectory){
		$data = 'f['.urlencode($objDirectory->url).']';
		if ($objDirectory->fichiers) {
			$resultat = '<a class="fichiers toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($objDirectory->pathFic())) {
			$resultat = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$resultat = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $resultat;
	}
	/**
	 * Retourne un lien HTML vers le zip des fichiers en vérifiant toutes les conditions
	 * @param Directory $objDirectory L'objet directory à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le lien pour l'admin
	 */
	static public function html_lien($objDirectory) {
		$path = $objDirectory->pathFic("_fichiers");

		$etiquette = static::$etiquette;
		$condition = $objDirectory->fichiers;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition===false) {
			return "";
		}
		$lien = Directory::lienTelecharger($etiquette, array("fichiers", $objDirectory->url), 'fichiers');
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
	static public function html_form($objDirectory) {
		$champ = static::html_select($objDirectory, array('Disponible'=>'true','Non disponible'=>'false',));
		return static::html_form_ligne($champ);
	}
	static public function prendreIni($objDirectory, $ini){
		parent::prendreIni($objDirectory, $ini);
		if ($objDirectory->fichiers == true) {
				$objDirectory->fichiers = $objDirectory->ajusterZip();
		}
	}
}
