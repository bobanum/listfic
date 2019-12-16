<?php
namespace Listfic;
// TODO : Ajouter automatiquement la line d'affichage de la source
// TODO : Permettre d'afficher plusieurs sources (dont les includes)
// TODO : Laisser le menu même si on ne montre pas la source
// TODO : Exemples = source automatique
// TODO : Exercices et travaux = source jamais (A moins d'une mention dans le ini)
// TODO : Un proxy qui donne les files au besoin (et qui peut les zipper...)
error_reporting(E_ALL);

use Listfic\Directory;
class Listfic {
	use \Listfic\Html;
	public $domain = "";
	public $directories = [];
	public $arbo = [];
	public $admin = false;
	static public $ajaxMode = true;
	static public $page = "";
	static public $ini = [];
	static public $catbase = array("Exemples","Exercices","Travaux");	//TODO Localize
	static public $exclusions = array('^_', '_$', '^\.', 'theophile', 'nbproject', 'fontes', 'images');	//TODO Put in config file
	public function __construct($domain=".") {
		$this->domain = $domain;
		$this->directories = $this->getDirectories();
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
	public function isExcluded($name) {
		$name = basename($name);
		$exclusion = implode('|', static::$exclusions);
		if (preg_match("#$exclusion#", $name)) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Récupère les infos des directories non exclus
	 * @return Directory[]
	 */
	public function getDirectories() {
		$result = [];
		$directories = (glob($this->domain."/*", GLOB_ONLYDIR));
		foreach ($directories as $directory) {
			if (!$this->isExcluded($directory)) {
				$result[$directory] = new Directory($directory);
			}
		}
		return $result;
	}
	/**
	 * Retourne les directories sous forme de tablbeaus imbriqués avec comme clé, la catégorie
	 * @param type $all Si faux, on filtre les résultats
	 * @return array
	 */
	public function arbo($all = false) {
		$result = array();
		foreach($this->directories as $path=>$directory){
			if ($all || $directory->visible || static::isAdmin()) {
				$category = $directory->category;
				$categories = explode("/", $category);
				$ptr = &$result;
				while (count($categories)) {
					$category = array_shift($categories);
					if (!isset($ptr[$category])) $ptr[$category] = array();
					$ptr = &$ptr[$category];
				}
				$ptr[$path] = $directory;
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
				$temp = $this->arbo_sort($temp);
				$nouveau[$cat] = $temp;
			}
		}
		// On enlève la catégorie "Autres" pour la mettre en dernier
		if (isset($nouveau['Autres'])) {
			$autres = $nouveau['Autres']; // Arranger???
			unset($nouveau['Autres']);
		}
		// On fait le tri des éléments restant (title et catégorie)
		$triNouveau = array();	// Variable qui sert à déterminer l'ordre de tri
		foreach($arbo as $cat=>&$value){
			if (is_array($value)) {
				$triNouveau[] = strtolower($cat);
				$value = static::arbo_sort($value);
			}else{
				$triNouveau[] = strtolower($value->prefix.$value->title.time());
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
			return $directory->category;
		}, $this->directories);
		return true;
	}
	/**
	 * Retourne le chemin relatif d'un fichier vers un autre
	 * @param string $de - Le path de départ. ex.: a/b/c.php
	 * @param string $a - Le path d'arrivé. ex.: a/d/e.php
	 * @return string Le chemin relatif. ex.: ../d/e.php
	 */
	static public function relative($from, $to){
		$from = realpath($from);
		$to = realpath($to);
		if (!is_dir($from)) $from = dirname($from);
		$from = str_replace("\\", "/", $from);
		$to = str_replace("\\", "/", $to);
		$from = explode("/", $from);
		$to = explode("/", $to);
		while(count($from) && count($to) && $from[0]==$to[0]) {
			array_shift($from);
			array_shift($to);
		}
		$path = "";
		$path .= str_repeat("../", count($from));
		$path .= implode("/", $to);
		return $path;
	}
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
			$directoryName = $data[1];
		} else {
			$type = $keys[0];
			$directoryName = $values[0];
		}
		$nomFic = $directoryName.'.zip';
//		var_dump($data, $keys, $values, $type, $directoryName, $nomFic);
//		exit;
		$path = static::getFile($type, $directoryName, $nomFic);
		if ($path) {
			$nomFinal = basename($path);
			header("content-type:application/zip");
			header("content-disposition:attachment; filename=".$nomFinal);
			readfile($path);
			exit;
		}
	}
	static public function getFile($type, $directoryName, $nomFic) {
		try {
			$directory = new Directory($directoryName);
		} catch (\Exception $exc) {
			exit($exc);
		}
		switch ($type) {
			case 'files': case 'f':
				$path = $directory->path_zip();
				if ($directory->files && file_exists($path)) {
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
		$reponses = implode('', Directory::executeStaticFunction('admin_gerer'));
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
		//Rendre les files de départ visibles
		if (!isset($_GET['f'])) return false;
		foreach($_GET['f'] as $directory=>$etat) {
			$directory = $this->domaine."/".$directory;
			$directoryObject = new Directory($directory);
			$directoryObject->files = ($etat == 'true');
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
	static public function login(){
		if (isset($_POST['login']) && $_POST['password']="elefan") {
			$_SESSION['admin'] = true;
			header("location:".static::$page."?admin"); exit;
		}
	}
}
Listfic::init();