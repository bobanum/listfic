<?php
namespace Listfic;
// TODO : Ajouter automatiquement la line d'affichage de la source
// TODO : Permettre d'afficher plusieurs sources (dont les includes)
// TODO : Laisser le menu même si on ne montre pas la source
// TODO : Exemples = source automatique
// TODO : Exercices et travaux = source jamais (A moins d'une mention dans le ini)
// TODO : Un proxy qui donne les files au besoin (et qui peut les zipper...)
// error_reporting(E_ALL);
// $liste = new Listfic();
// echo $liste->affichageArbo();

use Listfic\Directory;
trait Html {
	public function html_admin() {
		if (!isset($_GET['admin'])) {
			return '';
		}
		$result = '';
		if (!$this->admin) {
			return $this->html_admin_loginForm();
		} else {
			$result .= $this->html_admin_logout ();
		}
		$result .= $this->html_admin_adminForm();
		return $result;
	}
	public function html_head() {
		$result = '';
		if ($this->ajaxMode) {
			$result .= '<script src="'.$this->urlScript("listfic.js").'"></script>';
		}
		return $result;
	}
	/**
	 * Retourne l'affichage de l'arborescence courante.
	 * @return string
	 */
	public function html_arbo() {
		$result = '';
		foreach($this->arbo as $key=>&$val) {
			//TODO Anonymize
			$result .= '<article style="page-break-inside: avoid;">';
			$result .= '<h2>'.$key.'</h2>';
			$result .= $this->html_arboBranch($val, $this->isAdmin());
			$result .= '</article>';
		}
		return $result;
	}
	private function html_admin_loginForm(){
		$result = '<form action="" method="post">';
		$result .= '<input name="password" type="password" placeholder="Mot de passe" />';
		$result .= '<input name="login" type="hidden" />';
		$result .= '<input type="submit" />';
		$result .= '</form>';
		return $result;
	}
	private function html_admin_logout(){
		return '<div><a href="'.basename($_SERVER['PHP_SELF']).'">Quitter l\'administration</a></div>';
	}
	private function html_admin_adminForm() {
		if (!isset($_GET['a'])) {
			return '';
		}
		$directory = new Directory($_GET['a']);
		$result = $directory->html_updateForm();
		return $result;
	}
	/** AJUSTER
	 * Retourne une liste HTML des éléments de l'arborescence envoyée
	 * @param array $arbo
	 * @return string
	 */
	static public function html_arboBranch($arbo, $admin=false) {
		$result = '<ul class="category">';
		foreach($arbo as $key=>&$val) {
			if (is_a($val, '\Listfic\Directory')) {	// C'est un directory
				$result .= $val->html_projectLine($admin);
			}else{
				$result .= '<li class="category"><span>'.$key.'</span>';
				$result .= $this->html_arboBranch($val, $admin);
				$result .= '</li>';
			}
		}
		$result .= "</ul>";
		return $result;
	}
}
