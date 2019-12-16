<?php
namespace Listfic\Functionality;
class Source extends Functionality {
	static public $name = "Source";
	static public $fieldName = "source";
	static public $label = "Source";
	static public $description = 'Booléen. Doit-on afficher la source?';
	static public function html_form($directoryObject) {
		$champ = static::html_select($directoryObject, array('Visible'=>'true','Cachée'=>'false',));
		return static::html_form_line($champ);
	}
}
