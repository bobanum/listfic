<?php
namespace Listfic\Fonctionnalite;
class Ini extends Fonctionnalite {
	static public $nom = "Ini";
	static public $nomChamp = "";
	static public $etiquette = "Fichier INI";
	static public $description = 'Le fichier ini au complet';
	static public function html_form($objDossier){
		return "";
	}
	static public function html_bouton($objDossier){
		$resultat = '<a style="font-size: 150%; line-height: 0; position: relative; text-decoration: none; top: 0.21em;" href="?admin&a='.urlencode($objDossier->url).'">&#x270D;</a>';
		return $resultat;
	}
	public function admin_gerer() {
		if (!isset($_GET['a'])) return "";
		$dossier = array_keys($_GET['a']);
		$objDossier = new Dossier($dossier[0]);
		$resultat = $objDossier->affichageFormModifier();
		return $resultat;
	}
}
