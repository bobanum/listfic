<?php
namespace Listfic\Functionality;
class Title extends Functionality {
	static public $name = "Title";
	static public $fieldName = "title";
	static public $label = "Title";
	static public $description = 'Le title qui s\'affiche dans la liste';
	static public function html($directoryObject) {
		return '<a class="title" target="_blank" href="'.$directoryObject->url.'">'.$directoryObject->title.'</a>';
	}
	static public function ini_get($directoryObject, $ini){
		parent::ini_get($directoryObject, $ini);
		if (!$directoryObject->title) $directoryObject->title = static::findTitle($directoryObject);
		return $directoryObject->title;
	}
	/**
	 * Analyse le fichier index pour en extraire le title
	 * @return string
	 */
	static private function findTitle($directoryObject){
		$path = $directoryObject->path;
		$title = basename($path);
		if (count($files = glob($path."/index.*")) === 0) {
			return $title;
		}
		$patterns = [
			"<title.*>(.*)</title.*>",
			"<h1.*>(.*)</h1.*>",
		];

		$html = file_get_contents($files[0]);
		foreach ($patterns as $pattern) {
			$result = self::match($pattern, $html);
			if ($result) {
				return $result;
			}
		}
		return $title;
	}
	static private function match($pattern, $html){
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
