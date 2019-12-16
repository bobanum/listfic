<?php
namespace Listfic\Functionality;
class Liens extends Functionality {
	static public $nom = "Liens";
	static public $nomChamp = "liens";
	static public $etiquette = "Liens";
	static public $description = 'Un tableau de liens (étiquette=>url) ou une série de lignes (étiquette=url)';
	static public function prendreIni($objDirectory, $ini){
		parent::prendreIni($objDirectory, $ini);
		if (!is_array($objDirectory->liens)) {
			$lignes = trim($objDirectory->liens);
			if ($lignes) $lignes = preg_split("#\r\n|\n\r|\n|\r#", $lignes);
			else $lignes = array();
			$resultat = array();
			foreach ($lignes as $ligne) {
				$ligne = explode("=", $ligne, 2);
				$resultat[$ligne[0]] = $ligne[1];
			}
			$objDirectory->liens = $resultat;
		}
		return $objDirectory->liens;
	}
	static public function html_form($objDirectory) {
		$nomChamp = static::$nomChamp;
		$val = array();
		foreach ($objDirectory->liens as $etiquette=>$url) {
			$val[] = $etiquette."=".$url;
		}
		$val = implode("\r\n", $val);
		$champ = '';
		$champ .= '<textarea name="'.$nomChamp.'" id="'.$nomChamp.'" cols="40" rows="3" style="vertical-align:top;">'.$val.'</textarea>';
		return static::html_form_ligne($champ);
	}
}
