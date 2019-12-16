<?php
namespace Listfic;
//TODO Présentement, on ne peut pas afficher la source d'un fichier dans un sous-directory. À arranger.
//echo __FILE__."<br>";
//echo $_SERVER['PHP_SELF']."<br>";
//print_r(debug_backtrace());
error_reporting(E_ALL);
include_once 'autoload.php';
if (isset($_GET['data'])) {
	$data = Directory::decoder($_GET['data']);
	$path = realpath(realpath('.')."/../".$data['d']);
	$directory = new Directory($path);
	if ($directory->solution) {
		$path = $directory->pathFic(Directory::$suffixe_solution)."/".$data['p'];
		$code = highlight_file($path, true);
		$code = substr($code, 36, -10).'<br />';
		$code = str_replace('<br /></span>','</span><br />',$code);
		$code = str_replace('&nbsp;', " ", $code);
		$code = str_replace('<br />', "\r\n", $code);
		$code = preg_replace('#<span style="color: \#0000BB">(.*)( *)</span>#U', '<var>$1</var>$2', $code);
		$code = preg_replace('#<span style="color: \#007700">(.*)( *)</span>#U', '<span class="op">$1</span>$2', $code);
		$code = preg_replace('#<span style="color: \#DD0000">(.*)( *)</span>#Um', '<span class="string">$1</span>$2', $code);
		$code = preg_replace('#<span (.*)$#Um', '<span $1</span>', $code);
		$code = preg_replace('#^(.*)</span>#Um', '<span $1</span>', $code);
		//$code = preg_replace('#(.*)<br />#U', '<div>$1</div>', $code);
		$code = '<pre>'.$code.'</pre>';
		echo $code;
	}
	exit;
} else {
	ob_start('ajouterIframe');
}
function ajouterIframe($code) {
	chdir(dirname($_SERVER['SCRIPT_FILENAME']));
	$directory = new Directory('.');
	$code = "ici".$directory->url.$code;
	if ($directory->solution) {
		return str_replace('</body>', $directory->affichageIFrame().'</body>', $code);
	} else {
		return $code;
	}
}
?>
