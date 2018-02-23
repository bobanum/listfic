<?php
/*TODO Enlever la notion de flag*/
namespace Listfic;
use Listfic\Fonctionnalite\Fichiers;
use Listfic\Fonctionnalite\Solution;
use ZipArchive;
class Dossier {
	/** @var string - Le nom du petit fichier à laisser dans le dossier */
	static public $nomIni = "_ini.php";
	/** @var string - Tableau des regexp des fichiers/dossiers à ne pas inclure dans le ZIP. La clé n'est pas utilisée, mais représente la fonction du pattern. */
	static public $exclusionsZip = array('soulignementDebut'=>'^_', 'soulignementFin'=>'_$', 'pointDebut'=>'^\.');
	static public $etiquettes = array(
			"fichiers"=>"Fichiers",
			"solution"=>"Solution",
			"consignes"=>"Consignes",
	);
	//TODO Réviser :
	static public $fonctionnalites = array(
		'Ini','Titre','Categorie','Prefixe','Liens','Source','Visible',
	);
	/** @var string Ce qui se trouve juste avant le url pour faire un path absolu. Déterminé au init. */
	static public $racine;
	/** @var string Le dossier dans lequel mettre les fichiers zip. */
	static public $pathZip = "/_zip";
	/** @var string Le dossier dans lequel mettre les fichiers zip. */
	static public $suffixe_solution = "_solution";
	const PATH_ZIP = 1;
	const PATH_RELATIF = 2;
	const PATH_SOLUTION = 4;
	/** @var string Le path absolu vers le dossier */
	public $path;
	/** @var string L'adresse relative vers le dossier en fonction de la page courante */
	public $url;
	/** @var boolean Indique si le ini est modifié pour le sauvegarder */
	public $modifie = false;
	// Valeurs par défaut
	protected $_categorie = "Autres";
	protected $_prefixe = "";
	protected $_liens = array();
	protected $_titre = "";
	protected $_source = false;
	protected $_visible = false;
	protected $_fichiers = true;
	protected $_solution = true;
	/**
	 * Constructeur
	 * @param type $dir - Le dossier à analyser. Pour l'instant doit être la racine du site.
	 * @throws Exception
	 */
	public function __construct($dir=".") {
		if (!is_dir($dir)) $dir = dirname($dir);
		$dir = realpath($dir);
		if (!file_exists($dir)) {
			throw new Exception ("Dossier '$dir' inexistant");
		}
		$this->path = $dir;
		$this->url = $this->relatifSite($dir);
		$this->prendreIni()->mettreIni();
	}
	/**
	 * SETTER. Vérifie si un setgetter existe pour la propriété demandée
	 * @param string $name
	 * @param type $value
	 * @return Dossier
	 * @throws Exception
	 */
	public function __set($name, $value) {
		if (method_exists($this, $name)) {
			return $this->$name($value);
		}
		$name = '_'.$name;
		if (!property_exists($this, $name)) {
			throw new Exception('Propriété "'.substr($name,1).'" inconnue');
		}
		if ($this->$name != $value) {
			$this->$name = $value;
			$this->modifie = true;
		}
		return $this;
	}
	/**
	 * GETTER.
	 * @param string $name
	 * @return type
	 * @throws Exception
	 */
	public function __get($name) {
		if (method_exists($this, $name)) {
			return $this->$name();
		}
		$name = '_'.$name;
		if (!property_exists($this, $name)) {
			throw new Exception('Propriété "'.substr($name,1).'" inconnue');
		}
		return $this->$name;
	}
	/**
	 * Un hack pour accéder à une propriété avec une fonction pour faire du chaining
	 * @param type $name
	 * @return Dossier
	 * @throws Exception
	 */
	public function prop($name) {
		if (!property_exists($this, $name)) {
			throw new Exception('Propriété "'.substr($name,1).'" inconnue');
		}
		if (func_num_args()==1) {
			return $this->$name;
		}
		$this->$name = func_get_arg(1);
		return $this;
	}
	// ACCESSEURS ////////////////////////////////////////////////////////////////
	/**
	 * FONCTIONNALITE
	 * @return \Dossier
	 */
	public function fichiers() {
		if (func_num_args()==0) {
			return $this->_fichiers;
		}
		$fichiers = func_get_arg(0);
		if ($fichiers==true) {
			$this->ajusterSousDossier();
		}
		$this->_fichiers = $fichiers;
		return $this;
	}
	/**
	 * FONCTIONNALITE
	 * @return \Dossier
	 */
	public function solution() {
		if (func_num_args()==0) return $this->_solution;
		$solution = func_get_arg(0);
		if ($solution==true) {
			$this->ajusterSousDossier(self::PATH_SOLUTION);
		}
		$this->_solution = $solution;
		return $this;
	}
	/**
	 * Retourne le chemin vers fichier Ini
	 * @return string
	 * @throws Exception
	 */
	public function pathFicIni() {
			if (func_num_args()== 0) return $this->path."/".self::$nomIni;
			else throw new Exception("Propriété 'pathFicIni' en lecture seule");
	}
	// METHODES //////////////////////////////////////////////////////////////////
	/**
	 * Exécute une certaine méthode sur tous les objet des fonctionnalités
	 * @param string $methode
	 * @note La fonction prend des paramètres multiples
	 */
	public function executerFct($methode) {
		$params = array($this);
		for ($i=1; $i<func_num_args(); $i++) {
			array_push($params, func_get_arg($i));
		}
		$resultat = array();
		foreach (self::$fonctionnalites as $fct) {
			$fct = "Listfic\\Fonctionnalite\\$fct";
			if (method_exists($fct, $methode)) {
				$reponse = call_user_func_array(array($fct, $methode), $params);
				if ($reponse) $resultat[$fct] = $reponse;
			}
		}
		return $resultat;
	}
	/**
	 * Exécute une certaine méthode sur tous les objet des fonctionnalités
	 * @param string $methode
	 * @note La fonction prend des paramètres multiples
	 */
	static public function executerFctStatic($methode) {
		$params = array();
		for ($i=1; $i<func_num_args(); $i++) {
			array_push($params, func_get_arg($i));
		}
		$resultat = array();
		foreach (self::$fonctionnalites as $fct) {
			$fct = "Listfic\\Fonctionnalite\\$fct";
			if (method_exists($fct, $methode)) {
				$reponse = call_user_func_array(array($fct, $methode), $params);
				if ($reponse) {
					$resultat[$fct] = $reponse;
				}
			}
		}
		return $resultat;
	}
	/**
	 * Récupère les données d'initialisation à partir d'un array
	 * @param array $ini - Le fichier ini à traiter. Récupère le fichier en cas d'absence.
	 * @return Dossier
	 */
	public function prendreIni($ini=null) {
		if (is_null($ini)) {
			$pathFicIni = $this->pathFicIni;

			$ini = array();
			if (file_exists($pathFicIni)) {
				include($pathFicIni);
			}
		}
		$this->executerFct('prendreIni', $ini);
		return $this;
	}
	public function creerIni() {
		$ini = $this->executerFct('creerIni');
		$resultat = "";
		$resultat .= "<?php\r\n";
		$resultat .= "// Projet : ".$this->url." \r\n";
		$resultat .= "\$ini = array (\r\n";
		$resultat .= "".implode("\r\n",$ini).");\r\n";
		$resultat .= "?".">";
		return $resultat;
	}
	public function mettreIni($forcer=false) {
		if ($this->modifie == false && !$forcer) return $this;
		$pathFicIni = $this->pathFicIni;
		$ini = $this->creerIni();
		file_put_contents($pathFicIni, $ini);
		return $this;
	}
	/**
	 * Retourne le chemin absolu du sous-dossier de fichier ou de solution (ou autre);
	 * @param  string [$suffixe=""] Le suffixe vers le dossier
	 * @return string Un chemin absolu vers le sous-dossier
	 */
	public function pathFic($suffixe="") {
		$resultat = $this->path."/".basename($this->path).$suffixe;
		return $resultat;
	}
	/**
	 * Retourne le chemin du fichier zip en fonction de la variable statique $pathZip;
	 * @param  string [$suffixe=""] Le suffixe vers le dossier zippé/àzipper
	 * @return string Un chemin absolu vers le fichier zip
	 */
	public function pathZip($suffixe="") {
		$resultat = basename($this->path).$suffixe.".zip";
		if (empty(self::$pathZip)) {
			$dossier = $this->path;
		} else if (substr(self::$pathZip, 0, 2) === "./") {
			$dossier = $this->path."/".substr(self::$pathZip, 2);
		} else if (substr(self::$pathZip, 0, 3) === "../") {
			$dossier = dirname($this->path).substr(self::$pathZip, 3);
		} else if (substr(self::$pathZip, 0, 1) === "/") {
			$dossier = self::$racine."/".substr(self::$pathZip, 1);
		} else {
			$dossier = self::$racine."/".self::$pathZip;
		}
		$resultat = $dossier."/".$resultat;
		return $resultat;
	}
	/**
	 * S'assure qu'il y a un fichier zip au besoin (et le crée) et retourne true si c'est le cas
	 * @return boolean
	 */
	public function ajusterZip($flags=0) {
		$suffixe = ($flags & self::PATH_SOLUTION) ? self::$suffixe_solution : "";
		$pathFic = $this->pathFic($suffixe);
		$pathZip = $this->pathZip($suffixe);
		//s'il n'y a pas de ini_fichiers, on vérifie s'il y a un dossier du meme nom ou un zip
		if (file_exists($pathZip) && file_exists($pathFic)) {
			if (filemtime($pathZip)<filemtime($pathFic)) unlink($pathZip);	// Le zip est désuet
			else return true;
		}
		// Il n'y a que le zip
		if (file_exists($pathZip)) {
			return true;
		}
		// Il n'y a que le dossier... on zippe;
		if (file_exists($pathFic)){
			$this->zipperSousDossier($suffixe);
			return true;
		}
		return false;
	}
	/**
	 * S'assure qu'il y a un fichier zip au besoin (et le crée) et retourne true si c'est le cas
	 * @return boolean
	 */
	public function ajusterSousDossier($flags=0) {
		$suffixe = ($flags & self::PATH_SOLUTION) ? self::$suffixe_solution : "";
		$pathFic = $this->pathFic($suffixe);
		$pathZip = $this->pathZip($suffixe);
		// Il n'y a que le zip, on ne crée pas de dossier
		if (file_exists($pathZip)) {
			return true;
		}
		// Il n'y a que le dossier... on zippe;
		if (file_exists($pathFic)) {
			return true;
		} else {
			$this->sousDossier($suffixe);
		}
		return false;
	}
	/**
	 * Zippe un sous-dossier en lui donnant le même nom
	 * @param type $path Chemin absolu vers le dossier à zipper
	 * @param boolean $supprimerOriginal
	 */
	public function zipperSousDossier($suffixe, $supprimerOriginal=false) {
		$pathFic = $this->pathFic($suffixe);
		$pathZip = $this->pathZip($suffixe);
		$this->ajusterDossier(dirname($pathZip));
		$path = realpath($pathFic);
		$element = basename($pathFic);
		$zip = new ZipArchive;
		$res = $zip->open($pathZip, ZipArchive::CREATE);
		if ($res === TRUE) {
			//TODO Réviser l'obligation de séparer dossier et élément
			$this->zipper_ajouter($zip, $path, $element);
		}
		if ($supprimerOriginal === true) {
			$this->supprimerFichier($pathFic);
		}
	}
	/**
	 * Zippe un dossier en lui donnant le même nom
	 * @param type $path Chemin absolu vers le dossier à zipper
	 * @param boolean $supprimerOriginal
	 */
	public function zipper($path, $supprimerOriginal=false) {
		/*OBSELETE Remplacé par zipperSousDossier*/
		$path = realpath($path);
		$dossier = dirname($path);
		$element = basename($path);
		$zip = new ZipArchive;
		$res = $zip->open($path.'.zip', ZipArchive::CREATE);
		if ($res === TRUE) {
			//TODO Réviser l'obligation de séparer dossier et élément
			$this->zipper_ajouter($zip, $path, $element);
		}
		$zip->close();
		if ($supprimerOriginal === true) $this->supprimerFichier($path);
	}
	/**
	 * Ajoute un fichier ou un dossier à un zip
	 * @param type $zip
	 * @param type $path
	 * @param type $element
	 */
	public function zipper_ajouter($zip, $path, $element) {
		if ($this->zipper_nomValide($path)==false) {
			return false;
		}
		if (!file_exists($path)) {
			return false;
		}
		if (is_dir($path)) {
			$fics = glob($path."/*");
			foreach($fics as $fic) {
				$nom = basename($fic);
				$this->zipper_ajouter($zip, "$path/$nom", "$element/$nom");
			}
		} else if (filesize($path)==0) {
			$path = substr($path, 0, -strlen($element)).implode("/", array_slice(explode("/", $element), 1));
			$this->zipper_ajouter($zip, $path, $element);
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
	 * @param type $nom
	 * @return boolean
	 */
	public function zipper_nomValide($nom) {
		$nom = basename($nom);
		foreach(self::$exclusionsZip as $patt) {
			if (preg_match("#$patt#", $nom)) return false;
		}
		return true;
	}
	/**
	 * Zippe un dossier en lui donnant le même nom
	 * @param type $path Chemin absolu vers le dossier à zipper
	 * @param boolean $supprimerOriginal
	 */
	public function sousDossier($suffixe='', $supprimer=false) {
		$path = $this->path;
		$element = basename($path);
		$pathDossier = $path."/".$element.$suffixe;
		if (file_exists($pathDossier)) {
			if ($supprimer) {
				$this->supprimerFichier($pathDossier);
			} else {
				return $this;
			}
		}
		$this->sousDossier_ajouter($path, $pathDossier, true);
	}
	public function ajusterDossier($pathDossier) {
		if (file_exists($pathDossier)) {
			return $pathDossier;
		} else if (!$pathDossier) {
			return "";
		} else {
			$this->ajusterDossier(dirname($pathDossier));
			mkdir($pathDossier);
			return $pathDossier;
		}
	}
	/**
	 * Ajoute un fichier ou un dossier à un sous-dossier
	 * @param type $pathOrigine Le chemin absolu vers l'élément à copier
	 * @param type $pathDestination Le chemin absolu vers le dossier dans lequel on doit copier l'élément
	 */
	public function sousDossier_ajouter($pathOrigine, $pathDestination, $forcer=false) {
		$nomsAlias = array("images", "scripts", "script", "style", "styles");
		$extensionsNonAlias = array("php","htm","html","xhtml");
		if (!$forcer && $this->sousDossier_nomValide($pathOrigine)==false) return false;
		//$path = $pathDestination."/".basename($pathOrigine).$suffixe;
		$nom = basename($pathOrigine);
		$ext = pathinfo($pathOrigine, PATHINFO_EXTENSION);
		if (in_array($nom, $nomsAlias)) {
			file_put_contents($pathDestination, "");
		} elseif (is_dir($pathOrigine)) {
			$fics = glob($pathOrigine."/*");
			mkdir($pathDestination);
			foreach($fics as $fic) {
				$this->sousDossier_ajouter($fic, $pathDestination."/".basename($fic));
			}
		} elseif (in_array($ext, $extensionsNonAlias)) {
			copy($pathOrigine, $pathDestination);
		} else {
			file_put_contents($pathDestination, "");
		}
		return true;
	}
	/**
	 * Retourne true si le fichier doit être includ dans le zip en fonction de exclusionsZip
	 * @param type $path
	 * @return boolean
	 */
	public function sousDossier_nomValide($path) {
		$nom = basename($path);
		$valide = $this->zipper_nomValide($nom);
		if (!$valide) {
			return false;
		}
		$dossier = preg_quote(basename($this->path, "#"));
		if (is_dir($path) && preg_match("#^".$dossier.".*#", $nom)) {
			return false;
		}
		if (preg_match("#^".$dossier.".*\.zip#", $nom)) {
			return false;
		}
		return true;
	}
	/** Devrait être surchargée par l'application */
	public function ligneProjet($admin=false){
		$resultat = '';
		$resultat .= ($this->visible) ? '<li class="projet">' : '<li class="projet off">';
		$resultat .= ($admin) ? $this->boutonsAdmin() : '';
		$resultat .= $this->creerBoutonsLiens();
		$resultat .= '<a target="_blank" href="'.$this->url.'/">';
		$resultat .= ($this->prefixe) ? $this->prefixe. " : " : '';
		$resultat .= '<b>'.$this->titre.'</b>';
		if ($this->source) {
			$resultat .= '<span class="source_visible" title="Code source inclus dans la page">&#9873;</span>';
		}
		$resultat .= '</a>';
		$resultat .= '</li>';
		return $resultat;
	}
	public function affichageEcran() {
		$nom = basename($this->url).".png";
		if (!file_exists($this->path."/".$nom)) {
			return "";
		}
		return '<div class="ecran"><img src="'.$this->url.'/'.$nom.'" alt="'.$this->titre.'" /></div>';
	}
	public function affichageFormModifier() {
		$pathFicIni = $this->pathFicIni();
		$ini = file_get_contents ($pathFicIni);
		$form = '';
		$form .= '<div id="form" style="position:fixed; left:0; top:0; right:0; bottom:0; background-color:rgba(0,0,0,.5);z-index:2000; line-height:2em;">';
		$form .= '<form id="modifier" method="post" action="?admin&amp;ajax" style="width:800px; margin:0 auto; background-color:white; padding:2em; color:black; margin-top:2em;box-shadow:0 0 1em;">';
		$form .= '<h2 style="margin:0; text-align:center;">Modifier un projet</h2>';
		//$form .= '<div><textarea name="ini" cols="100" rows="30">'.$ini.'</textarea></div>';
		$form .= implode("", $this->executerFct('html_form'));
		$form .= '<div><input name="modifier" type="hidden" value="'.urlencode($this->url).'" /><input type="submit" name="envoyer" /><input type="submit" name="annuler" value="Annuler"/></div>';
		$form .= '</form>';
		$form .= '</div>';
		return $form;
	}
	public function html_lienFichier($nom, $extensions=["htm","html","php"]) {
		$etiquette = self::$etiquettes[$nom];
		if (isset($this->liens[$etiquette])) {
			return "";
		}
		foreach ($extensions as $extension) {
			$path = "$this->path/$nom.$extension";
			$url = "$this->url/$nom.$extension";
			if (file_exists($path)) {
				return '<a href="'.$url.'" class="'.$nom.'" title="'.$etiquette.'"></a>';
			}
		}
		return "";
	}
	/**
	 * Retourne une liste de liens html associés au dossier
	 * @return string La liste de liens
	 */
	public function creerLiens() {
		//TODO Vérifier la précéance entre les liens ds ini et la présence du fichier. Présemtement, le fichier l'emporte
		$liens = array();
		// Lien CONSIGNES
		$lien = $this->html_lienFichier("consignes");
		if ($lien) {
			$liens[] = $lien;
		}
		// Lien FICHIERS
		$etiquette = Fichiers::$etiquette;
		if (!isset($this->liens[$etiquette]) && ($lien = Fichiers::html_lien($this))!="") {
			$liens[] = $lien;
		}
		// Lien Solution
		$etiquette = Solution::$etiquette;
		if (!isset($this->liens[$etiquette]) && ($lien = Solution::html_lien($this))!="") {
			$liens[] = $lien;
		}
		// Autres liens
		foreach ($this->liens as $etiquette=>$adresse) {
			// C'est un lien absolu : on ne vérifie pas la présence
			if (preg_match('#^/|^[a-z+]*:\/\/#', $adresse)) {
				$liens[] = '<a href="'.$adresse.'" title="'.$etiquette.'"></a>';
			} else {
				$path = $this->path.'/'.$this->url;
				$url = $this->url.'/'.$adresse;
				//if (file_exists($path))
					$liens[] = '<a href="'.$url.'" title="'.$etiquette.'"></a>';
			}
		}
		return $liens;
	}
	public function lienFichiers($flags=0) {
		// Lien FICHIERS
		$type = ($flags & self::PATH_SOLUTION) ? "solution" : "fichiers";
		$etiquette = self::$etiquettes[$type];
		$data[$type] = $this->url;
		return self::lienTelecharger($etiquette, $data, $type);
	}
	static public function lienTelecharger($etiquette, $data, $class='') {
		if ($class) $class = ' class="telecharger '.$class.'"';
		else $class = ' class="telecharger"';
		return '<a href="telecharger.php?'.self::encoder($data).'"'.$class.' title="'.$etiquette.'"></a>';
	}
	static public function encoder($data) {
		$data = serialize($data);
		$data = base64_encode($data);
		$data = str_rot13($data);
		$data = str_replace('=','',$data);
		$data = strtr($data, '+/', '-_');
		return $data;
	}
	static public function decoder($data) {
		$data = strtr($data, '-_', '+/');
		$data = str_pad($data, 4*ceil(strlen($data)/4), "=", STR_PAD_RIGHT);
		$data = str_rot13($data);
		$data = base64_decode($data);
		$data = unserialize($data);
		return $data;
	}

	/**
	 * Supprime au complet un fichier ou un dossier
	 * @param type $fichier Chemin vers le dossier ou fichier
	 * @return boolean Retourne true s'il y a eu suppression
	 */
	static public function supprimerFichier($fichier) {
		if (!file_exists($fichier)) return false;
		if (is_dir($fichier)) {
			$contenu = glob("{$fichier}/*");
			foreach($contenu as $nomfic) {
				$this->supprimerFichier($nomfic);
			}
			rmdir($fichier);
		}else{
			unlink($fichier);
		}
		return true;
	}
	/**
	 * Retourne la liste html de la portion d'arbo envoyée
	 * @param type $arbo
	 * @return string Du html
	 */
	static public function relatif($de, $a){
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
	public function relatifSite($path){
		return self::relatif(self::$racine, $path);
	}
	public function relatifDossier($path){
		$path = $this->relatifSite($path);
		$path = substr($path, strlen($this->url)+1);
		return $path;
	}
	// Valeurs par défaut
	//protected $_description = "";
	static public function init() {
		// Pas besoin d'appeler le parent puisque le init du parent est déjà appelé
		self::$fonctionnalites[] = 'Fichiers';
		self::$fonctionnalites[] = 'Solution';
		self::$racine = realpath('.');
	}
	// ACCESSEURS ////////////////////////////////////////////////////////////////

	// METHODES //////////////////////////////////////////////////////////////////
	/** Ancienne version... AJUSTER */
	public function boutonsAdmin(){
		$resultat = '<span class="admin">';

		//$resultat .= '<a style="" href="admin.php?a='.urlencode($this->url).'">Paramètres</a>';
		$ajax = (Listfic::$modeAjax) ? '&amp;ajax' : '';
		$commande = urlencode('['.urlencode($this->url).']');
		$resultat .= '<a style="" href="?admin'.$ajax.'&amp;a'.$commande.'">Paramètres</a>';
		if ($this->visible) {
			$resultat .= '<a class="visibilite toggle on" href="?admin'.$ajax.'&amp;v'.$commande.'=false">Masquer le projet</a>';
		} else {
			$resultat .= '<a class="visibilite toggle off" href="?admin'.$ajax.'&amp;v'.$commande.'=true">Afficher le projet</a>';
		}
		if ($this->fichiers) {
			$resultat .= '<a class="fichiers toggle on" href="?admin'.$ajax.'&amp;f'.$commande.'=false">Retirer le dossier de départ</a>';
		} else if (file_exists($this->pathFic())||file_exists($this->pathZip())) {
			$resultat .= '<a class="fichiers toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Publier le dossier de départ</a>';
		} else {
			$resultat .= '<a class="fichiers toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Créer un dossier de départ</a>';
		}
		if ($this->solution) {
			$resultat .= '<a class="solution toggle on" href="?admin'.$ajax.'&amp;s'.$commande.'=false">Retirer le dossier de solution</a>';
		} else if (file_exists($this->pathFic(self::$suffixe_solution))||file_exists($this->pathZip(self::$suffixe_solution))) {
			$resultat .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Publier le dossier de solution</a>';
		} else {
			$resultat .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Créer un dossier de solution</a>';
		}
		$resultat .= '</span>';
		return $resultat;
	}
	/** AJUSTER
	 * Retourne une liste de liens html associés au dossier
	 * @return string La liste de liens
	 */
	public function creerBoutonsLiens() {
		$liens = $this->creerLiens();
		$liens = implode("", $liens);
		if ($liens) {
			return ' <span class="boutons-liens">'.$liens.'</span>';
		}
		else return '';
	}
}
Dossier::init();

