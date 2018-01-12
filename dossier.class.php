<?php
include_once("dossier_abstract.class.php");
Dossier::init();
class Dossier extends Dossier_abstract {
	// Valeurs par défaut
	//protected $_description = "";
	static public function init() {
		// Pas besoin d'appeler le parent puisque le init du parent est déjà appelé
		static::$fonctionnalites[] = 'fct_fichiers';
		static::$fonctionnalites[] = 'fct_solution';
	}
	// ACCESSEURS ////////////////////////////////////////////////////////////////

	// METHODES //////////////////////////////////////////////////////////////////
	/** Ancienne version... AJUSTER */
	public function ligneProjet($admin=false){
		$resultat = '';
		$resultat .= ($this->visible) ? '<li class="projet clearfix">' : '<li class="projet off clearfix">';
		$resultat .= ($admin) ? $this->boutonsAdmin() : '';
		$resultat .= $this->creerBoutonsLiens();
		$resultat .= '<a target="_blank" href="'.$this->url.'">';
		$resultat .= ($this->prefixe) ? $this->prefixe. " : " : '';
		$resultat .= '<b>'.$this->titre.'</b>';
		$resultat .= '</a>';
		if ($this->source) $resultat .= '<sup style="cursor:pointer;" title="Code source inclus dans la page">&#9873;</sup>';
		$resultat .= '</li>';
		return $resultat;
	}
	/** Ancienne version... AJUSTER */
	public function boutonsAdmin(){
		$resultat = '<span class="admin">';

		//$resultat .= '<a style="" href="admin.php?a='.urlencode($this->url).'">Paramètres</a>';
		$ajax = (Listfic::$modeAjax) ? '&amp;ajax' : '';
		$commande = urlencode('['.urlencode($this->url).']');
		$resultat .= '<a style="" href="?admin'.$ajax.'&amp;a'.$commande.'">Paramètres</a>';
		if ($this->visible) {
			$resultat .= '<a class="visibilite toggle on" href="?admin'.$ajax.'&amp;v'.$commande.'=false">Masquer le projet</a>';
		} else {
			$resultat .= '<a class="visibilite toggle off" href="?admin'.$ajax.'&amp;v'.$commande.'=true">Afficher le projet</a>';
		}
		if ($this->fichiers) {
			$resultat .= '<a class="fichiers toggle on" href="?admin'.$ajax.'&amp;f'.$commande.'=false">Retirer le dossier de départ</a>';
		} else if (file_exists($this->pathFichiers())||file_exists($this->pathFichiers().".zip")) {
			$resultat .= '<a class="fichiers toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Publier le dossier de départ</a>';
		} else {
			$resultat .= '<a class="fichiers toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Créer un dossier de départ</a>';
		}
		if ($this->solution) {
			$resultat .= '<a class="solution toggle on" href="?admin'.$ajax.'&amp;s'.$commande.'=false">Retirer le dossier de solution</a>';
		} else if (file_exists($this->pathFichiers(static::PATH_SOLUTION))||file_exists($this->pathFichiers(static::PATH_SOLUTION).".zip")) {
			$resultat .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Publier le dossier de solution</a>';
		} else {
			$resultat .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Créer un dossier de solution</a>';
		}
		$resultat .= '</span>';
		return $resultat;
	}
	/** AJUSTER
	 * Retourne une liste de liens html associés au dossier
	 * @return string La liste de liens
	 */
	public function creerBoutonsLiens() {
		$liens = $this->creerLiens();
		$liens = implode("", $liens);
		if ($liens) return ' <span class="boutons-liens">'.$liens.'</span>';
		else return '';
	}
}
?>
