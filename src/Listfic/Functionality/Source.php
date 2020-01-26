<?php
namespace Listfic\Functionality;
class Source extends Functionality {
	public $fieldName = "source";
	protected $_value = false;
	private $choices = [
		'Visible'=>'true',
		'Cachée'=>'false',
	];
	public function html_form() {
		$champ = $this->html_select($this->choices);
		return $this->html_form_line($champ);
	}
}
