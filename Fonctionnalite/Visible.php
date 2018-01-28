<?php
namespace Listfic\Fonctionnalite;
class Visible extends Fonctionnalite {
	static public $nom = "Visible";
	static public $nomChamp = "visible";
	static public $etiquette = "Visible";
	static public $description = 'Booléen. Le dossier est-il visible dans la liste? Il reste tout de même accessible.';
	public function admin_gerer() {
		//Rendre le projet visible
		if (!isset($_GET['v'])) return false;
		$resultat = '';
		foreach($_GET['v'] as $dossier=>$etat) {
			//$dossier = $this->domaine."/".$dossier;
			$objDossier = new Dossier($dossier);
			if ($etat == 'true') $objDossier->visible = true;
			else $objDossier->visible = false;
			$objDossier->mettreIni(true);
			$resultat .= $objDossier->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDossier){
		$data = 'v['.urlencode($objDossier->url).']';
		if ($objDossier->visible) {
			$resultat = '<a class="visibilite toggle on" href="?admin&'.$data.'=false">V</a>';
		} else {
			$resultat = '<a class="visibilite toggle off" href="?admin&'.$data.'=true">V</a>';
		}
		return $resultat;
	}
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Visible'=>'true','Caché'=>'false',));
		return static::html_form_ligne($champ);
	}
}
