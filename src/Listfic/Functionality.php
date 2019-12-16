<?php
namespace Listfic\Functionality;
class Functionality {
	static public $nom = "Functionality";
	static public $nomChamp = "functionality";
	static public $etiquette = "Functionality";
	static public $description = "La description de la Functionality";

	static public function html_bouton($objDirectory){
		return "";
	}
	static public function html($objDirectory) {
		$nomChamp = static::$nomChamp;
		return $objDirectory->$nomChamp;
	}
	static public function prendreIni($objDirectory, $ini){
		$nomChamp = static::$nomChamp;
		if (!$nomChamp) return;
		if (isset($ini[$nomChamp])) {
			$val = $ini[$nomChamp];
			if ($val==='true') $val = true;
			if ($val==='false') $val = false;
			$objDirectory->prop('_'.$nomChamp, $val);
		} else {
			$objDirectory->modifie = true;
		}
		return $objDirectory->$nomChamp;
	}
	static public function creerIni($objDirectory) {
		$nomChamp = static::$nomChamp;
		if (!$nomChamp) return;
		$resultat = '';
		$resultat .= "\t//".static::$description."\r\n";
		$resultat .= "\t'".static::$nomChamp."'";
		$resultat .= " => ".var_export($objDirectory->$nomChamp, true).",\r\n";
		return $resultat;
	}
	static public function admin_gerer() {
		return "";
	}
	static public function html_form($objDirectory) {
		$nomChamp = static::$nomChamp;
		$resultat = '<input type="text" name="'.$nomChamp.'" id="'.$nomChamp.'" value="'.$objDirectory->$nomChamp.'" size="38" />';
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
	static public function html_select($objDirectory, $choix=array()){
		$nomChamp = static::$nomChamp;
		$resultat = '';
		$resultat .= '<select name="'.$nomChamp.'" id="'.$nomChamp.'">';
		$courant = $objDirectory->$nomChamp;
		if (!is_string($courant)) $courant = var_export($courant,true);
		foreach ($choix as $etiquette=>$value) {
			$selected = ($value===$courant) ? ' selected="selected"' : '';
			$resultat .= '<option value="'.$value.'"'.$selected.'>'.$etiquette.'</option>';
		}
		$resultat .= '</select>';
		return $resultat;
	}
}

