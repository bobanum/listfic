<?php
include_once "listfic_abstract.class.php";
include_once "dossier.class.php";
class Listfic extends Listfic_abstract {
	/** AJUSTER
	 * Retourne une liste HTML des éléments de l'arborescence envoyée
	 * @param array $arbo
	 * @return string
	 */
	static public function creerAffichageArbo($arbo, $admin=false) {
		$resultat = '<ul class="categorie">';
		foreach($arbo as $cle=>&$val) {
			if (is_a($val, "Dossier")) {	// C'est un dossier
				$resultat .= $val->ligneProjet($admin);
			}else{
				$resultat .= '<li class="categorie"><span>'.$cle.'</span>';
				$resultat .= static::creerAffichageArbo($val, $admin);
				$resultat .= '</li>';
			}
		}
		$resultat .= "</ul>";
		return $resultat;
	}
}
?>
