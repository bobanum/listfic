<?php
namespace Listfic\Functionality;

use Exception;

class Functionality {
	protected $name = "Functionality";
	protected $fieldName = "functionality";
	protected $label = "Functionality";
	protected $dataType = "string";
	protected $description = "La description de la Functionality";

	protected $directory = null;
	protected $_value = null;

	public function __construct($directory, $ini = []) {
		$this->directory = $directory;
		$this->ini_get($ini);
	}
	public function __get($name) {
		$get_name = "get_$name";
		if (method_exists($this, $get_name)) {
			return $this->$get_name();
		}
		throw new Exception("Undefined property '$name");
	}
	public function __set($name, $val) {
		$set_name = "set_$name";
		if (method_exists($this, $set_name)) {
			return $this->$set_name($val);
		}
		throw new Exception("Undefined property '$name'.");
	}
	// public function __toString() {
	// 	return "{$this->value}";
	// }
	public function get_value() {
		return $this->_value;
	}
	public function set_value($val) {
		$this->_value = $val;
	}
	public function html_links() {

	}
	public function html_button(){
		return "<button>Empty</button>";
	}
	public function html() {
		return '<div>'.$this->value.'</div>';
	}
	public function ini_get($ini){
		$fieldName = $this->fieldName;
		if (!$fieldName) {
			return;
		}
		if (isset($ini[$fieldName])) {
			$val = $ini[$fieldName];
			if ($val === 'true') {
				$val = true;
			} else if ($val === 'false') {
				$val = false;
			}
			$this->value = $val;
		} else {
			$this->directory->modified = true;
		}
		return $this->value;
	}
	public function ini_create() {
		$fieldName = $this->fieldName;
		if (!$fieldName) {
			return;
		}
		$result = '';
		$result .= "\t//".$this->description."\r\n";
		$result .= "\t'".$this->fieldName."'";
		$result .= " => ".var_export($this->value, true).",\r\n";
		return $result;
	}
	static protected function admin_process() {
		return "admin";
	}
	protected function html_form() {
		$fieldName = $this->fieldName;
		$result = '<input type="text" name="'.$fieldName.'" id="'.$fieldName.'" value="'.$this->value.'" size="38" />';
		$result = static::html_form_line($result);
		return $result;
	}
	protected function html_form_line($field){
		$fieldName = $this->fieldName;
		$result = '';
		$result .= '<div class="'.$fieldName.'">';
		$result .= '<label for="'.$fieldName.'">'.$this->label.'</label>';
		$result .= $field;
		$result .= '<span>'.$this->description.'</span>';
		$result .= '</div>';
		return $result;
	}
	protected function html_select($choices=[]){
		$fieldName = $this->fieldName;
		$result = '';
		$result .= '<select name="'.$fieldName.'" id="'.$fieldName.'">';
		$current = $this->value;
		if (!is_string($current)) {
			$current = var_export($current, true);
		}
		foreach ($choices as $label => $value) {
			$selected = ($value === $current) ? ' selected="selected"' : '';
			$result .= '<option value="'.$value.'"'.$selected.'>'.$label.'</option>';
		}
		$result .= '</select>';
		return $result;
	}
}

