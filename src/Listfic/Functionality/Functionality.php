<?php
namespace Listfic\Functionality;
class Functionality {
	static public $name = "Functionality";
	static public $fieldName = "functionality";
	static public $label = "Functionality";
	static public $description = "La description de la Functionality";

	static public function html_button($directoryObject){
		return "";
	}
	static public function html($directoryObject) {
		$fieldName = static::$fieldName;
		return $directoryObject->$fieldName;
	}
	static public function ini_get($directoryObject, $ini){
		$fieldName = static::$fieldName;
		if (!$fieldName) {
			return;
		}
		if (isset($ini[$fieldName])) {
			$val = $ini[$fieldName];
			if ($val==='true') {
				$val = true;
			} else if ($val==='false') {
				$val = false;
			}
			$directoryObject->prop('_'.$fieldName, $val);
		} else {
			$directoryObject->modified = true;
		}
		return $directoryObject->$fieldName;
	}
	static public function ini_create($directoryObject) {
		$fieldName = static::$fieldName;
		if (!$fieldName) {
			return;
		}
		$result = '';
		$result .= "\t//".static::$description."\r\n";
		$result .= "\t'".static::$fieldName."'";
		$result .= " => ".var_export($directoryObject->$fieldName, true).",\r\n";
		return $result;
	}
	static public function admin_process() {
		return "";
	}
	static public function html_form($directoryObject) {
		$fieldName = static::$fieldName;
		$result = '<input type="text" name="'.$fieldName.'" id="'.$fieldName.'" value="'.$directoryObject->$fieldName.'" size="38" />';
		return static::html_form_line($result);
	}
	static protected function html_form_line($champ){
		$fieldName = static::$fieldName;
		$result = '';
		$result .= '<div>';
		$result .= '<label for="'.$fieldName.'">'.static::$label.'</label>';
		$result .= $champ;
		$result .= '<span>'.static::$description.'</span>';
		$result .= '</div>';
		return $result;
	}
	static public function html_select($directoryObject, $choices=[]){
		$fieldName = static::$fieldName;
		$result = '';
		$result .= '<select name="'.$fieldName.'" id="'.$fieldName.'">';
		$current = $directoryObject->$fieldName;
		if (!is_string($current)) {
			$current = var_export($current,true);
		}
		foreach ($choices as $label=>$value) {
			$selected = ($value===$current) ? ' selected="selected"' : '';
			$result .= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
		}
		$result .= '</select>';
		return $result;
	}
}

