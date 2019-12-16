<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Visible extends Functionality {
	static public $nom = "Visible";
	static public $nomChamp = "visible";
	static public $etiquette = "Visible";
	static public $description = 'Booléen. Le directory est-il visible dans la liste? Il reste tout de même accessible.';
	static public function admin_gerer() {
		//Rendre le projet visible
		if (!isset($_GET['v'])) return false;
		$resultat = '';
		foreach($_GET['v'] as $directory=>$etat) {
			//$directory = $this->domaine."/".$directory;
			$objDirectory = new Directory($directory);
			if ($etat == 'true') $objDirectory->visible = true;
			else $objDirectory->visible = false;
			$objDirectory->mettreIni(true);
			$resultat .= $objDirectory->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDirectory){
		$data = 'v['.urlencode($objDirectory->url).']';
		if ($objDirectory->visible) {
			$resultat = '<a class="visibilite toggle on" href="?admin&'.$data.'=false">V</a>';
		} else {
			$resultat = '<a class="visibilite toggle off" href="?admin&'.$data.'=true">V</a>';
		}
		return $resultat;
	}
	static public function html_form($objDirectory) {
		$champ = static::html_select($objDirectory, array('Visible'=>'true','Caché'=>'false',));
		return static::html_form_ligne($champ);
	}
}
