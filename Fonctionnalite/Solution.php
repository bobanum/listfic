<?php
namespace Listfic\Fonctionnalite;
use Listfic\Dossier;
class Solution extends Fonctionnalite {
	static public $nom = "Solution";
	static public $nomChamp = "solution";
	static public $etiquette = "Solution";
	static public $description = 'Booléen. Y a-t-il des fichiers de solution?';
	public function admin_gerer() {
		//Rendre la solution visible
		if (!isset($_GET['s'])) {
			return false;
		}
		$resultat = '';
		foreach($_GET['s'] as $dossier=>$etat) {
			$objDossier = new Dossier($dossier);
			$objDossier->solution = ($etat == 'true');
			$objDossier->mettreIni(true);
			$resultat .= $objDossier->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDossier){
		$data = 's['.urlencode($objDossier->url).']';
		if ($objDossier->solution) {
			$resultat = '<a class="solution toggle on" href="?admin&'.$data.'=false">S</a>';
		} else if (file_exists($objDossier->pathFichiers(Dossier::PATH_SOLUTION))) {
			$resultat = '<a class="solution toggle off" href="?admin&'.$data.'=true">S</a>';
		} else {
			$resultat = '<a class="solution toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $resultat;
	}
	/**
	 * Retourne un lien HTML vers le zip de la solution en vérifiant toutes les conditions
	 * @param Dossier $objDossier L'objet dossier à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le lien pour l'admin
	 */
	static public function html_lien($objDossier) {
		$path = $objDossier->path;
		$path .= "/".basename($path)."_solution";

		$etiquette = static::$etiquette;
		$condition = $objDossier->solution;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition===false) {
			return "";
		}
		$lien = Dossier::lienTelecharger($etiquette, array("solution", $objDossier->url), 'solution');
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
		$path = $objDossier->path.'/'.$condition;
		$url = $objDossier->url.'/'.$condition;
		if (file_exists($path)) {
			return '<a href="'.$url.'">'.$etiquette.'</a>';
		}
		return "";
	}
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if ($objDossier->solution == true) {
			$objDossier->solution = $objDossier->ajusterZip(Dossier::PATH_SOLUTION);
		}
	}
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Disponible'=>'true','Non disponible'=>'false',));
		return static::html_form_ligne($champ);
	}
}
