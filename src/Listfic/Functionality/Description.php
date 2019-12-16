<?php
namespace Listfic\Functionality;
class Description extends Functionality {
	static public $nom = "Description";
	static public $nomChamp = "description";
	static public $etiquette = "Description";
	static public $description = 'Une description plus ou moins longue du projet';
	static public function html($objDirectory) {
		$description = $objDirectory->description;
		$description = preg_split('#\r\n|\n\r|\n|\r#', $description);
		$description = array_reduce($description, function(&$r, $i) {
			$r .= '<p>'.$i.'</p>';
			return $r;
		});
		return '<div class="description">'.$description.'</div>';
	}
	static public function html_form($objDirectory) {
		$nomChamp = static::$nomChamp;
		$champ = '';
		$champ .= '<textarea name="'.$nomChamp.'" id="'.$nomChamp.'" cols="60" rows="7" style="vertical-align:top;">'.$objDirectory->$nomChamp.'</textarea>';
		return static::html_form_ligne($champ);
	}
}
