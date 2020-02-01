<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Files extends Functionality {
	use ZipTrait {
		__construct as private ZipTrait__construct;
	}
	static protected $fieldName = 'files';
	static protected $separator = '_';
	static protected $suffix = 'files';
	static public $_pathZip = '';
	protected $_value = false;
	protected $choices = [
		'Disponible'=>'true',
		'Non disponible'=>'false',
	];
	public function __construct($directory, $ini=[]) {
		parent::__construct($directory, $ini);
		$this->ZipTrait__construct();
	}
	static public function admin_process() {
		if (!isset($_GET[static::$fieldName])) {
			return false;
		}
		$result = static::admin_activate($_GET[static::$fieldName]);
		$result = array_map(function($directory) {
			return $directory->html_projectLine(true);
		}, $result);
		return implode("", $result);
	}
	static protected function admin_activate($projects, $forcedState=null) {
		$result = [];
		foreach($projects as $directory=>$status) {
			$directoryObject = new Directory($directory);
			if (is_null($forcedState)) {
				$directoryObject->activate($status);
			} else {
				$directoryObject->activate($forcedState);
			}
			$result[] = $directoryObject;
		}
		return $result;
	}
	protected function activate($state=null) {
		$result = [];
		if (is_null($state) || $state === "") {
			$this->_value = !$this->_value;
		} else {
			$this->_value = ($state === 'true' || $state === true);
		}
		$this->ini_put(true);
		return $this->_value;
	}
	public function html_button(){
		$data = static::$fieldName.'['.urlencode($this->directory->url()).']';
		if ($this->value) {
			$result = '<a class="'.static::$fieldName.' toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($this->path())) {
			$result = '<a class="'.static::$fieldName.' toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$result = '<a class="'.static::$fieldName.' toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
		}
		return $result;
	}
	/**
	 * Retourne un link HTML vers le zip des files en vérifiant toutes les conditions
	 * @return array Le <a> résultant
	 * @todo Permettre de forcer le link pour l'admin
	 */
	public function html_links() {
		$path = $this->path();
		$label = $this->label;
		$condition = $this->value;
		$result = [];

		if (!file_exists($path)) {
			return $result;
		}
		if ($condition === false) {
			return $result;
		}
		$result[static::$fieldName] = $this->directory->link_download($label, ["files", $this->directory->url()], 'files');
		if ($condition === true) {
			return $result;
		}
		if (($time = strtotime($condition)) !== false) {
			//TODO Réviser l'affichage par date...
			if ($time < time()) {
				return $result;
			} else {
				return [];
			}
		}
		//TODO Réviser l'utilisation d'une autre adresse
		$path = $this->directory->path($condition);
		$url = $this->directory->url($condition);
		if (file_exists($path)) {
			return [static::$fieldName => '<a href="'.$url.'">'.$label.'</a>'];
		}
		return [];
	}
	public function html_form() {
		$result = $this->html_select($this->choices);
		$result = $this->html_form_line($result);
		return $result;
	}
	public function ini_get($ini){
		parent::ini_get($ini);
		if ($this->value === true) {
			$this->value = $this->adjustZip();
		}
	}
	public function toArray() {
		$result = [
			'visible' => $this->value,
			'has_folder' => file_exists($this->path),
			'url_folder' => $this->url,
			'has_archive' => file_exists($this->zipPath),
			'url_archive' => $this->zipUrl,
		];
		return [static::$fieldName => $result];
	}
	public function get_root() {
		return $this->directory->path();
	}
	public function get_name() {
		return $this->directory->name;
	}
	/**
	 * Retourne le chemin absolu du sous-directory de initial ou de solution (ou autre);
	 * @param  string [$suffix=""] Le suffix vers le directory
	 * @return string Un chemin absolu vers le sous-directory
	 */
	public function get_path() {
		$result = $this->root.'/'.static::$separator.static::$suffix;
		return $result;
	}
	public function get_url() {
		var_dump($this->path);
		return $this->directory->path2url($this->path);
	}
	public function get_zipPath() {
		return $this->archiveFolder.'/'.$this->zipFilename;
	}
	public function get_zipUrl() {
		$result = dirname(dirname($this->root)).'/'.static::$suffix.'/'.$this->name;
		return $this->directory->path2url($result);
	}
	public function get_zipFilename() {
		return $this->zippedFolder.'.zip';
	}
	public function get_zippedFolder() {
		return $this->name.static::$separator.static::$suffix;
	}
	static public function process() {
		var_dump(preg_match('#^/'.static::$suffix.'/([a-zA-Z0-9]+)/?(.*)$#', $_SERVER['PATH_INFO'] === '/solution/cal'));
	}
}
