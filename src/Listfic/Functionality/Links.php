<?php
namespace Listfic\Functionality;
class Links extends Functionality {
	public $fieldName = "links";
	public $label = "Liens";
	public $description = 'Un tableau de links (étiquette=>url) ou une série de lines (étiquette=url)';
	public function ini_get($ini){
		parent::ini_get($ini);
		if (!is_array($this->value)) {
			$lines = trim($this->value);
			if ($lines) {
				$lines = preg_split("#\r\n|\n\r|\n|\r#", $lines);
			} else {
				$lines = [];
			}
			$result = [];
			foreach ($lines as $line) {
				$line = explode("=", $line, 2);
				$result[$line[0]] = $line[1];
			}
			$this->value = $result;
		}
		return $this->value;
	}
	public function html_form() {
		$fieldName = $this->fieldName;
		$val = [];
		foreach ($this->value as $label=>$url) {
			$val[] = $label."=".$url;
		}
		$val = implode("\r\n", $val);
		$result = '';
		$result .= '<textarea name="'.$fieldName.'" id="'.$fieldName.'" cols="40" rows="3" style="vertical-align:top;">';
		$result .= $val;
		$result .= '</textarea>';
		$result = $this->html_form_line($result);
		return $result;
	}
}
