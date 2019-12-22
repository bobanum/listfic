<?php
/*TODO Enlever la notion de flag*/
namespace Listfic;

use Exception;
use ZipArchive;
class Directory {
	use \Listfic\Directory_Html;
	/** @var string - Le nom du petit fichier à laisser dans le directory */
	public $iniFilename = "_ini.php";
	//TODO Localization
	// public $labels = [
	// 	"files"=>"Files",
	// 	"solution"=>"Solution",
	// 	"directives"=>"Directives",
	// ];

	private $functionalities = [
		// 'ini' => null,
		// 'title' => null,
		// 'category' => null,
		// 'prefix' => null,
		// 'links' => null,
		// 'directives' => null,
		// 'source' => null,
		// 'visible' => null,
		'files' => null,
		'solution' => null,
	];
	public $listfic = null;
	/** @var string Le directory dans lequel mettre les files zip. */
	private $_zipPath = "/_zip";
	/** @var string Le path absolu vers le directory */
	private $_path;
	/** @var string L'adresse relative vers le directory en fonction de la page courante */
	private $_url;
	private $_name;
	private $updated_at;
	/** @var boolean Indique si le ini est modifié pour le sauvegarder */
	public $modified = false;
	/**
	 * Constructeur
	 * @param type $directory - Le directory à analyser. Pour l'instant doit être la racine du site.
	 * @throws Exception
	 */
	public function __construct($directory) {
		if (!is_dir($directory)) {
			$directory = dirname($directory);
		}
		$path = realpath($directory);
		if (!$path) {
			throw new \Exception ("Directory '$directory' inexistant");
		}
		$this->updated_at = filemtime($directory);
		$this->_path = $path;
		$this->_name = basename($path);
		$this->_url = $this->path2url($path);
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
	public function get_name() {
		return $this->_name;
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
		$test = $this->listfic->relative_domain($this->_path);
		if (!is_null($file)) {
			$result .= "/$file";
		}
		return $result;
	}
	public function toArray() {
		$result = [];
		$result['url'] = $this->url();
		$result['updated_at'] = $this->updated_at;
		$functionalities = array_map(function ($functionality) {
			return $functionality->toArray();
		}, $this->functionalities);
		foreach($functionalities as $name=>$functionality) {
			foreach($functionality as $nameField=>$field) {
				if ($name !== $nameField) {
					$name = "{$name}_{$nameField}";
				}
				$result[$name] = $field;
			}
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
	public function zzzrelative($de, $a){
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
	public function path2url($path) {
		$result = ($_SERVER['HTTP_HOST'] === "HTTP/1.1"?"http:/":"https:/").$_SERVER['HTTP_HOST'];
		$path = str_replace($_SERVER['DOCUMENT_ROOT'], "", $path);
		$path = str_replace("\\", "/", $path);

		if ($path && $path[0] !== "/") {
			$result .= "/";
		}
		$result .= $path;
		return $result;
	}
}
