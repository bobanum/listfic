<?php
namespace Listfic\Functionality;
class Links extends Functionality {
	static public $name = "Liens";
	static public $fieldName = "links";
	static public $label = "Liens";
	static public $description = 'Un tableau de links (étiquette=>url) ou une série de lines (étiquette=url)';
	static public function ini_get($directoryObject, $ini){
		parent::ini_get($directoryObject, $ini);
		if (!is_array($directoryObject->links)) {
			$lines = trim($directoryObject->links);
			if ($lines) {
				$lines = preg_split("#\r\n|\n\r|\n|\r#", $lines);
			} else {
				$lines = [];
			}
			$result = [];
			foreach ($lines as $line) {
				$line = explode("=", $line, 2);
				$result[$line[0]] = $line[1];
			}
			$directoryObject->links = $result;
		}
		return $directoryObject->links;
	}
	static public function html_form($directoryObject) {
		$fieldName = static::$fieldName;
		$val = [];
		foreach ($directoryObject->links as $label=>$url) {
			$val[] = $label."=".$url;
		}
		$val = implode("\r\n", $val);
		$champ = '';
		$champ .= '<textarea name="'.$fieldName.'" id="'.$fieldName.'" cols="40" rows="3" style="vertical-align:top;">'.$val.'</textarea>';
		return static::html_form_line($champ);
	}
}
