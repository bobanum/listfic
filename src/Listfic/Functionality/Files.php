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
	public $suffix = 'initial';
	public $_pathZip = '';
	protected $_value = false;
	private $choices = [
		'Disponible'=>'true',
		'Non disponible'=>'false',
	];
	public function __construct($directory, $ini=[]) {
		parent::__construct($directory, $ini);
		$this->ZipTrait__construct();
	}
	static public function admin_process() {
		//Rendre les files de départ visibles
		if (!isset($_GET['f'])) {
			return false;
		}
		$result = '';
		foreach($_GET['f'] as $directory=>$status) {
			$directoryObject = new Directory($directory);
			$directoryObject->files = ($status === 'true');
			$directoryObject->ini_put(true);
			$result .= $directoryObject->html_projectLine(true);
		}
		return $result;
	}

	public function html_button(){
		$data = 'f['.urlencode($this->directory->url()).']';
		if ($this->value) {
			$result = '<a class="files toggle on" href="?admin&'.$data.'=false">F</a>';
		} else if (file_exists($this->path())) {
			$result = '<a class="files toggle off" href="?admin&'.$data.'=true">F</a>';
		} else {
			$result = '<a class="files toggle off" href="?admin&'.$data.'=true">&nbsp;</a>';
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
		$champ = $this->html_select($this->choices);
		return $this->html_form_line($champ);
	}
	public function ini_get($ini){
		parent::ini_get($ini);
		if ($this->value === true) {
			$this->value = $this->adjustZip();
		}
	}
}
