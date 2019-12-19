<?php
/*TODO Enlever la notion de flag*/
namespace Listfic;

use Exception;
use Listfic\Functionality\Files;
use Listfic\Functionality\Solution;
use ZipArchive;
class Directory {
	/** @var string - Le nom du petit fichier à laisser dans le directory */
	public $iniFilename = "_ini.php";
	/** @var string - Tableau des regexp des files/directories à ne pas inclure dans le ZIP. La clé n'est pas utilisée, mais représente la fonction du pattern. */
	public $zipExclusions = [
		/*'underscoreStart'=>'^_', */
		'underscoreEnd'=>'_$', 
		'dotStart'=>'^\.',
	];
	//TODO Localization
	// public $labels = [
	// 	"files"=>"Files",
	// 	"solution"=>"Solution",
	// 	"directives"=>"Directives",
	// ];

	private $functionalities = [
		'ini' => null,
		'title' => null,
		'category' => null,
		'prefix' => null,
		'links' => null,
		'directives' => null,
		'source' => null,
		'visible' => null,
		'files' => null,
		'solution' => null,
	];
	/** @var string Ce qui se trouve juste avant le url pour faire un path absolu. Déterminé au init. */
	private $root;
	/** @var string Le directory dans lequel mettre les files zip. */
	public $zipPath = "/_zip";
	public $solution_suffix = "_solution";
	public $files_suffix = "_fichiers";
	const PATH_ZIP = 1;
	const PATH_RELATIVE = 2;
	const PATH_SOLUTION = 4;
	/** @var string Le path absolu vers le directory */
	private $_path;
	/** @var string L'adresse relative vers le directory en fonction de la page courante */
	private $_url;
	private $_name;
	/** @var boolean Indique si le ini est modifié pour le sauvegarder */
	public $modified = false;
	// Valeurs par défaut
	// protected $_category = "Autres";
	// protected $_prefix = "";
	// protected $_links = [];
	// protected $_title = "";
	// protected $_source = false;
	// protected $_visible = false;
	// protected $_files = true;
	// protected $_solution = true;
	/**
	 * Constructeur
	 * @param type $directory - Le directory à analyser. Pour l'instant doit être la racine du site.
	 * @throws Exception
	 */
	public function __construct($directory=".") {
		$this->root = realpath('.');
		if (!is_dir($directory)) {
			$directory = dirname($directory);
		}
		$directory = realpath($directory);
		if (!file_exists($directory)) {
			throw new \Exception ("Directory '$directory' inexistant");
		}
		$this->_path = $directory;
		$this->_name = basename($directory);
		$this->_url = $this->relative_site($directory);
		$this->ini_get();
		$this->ini_put();
	}
	/**
	 * SETTER. Vérifie si un setgetter existe pour la propriété demandée
	 * @param string $name
	 * @param type $value
	 * @return Directory
	 * @throws Exception
	 * @todo Revise __get and __set
	 */
	public function __set($name, $value) {
		$set_name = "set_$name";
		if (method_exists($this, $set_name)) {
			return $this->$set_name($value);
		}
		if (array_key_exists($name, $this->functionalities)) {
			throw new Exception("Property '$name' is read only.");
		}
		$parts = explode("_", $name, 2);
		if (count($parts) === 2) {
			$name = $parts[0];
			$prop = $parts[1];
			//TODO Manage multiple underscores
			if (array_key_exists($name, $this->functionalities)) {
				return $this->functionalities[$name]->$prop = $value;
			}
		}
		throw new \Exception("Unknown property '{$name}'.");
	}
	/**
	 * GETTER.
	 * @param string $name
	 * @return type
	 * @throws Exception
	 */
	public function __get($name) {
		$get_name = "get_$name";
		if (method_exists($this, $get_name)) {
			return $this->$get_name();
		}
		if (array_key_exists($name, $this->functionalities)) {
			return $this->functionalities[$name];
		}
		$parts = explode("_", $name, 2);
		if (count($parts) === 2) {
			$name = $parts[0];
			$prop = $parts[1];
			//TODO Manage multiple underscores
			if (array_key_exists($name, $this->functionalities)) {
				return $this->functionalities[$name]->$prop;
			}
		}
		throw new \Exception("Unknown property '{$name}'.");
	}
	public function path($file = null) {
		$result = $this->_path;
		if (!is_null($file)) {
			$result .= "/$file";
		}
		return $result;
	}
	public function url($file = null) {
		$result = $this->_url;
		if (!is_null($file)) {
			$result .= "/$file";
		}
		return $result;
	}
	// ACCESSORS ////////////////////////////////////////////////////////////////
	/**
	 * FUNCTIONALITY
	 * @return \Directory
	 */
	// public function files() {
	// 	if (func_num_args()==0) {
	// 		return $this->_files;
	// 	}
	// 	$files = func_get_arg(0);
	// 	if ($files==true) {
	// 		$this->adjustSubDirectory();
	// 	}
	// 	$this->_files = $files;
	// 	return $this;
	// }
	/**
	 * FUNCTIONALITY
	 * @return \Directory
	 */
	// public function solution() {
	// 	if (func_num_args()==0) {
	// 		return $this->_solution;
	// 	}
	// 	$solution = func_get_arg(0);
	// 	if ($solution==true) {
	// 		$this->adjustSubDirectory(self::PATH_SOLUTION);
	// 	}
	// 	$this->_solution = $solution;
	// 	return $this;
	// }
	/**
	 * Retourne le chemin vers fichier Ini
	 * @return string
	 * @throws Exception
	 */
	public function get_path_iniFile() {
		return $this->path($this->iniFilename);
	}
	// METHODS //////////////////////////////////////////////////////////////////
	/**
	 * Exécute une certaine method sur tous les objet des fonctionnalités
	 * @param string $methodName
	 * @note La fonction prend des paramètres multiples
	 */
	public function executeFunction($methodName) {
		$params = [$this];
		for ($i = 1; $i < func_num_args(); $i += 1) {
			array_push($params, func_get_arg($i));
		}
		$result = [];
		foreach ($this->functionalities as $functionality) {
			$functionality = "Listfic\\Functionality\\$functionality";
			$test = class_exists("$functionality");
			if (method_exists($functionality, $methodName)) {
				$reponse = call_user_func_array([$functionality, $methodName], $params);
				if ($reponse) {
					$result[$functionality] = $reponse;
				}
			}
		}
		return $result;
	}
	/**
	 * Exécute une certaine method sur tous les objet des fonctionnalités
	 * @param string $method
	 * @note La fonction prend des paramètres multiples
	 */
	public function executeStaticFunction($method) {
		$params = [];
		for ($i=1; $i<func_num_args(); $i++) {
			array_push($params, func_get_arg($i));
		}
		$result = [];
		foreach ($this->functionalities as $functionality) {
			$functionality = "Listfic\\Functionality\\$functionality";
			if (method_exists($functionality, $method)) {
				$reponse = call_user_func_array([$functionality, $method], $params);
				if ($reponse) {
					$result[$functionality] = $reponse;
				}
			}
		}
		return $result;
	}
	/**
	 * Récupère les données d'initialisation à partir d'un array
	 * @param array $ini - Le fichier ini à traiter. Récupère le fichier en cas d'absence.
	 * @return Directory
	 */
	public function ini_get($ini = null) {
		if (is_null($ini)) {
			$path_iniFile = $this->path_iniFile;

			$ini = [];
			if (file_exists($path_iniFile)) {
				include($path_iniFile);
			}
		}
		foreach($this->functionalities as $class=>&$functionality) {
			$class = "\\Listfic\\Functionality\\" . ucfirst($class);
			$functionality = new $class($this, $ini);
		}

		// $this->executeFunction('ini_get', $ini);
		return $this;
	}
	public function ini_create() {
		// $ini = $this->executeFunction('ini_create');
		$ini = array_map(function($functionality) {
			return $functionality->ini_create();
		}, $this->functionalities);
		$ini = implode("\r\n", $ini);

		$result[] = "<?php";
		$result[] = "// Project : ".$this->url()." ";
		$result[] = "\$ini = [";
		$result[] = $ini;
		$result[] = "];";
		$result = implode("\r\n", $result);
		return $result;
	}
	public function ini_put($forcer=false) {
		if ($this->modified === false && !$forcer) {
			return $this;
		}
		$path_iniFile = $this->path_iniFile;
		$ini = $this->ini_create();
		file_put_contents($path_iniFile, $ini);
		return $this;
	}
	/**
	 * Retourne le chemin absolu du sous-directory de fichier ou de solution (ou autre);
	 * @param  string [$suffix=""] Le suffix vers le directory
	 * @return string Un chemin absolu vers le sous-directory
	 */
	public function path_file($suffix = "") {
		//TODO Transfer to File class
		$result = $this->path($this->_name.$suffix);
		if (file_exists($result)) {
			return $result;
		}
		if ($suffix === "") {
			$suffix = $this->files_suffix;
		}
		$result = $this->path($suffix);
		if (file_exists($result)) {
			return $result;
		}
		if ($suffix === $this->files_suffix) {
			$result = $this->path($this->_name);
			if (file_exists($result)) {
				return $result;
			}
		}
		return false;
	}
	/**
	 * Retourne le chemin du fichier zip en fonction de la variable statique $pathZip;
	 * @param  string [$suffix=""] Le suffix vers le directory zippé/àzipper
	 * @return string Un chemin absolu vers le fichier zip
	 */
	public function path_zip($suffix = "") {
		$result = $this->_name.$suffix.".zip";
		if (empty($this->zipPath)) {
			$directory = $this->path();
		} else if (substr($this->zipPath, 0, 2) === "./") {
			$directory = $this->path(substr($this->zipPath, 2));
		} else if (substr($this->zipPath, 0, 3) === "../") {
			$directory = dirname($this->path()).substr($this->zipPath, 3);
		} else if (substr($this->zipPath, 0, 1) === "/") {
			$directory = $this->root."/".substr($this->zipPath, 1);
		} else {
			$directory = $this->root."/".$this->zipPath;
		}
		$result = $directory."/".$result;
		return $result;
	}
	/**
	 * S'assure qu'il y a un fichier zip au besoin (et le crée) et retourne true si c'est le cas
	 * @return boolean
	 */
	public function adjustZip($flags=0) {
		$suffix = ($flags & self::PATH_SOLUTION) ? $this->solution_suffix : "";
		$pathFile = $this->path_file($suffix);
		$pathZip = $this->path_zip($suffix);
		//s'il n'y a pas de ini_fichiers, on vérifie s'il y a un directory du meme nom ou un zip
		if (file_exists($pathZip) && file_exists($pathFile)) {
			if (filemtime($pathZip)<filemtime($pathFile)) {
				unlink($pathZip);	// Le zip est désuet
			} else {
				return true;
			}
		}
		// Il n'y a que le zip
		if (file_exists($pathZip)) {
			return true;
		}
		// Il n'y a que le directory... on zippe;
		if (file_exists($pathFile)){
			$this->zip_SubDirectory($suffix);
			return true;
		}
		return false;
	}
	/**
	 * S'assure qu'il y a un fichier zip au besoin (et le crée) et retourne true si c'est le cas
	 * @return boolean
	 */
	public function adjustSubDirectory($flags=0) {
		$suffix = ($flags & self::PATH_SOLUTION) ? $this->solution_suffix : "";
		$pathFile = $this->path_file($suffix);
		$pathZip = $this->path_zip($suffix);
		// Il n'y a que le zip, on ne crée pas de directory
		if (file_exists($pathZip)) {
			return true;
		}
		// Il n'y a que le directory... on zippe;
		if (file_exists($pathFile)) {
			return true;
		} else {
			$this->subDirectory($suffix);
		}
		return false;
	}
	/**
	 * Zippe un sous-directory en lui donnant le même nom
	 * @param type $path Chemin absolu vers le directory à zipper
	 * @param boolean $supprimerOriginal
	 */
	public function zip_SubDirectory($suffix, $supprimerOriginal=false) {
		$pathFile = $this->path_file($suffix);
		$pathZip = $this->path_zip($suffix);
		$this->adjustDirectory(dirname($pathZip));
		$path = realpath($pathFile);
		$element = $this->_name;
		if ($suffix && $suffix !== $this->files_suffix) {
			$element .= $suffix;
		}
		$zip = new ZipArchive;
		$res = $zip->open($pathZip, ZipArchive::CREATE);
		if ($res === TRUE) {
			//TODO Réviser l'obligation de séparer directory et élément
			$this->zip_add($zip, $path, $element);
		}
		if ($supprimerOriginal === true) {
			$this->deleteFile($pathFile);
		}
	}
	/**
	 * Zippe un directory en lui donnant le même nom
	 * @param type $path Chemin absolu vers le directory à zipper
	 * @param boolean $supprimerOriginal
	 */
	public function zipper($path, $supprimerOriginal=false) {
		/*OBSELETE Remplacé par zipperSousDirectory*/
		$path = realpath($path);
		$element = basename($path);
		$zip = new ZipArchive;
		$res = $zip->open($path.'.zip', ZipArchive::CREATE);
		if ($res === TRUE) {
			//TODO Réviser l'obligation de séparer directory et élément
			$this->zip_add($zip, $path, $element);
		}
		$zip->close();
		if ($supprimerOriginal === true) $this->deleteFile($path);
	}
	/**
	 * Ajoute un fichier ou un directory à un zip
	 * @param type $zip
	 * @param type $path
	 * @param type $element
	 */
	public function zip_add($zip, $path, $element) {
		if ($this->zip_validName($path)==false) {
			return false;
		}
		if (!file_exists($path)) {
			return false;
		}
		if (is_dir($path)) {
			$files = glob($path."/*");
			foreach($files as $file) {
				$name = basename($file);
				$this->zip_add($zip, "$path/$name", "$element/$name");
			}
		} else if (filesize($path)==0) {
			$path = dirname(dirname($path)).'/'.basename($path);
			$this->zip_add($zip, $path, $element);
		} else {
			if (substr($path, -4) === ".php") {
				$code = file_get_contents($path);
				$pattern = '#<[^<]+source\.php[^>]+>#';
				$code = preg_replace($pattern, '', $code);
				$zip->addFromString($element, $code);
			} else {
				$zip->addFile($path, $element);
			}
		}
		return true;
	}
	/**
	 * Retourne true si le fichier doit être inclus dans le zip en fonction de exclusionsZip
	 * @param type $name
	 * @return boolean
	 */
	public function zip_validName($name) {
		$name = basename($name);
		foreach($this->zipExclusions as $patt) {
                    if (preg_match("#$patt#", $name)) {
                        return false;
                    }
		}
		return true;
	}
	/**
	 * Zippe un directory en lui donnant le même nom
	 * @param type $path Chemin absolu vers le directory à zipper
	 * @param boolean $supprimerOriginal
	 */
	public function subDirectory($suffix='', $delete=false) {
		$path = $this->path();
		$element = basename($path);
		$pathDirectory = $path."/".$element.$suffix;
		if (file_exists($pathDirectory)) {
			if ($delete) {
				$this->deleteFile($pathDirectory);
			} else {
				return $this;
			}
		}
		$this->subDirectory_add($path, $pathDirectory, true);
	}
	public function adjustDirectory($directoryPath) {
		if (file_exists($directoryPath)) {
			return $directoryPath;
		} else if (!$directoryPath) {
			return "";
		} else {
			$this->adjustDirectory(dirname($directoryPath));
			mkdir($directoryPath);
			return $directoryPath;
		}
	}
	/**
	 * Ajoute un fichier ou un directory à un sous-directory
	 * @param type $pathFrom Le chemin absolu vers l'élément à copier
	 * @param type $pathTo Le chemin absolu vers le directory dans lequel on doit copier l'élément
	 */
	public function subDirectory_add($pathFrom, $pathTo, $force=false) {
		$aliasesNames = ["images", "scripts", "script", "style", "styles"];
		$nonAliasExtensions = ["php","htm","html","xhtml"];
		if (!$force && $this->subDirectory_validName($pathFrom) === false) {
			return false;
		}
		//$path = $pathDestination."/".basename($pathOrigine).$suffix;
		$name = basename($pathFrom);
		$ext = pathinfo($pathFrom, PATHINFO_EXTENSION);
		if (in_array($name, $aliasesNames)) {
			file_put_contents($pathTo, "");
		} elseif (is_dir($pathFrom)) {
			$files = glob($pathFrom."/*");
			mkdir($pathTo);
			foreach($files as $file) {
				$this->subDirectory_add($file, $pathTo."/".basename($file));
			}
		} elseif (in_array($ext, $nonAliasExtensions)) {
			copy($pathFrom, $pathTo);
		} else {
			file_put_contents($pathTo, "");
		}
		return true;
	}
	/**
	 * Retourne true si le fichier doit être includ dans le zip en fonction de exclusionsZip
	 * @param type $path
	 * @return boolean
	 */
	public function subDirectory_validName($path) {
		$name = basename($path);
		$valid = $this->zip_validName($name);
		if (!$valid) {
			return false;
		}
		$directory = preg_quote(basename($this->path(), "#"));
		if (is_dir($path) && preg_match("#^".$directory.".*#", $name)) {
			return false;
		}
		if (preg_match("#^".$directory.".*\.zip#", $name)) {
			return false;
		}
		return true;
	}
	/** Devrait être surchargée par l'application */
	public function html_projectLine($admin = false){
		$result = '';
		$result .= ($this->visible) ? '<li class="projet">' : '<li class="projet off">';
		$result .= ($admin) ? $this->html_adminButtons() : '';
		$result .= $this->createLinksButtons();
		$result .= '<a target="_blank" href="'.$this->url().'/">';
		$result .= ($this->prefix->value) ? $this->prefix->value. " : " : '';
		$result .= '<b>'.$this->title->value.'</b>';
		if ($this->source) {
			$result .= '<span class="source_visible" title="Code source inclus dans la page">&#9873;</span>';
		}
		$result .= '</a>';
		$result .= '</li>';
		return $result;
	}
	//TODO Create Functionality
	public function html_screen() {
		$name = basename($this->url()).".png";
		if (!file_exists($this->path($name))) {
			return "";
		}
		return '<div class="ecran"><img src="'.$this->url($name).'" alt="'.$this->title->value.'" /></div>';
	}
	public function html_iframe() {
		return false;	//TODO Vérifier la pertinence
	}
	public function html_updateForm() {
		$path_iniFile = $this->path_iniFile;
		$ini = file_get_contents ($path_iniFile);
		$form = '';
		$form .= '<div id="form" style="position:fixed; left:0; top:0; right:0; bottom:0; background-color:rgba(0,0,0,.5);z-index:2000; line-height:2em;">';
		$form .= '<form id="modifier" method="post" action="?admin&amp;ajax" style="width:800px; margin:0 auto; background-color:white; padding:2em; color:black; margin-top:2em;box-shadow:0 0 1em;">';
		$form .= '<h2 style="margin:0; text-align:center;">Modifier un projet</h2>';
		//$form .= '<div><textarea name="ini" cols="100" rows="30">'.$ini.'</textarea></div>';
		$form .= implode("", $this->executeFunction('html_form'));
		$form .= '<div><input name="modifier" type="hidden" value="'.urlencode($this->url()).'" /><input type="submit" name="envoyer" /><input type="submit" name="annuler" value="Annuler"/></div>';
		$form .= '</form>';
		$form .= '</div>';
		return $form;
	}
	/**
	 * Retourne une liste de links html associés au directory
	 * @return array La liste de links
	 */
	public function createLinks() {
		//TODO Vérifier la précéance entre les links ds ini et la présence du fichier. Présemtement, le fichier l'emporte
		$result = [];
		// Link DIRECTIVES
		$result = array_replace($result, $this->directives->html_links());
		// Link FICHIERS
		$result = array_replace($result, $this->files->html_links());
		
		// $label = $this->files->label;
		// if (!isset($this->links->value[$label]) && ($link = $this->links->html_link()) !== "") {
		// 	$result[] = $link;
		// }
		// Link Solution
		$label = $this->solution->label;
		if (!isset($this->links->value[$label]) && ($link = $this->solution->html_link()) !== "") {
			$result[] = $link;
		}
		// Autres links
		foreach ($this->links->value as $label=>$address) {
			// C'est un link absolu : on ne vérifie pas la présence
			if (preg_match('#^/|^[a-z+]*:\/\/#', $address)) {
				$result[] = '<a href="'.$address.'" title="'.$label.'"></a>';
			} else {
				// $path = $this->path($this->url());
				$url = $this->url($address);
				//if (file_exists($path))
					$result[] = '<a href="'.$url.'" title="'.$label.'"></a>';
			}
		}
		return $result;
	}
	public function link_files($flags=0) {
		// Lien FICHIERS
		$type = ($flags & self::PATH_SOLUTION) ? "solution" : "files";
		$label = $this->labels[$type];
		$data[$type] = $this->url();
		return $this->link_download($label, $data, $type);
	}
	public function link_download($label, $data, $class='') {
		$attrs = [];
		if ($class) {
			$attrs['class'] = 'telecharger '.$class.'';
		} else {
			$attrs['class'] = 'telecharger';
		}
		$attrs['href'] = 'telecharger.php?'.$data[0].'='.$data[1].'';
//		$attrs['href'] = 'telecharger.php?'.$this->encoder($data).'';
		$attrs['title'] = $label;
		$attrs = $this->attrString($attrs);
		return '<a '.$attrs.'></a>';
	}
	public function attrString($attrs) {
		$result = [];
		foreach ($attrs as $name=>$val) {
			$result[] = ''.$name.'="'.htmlspecialchars($val).'"';
		}
		$result = implode(" ", $result);
		return $result;
	}
	public function encode($data) {
		$data = serialize($data);
		$data = base64_encode($data);
		$data = str_rot13($data);
		$data = str_replace('=','',$data);
		$data = strtr($data, '+/', '-_');
		return $data;
	}
	public function decode($data) {
		$data = strtr($data, '-_', '+/');
		$data = str_pad($data, 4*ceil(strlen($data)/4), "=", STR_PAD_RIGHT);
		$data = str_rot13($data);
		$data = base64_decode($data);
		$data = unserialize($data);
		return $data;
	}

	/**
	 * Supprime au complet un fichier ou un directory
	 * @param type $file Chemin vers le directory ou fichier
	 * @return boolean Retourne true s'il y a eu suppression
	 */
	public function deleteFile($file) {
		if (!file_exists($file)) return false;
		if (is_dir($file)) {
			$contenu = glob("{$file}/*");
			foreach($contenu as $nomfic) {
				$this->deleteFile($nomfic);
			}
			rmdir($file);
		}else{
			unlink($file);
		}
		return true;
	}
	/**
	 * Retourne la liste html de la portion d'arbo envoyée
	 * @param type $arbo
	 * @return string Du html
	 */
	public function relative($de, $a){
		$de = realpath($de);
		$a = realpath($a);
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
	public function relative_site($path){
		return $this->relative($this->root, $path);
	}
	public function relative_directory($path){
		$path = $this->relative_site($path);
		$path = substr($path, strlen($this->url())+1);
		return $path;
	}
	// ACCESSORS ////////////////////////////////////////////////////////////////

	// METHODS //////////////////////////////////////////////////////////////////
	/** Ancienne version... AJUSTER */
	public function html_adminButtons(){
		$result = '<span class="admin">';

		//$result .= '<a style="" href="admin.php?a='.urlencode($this->url()).'">Paramètres</a>';
		$ajax = (Listfic::$ajaxMode) ? '&amp;ajax' : '';
		$commande = urlencode('['.urlencode($this->url()).']');
		$result .= '<a style="" href="?admin'.$ajax.'&amp;a'.$commande.'">Paramètres</a>';
		if ($this->visible) {
			$result .= '<a class="visibilite toggle on" href="?admin'.$ajax.'&amp;v'.$commande.'=false">Masquer le projet</a>';
		} else {
			$result .= '<a class="visibilite toggle off" href="?admin'.$ajax.'&amp;v'.$commande.'=true">Afficher le projet</a>';
		}
		if ($this->files) {
			$result .= '<a class="files toggle on" href="?admin'.$ajax.'&amp;f'.$commande.'=false">Retirer le directory de départ</a>';
		} else if (file_exists($this->path_file())||file_exists($this->path_zip())) {
			$result .= '<a class="files toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Publier le directory de départ</a>';
		} else {
			$result .= '<a class="files toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Créer un directory de départ</a>';
		}
		if ($this->solution) {
			$result .= '<a class="solution toggle on" href="?admin'.$ajax.'&amp;s'.$commande.'=false">Retirer le directory de solution</a>';
		} else if (file_exists($this->path_file($this->solution_suffix))||file_exists($this->path_zip($this->solution_suffix))) {
			$result .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Publier le directory de solution</a>';
		} else {
			$result .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Créer un directory de solution</a>';
		}
		$result .= '</span>';
		return $result;
	}
	/** AJUSTER
	 * Retourne une liste de links html associés au directory
	 * @return string La liste de links
	 */
	public function createLinksButtons() {
		$links = $this->createLinks();
		$links = implode("", $links);
		if ($links) {
			return ' <span class="buttons-links">'.$links.'</span>';
		}
		else return '';
	}
}
