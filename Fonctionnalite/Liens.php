<?php
namespace Listfic\Fonctionnalite;
class Liens extends Fonctionnalite {
	static public $nom = "Liens";
	static public $nomChamp = "liens";
	static public $etiquette = "Liens";
	static public $description = 'Un tableau de liens (étiquette=>url) ou une série de lignes (étiquette=url)';
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if (!is_array($objDossier->liens)) {
			$lignes = trim($objDossier->liens);
			if ($lignes) $lignes = preg_split("#\r\n|\n\r|\n|\r#", $lignes);
			else $lignes = array();
			$resultat = array();
			foreach ($lignes as $ligne) {
				$ligne = explode("=", $ligne, 2);
				$resultat[$ligne[0]] = $ligne[1];
			}
			$objDossier->liens = $resultat;
		}
		return $objDossier->liens;
	}
	static public function html_form($objDossier) {
		$nomChamp = static::$nomChamp;
		$val = array();
		foreach ($objDossier->liens as $etiquette=>$url) {
			$val[] = $etiquette."=".$url;
		}
		$val = implode("\r\n", $val);
		$champ = '';
		$champ .= '<textarea name="'.$nomChamp.'" id="'.$nomChamp.'" cols="40" rows="3" style="vertical-align:top;">'.$val.'</textarea>';
		return static::html_form_ligne($champ);
	}
}
