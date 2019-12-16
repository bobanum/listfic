<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Ini extends Functionality {
	static public $nom = "Ini";
	static public $nomChamp = "";
	static public $etiquette = "Fichier INI";
	static public $description = 'Le fichier ini au complet';
	static public function html_form($objDirectory){
		return "";
	}
	static public function html_bouton($objDirectory){
		$resultat = '<a style="font-size: 150%; line-height: 0; position: relative; text-decoration: none; top: 0.21em;" href="?admin&a='.urlencode($objDirectory->url).'">&#x270D;</a>';
		return $resultat;
	}
	static public function admin_gerer() {
		if (!isset($_GET['a'])) return "";
		$directory = array_keys($_GET['a']);
		$objDirectory = new Directory($directory[0]);
		$resultat = $objDirectory->affichageFormModifier();
		return $resultat;
	}
}
