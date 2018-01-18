<?php
// TODO : Ajouter automatiquement la ligne d'affichage de la source
// TODO : Permettre d'afficher plusieurs sources (dont les includes)
// TODO : Laisser le menu même si on ne montre pas la source
// TODO : Exemples = source automatique
// TODO : Exercices et travaux = source jamais (A moins d'une mention dans le ini)
// TODO : Un proxy qui donne les fichiers au besoin (et qui peut les zipper...)
//error_reporting(E_ALL);
//$liste = new Listfic();
//echo $liste->affichageArbo();
include_once "dossier_abstract.class.php";
include_once "fonctionnalite.class.php";
Listfic_abstract::init();
abstract class Listfic_abstract {
	public $domaine = "";
	public $dossiers = array();
	public $arbo = array();
	public $admin = false;
	static public $modeAjax = true;
	static public $page = "";
	static public $ini = array();
	static public $catbase = array("Exemples","Exercices","Travaux");
	static public $exclusions = array('^_', '_$', '^\.', 'theophile', 'nbproject', 'fontes', 'images');
	public function __construct($domaine=".") {
			$this->domaine = $domaine;
			$this->prendreDossiers();
			$this->arbo = $this->trierArbo($this->creerArbo());
	}
	/** Est exécuté au chargement */
	static public function init() {
		static::$page = basename($_SERVER['PHP_SELF']);
	}
	/**
	 * Retourne true si l'e nom de dossier envoyé n'ect pas exclu'usager est administrateur
	 * @param string $nom
	 * @return boolean
	 */
	static public function estAdmin($checkGet=true) {
		if (!isset($_SESSION['admin'])) return false;
		if (!$checkGet) return true;
		if (!isset($_GET['admin'])) return false;
		return true;
	}
	/**
	 * Retourne true si le nom de dossier envoyé n'ect pas exclu
	 * @param string $nom
	 * @return boolean
	 */
	public function estActif($nom) {
		$nom = basename($nom);
		$exclusion = implode('|', static::$exclusions);
		if (preg_match("#$exclusion#", $nom)) return false;
		return true;
	}
	/**
	 * Récupère les infos des dossiers non exclus
	 * @return this
	 */
	public function prendreDossiers() {
		$fics = (glob($this->domaine."/*", GLOB_ONLYDIR));
		foreach ($fics as $dossier) {
			if ($this->estActif($dossier)) $this->dossiers[$dossier] = new Dossier($dossier);
		}
		return $this;
	}
	/**
	 * Retourne les dossiers sous forme de tablbeaus imbriqués avec comme clé, la catégorie
	 * @param type $tout Si faux, on filtre les résultats
	 * @return array
	 */
	public function creerArbo($tout=false) {
		$resultat = array();
		foreach($this->dossiers as $chemin=>$dossier){
			if ($dossier->visible || static::estAdmin()) {
				$categorie = $dossier->categorie;
				$categories = explode("/", $categorie);
				$ptr = &$resultat;
				while (count($categories)) {
					$categorie = array_shift($categories);
					if (!isset($ptr[$categorie])) $ptr[$categorie] = array();
					$ptr = &$ptr[$categorie];
				}
				$ptr[$chemin] = $dossier;
			}
		}
		return $resultat;
	}
	public function trierArbo($arbo) {
		$nouveau = array();
		// On commence par placer Les éléments standards
		foreach(static::$catbase as $cat){
			if (isset($arbo[$cat])) {
				$temp = $arbo[$cat];
				unset($arbo[$cat]);
				//uasort($temp, "static::comparerTitres");
				$temp = $this->trierArbo($temp);
				$nouveau[$cat] = $temp;
			}
		}
		// On enlève la catégorie "Autres" pour la mettre en dernier
		if (isset($nouveau['Autres'])) {
			$autres = $nouveau['Autres']; // Arranger???
			unset($nouveau['Autres']);
		}
		// On fait le tri des éléments restant (titre et catégorie)
		$triNouveau = array();	// Variable qui sert à déterminer l'ordre de tri
		foreach($arbo as $cat=>&$valeur){
			if (is_array($valeur)) {
				$triNouveau[] = strtolower($cat);
				$valeur = static::trierArbo($valeur);
			}else{
				$triNouveau[] = strtolower($valeur->prefixe.$valeur->titre.time());
			}
		}
		array_multisort($triNouveau, $arbo);
		$nouveau = array_merge($nouveau, $arbo);
		// On remet la catégorie "Autres" en dernier
		if (isset($autres)) $nouveau['Autres'] = $autres;
		return $nouveau;
	}
	public function categories() {
		$resultat = array_map(function ($dossier) {
			return $dossier->categorie;
		}, $this->dossiers);

		var_dump($resultat);
		return true;
	}
	/**
	 * Retourne l'affichage de l'arborescence courante.
	 * @return string
	 */
	public function affichageArbo() {
		$resultat = '';
		foreach($this->arbo as $cle=>&$val) {
				$resultat .= '<article>';
				$resultat .= '<h2>'.$cle.'</h2>';
				$resultat .= static::creerAffichageArbo($val, static::estAdmin());
				$resultat .= '</article>';
		}
		return $resultat;
	}
	/**
	 * Retourne le chemin relatif d'un fichier vers un autre
	 * @param string $de - Le path de départ. ex.: a/b/c.php
	 * @param string $a - Le path d'arrivé. ex.: a/d/e.php
	 * @return string Le chemin relatif. ex.: ../d/e.php
	 */
	static public function relatif($de, $a){
		$de = realpath($de);
		$a = realpath($a);
		// echo $de."--ª".$a."<br/>";
		if (!is_dir($de)) $de = dirname($de);
		$de = str_replace("\\", "/", $de);
		$a = str_replace("\\", "/", $a);
		$de = explode("/", $de);
		$a = explode("/", $a);
		while(count($de) && count($a) && $de[0]==$a[0]) {
			array_shift($de);
			array_shift($a);
		}
		$path = "";
		$path .= str_repeat("../", count($de));
		$path .= implode("/", $a);
		return $path;
	}
	/** Devrait être surchargé
	 * Retourne une liste HTML des éléments de l'arborescence envoyée
	 * @param array $arbo
	 * @return string
	 */
	static public function creerAffichageArbo($arbo, $admin=false) {
		$resultat = '<ul class="projets">';
		foreach($arbo as $cle=>&$val) {
			if (is_a($val, "Dossier")) {	// C'est un dossier
				$resultat .= '<li id="projet-'.$val->url.'" class="projet">'.$val->ligneProjet($admin).'</li>';
			}else{
				$resultat .= '<li><span>'.$cle.'</span>';
				$resultat .= static::creerAffichageArbo($val, $admin);
				$resultat .= '</li>';
			}
		}
		$resultat .= "</ul>";
		return $resultat;
	}
	//////////////////////////////////////////////////////////////////////////////
	// ADMINISTRATION ////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	static public function gererTelecharger() {
		if (!count($_GET)==1) return;
		$data = array_keys($_GET);
		$data = Dossier::decoder($data[0]);
		if (!$data) return;
		if (isset($data[2])) $data[2] = str_replace(array('$0','$1'), array($data[0],$data[1]), $data[2]);
		else $data[2] = $data[1].'.zip';
		if (isset($data[3])) $data[3] = str_replace(array('$0','$1'), array($data[0],$data[1]), $data[3]);
		else $data[3] = $data[2];
		if (!($fichier = static::recupererFic($data))) return;
		$nomFinal = $data[3];
		header("content-type:application/zip");
		header("content-disposition:attachment; filename=".$nomFinal);
		echo $fichier;
		exit;
	}
	static public function recupererFic($data) {
		$type=$data[0];
		$nomDossier = $data[1];
		try {
			$dossier = new Dossier($nomDossier);
		} catch (Exception $exc) {
			exit(erreur(2));
		}
		switch ($type) {
			case 'fichiers': case 'f':
				$path = $dossier->pathFichiers(Dossier::PATH_ZIP);
				if ($dossier->fichiers && file_exists($path)) return file_get_contents($path);
			break;
			case 'solution': case 's':
				$path = $dossier->pathFichiers(Dossier::PATH_ZIP|Dossier::PATH_SOLUTION);
				if ($dossier->solution && file_exists($path)) return file_get_contents($path);
			break;
			case 'url': case 'u':
				$nomFic=$data[2];
				$nomFic = str_replace(array('$0','$1'), array($data[0],$data[1]), $nomFic);
				$path = $dossier->path.'/'.$nomFic;
				if (file_exists($path)) return file_get_contents($path);
			break;
		}
		return '';
	}
	public function admin_gerer() {
		if (isset($_GET['quitter'])) {
			session_destroy();
			header("location:".static::$page."");exit();
		}
		static::admission();
		if (!static::estAdmin()) return '';
		$reponses = implode('', Dossier::executerFctStatic('admin_gerer'));
		//$finir = false
			// | $this->admin_gererVisibilite()
			// | $this->admin_gererFichiers()
			// | $this->admin_gererSolution()
			// |
		$reponses .= $this->admin_gererModifier();
		// Je ne me souviens plus pourquoi ce if
		if (isset($_GET['t'])) {
			unset($_GET['t']);
		}
		if ($reponses) {
			if (isset($_GET['ajax'])) {
				header('content-type:text/xml');
				echo $reponses;
				exit;
			} else {
				header("location:index.php?admin");
				exit;
			}
		}
		return '';
	}
	/*public function admin_gererVisibilite() {
		//Rendre le projet visible
		if (!isset($_GET['v'])) return false;
		foreach($_GET['v'] as $dossier=>$etat) {
			$dossier = $this->domaine."/".$dossier;
			$objDossier = new Dossier($dossier);
			if ($etat == 'true') $objDossier->visible = true;
			else $objDossier->visible = false;
			$objDossier->mettreIni(true);
		}
		return true;
	}*/
	/*public function admin_gererFichiers() {
		//Rendre les fichiers de départ visibles
		if (!isset($_GET['f'])) return false;
		foreach($_GET['f'] as $dossier=>$etat) {
			$dossier = $this->domaine."/".$dossier;
			$objDossier = new Dossier($dossier);
			$objDossier->fichiers = ($etat == 'true');
			$objDossier->mettreIni(true);
		}
		return true;
	}*/
	/*public function admin_gererSolution() {
		//Rendre la solution visible
		if (!isset($_GET['s'])) return false;
		foreach($_GET['s'] as $dossier=>$etat) {
			$dossier = $this->domaine."/".$dossier;
			$objDossier = new Dossier($dossier);
			$objDossier->solution = ($etat == 'true');
			$objDossier->mettreIni(true);
		}
		return true;
	}*/
	public function admin_gererModifier() {
		if (!isset($_POST['modifier'])) return '';
		if (isset($_POST['annuler'])) return '';
		$objDossier = new Dossier($_POST['modifier']);
		$objDossier->prendreIni($_POST);
		$ini = $objDossier->creerIni();
		//$ini = $_POST['ini'];
		$path = $objDossier->path."/".Dossier::$nomIni."";
		unlink($path);
		file_put_contents($path, $ini);
		return $objDossier->ligneProjet(true);
	}
	static public function urlScript($fichier=null) {
		$script = explode("\\", __FILE__);
		$page = explode("\\", $_SERVER['SCRIPT_FILENAME']);
		array_pop($script);
		array_pop($page);
		while (count($script)>0 && count($page)>0 && $script[0] === $page[0]) {
			array_shift($script);
			array_shift($page);
		}
		$page = array_fill(0, count($page), "..");
		$url = array_merge($page, $script);
		if ($fichier) {
			array_push($url, $fichier);
		}
		$url = implode("/", $url);
		return $url;
	}
	static public function bloquer(){
		if (!static::estAdmin()) {
			if (!isset($_GET['l'])){
				header("location:?l"); exit;
			}
		}
	}
	public function admin_affichage(){
		if (!isset($_GET['admin'])) return '';
		$resultat = '';
		if (!$this->admin) return $this->admin_affichageFormLogin();
		else $resultat .= $this->admin_affichageLogout ();
echo $_GET['a'];		if (isset($_GET['a'])) $resultat .= $this->admin_affichageFormModifier();
		return $resultat;
	}
	public function admin_affichageFormLogin(){
		$resultat = '<form action="" method="post">';
		$resultat .= '<input name="password" type="password" placeholder="Mot de passe" />';
		$resultat .= '<input name="login" type="hidden" />';
		$resultat .= '<input type="submit" />';
		$resultat .= '</form>';
		return $resultat;
	}
	public function admin_affichageLogout(){
		return '<div><a href="'.basename($_SERVER['PHP_SELF']).'">Quitter l\'administration</a></div>';
	}
	public function admin_affichageFormModifier($dossier) {
		$objDossier = new Dossier($dossier);
		$form = $objDossier->affichageFormModifier();
		return $form;
	}
	public function head() {
		$resultat = '';
		if (static::$modeAjax) {
			$resultat .= '<script src="'.self::urlScript("listfic.js").'"></script>';
		}
		return $resultat;
	}
	static public function admission(){
		if (isset($_POST['login']) && $_POST['password']="elefan") {
			$_SESSION['admin'] = true;
			header("location:".static::$page."?admin"); exit;
		}
	}
}
