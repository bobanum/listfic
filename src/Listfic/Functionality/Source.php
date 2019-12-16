<?php
namespace Listfic\Fonctionnalite;
class Source extends Fonctionnalite {
	static public $nom = "Source";
	static public $nomChamp = "source";
	static public $etiquette = "Source";
	static public $description = 'Booléen. Doit-on afficher la source?';
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Visible'=>'true','Cachée'=>'false',));
		return static::html_form_ligne($champ);
	}
}
