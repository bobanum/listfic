<?php
namespace Listfic\Functionality;
class Title extends Functionality {
	public $name = "Title";
	public $fieldName = "title";
	public $label = "Title";
	public $description = 'Le title qui s\'affiche dans la liste';
	private $patterns = [
		"<title.*>(.*)</title.*>",
		"<h1.*>(.*)</h1.*>",
	];

	public function html() {
		return '<a class="title" target="_blank" href="'.$this->directory->url.'">'.$this->value.'</a>';
	}
	public function ini_get($ini){
		parent::ini_get($ini);
		if (!$this->value) {
			$this->value = $this->findTitle();
		}
		return $this->value;
	}
	/**
	 * Analyse le fichier index pour en extraire le title
	 * @return string
	 */
	private function findTitle(){
		$path = $this->directory->path;
		$title = basename($path);
		if (count($files = glob($path."/index.*")) === 0) {
			return $title;
		}

		$html = file_get_contents($files[0]);
		foreach ($this->patterns as $pattern) {
			$result = $this->match($pattern, $html);
			if ($result) {
				return $result;
			}
		}
		return $title;
	}
	private function match($pattern, $html){
		preg_match("#$pattern#", $html, $matches);
		if (count($matches) === 0) {
			return false;
		}
		$result = trim($matches[1]);
		if ($result === "") {
			return false;
		}
		return $result;
	}
}
