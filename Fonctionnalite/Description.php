<?php
namespace Listfic\Fonctionnalite;
class Description extends Fonctionnalite {
	static public $nom = "Description";
	static public $nomChamp = "description";
	static public $etiquette = "Description";
	static public $description = 'Une description plus ou moins longue du projet';
	static public function html($objDossier) {
		$description = $objDossier->description;
		$description = preg_split('#\r\n|\n\r|\n|\r#', $description);
		$description = array_reduce($description, function(&$r, $i) {
			$r .= '<p>'.$i.'</p>';
			return $r;
		});
		return '<div class="description">'.$description.'</div>';
	}
	static public function html_form($objDossier) {
		$nomChamp = static::$nomChamp;
		$champ = '';
		$champ .= '<textarea name="'.$nomChamp.'" id="'.$nomChamp.'" cols="60" rows="7" style="vertical-align:top;">'.$objDossier->$nomChamp.'</textarea>';
		return static::html_form_ligne($champ);
	}
}
