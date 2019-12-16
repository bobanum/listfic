<?php
namespace Listfic\Functionality;
class Links extends Functionality {
	static public $name = "Liens";
	static public $fieldName = "liens";
	static public $label = "Liens";
	static public $description = 'Un tableau de liens (étiquette=>url) ou une série de lignes (étiquette=url)';
	static public function getIni($directoryObject, $ini){
		parent::getIni($directoryObject, $ini);
		if (!is_array($directoryObject->liens)) {
			$lignes = trim($directoryObject->liens);
			if ($lignes) $lignes = preg_split("#\r\n|\n\r|\n|\r#", $lignes);
			else $lignes = array();
			$result = array();
			foreach ($lignes as $ligne) {
				$ligne = explode("=", $ligne, 2);
				$result[$ligne[0]] = $ligne[1];
			}
			$directoryObject->liens = $result;
		}
		return $directoryObject->liens;
	}
	static public function html_form($directoryObject) {
		$fieldName = static::$fieldName;
		$val = array();
		foreach ($directoryObject->liens as $label=>$url) {
			$val[] = $label."=".$url;
		}
		$val = implode("\r\n", $val);
		$champ = '';
		$champ .= '<textarea name="'.$fieldName.'" id="'.$fieldName.'" cols="40" rows="3" style="vertical-align:top;">'.$val.'</textarea>';
		return static::html_form_line($champ);
	}
}
