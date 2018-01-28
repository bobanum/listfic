<?php
use Listfic\Dossier
class Gerer {
	const COPIER = 'Copier l\'original';
	const REFERENCE = 'Créer une référence';
	const SUPPRIMER = 'Supprimer';
	const MODIFIER = 'Modifier';
	const SUPPRIMERTOUT = 'Supprimer partout';
	static public $suffixes = array("Fichiers"=>"", "Solution"=>Dossier::$suffixe_solution);
	static public function etatFic($fic, $suffixe="") {
		$path = dirname($fic);
		$dossier = basename($path);
		$nomfic = basename($fic);
		$fic2 = "$path/$dossier$suffixe/$nomfic";
		$affichage = '';
		$menu[self::COPIER] = "dossier=$dossier&fichier=$nomfic&suffixe=$suffixe&action=copier";
		$menu[self::REFERENCE] = "dossier=$dossier&fichier=$nomfic&suffixe=$suffixe&action=reference";
		$menu[self::SUPPRIMER] = "dossier=$dossier&fichier=$nomfic&suffixe=$suffixe&action=supprimer";
		$menu[self::MODIFIER] = "dossier=$dossier&fichier=$nomfic&suffixe=$suffixe&action=modifier";
		if (file_exists($fic2)) {
			if (filesize($fic2)==0) {
				$menu[self::REFERENCE] = false;
				$affichage .= '<td>'.self::htmlmenu($menu).'0</td>';
			} else if (file_get_contents($fic) == file_get_contents($fic2)) {
				$menu[self::COPIER] = false;
				$affichage .= '<td>'.self::htmlmenu($menu).'=</td>';
			} else {
				$affichage .= '<td>'.self::htmlmenu($menu).'≠</td>';
			}
		} else {
			$menu[self::SUPPRIMER] = false;
			$menu[self::MODIFIER] = false;
			$affichage .= '<td>'.self::htmlmenu($menu).'-</td>';
		}
		return $affichage;
	}
	static public function htmlMenu($array) {
		$resultat = '';
		$resultat .= '<ul>';
		foreach($array as $cle=>$val) {
			$resultat .= '<li>';
			if ($val === false) {
				$resultat .= '<span>'.$cle.'</span>';
			} else {
				$resultat .= '<a href="?'.$val.'" onclick="return Gerer.clicmenu.apply(this, arguments);">'.$cle.'</a>';
			}
			$resultat .= '</li>';
		}
		$resultat .= '</ul>';
		return $resultat;
	}
	static public function ficsDossier($dossier, $suffixes) {
		$suffixe_solution = Dossier::$suffixe_solution;
		$fics = array_merge(glob("$dossier/*"), glob("$dossier/$dossier/*"), glob("$dossier/{$dossier}{$suffixe_solution}/*"));
		$fics = array_diff($fics, glob("$dossier/$dossier"));
		foreach($suffixes as $s) {
			$fics = array_diff($fics, glob("$dossier/{$dossier}{$s}"));
		}
		$resultat = array();
		$arr = array(0=>null);
		foreach($suffixes as $s) {
			$arr[$s] = null;
		}
		foreach($fics as $cle=>$val) {
			$f = basename($val);
			$d = dirname($val);
			if (!isset($resultat[$f])) $resultat[$f] = $arr;
			if ($d == "$dossier") {
				$resultat[$f][0] = filesize("$dossier/$f");
			} else {
				foreach($suffixes as $s) {
					if ($d == "$dossier/$dossier{$s}") $resultat[$f][$s] = filesize("$dossier/$dossier{$s}/$f");
				}
			}
		}
		ksort($resultat);
		return $resultat;
	}
	static public function execDossier($dossier) {
		$affichage = '';
		$affichage .= '<div>Dossier : <a href="gerersource.php">racine</a>/'.$dossier.'</div>';
		$suffixes = self::$suffixes;
		foreach (self::$suffixes as $nom=>$s) {
			if (!file_exists("$dossier/$dossier$s")) {
				$data = "dossier=$dossier&suffixe=$s&action=creerdossier";
				$affichage .= '<div><a href="?'.$data.'">Ajouter un dossier "'.$nom.'"</a></div>';
				unset($suffixes[$nom]);
			}
		}
		$affichage .= '<table border="1">';
		$affichage .= '<thead>';
		$affichage .= '<tr>';
		$affichage .= '<th>Nom</th>';
		$fics = self::ficsDossier($dossier, $suffixes);
		$affichage .= '<th>Original</th>';
		foreach ($suffixes as $nom=>$s) {
			$affichage .= '<th>'.$nom.'</th>';
		}
		$affichage .= '</tr>';
		$affichage .= '</thead>';
		$affichage .= '<tbody>';
		foreach($fics as $nomfic=>$fic_array) {
			$affichage .= '<tr>';
			$menu = array(self::SUPPRIMERTOUT=>"dossier=$dossier&fichier=$nomfic&action=supprimertout");
			$affichage .= '<td class="nom">'.self::htmlmenu($menu).''.$nomfic.'</td>';
			$dataUrl = "dossier=$dossier&fichier=$nomfic";
			$menu = array();
			$menu[self::COPIER] = "action=copier&$dataUrl";
			$menu[self::REFERENCE] = "action=reference&$dataUrl";
			$menu[self::SUPPRIMER] = "action=supprimer&$dataUrl";
			$menu[self::MODIFIER] = "action=modifier&$dataUrl";
			if (isset($fic_array[0])) {
				$menu[self::COPIER] = $menu[self::REFERENCE] = false;
				$affichage .= '<td class="present" title="taille:'.$fic_array[0].'">'.self::htmlmenu($menu).'√</td>';
				$texte0 = file_get_contents("$dossier/$nomfic");
			} else {
				$menu[self::COPIER] = $menu[self::REFERENCE] = $menu[self::SUPPRIMER] = $menu[self::MODIFIER] = false;
				$affichage .= '<td class="absent">'.self::htmlmenu($menu).'-</td>';
				$texte0 = null;
			}
			foreach ($suffixes as $nom=>$s) {
				$dataUrl = "dossier=$dossier&fichier=$nomfic&suffixe=$s";
				if (isset($fic_array[$s])) {
					if ($fic_array[$s]==0) {
						$affichage .= self::td($dataUrl, 'reference');
					} else if ($fic_array[0] === $fic_array[$s] && $texte0 == file_get_contents("$dossier/$dossier$s/$nomfic")) {
						$affichage .= self::td($dataUrl, 'pareil');
					} else {
						$affichage .= self::td($dataUrl, 'different');
					}
				} else {
					$affichage .= self::td($dataUrl, 'absent');
				}
			}
			$affichage .= '</tr>';
		}
		return $affichage;
		foreach($fics as $nomfic=>$fic_array) {
			$affichage .= '<tr>';
			// $menu[self::COPIER] = "dossier=$dossier&fichier=$nomfic&action=copier";
			// $menu[self::REFERENCE] = "dossier=$dossier&fichier=$nomfic&action=reference";
			// $menu[self::SUPPRIMER] = "dossier=$dossier&fichier=$nomfic&action=supprimer";
			$menu[self::MODIFIER] = "dossier=$dossier&fichier=$nomfic&action=modifier";
			$affichage .= '<td>'.self::htmlmenu($menu).''.$nomfic.'</td>';
			// $affichage .= '<td><a href="?dossier='.$dossier.'&fichier='.$nomfic.'" onclick="return Gerer.clicmenu.apply(this, arguments);">'.self::htmlmenu($menu).''.$nomfic.'</a></td>';
			foreach ($suffixes as $nom=>$suf) {
				$affichage .= self::etatFic($fic, $suf);
			}
			$affichage .= '</tr>';
		}
		$affichage .= '</tbody>';
		$affichage .= '</table>';
		return $affichage;
	}
	static public function baseMenu($dossier, $fichier, $suffixe) {
	}
	static public function td($dataUrl, $class) {
		$affichage = '';
		//$dataUrl = "dossier=$dossier&fichier=$nomfic&suffixe=$suffixe";
		$menu = array();
		$menu[self::COPIER] = "action=copier&$dataUrl";
		$menu[self::REFERENCE] = "action=reference&$dataUrl";
		$menu[self::SUPPRIMER] = "action=supprimer&$dataUrl";
		$menu[self::MODIFIER] = "action=modifier&$dataUrl";
		if ($class=="reference") {
			$menu[self::REFERENCE] = false;
			$affichage .= '<td class="reference">'.self::htmlmenu($menu).'0</td>';
		} else if ($class=="pareil") {
			$menu[self::COPIER] = false;
			$affichage .= '<td class="pareil">'.self::htmlmenu($menu).'=</td>';
		} else if ($class=="different") {
			$affichage .= '<td class="different">'.self::htmlmenu($menu).'≠</td>';
		} else if ($class=="absent") {
			$menu[self::SUPPRIMER] = $menu[self::MODIFIER] = false;
			$affichage .= '<td class="absent">'.self::htmlmenu($menu).'-</td>';
		} else {
			$affichage .= '<td>'.self::htmlmenu($menu).'-?</td>';
		}
		return $affichage;
	}
	static public function execRacine() {
		$fics = glob("*", GLOB_ONLYDIR);
		$fics = array_diff($fics, glob("_*", GLOB_ONLYDIR));
		$fics = array_diff($fics, glob("*_", GLOB_ONLYDIR));

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
		foreach($fics as $fic) {
			$affichage .= '<tr>';
			$affichage .= '<td><a href="?dossier='.basename($fic).'">'.basename($fic).'</a></td>';
			if (file_exists($fic.'/'.basename($fic))) {
				$affichage .= '<td>X</td>';
			}
			else {
				$affichage .= '<td>-</td>';
			}
			if (file_exists($fic.'/'.basename($fic).Dossier::$suffixe_solution)) {
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
	static public function execFichier($dossier, $fichier, $suffixe=null) {
		if (!isset($_GET['action']) || $_GET['action']=="modifier") {
			$resultat = self::editeur($dossier, $fichier, $suffixe);
			header("content-type:text/html");
			echo $resultat;
		}
		exit;
	}
	static public function aller($data="") {
		header("location:gerersource.php$data");
		exit;
	}
	static public function exec() {
		if (isset($_GET['action'])) {
			$action = $_GET['action'];
			if ($action == "copier") {
				if (!isset($_GET['dossier']) || !isset($_GET['suffixe']) || !isset($_GET['fichier'])) {
					print_r($_GET);
					exit("false".__LINE__);
				}
				$dossier = $_GET['dossier'];
				$fichier = $_GET['fichier'];
				$suffixe = $_GET['suffixe'];
				$path0 = "$dossier/$fichier";
				$path = "$dossier/$dossier$suffixe/$fichier";
				if (!file_exists($path0)) {
					exit("false".__LINE__);
				}
				if (file_exists($path)) {
					self::unlink($path);
				}
				self::copy($path0, $path);
				$dataUrl = "dossier=$dossier&fichier=$fichier&suffixe=$suffixe";
				$retour = self::td($dataUrl, 'pareil');
				echo $retour;
				exit();
			} elseif ($action == 'reference') {
				if (!isset($_GET['dossier']) || !isset($_GET['suffixe']) || !isset($_GET['fichier'])) {
					print_r($_GET);
					exit("false".__LINE__);
				}
				$dossier = $_GET['dossier'];
				$fichier = $_GET['fichier'];
				$suffixe = $_GET['suffixe'];
				$path0 = "$dossier/$fichier";
				$path = "$dossier/$dossier$suffixe/$fichier";
				if (file_exists($path)) {
					self::unlink($path);
				}
				file_put_contents($path, '');
				$dataUrl = "dossier=$dossier&fichier=$fichier&suffixe=$suffixe";
				$retour = self::td($dataUrl, 'reference');
				echo $retour;
				exit();
			} elseif ($action == 'supprimer') {
				if (!isset($_GET['dossier']) || !isset($_GET['suffixe']) || !isset($_GET['fichier'])) {
					print_r($_GET);
					exit("false".__LINE__);
				}
				$dossier = $_GET['dossier'];
				$fichier = $_GET['fichier'];
				$suffixe = $_GET['suffixe'];
				$path = "$dossier/$dossier$suffixe/$fichier";
				if (file_exists($path)) {
					self::unlink($path);
				}
				$dataUrl = "dossier=$dossier&fichier=$fichier&suffixe=$suffixe";
				$retour = self::td($dataUrl, 'absent');
				echo $retour;
				exit();
			} elseif ($action == 'creerdossier') {
				if (!isset($_GET['dossier'])) {
					self::aller();
				}
				$dossier = $_GET['dossier'];
				if (!isset($_GET['suffixe'])) {
					self::aller("?dossier=$dossier");
				}
				$suffixe = $_GET['suffixe'];
				echo $path = "$dossier/$dossier$suffixe";
				if (!file_exists($path)) {
					mkdir($path);
				}
				self::aller("?dossier=$dossier");
			}
		}
		if (isset($_POST['action'])) {
			$action = $_POST['action'];
			$dossier = $_POST['dossier'];
			$fichier = $_POST['fichier'];
			$suffixe = null;
			if (isset($_POST['suffixe'])) {
				$suffixe = $_POST['suffixe'];
			}
			$fic = self::pathfic($dossier, $fichier, $suffixe);
			if ($action=="sauvegarder") {
				rename($fic, "$fic.bak");
				$nouveau = $_POST['texte'];
				file_put_contents($fic, $nouveau);
				exit('true');
			}
		} else if (isset($_GET['dossier'])) {
			$dossier = $_GET['dossier'];
			if (isset($_GET['fichier'])) {
				$fichier = $_GET['fichier'];
				$suffixe = null;
				if (isset($_GET['suffixe'])) $suffixe = $_GET['suffixe'];
				$affichage = self::execFichier($dossier, $fichier, $suffixe);
			} else {
				$affichage = self::execDossier($dossier);
			}
			$affichage = self::execDossier($dossier);
		} else {
			$affichage = self::execRacine();
		}
		return $affichage;
	}
	static public function copy($src,$dst) {
	    if ( !is_dir($src) ) {
	        copy($src,$dst);
	        return;
	    }
	    $dir = opendir($src);
	    @mkdir($dst);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            self::copy($src . '/' . $file,$dst . '/' . $file);
	        }
	    }
	    closedir($dir);
	}
	static public function unlink($src) {
	    if ( !is_dir($src) ) {
	        unlink($src);
	        return;
	    }
	    $dir = opendir($src);
	    while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
	            self::unlink($src . '/' . $file);
	        }
	    }
	    rmdir($src);
	    closedir($dir);
	}
	static public function pathfic($dossier, $fichier, $suffixe=null) {
		if (is_null($suffixe)) $fic =  "$dossier/$fichier";
		else $fic = "$dossier/$dossier$suffixe/$fichier";
		return $fic;
	}
	static public function editeur($dossier, $fichier, $suffixe=null) {
		$fic = self::pathfic($dossier, $fichier, $suffixe);
		$txt = file_get_contents($fic);
		$resultat = '';
		$resultat .= '<div id="ecran">';
		$resultat .= '<div class="zone">';
		$resultat .= '<div class="contenu">';
		$resultat .= '<form action="gerersource.php" method="post" onsubmit="return Gerer.submitEditeur.apply(this, arguments);">';
		$resultat .= '<span class="readonly" onclick="return Gerer.clicreadonly.apply(this,arguments)"></span><textarea name="texte" readonly="readonly">'.htmlspecialchars($txt).'</textarea>';
		$resultat .= '<div class="boutons">';
		$resultat .= '<input type="hidden" name="action" value="sauvegarder"/>';
		$resultat .= '<input type="hidden" name="dossier" value="'.htmlspecialchars($dossier).'"/>';
		$resultat .= '<input type="hidden" name="fichier" value="'.htmlspecialchars($fichier).'"/>';
		if (!is_null($suffixe)) $resultat .= '<input type="hidden" name="suffixe" value="'.htmlspecialchars($suffixe).'"/>';
		$resultat .= '<input type="submit" id="envoyer" disabled="disabled"/>';
		$resultat .= '<input type="submit" onclick="this.form.clickedButton=this;" name="annuler" value="Annuler"/>';
		$resultat .= '</div>';
		$resultat .= '</form>';
		$resultat .= '</div>';
		$resultat .= '</div>';
		$resultat .= '</div>';
		return $resultat;
	}
}
