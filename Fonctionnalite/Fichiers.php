<?php
namespace Listfic\Fonctionnalite;
use Listfic\Dossier;
class Fichiers extends Fonctionnalite {
	static public $nom = "Fichiers";
	static public $nomChamp = "fichiers";
	static public $etiquette = "Fichiers";
	static public $description = 'Booléen. Y a-t-il des fichiers à télécharger?';

	static public function admin_gerer() {
		//Rendre les fichiers de départ visibles
		if (!isset($_GET['f'])) return false;
		$resultat = '';
		foreach($_GET['f'] as $dossier=>$etat) {
			$objDossier = new Dossier($dossier);
			$objDossier->fichiers = ($etat == 'true');
			$objDossier->mettreIni(true);
			$resultat .= $objDossier->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDossier){
		$data = 'f['.urlencode($objDossier->url).']';
		if ($objDossier->fichiers) {
			$resultat = '<a class="fichiers toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($objDossier->pathFic())) {
			$resultat = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$resultat = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $resultat;
	}
	/**
	 * Retourne un lien HTML vers le zip des fichiers en vérifiant toutes les conditions
	 * @param Dossier $objDossier L'objet dossier à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le lien pour l'admin
	 */
	static public function html_lien($objDossier) {
		$path = $objDossier->path;
		$path .= "/".basename($path);

		$etiquette = static::$etiquette;
		$condition = $objDossier->fichiers;
		if (!file_exists($path)) {
			return "";
		}
		if ($condition===false) {
			return "";
		}
		$lien = Dossier::lienTelecharger($etiquette, array("fichiers", $objDossier->url), 'fichiers');
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
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Disponible'=>'true','Non disponible'=>'false',));
		return static::html_form_ligne($champ);
	}
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if ($objDossier->fichiers == true) {
				$objDossier->fichiers = $objDossier->ajusterZip();
		}
	}
}
