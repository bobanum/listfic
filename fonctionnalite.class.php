<?php
include_once "dossier_abstract.class.php";
include_once "listfic_abstract.class.php";
abstract class Fonctionnalite {
	static public $nom = "Fonctionnalite";
	static public $nomChamp = "fonctionnalite";
	static public $etiquette = "Fonctionnalité";
	static public $description = "La description de la conctionnalité";

	static public function html_bouton($objDossier){
		return "";
	}
	static public function html($objDossier) {
		$nomChamp = static::$nomChamp;
		return $objDossier->$nomChamp;
	}
	static public function prendreIni($objDossier, $ini){
		$nomChamp = static::$nomChamp;
		if (!$nomChamp) return;
		if (isset($ini[$nomChamp])) {
			$val = $ini[$nomChamp];
			if ($val==='true') $val = true;
			if ($val==='false') $val = false;
			$objDossier->prop('_'.$nomChamp, $val);
		} else {
			$objDossier->modifie = true;
		}
		return $objDossier->$nomChamp;
	}
	static public function creerIni($objDossier) {
		$nomChamp = static::$nomChamp;
		if (!$nomChamp) return;
		$resultat = '';
		$resultat .= "\t//".static::$description."\r\n";
		$resultat .= "\t'".static::$nomChamp."'";
		$resultat .= " => ".var_export($objDossier->$nomChamp, true).",\r\n";
		return $resultat;
	}
	public function admin_gerer() {
		return "";
	}
	static public function html_form($objDossier) {
		$nomChamp = static::$nomChamp;
		$resultat = '<input type="text" name="'.$nomChamp.'" id="'.$nomChamp.'" value="'.$objDossier->$nomChamp.'" size="38" />';
		return static::html_form_ligne($resultat);
	}
	static protected function html_form_ligne($champ){
		$nomChamp = static::$nomChamp;
		$resultat = '';
		$resultat .= '<div>';
		$resultat .= '<label for="'.$nomChamp.'">'.static::$etiquette.'</label>';
		$resultat .= $champ;
		$resultat .= '<span>'.static::$description.'</span>';
		$resultat .= '</div>';
		return $resultat;
	}
	static public function html_select($objDossier, $choix=array()){
		$nomChamp = static::$nomChamp;
		$resultat = '';
		$resultat .= '<select name="'.$nomChamp.'" id="'.$nomChamp.'">';
		$courant = $objDossier->$nomChamp;
		if (!is_string($courant)) $courant = var_export($courant,true);
		foreach ($choix as $etiquette=>$value) {
			$selected = ($value===$courant) ? ' selected="selected"' : '';
			$resultat .= '<option value="'.$value.'"'.$selected.'>'.$etiquette.'</option>';
		}
		$resultat .= '</select>';
		return $resultat;
	}
}
class Fct_ini extends Fonctionnalite {
	static public $nom = "Fct_ini";
	static public $nomChamp = "";
	static public $etiquette = "Fichier INI";
	static public $description = 'Le fichier ini au complet';
	static public function html_form($objDossier){
		return "";
	}
	static public function html_bouton($objDossier){
		$resultat = '<a style="font-size: 150%; line-height: 0; position: relative; text-decoration: none; top: 0.21em;" href="?admin&a='.urlencode($objDossier->url).'">&#x270D;</a>';
		return $resultat;
	}
	public function admin_gerer() {
		if (!isset($_GET['a'])) return "";
		$dossier = array_keys($_GET['a']);
		$objDossier = new Dossier($dossier[0]);
		$resultat = $objDossier->affichageFormModifier();
		return $resultat;
	}
}
class Fct_titre extends Fonctionnalite {
	static public $nom = "Fct_titre";
	static public $nomChamp = "titre";
	static public $etiquette = "Titre";
	static public $description = 'Le titre qui s\'affiche dans la liste';
	static public function html($objDossier) {
		return '<a class="titre" target="_blank" href="'.$objDossier->url.'">'.$objDossier->titre.'</a>';
	}
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if (!$objDossier->titre) $objDossier->titre = static::recupererTitre($objDossier);
		return $objDossier->titre;
	}
	/**
	 * Analyse le fichier index pour en extraire le titre
	 * @return string
	 */
	static public function recupererTitre($objDossier){
		$path = $objDossier->path;
		$titre = basename($path);
		if (count($fics=glob($path."/index.*"))==0) return $titre;
		$html = file_get_contents($fics[0]);
		preg_match("#<title>(.*)</title>#", $html, $temp);
		if (count($temp) == 0) return $titre;
		$temp = trim($temp[1]);
		if ($temp == "") return $titre;
		return $temp;
	}
}
class Fct_categorie extends Fonctionnalite {
	static public $nom = "Fct_categorie";
	static public $nomChamp = "categorie";
	static public $etiquette = "Catégorie";
	static public $description = 'Categories sous forme "Catégorie/Sous-Catégorie/..."';
}
class Fct_prefixe extends Fonctionnalite {
	static public $nom = "Fct_prefixe";
	static public $nomChamp = "prefixe";
	static public $etiquette = "Préfixe";
	static public $description = 'Un préfixe à mettre devant le titre pour le tri "Cours 01 : "';
}
class Fct_liens extends Fonctionnalite {
	static public $nom = "Fct_liens";
	static public $nomChamp = "liens";
	static public $etiquette = "Liens";
	static public $description = 'Un tableau de liens (étiquette=>url) ou une série de lignes (étiquette=url)';
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if (!is_array($objDossier->liens)) {
			$lignes = trim($objDossier->liens);
			if ($lignes) $lignes = preg_split("#\r\n|\n\r|\n|\r#", $lignes);
			else $lignes = array();
			$resultat = array();
			foreach ($lignes as $ligne) {
				$ligne = explode("=", $ligne, 2);
				$resultat[$ligne[0]] = $ligne[1];
			}
			$objDossier->liens = $resultat;
		}
		return $objDossier->liens;
	}
	static public function html_form($objDossier) {
		$nomChamp = static::$nomChamp;
		$val = array();
		foreach ($objDossier->liens as $etiquette=>$url) {
			$val[] = $etiquette."=".$url;
		}
		$val = implode("\r\n", $val);
		$champ = '';
		$champ .= '<textarea name="'.$nomChamp.'" id="'.$nomChamp.'" cols="40" rows="3" style="vertical-align:top;">'.$val.'</textarea>';
		return static::html_form_ligne($champ);
	}
}
class Fct_source extends Fonctionnalite {
	static public $nom = "Fct_source";
	static public $nomChamp = "source";
	static public $etiquette = "Source";
	static public $description = 'Booléen. Doit-on afficher la source?';
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Visible'=>'true','Cachée'=>'false',));
		return static::html_form_ligne($champ);
	}
}
class Fct_visible extends Fonctionnalite {
	static public $nom = "Fct_visible";
	static public $nomChamp = "visible";
	static public $etiquette = "Visible";
	static public $description = 'Booléen. Le dossier est-il visible dans la liste? Il reste tout de même accessible.';
	public function admin_gerer() {
		//Rendre le projet visible
		if (!isset($_GET['v'])) return false;
		$resultat = '';
		foreach($_GET['v'] as $dossier=>$etat) {
			//$dossier = $this->domaine."/".$dossier;
			$objDossier = new Dossier($dossier);
			if ($etat == 'true') $objDossier->visible = true;
			else $objDossier->visible = false;
			$objDossier->mettreIni(true);
			$resultat .= $objDossier->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDossier){
		$data = 'v['.urlencode($objDossier->url).']';
		if ($objDossier->visible) {
			$resultat = '<a class="visibilite toggle on" href="?admin&'.$data.'=false">V</a>';
		} else {
			$resultat = '<a class="visibilite toggle off" href="?admin&'.$data.'=true">V</a>';
		}
		return $resultat;
	}
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Visible'=>'true','Caché'=>'false',));
		return static::html_form_ligne($champ);
	}
}
class Fct_fichiers extends Fonctionnalite {
	static public $nom = "Fct_fichiers";
	static public $nomChamp = "fichiers";
	static public $etiquette = "Fichiers";
	static public $description = 'Booléen. Y a-t-il des fichiers à télécharger?';

	public function admin_gerer() {
		//Rendre les fichiers de départ visibles
		if (!isset($_GET['f'])) return false;
		$resultat = '';
		foreach($_GET['f'] as $dossier=>$etat) {
			$objDossier = new Dossier($dossier);
			$objDossier->fichiers = ($etat == 'true');
			$objDossier->mettreIni(true);
			$resultat .= $objDossier->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDossier){
		$data = 'f['.urlencode($objDossier->url).']';
		if ($objDossier->fichiers) {
			$resultat = '<a class="fichiers toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($objDossier->pathFichiers())) {
			$resultat = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$resultat = '<a class="fichiers toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $resultat;
	}
	/**
	 * Retourne un lien HTML vers le zip des fichiers en vérifiant toutes les conditions
	 * @param Dossier $objDossier L'objet dossier à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le lien pour l'admin
	 */
	static public function html_lien($objDossier) {
		$path = $objDossier->path;
		$path .= "/".basename($path);

		$etiquette = static::$etiquette;
		$condition = $objDossier->fichiers;
		if (!file_exists($path)) return "";
		if ($condition===false) return "";
		$lien = Dossier::lienTelecharger($etiquette, array("fichiers", $objDossier->url), 'fichiers');
		if ($condition===true) return $lien;
		if (($time=strtotime($condition))!==false) {
			//TODO Réviser l'affichage par date...
			if ($time<time()) return $lien;
			else return "";
		}
		//TODO Réviser l'utilisation d'une autre adresse
		$path = $objDossier->path.'/'.$condition;
		$url = $objDossier->url.'/'.$condition;
		if (file_exists($path)) return '<a href="'.$url.'">'.$etiquette.'</a>';
		return "";
	}
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Disponible'=>'true','Non disponible'=>'false',));
		return static::html_form_ligne($champ);
	}
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if ($objDossier->fichiers == true) {
				$objDossier->fichiers = $objDossier->ajusterZip();
		}
	}
}
class Fct_solution extends Fonctionnalite {
	static public $nom = "Fct_solution";
	static public $nomChamp = "solution";
	static public $etiquette = "Solution";
	static public $description = 'Booléen. Y a-t-il des fichiers de solution?';
	public function admin_gerer() {
		//Rendre la solution visible
		if (!isset($_GET['s'])) return false;
		$resultat = '';
		foreach($_GET['s'] as $dossier=>$etat) {
			$objDossier = new Dossier($dossier);
			$objDossier->solution = ($etat == 'true');
			$objDossier->mettreIni(true);
			$resultat .= $objDossier->ligneProjet(true);
		}
		return $resultat;
	}
	static public function html_bouton($objDossier){
		$data = 's['.urlencode($objDossier->url).']';
		if ($objDossier->solution) {
			$resultat = '<a class="solution toggle on" href="?admin&'.$data.'=false">S</a>';
		} else if (file_exists($objDossier->pathFichiers(Dossier::PATH_SOLUTION))) {
			$resultat = '<a class="solution toggle off" href="?admin&'.$data.'=true">S</a>';
		} else {
			$resultat = '<a class="solution toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $resultat;
	}
	/**
	 * Retourne un lien HTML vers le zip de la solution en vérifiant toutes les conditions
	 * @param Dossier $objDossier L'objet dossier à analyser
	 * @return string Le <a> résultant
	 * @todo Permettre de forcer le lien pour l'admin
	 */
	static public function html_lien($objDossier) {
		$path = $objDossier->path;
		$path .= "/".basename($path)."_solution";

		$etiquette = static::$etiquette;
		$condition = $objDossier->solution;
		if (!file_exists($path)) return "";
		if ($condition===false) return "";
		$lien = Dossier::lienTelecharger($etiquette, array("solution", $objDossier->url), 'solution');
		if ($condition===true) return $lien;
		if (($time=strtotime($condition))!==false) {
			//TODO Réviser l'affichage par date...
			if ($time<time()) return $lien;
			else return "";
		}
		//TODO Réviser l'utilisation d'une autre adresse
		$path = $objDossier->path.'/'.$condition;
		$url = $objDossier->url.'/'.$condition;
		if (file_exists($path)) return '<a href="'.$url.'">'.$etiquette.'</a>';
		return "";
	}
	static public function prendreIni($objDossier, $ini){
		parent::prendreIni($objDossier, $ini);
		if ($objDossier->solution == true) {
			$objDossier->solution = $objDossier->ajusterZip(Dossier::PATH_SOLUTION);
		}
	}
	static public function html_form($objDossier) {
		$champ = static::html_select($objDossier, array('Disponible'=>'true','Non disponible'=>'false',));
		return static::html_form_ligne($champ);
	}
}
class Fct_dateAjout extends Fonctionnalite {
	static public $nom = "Fct_dateAjout";
	static public $nomChamp = "dateAjout";
	static public $etiquette = "Date d'ajout";
	static public $description = 'La date d\'ajout du projet';
}
class Fct_description extends Fonctionnalite {
	static public $nom = "Fct_description";
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
?>
