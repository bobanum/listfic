<?php
namespace Listfic\Functionality;
class Source extends Functionality {
	static public $nom = "Source";
	static public $nomChamp = "source";
	static public $etiquette = "Source";
	static public $description = 'Booléen. Doit-on afficher la source?';
	static public function html_form($objDirectory) {
		$champ = static::html_select($objDirectory, array('Visible'=>'true','Cachée'=>'false',));
		return static::html_form_ligne($champ);
	}
}
