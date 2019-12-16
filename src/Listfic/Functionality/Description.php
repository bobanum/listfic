<?php
namespace Listfic\Functionality;
class Description extends Functionality {
	static public $name = "Description";
	static public $fieldName = "description";
	static public $label = "Description";
	static public $description = 'Une description plus ou moins longue du projet';
	static public function html($directoryObject) {
		$description = $directoryObject->description;
		$description = preg_split('#\r\n|\n\r|\n|\r#', $description);
		$description = array_reduce($description, function(&$r, $i) {
			$r .= '<p>'.$i.'</p>';
			return $r;
		});
		return '<div class="description">'.$description.'</div>';
	}
	static public function html_form($directoryObject) {
		$fieldName = static::$fieldName;
		$champ = '';
		$champ .= '<textarea name="'.$fieldName.'" id="'.$fieldName.'" cols="60" rows="7" style="vertical-align:top;">'.$directoryObject->$fieldName.'</textarea>';
		return static::html_form_line($champ);
	}
}
