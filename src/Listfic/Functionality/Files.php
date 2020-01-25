<?php
namespace Listfic\Functionality;
use Listfic\Directory;
class Files extends Functionality {
	use ZipTrait {
		__construct as private ZipTrait__construct;
	}
	public $fieldName = "files";
	public $label = "Files";
	public $description = 'Booléen. Y a-t-il des files à télécharger?';
	public $suffix = 'files';
	public $_pathZip = '';
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
		if (!isset($_GET[$this->fieldName])) {
			return false;
		}
		$result = self::admin_activate($_GET[$this->fieldName]);
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
		$data = $this->fieldName.'['.urlencode($this->directory->url()).']';
		if ($this->value) {
			$result = '<a class="'.$this->fieldName.' toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($this->path())) {
			$result = '<a class="'.$this->fieldName.' toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$result = '<a class="'.$this->fieldName.' toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
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
		$result[$this->fieldName] = $this->directory->link_download($label, ["files", $this->directory->url()], 'files');
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
			return [$this->fieldName => '<a href="'.$url.'">'.$label.'</a>'];
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
		$result = [];
		if ($this->value) {
			$result[$this->fieldName] = [
				'url_folder' => $this->folderUrl,
				'url_archive' => $this->zipUrl,
			];
		} else {
			$result[$this->fieldName] = false;
		}
		return $result;
	}
	/**
	 * Retourne le chemin absolu du sous-directory de initial ou de solution (ou autre);
	 * @param  string [$suffix=""] Le suffix vers le directory
	 * @return string Un chemin absolu vers le sous-directory
	 */
	public function path() {
		// Allowed patterns : suffixe || namesuffixe
		$suffix = $this->suffix;
		$result = $this->directory->path($suffix);
		if (file_exists($result)) {
			return $result;
		}
		$result = $this->directory->path($this->directory->name . $suffix);
		if (file_exists($result)) {
			return $result;
		}
		return "";
	}
}
