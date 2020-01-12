<?php
namespace Listfic\Functionality;
class Description extends Functionality {
	public $fieldName = "description";
	public $label = "Description";
	public $description = 'Une description plus ou moins longue du projet';
	public function html() {
		$description = preg_split('#\r\n|\n\r|\n|\r#', $this->value);
		$description = array_reduce($description, function(&$r, $i) {
			$r .= '<p>'.$i.'</p>';
			return $r;
		});
		return '<div class="description">'.$description.'</div>';
	}
	public function html_form() {
		$fieldName = $this->fieldName;
		$champ = '';
		$champ .= '<textarea name="'.$fieldName.'" id="'.$fieldName.'" cols="60" rows="7" style="vertical-align:top;">';
		$champ .= $this->value;
		$champ .= '</textarea>';
		$result = $this->html_form_line($champ);
		return $result;
	}
}
