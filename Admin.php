<?php
use Listfic\Directory;
class Admin {
	const COPY = 'Copier l\'original';
	const REFERENCE = 'Créer une référence';
	const DELETE = 'Supprimer';
	const UPDATE = 'Modifier';
	const DELETEALL = 'Supprimer partout';
	static public $suffixes = [
		"Fichiers"=>"", 
		"Solution"=>"solution", //TOFIX Directory::$suffixe_solution,
	];
	static public function fileStatus($file, $suffix="") {
		$path = dirname($file);
		$directory = basename($path);
		$nomfic = basename($file);
		$fic2 = "$path/$directory$suffix/$nomfic";
		$affichage = '';
		$menu[self::COPY] = "directory=$directory&fichier=$nomfic&suffix=$suffix&action=copier";
		$menu[self::REFERENCE] = "directory=$directory&fichier=$nomfic&suffix=$suffix&action=reference";
		$menu[self::DELETE] = "directory=$directory&fichier=$nomfic&suffix=$suffix&action=supprimer";
		$menu[self::UPDATE] = "directory=$directory&fichier=$nomfic&suffix=$suffix&action=modifier";
		if (file_exists($fic2)) {
			if (filesize($fic2)==0) {
				$menu[self::REFERENCE] = false;
				$affichage .= '<td>'.self::html_menu($menu).'0</td>';
			} else if (file_get_contents($file) == file_get_contents($fic2)) {
				$menu[self::COPY] = false;
				$affichage .= '<td>'.self::html_menu($menu).'=</td>';
			} else {
				$affichage .= '<td>'.self::html_menu($menu).'≠</td>';
			}
		} else {
			$menu[self::DELETE] = false;
			$menu[self::UPDATE] = false;
			$affichage .= '<td>'.self::html_menu($menu).'-</td>';
		}
		return $affichage;
	}
	static public function html_menu($array) {
		$result = '';
		$result .= '<ul>';
		foreach($array as $key=>$val) {
			$result .= '<li>';
			if ($val === false) {
				$result .= '<span>'.$key.'</span>';
			} else {
				$result .= '<a href="?'.$val.'" onclick="return Gerer.clicmenu.apply(this, arguments);">'.$key.'</a>';
			}
			$result .= '</li>';
		}
		$result .= '</ul>';
		return $result;
	}
	static public function directoryFiles($directory, $suffixes) {
		$suffixe_solution = Directory::$solution_suffix;
		$files = array_merge(glob("$directory/*"), glob("$directory/$directory/*"), glob("$directory/{$directory}{$suffixe_solution}/*"));
		$files = array_diff($files, glob("$directory/$directory"));
		foreach($suffixes as $s) {
			$files = array_diff($files, glob("$directory/{$directory}{$s}"));
		}
		$result = array();
		$arr = array(0=>null);
		foreach($suffixes as $s) {
			$arr[$s] = null;
		}
		foreach($files as $key=>$val) {
			$f = basename($val);
			$d = dirname($val);
			if (!isset($result[$f])) $result[$f] = $arr;
			if ($d == "$directory") {
				$result[$f][0] = filesize("$directory/$f");
			} else {
				foreach($suffixes as $s) {
					if ($d == "$directory/$directory{$s}") $result[$f][$s] = filesize("$directory/$directory{$s}/$f");
				}
			}
		}
		ksort($result);
		return $result;
	}
	static public function execDirectory($directory) {
		$affichage = '';
		$affichage .= '<div>Directory : <a href="gerersource.php">racine</a>/'.$directory.'</div>';
		$suffixes = self::$suffixes;
		foreach (self::$suffixes as $name=>$s) {
			if (!file_exists("$directory/$directory$s")) {
				$data = "directory=$directory&suffix=$s&action=creerdirectory";
				$affichage .= '<div><a href="?'.$data.'">Ajouter un directory "'.$name.'"</a></div>';
				unset($suffixes[$name]);
			}
		}
		$affichage .= '<table border="1">';
		$affichage .= '<thead>';
		$affichage .= '<tr>';
		$affichage .= '<th>Nom</th>';
		$files = self::directoryFiles($directory, $suffixes);
		$affichage .= '<th>Original</th>';
		foreach ($suffixes as $name=>$s) {
			$affichage .= '<th>'.$name.'</th>';
		}
		$affichage .= '</tr>';
		$affichage .= '</thead>';
		$affichage .= '<tbody>';
		foreach($files as $nomfic=>$fic_array) {
			$affichage .= '<tr>';
			$menu = array(self::DELETEALL=>"directory=$directory&fichier=$nomfic&action=supprimertout");
			$affichage .= '<td class="nom">'.self::html_menu($menu).''.$nomfic.'</td>';
			$dataUrl = "directory=$directory&fichier=$nomfic";
			$menu = array();
			$menu[self::COPY] = "action=copier&$dataUrl";
			$menu[self::REFERENCE] = "action=reference&$dataUrl";
			$menu[self::DELETE] = "action=supprimer&$dataUrl";
			$menu[self::UPDATE] = "action=modifier&$dataUrl";
			if (isset($fic_array[0])) {
				$menu[self::COPY] = $menu[self::REFERENCE] = false;
				$affichage .= '<td class="present" title="taille:'.$fic_array[0].'">'.self::html_menu($menu).'√</td>';
				$texte0 = file_get_contents("$directory/$nomfic");
			} else {
				$menu[self::COPY] = $menu[self::REFERENCE] = $menu[self::DELETE] = $menu[self::UPDATE] = false;
				$affichage .= '<td class="absent">'.self::html_menu($menu).'-</td>';
				$texte0 = null;
			}
			foreach ($suffixes as $name=>$s) {
				$dataUrl = "directory=$directory&fichier=$nomfic&suffix=$s";
				if (isset($fic_array[$s])) {
					if ($fic_array[$s]==0) {
						$affichage .= self::html_td($dataUrl, 'reference');
					} else if ($fic_array[0] === $fic_array[$s] && $texte0 == file_get_contents("$directory/$directory$s/$nomfic")) {
						$affichage .= self::html_td($dataUrl, 'pareil');
					} else {
						$affichage .= self::html_td($dataUrl, 'different');
					}
				} else {
					$affichage .= self::html_td($dataUrl, 'absent');
				}
			}
			$affichage .= '</tr>';
		}
		return $affichage;
		foreach($files as $nomfic=>$fic_array) {
			$affichage .= '<tr>';
			// $menu[self::COPIER] = "directory=$directory&fichier=$nomfic&action=copier";
			// $menu[self::REFERENCE] = "directory=$directory&fichier=$nomfic&action=reference";
			// $menu[self::SUPPRIMER] = "directory=$directory&fichier=$nomfic&action=supprimer";
			$menu[self::UPDATE] = "directory=$directory&fichier=$nomfic&action=modifier";
			$affichage .= '<td>'.self::html_menu($menu).''.$nomfic.'</td>';
			// $affichage .= '<td><a href="?directory='.$directory.'&fichier='.$nomfic.'" onclick="return Gerer.clicmenu.apply(this, arguments);">'.self::htmlmenu($menu).''.$nomfic.'</a></td>';
			foreach ($suffixes as $name=>$suf) {
				// $affichage .= self::etatFic($file, $suf);
			}
			$affichage .= '</tr>';
		}
		$affichage .= '</tbody>';
		$affichage .= '</table>';
		return $affichage;
	}
	static public function menuBase($directory, $file, $suffix) {
	}
	static public function html_td($dataUrl, $class) {
		$affichage = '';
		//$dataUrl = "directory=$directory&fichier=$nomfic&suffix=$suffix";
		$menu = array();
		$menu[self::COPY] = "action=copier&$dataUrl";
		$menu[self::REFERENCE] = "action=reference&$dataUrl";
		$menu[self::DELETE] = "action=supprimer&$dataUrl";
		$menu[self::UPDATE] = "action=modifier&$dataUrl";
		if ($class=="reference") {
			$menu[self::REFERENCE] = false;
			$affichage .= '<td class="reference">'.self::html_menu($menu).'0</td>';
		} else if ($class=="pareil") {
			$menu[self::COPY] = false;
			$affichage .= '<td class="pareil">'.self::html_menu($menu).'=</td>';
		} else if ($class=="different") {
			$affichage .= '<td class="different">'.self::html_menu($menu).'≠</td>';
		} else if ($class=="absent") {
			$menu[self::DELETE] = $menu[self::UPDATE] = false;
			$affichage .= '<td class="absent">'.self::html_menu($menu).'-</td>';
		} else {
			$affichage .= '<td>'.self::html_menu($menu).'-?</td>';
		}
		return $affichage;
	}
	static public function execRoot() {
		$files = glob("*", GLOB_ONLYDIR);
		$files = array_diff($files, glob("_*", GLOB_ONLYDIR));
		$files = array_diff($files, glob("*_", GLOB_ONLYDIR));

		$affichage = '';
		$affichage .= '<table border="1">';
		$affichage .= '<thead>';
		$affichage .= '<tr>';
		$affichage .= '<th>Nom</th>';
		$affichage .= '<th>F</th>';
		$affichage .= '<th>S</th>';
		$affichage .= '</tr>';
		$affichage .= '</thead>';
		$affichage .= '<tbody>';
		foreach($files as $file) {
			$affichage .= '<tr>';
			$affichage .= '<td><a href="?directory='.basename($file).'">'.basename($file).'</a></td>';
			if (file_exists($file.'/'.basename($file))) {
				$affichage .= '<td>X</td>';
			}
			else {
				$affichage .= '<td>-</td>';
			}
			if (file_exists($file.'/'.basename($file).Directory::$solution_suffix)) {
				$affichage .= '<td>X</td>';
			}
			else {
				$affichage .= '<td>-</td>';
			}
			$affichage .= '</tr>';
		}
		$affichage .= '</tbody>';
		$affichage .= '</table>';
		return $affichage;
	}
	static public function execFile($directory, $file, $suffix=null) {
		if (!isset($_GET['action']) || $_GET['action']=="modifier") {
			$result = self::editor($directory, $file, $suffix);
			header("content-type:text/html");
			echo $result;
		}
		exit;
	}
	static public function goto($data="") {
		header("location:gerersource.php$data");
		exit;
	}
	static public function exec() {
		if (isset($_GET['action'])) {
			$action = $_GET['action'];
			if ($action == "copier") {
				if (!isset($_GET['directory']) || !isset($_GET['suffix']) || !isset($_GET['fichier'])) {
					print_r($_GET);
					exit("false".__LINE__);
				}
				$directory = $_GET['directory'];
				$file = $_GET['fichier'];
				$suffix = $_GET['suffix'];
				$path0 = "$directory/$file";
				$path = "$directory/$directory$suffix/$file";
				if (!file_exists($path0)) {
					exit("false".__LINE__);
				}
				if (file_exists($path)) {
					self::unlink($path);
				}
				self::copy($path0, $path);
				$dataUrl = "directory=$directory&fichier=$file&suffix=$suffix";
				$retour = self::html_td($dataUrl, 'pareil');
				echo $retour;
				exit();
			} elseif ($action == 'reference') {
				if (!isset($_GET['directory']) || !isset($_GET['suffix']) || !isset($_GET['fichier'])) {
					print_r($_GET);
					exit("false".__LINE__);
				}
				$directory = $_GET['directory'];
				$file = $_GET['fichier'];
				$suffix = $_GET['suffix'];
				$path0 = "$directory/$file";
				$path = "$directory/$directory$suffix/$file";
				if (file_exists($path)) {
					self::unlink($path);
				}
				file_put_contents($path, '');
				$dataUrl = "directory=$directory&fichier=$file&suffix=$suffix";
				$retour = self::html_td($dataUrl, 'reference');
				echo $retour;
				exit();
			} elseif ($action == 'supprimer') {
				if (!isset($_GET['directory']) || !isset($_GET['suffix']) || !isset($_GET['fichier'])) {
					print_r($_GET);
					exit("false".__LINE__);
				}
				$directory = $_GET['directory'];
				$file = $_GET['fichier'];
				$suffix = $_GET['suffix'];
				$path = "$directory/$directory$suffix/$file";
				if (file_exists($path)) {
					self::unlink($path);
				}
				$dataUrl = "directory=$directory&fichier=$file&suffix=$suffix";
				$retour = self::html_td($dataUrl, 'absent');
				echo $retour;
				exit();
			} elseif ($action == 'creerdirectory') {
				if (!isset($_GET['directory'])) {
					self::goto();
				}
				$directory = $_GET['directory'];
				if (!isset($_GET['suffix'])) {
					self::goto("?directory=$directory");
				}
				$suffix = $_GET['suffix'];
				echo $path = "$directory/$directory$suffix";
				if (!file_exists($path)) {
					mkdir($path);
				}
				self::goto("?directory=$directory");
			}
		}
		if (isset($_POST['action'])) {
			$action = $_POST['action'];
			$directory = $_POST['directory'];
			$file = $_POST['fichier'];
			$suffix = null;
			if (isset($_POST['suffix'])) {
				$suffix = $_POST['suffix'];
			}
			$file = self::filePath($directory, $file, $suffix);
			if ($action=="sauvegarder") {
				rename($file, "$file.bak");
				$nouveau = $_POST['texte'];
				file_put_contents($file, $nouveau);
				exit('true');
			}
		} else if (isset($_GET['directory'])) {
			$directory = $_GET['directory'];
			if (isset($_GET['fichier'])) {
				$file = $_GET['fichier'];
				$suffix = null;
				if (isset($_GET['suffix'])) $suffix = $_GET['suffix'];
				$affichage = self::execFile($directory, $file, $suffix);
			} else {
				$affichage = self::execDirectory($directory);
			}
			$affichage = self::execDirectory($directory);
		} else {
			$affichage = self::execRoot();
		}
		return $affichage;
	}
	static public function copy($src,$dst) {
	    if ( !is_dir($src) ) {
	        copy($src,$dst);
	        return;
	    }
	    $directory = opendir($src);
	    @mkdir($dst);
	    while(false !== ( $file = readdir($directory)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            self::copy($src . '/' . $file,$dst . '/' . $file);
	        }
	    }
	    closedir($directory);
	}
	static public function unlink($src) {
	    if ( !is_dir($src) ) {
	        unlink($src);
	        return;
	    }
	    $directory = opendir($src);
	    while(false !== ( $file = readdir($directory)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            self::unlink($src . '/' . $file);
	        }
	    }
	    rmdir($src);
	    closedir($directory);
	}
	static public function filePath($directory, $file, $suffix=null) {
		if (is_null($suffix)) $file =  "$directory/$file";
		else $file = "$directory/$directory$suffix/$file";
		return $file;
	}
	static public function editor($directory, $file, $suffix=null) {
		$file = self::filePath($directory, $file, $suffix);
		$txt = file_get_contents($file);
		$result = '';
		$result .= '<div id="ecran">';
		$result .= '<div class="zone">';
		$result .= '<div class="contenu">';
		$result .= '<form action="gerersource.php" method="post" onsubmit="return Gerer.submitEditeur.apply(this, arguments);">';
		$result .= '<span class="readonly" onclick="return Gerer.clicreadonly.apply(this,arguments)"></span><textarea name="texte" readonly="readonly">'.htmlspecialchars($txt).'</textarea>';
		$result .= '<div class="boutons">';
		$result .= '<input type="hidden" name="action" value="sauvegarder"/>';
		$result .= '<input type="hidden" name="directory" value="'.htmlspecialchars($directory).'"/>';
		$result .= '<input type="hidden" name="fichier" value="'.htmlspecialchars($file).'"/>';
		if (!is_null($suffix)) $result .= '<input type="hidden" name="suffix" value="'.htmlspecialchars($suffix).'"/>';
		$result .= '<input type="submit" id="envoyer" disabled="disabled"/>';
		$result .= '<input type="submit" onclick="this.form.clickedButton=this;" name="annuler" value="Annuler"/>';
		$result .= '</div>';
		$result .= '</form>';
		$result .= '</div>';
		$result .= '</div>';
		$result .= '</div>';
		return $result;
	}
}
