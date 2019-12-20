<?php
namespace Listfic\Functionality;
// use Listfic\Directory;
use ZipArchive;
trait ZipTrait {
	private $_pathZip = '';
	protected $_value = false;
	/** @var string - Tableau des regexp des files/directories à ne pas inclure dans le ZIP. La clé n'est pas utilisée, mais représente la fonction du pattern. */
	public $zipExclusions = [
		/*'underscoreStart'=>'^_', */
		'underscoreEnd'=>'_$', 
		'dotStart'=>'^\.',
	];
	public $zipFilenamePattern = "{{name}}{{suffix}}";

	public function get_pathZip() {
		//TODO Check config for global zip placement
		return $this->_pathZip;
	}
	/**
	 * Zippe un sous-directory en lui donnant le même nom
	 * @param type $path Chemin absolu vers le directory à zipper
	 */
	public function zip() {
		$pathFile = $this->path();
		$pathZip = $this->path_zip();
		$this->adjustDirectory(dirname($pathZip));
		$path = realpath($pathFile);
		$element = basename($pathFile);
		$zip = new ZipArchive;
		$res = $zip->open($pathZip, ZipArchive::CREATE);
		if ($res === TRUE) {
			$this->zip_add($zip, $path, $this->zip_filename());
		}
		return $this;
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
	 * @param boolean $deleteOriginal
	 */
	/**
	 * S'assure qu'il y a un fichier zip au besoin (et le crée) et retourne true si c'est le cas
	 * @return boolean
	 */
	public function adjustZip() {
		$suffix = $this->suffix;
		$pathFunctionality = $this->path();
		$pathZip = $this->path_zip();
		if (!file_exists($pathZip) && !file_exists($pathFunctionality)) {
			return false;
		}
		if (!file_exists($pathZip)) {
			$this->zip();
			return $pathZip;
		}
		if (!file_exists($pathFunctionality)) {
			return $pathZip;
		}
		//s'il n'y a pas de ini_fichiers, on vérifie s'il y a un directory du meme nom ou un zip
		if (filemtime($pathZip) >= filemtime($pathFunctionality)) {
			return $pathZip;
		}
		unlink($pathZip);	// Le zip est désuet
		$this->zip();
		return $pathZip;
	}
	public function zip_filename() {
		$filename = $this->zipFilenamePattern;
		$filename = str_replace("{{name}}", '{$this->directory->name}', $filename);
		$filename = str_replace("{{suffix}}", '{$this->suffix}', $filename);
		$result = eval("return \"$filename\";");
		return $result;
	}
	public function path_zip() {
		//TODO Revise
		$fileName = $this->zip_filename().".zip";
		if (empty($this->zipPath)) {
			//No path for zip = inside directory
			return $this->directory->path($fileName);
		}
		$zipPath = $this->zipPath;
		while (substr($zipPath, -1) === "/") {
			$zipPath = substr($zipPath, 0, -1);
		}
		if (preg_match("#^[/\]{2}|^[a-zA-Z]:[/\]#", $zipPath)) {
			//Absolute path
			return "{$zipPath}/{$fileName}";
		}
		if (substr($zipPath, 0, 1) === "/") {
			//In root directory
			return $this->directory->root . $zipPath;
		}
		return this.path("{$zipPath}/{$fileName}");
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
}
