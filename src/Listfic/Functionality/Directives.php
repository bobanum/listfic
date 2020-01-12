<?php
namespace Listfic\Functionality;
class Directives extends Functionality {
	public $fieldName = "directives";
	public $label = "Directives";
	public $description = 'Directives...';
	public $filenames = ["directives", "consignes", "instructions", "readme"];
	public $extensions = ["htm", "html", "php", "md"];
	protected $_value = "";
	/**
	 * @returns array
	*/
	public function html_links() {
		foreach ($this->filenames as $filename) {
			foreach ($this->extensions as $extension) {
				$file = "{$filename}.{$extension}";
				$path = $this->directory->path($file);
				$url = $this->directory->url($file);
				if (file_exists($path)) {
					return [$this->fieldName => '<a href="'.$url.'" class="'.$this->fieldName.'" title="'.$this->label.'"></a>'];
				}
			}
		}
		return [];
	}

}
