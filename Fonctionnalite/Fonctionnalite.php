<?php
namespace Listfic\Fonctionnalite;
class Fonctionnalite {
	static public $nom = "Fonctionnalite";
	static public $nomChamp = "fonctionnalite";
	static public $etiquette = "Fonctionnalité";
	static public $description = "La description de la Fonctionnalité";

	static public function html_bouton($objDossier){
		return "";
	}
	static public function html($objDossier) {
		$nomChamp = static::$nomChamp;
		return $objDossier->$nomChamp;
	}
	static public function prendreIni($objDossier, $ini){
		$nomChamp = static::$nomChamp;
		if (!$nomChamp) return;
		if (isset($ini[$nomChamp])) {
			$val = $ini[$nomChamp];
			if ($val==='true') $val = true;
			if ($val==='false') $val = false;
			$objDossier->prop('_'.$nomChamp, $val);
		} else {
			$objDossier->modifie = true;
		}
		return $objDossier->$nomChamp;
	}
	static public function creerIni($objDossier) {
		$nomChamp = static::$nomChamp;
		if (!$nomChamp) return;
		$resultat = '';
		$resultat .= "\t//".static::$description."\r\n";
		$resultat .= "\t'".static::$nomChamp."'";
		$resultat .= " => ".var_export($objDossier->$nomChamp, true).",\r\n";
		return $resultat;
	}
	public function admin_gerer() {
		return "";
	}
	static public function html_form($objDossier) {
		$nomChamp = static::$nomChamp;
		$resultat = '<input type="text" name="'.$nomChamp.'" id="'.$nomChamp.'" value="'.$objDossier->$nomChamp.'" size="38" />';
		return static::html_form_ligne($resultat);
	}
	static protected function html_form_ligne($champ){
		$nomChamp = static::$nomChamp;
		$resultat = '';
		$resultat .= '<div>';
		$resultat .= '<label for="'.$nomChamp.'">'.static::$etiquette.'</label>';
		$resultat .= $champ;
		$resultat .= '<span>'.static::$description.'</span>';
		$resultat .= '</div>';
		return $resultat;
	}
	static public function html_select($objDossier, $choix=array()){
		$nomChamp = static::$nomChamp;
		$resultat = '';
		$resultat .= '<select name="'.$nomChamp.'" id="'.$nomChamp.'">';
		$courant = $objDossier->$nomChamp;
		if (!is_string($courant)) $courant = var_export($courant,true);
		foreach ($choix as $etiquette=>$value) {
			$selected = ($value===$courant) ? ' selected="selected"' : '';
			$resultat .= '<option value="'.$value.'"'.$selected.'>'.$etiquette.'</option>';
		}
		$resultat .= '</select>';
		return $resultat;
	}
}

