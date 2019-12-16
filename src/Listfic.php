<?php
// namespace Listfic;
// TODO : Ajouter automatiquement la ligne d'affichage de la source
// TODO : Permettre d'afficher plusieurs sources (dont les includes)
// TODO : Laisser le menu même si on ne montre pas la source
// TODO : Exemples = source automatique
// TODO : Exercices et travaux = source jamais (A moins d'une mention dans le ini)
// TODO : Un proxy qui donne les fichiers au besoin (et qui peut les zipper...)
// error_reporting(E_ALL);
// $liste = new Listfic();
// echo $liste->affichageArbo();

use Listfic\Directory;
class Listfic {
	public $domain = "";
	public $directories = array();
	public $arbo = array();
	public $admin = false;
	static public $ajaxMode = true;
	static public $page = "";
	static public $ini = array();
	static public $catbase = array("Exemples","Exercices","Travaux");
	static public $exclusions = array('^_', '_$', '^\.', 'theophile', 'nbproject', 'fontes', 'images');
	public function __construct($domain=".") {
			$this->domain = $domain;
			$this->getDirectories();
			$this->arbo = $this->arbo_sort($this->arbo());
	}
	/** Est exécuté au chargement */
	static public function init() {
		static::$page = basename($_SERVER['PHP_SELF']);
	}
	/**
	 * Retourne true si l'e nom de directory envoyé n'ect pas exclu'usager est administrateur
	 * @param string $name
	 * @return boolean
	 */
	static public function isAdmin($checkGet=true) {
		if (!isset($_SESSION['admin'])) return false;
		if (!$checkGet) return true;
		if (!isset($_GET['admin'])) return false;
		return true;
	}
	/**
	 * Retourne true si le nom de directory envoyé n'ect pas exclu
	 * @param string $name
	 * @return boolean
	 */
	public function isActive($name) {
		$name = basename($name);
		$exclusion = implode('|', static::$exclusions);
		if (preg_match("#$exclusion#", $name)) return false;
		return true;
	}
	/**
	 * Récupère les infos des directories non exclus
	 * @return this
	 */
	public function getDirectories() {
		$files = (glob($this->domain."/*", GLOB_ONLYDIR));
		foreach ($files as $directory) {
			if ($this->isActive($directory)) {
				$this->directories[$directory] = new Directory($directory);
			}
		}
		return $this;
	}
	/**
	 * Retourne les directories sous forme de tablbeaus imbriqués avec comme clé, la catégorie
	 * @param type $all Si faux, on filtre les résultats
	 * @return array
	 */
	public function arbo($all=false) {
		$result = array();
		foreach($this->directories as $chemin=>$directory){
			if (!empty($directory->visible) || static::isAdmin()) {
				$categorie = $directory->categorie;
				$categories = explode("/", $categorie);
				$ptr = &$result;
				while (count($categories)) {
					$categorie = array_shift($categories);
					if (!isset($ptr[$categorie])) $ptr[$categorie] = array();
					$ptr = &$ptr[$categorie];
				}
				$ptr[$chemin] = $directory;
			}
		}
		return $result;
	}
	public function arbo_sort($arbo) {
		$nouveau = array();
		// On commence par placer Les éléments standards
		foreach(static::$catbase as $cat){
			if (isset($arbo[$cat])) {
				$temp = $arbo[$cat];
				unset($arbo[$cat]);
				//uasort($temp, "static::comparerTitres");
				$temp = $this->arbo_sort($temp);
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
		foreach($arbo as $cat=>&$value){
			if (is_array($value)) {
				$triNouveau[] = strtolower($cat);
				$value = static::arbo_sort($value);
			}else{
				$triNouveau[] = strtolower($value->prefix.$value->titre.time());
			}
		}
		array_multisort($triNouveau, $arbo);
		$nouveau = array_merge($nouveau, $arbo);
		// On remet la catégorie "Autres" en dernier
		if (isset($autres)) $nouveau['Autres'] = $autres;
		return $nouveau;
	}
	public function categories() {
		$result = array_map(function ($directory) {
			return $directory->categorie;
		}, $this->directories);
		return true;
	}
	/**
	 * Retourne l'affichage de l'arborescence courante.
	 * @return string
	 */
	public function html_arbo() {
		$result = '';
		foreach($this->arbo as $key=>&$val) {
				$result .= '<article style="page-break-inside: avoid;">';
				$result .= '<h2>'.$key.'</h2>';
				$result .= static::html_arboBranch($val, static::isAdmin());
				$result .= '</article>';
		}
		return $result;
	}
	/**
	 * Retourne le chemin relatif d'un fichier vers un autre
	 * @param string $de - Le path de départ. ex.: a/b/c.php
	 * @param string $a - Le path d'arrivé. ex.: a/d/e.php
	 * @return string Le chemin relatif. ex.: ../d/e.php
	 */
	static public function relative($de, $a){
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
#	static public function creerAffichageArbo($arbo, $admin=false) {
#		$result = '<ul class="projets">';
#		foreach($arbo as $key=>&$val) {
#			if (is_a($val, '\Listfic\Directory')) {	// C'est un directory
#				$result .= '<li id="projet-'.$val->url.'" class="projet">'.$val->ligneProjet($admin).'</li>';
#			}else{
#				$result .= '<li><span>'.$key.'</span>';
#				$result .= static::creerAffichageArbo($val, $admin);
#				$result .= '</li>';
#			}
#		}
#		$result .= "</ul>";
#		return $result;
#	}
	//////////////////////////////////////////////////////////////////////////////
	// ADMINISTRATION ////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	/**
	 * [[Description]]
	 */
	static public function processDownload($data=null) {
		if (is_null($data)) {
			$data = $_GET;
		}
		if (!$data || count($data) === 0) {
			return "";
		}
		$keys = array_keys($data);
		$values = array_values($data);
		if (count($data) === 1 && $values[0] === "") {
			$data = Directory::decode($keys[0]);
			if (!$data) {
				return;
			}
			$type = $data[0];
			$nomDirectory = $data[1];
		} else {
			$type = $keys[0];
			$nomDirectory = $values[0];
		}
		$nomFic = $nomDirectory.'.zip';
//		var_dump($data, $keys, $values, $type, $nomDirectory, $nomFic);
//		exit;
		$path = static::getFile($type, $nomDirectory, $nomFic);
		if ($path) {
			$nomFinal = basename($path);
			header("content-type:application/zip");
			header("content-disposition:attachment; filename=".$nomFinal);
			readfile($path);
			exit;
		}
	}
	static public function getFile($type, $nomDirectory, $nomFic) {
		try {
			$directory = new Directory($nomDirectory);
		} catch (\Exception $exc) {
			exit($exc);
		}
		switch ($type) {
			case 'fichiers': case 'f':
				$path = $directory->path_zip();
				if ($directory->fichiers && file_exists($path)) {
					return $path;
				} else {
					return "";
				}
			break;
			case 'solution': case 's':
				$path = $directory->path_zip(Directory::$solution_suffix);
				if ($directory->solution && file_exists($path)) {
					return $path;
				} else {
					return "";
				}
			break;
			case 'url': case 'u':
				$path = $directory->path.'/'.$nomFic;
				if (file_exists($path)) {
					return $path;
				} else {
					return "";
				}
			break;
		}
		return "";
	}
	public function admin_process() {
		if (isset($_GET['quitter'])) {
			session_destroy();
			header("location:".static::$page."");exit();
		}
		static::login();
		if (!static::isAdmin()) return '';
		$reponses = implode('', Directory::executerStaticFunction('admin_gerer'));
		//$finir = false
			// | $this->admin_gererVisibilite()
			// | $this->admin_gererFichiers()
			// | $this->admin_gererSolution()
			// |
		$reponses .= $this->admin_processUpdate();
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
		foreach($_GET['v'] as $directory=>$etat) {
			$directory = $this->domaine."/".$directory;
			$directoryObject = new Directory($directory);
			if ($etat == 'true') $directoryObject->visible = true;
			else $directoryObject->visible = false;
			$directoryObject->mettreIni(true);
		}
		return true;
	}*/
	/*public function admin_gererFichiers() {
		//Rendre les fichiers de départ visibles
		if (!isset($_GET['f'])) return false;
		foreach($_GET['f'] as $directory=>$etat) {
			$directory = $this->domaine."/".$directory;
			$directoryObject = new Directory($directory);
			$directoryObject->fichiers = ($etat == 'true');
			$directoryObject->mettreIni(true);
		}
		return true;
	}*/
	/*public function admin_gererSolution() {
		//Rendre la solution visible
		if (!isset($_GET['s'])) return false;
		foreach($_GET['s'] as $directory=>$etat) {
			$directory = $this->domaine."/".$directory;
			$directoryObject = new Directory($directory);
			$directoryObject->solution = ($etat == 'true');
			$directoryObject->mettreIni(true);
		}
		return true;
	}*/
	public function admin_processUpdate() {
		if (!isset($_POST['modifier'])) return '';
		if (isset($_POST['annuler'])) return '';
		$directoryObject = new Directory($_POST['modifier']);
		$directoryObject->ini_get($_POST);
		$ini = $directoryObject->ini_create();
		//$ini = $_POST['ini'];
		$path = $directoryObject->path."/".Directory::$iniFilename."";
		unlink($path);
		file_put_contents($path, $ini);
		return $directoryObject->html_projectLine(true);
	}
	static public function urlScript($file=null) {
		$script = explode("\\", __FILE__);
		$page = explode("\\", $_SERVER['SCRIPT_FILENAME']);
		array_pop($script);
		array_pop($page);
		while (count($script)>0 && count($page)>0 && $script[0] === $page[0]) {
			array_shift($script);
			array_shift($page);
		}
		if (count($page) > 0) {
			$page = array_fill(0, count($page), "..");
		} else {
			$page = [];
		}
		$url = array_merge($page, $script);
		if ($file) {
			array_push($url, $file);
		}
		$url = implode("/", $url);
		return $url;
	}
	static public function restrict(){
		if (!static::isAdmin()) {
			if (!isset($_GET['l'])){
				header("location:?l"); exit;
			}
		}
	}
	public function admin_html(){
		if (!isset($_GET['admin'])) return '';
		$result = '';
		if (!$this->admin) {
			return $this->admin_html_loginForm();
		} else {
			$result .= $this->admin_html_logout ();
		}
		echo $_GET['a'];// TOFIX
		if (isset($_GET['a'])) {
			$result .= $this->admin_html_adminForm($_GET['a']);
		}
		return $result;
	}
	public function admin_html_loginForm(){
		$result = '<form action="" method="post">';
		$result .= '<input name="password" type="password" placeholder="Mot de passe" />';
		$result .= '<input name="login" type="hidden" />';
		$result .= '<input type="submit" />';
		$result .= '</form>';
		return $result;
	}
	public function admin_html_logout(){
		return '<div><a href="'.basename($_SERVER['PHP_SELF']).'">Quitter l\'administration</a></div>';
	}
	public function admin_html_adminForm($directory) {
		$directoryObject = new Directory($directory);
		$form = $directoryObject->html_updateForm();
		return $form;
	}
	public function head() {
		$result = '';
		if (static::$ajaxMode) {
			$result .= '<script src="'.self::urlScript("listfic.js").'"></script>';
		}
		return $result;
	}
	static public function login(){
		if (isset($_POST['login']) && $_POST['password']="elefan") {
			$_SESSION['admin'] = true;
			header("location:".static::$page."?admin"); exit;
		}
	}
	/** AJUSTER
	 * Retourne une liste HTML des éléments de l'arborescence envoyée
	 * @param array $arbo
	 * @return string
	 */
	static public function html_arboBranch($arbo, $admin=false) {
		$result = '<ul class="categorie">';
		foreach($arbo as $key=>&$val) {
			if (is_a($val, '\Listfic\Directory')) {	// C'est un directory
				$result .= $val->html_projectLine($admin);
			}else{
				$result .= '<li class="categorie"><span>'.$key.'</span>';
				$result .= static::html_arboBranch($val, $admin);
				$result .= '</li>';
			}
		}
		$result .= "</ul>";
		return $result;
	}
}
Listfic::init();
