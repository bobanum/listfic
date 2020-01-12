<?php
/*TODO Enlever la notion de flag*/
namespace Listfic;

trait Directory_Html {
	public function html_projectLine($admin = false){
		$result = '';
		$result .= ($this->visible) ? '<li class="projet">' : '<li class="projet off">';
		$result .= ($admin) ? $this->html_adminButtons() : '';
		$result .= $this->createLinksButtons();
		$result .= '<a target="_blank" href="'.$this->url().'/">';
		$result .= ($this->prefix->value) ? $this->prefix->value. " : " : '';
		$result .= '<b>'.$this->title->value.'</b>';
		if ($this->source) {
			$result .= '<span class="source_visible" title="Code source inclus dans la page">&#9873;</span>';
		}
		$result .= '</a>';
		$result .= '</li>';
		return $result;
	}
	//TODO Create Functionality
	public function html_screen() {
		$name = basename($this->url()).".png";
		if (!file_exists($this->path($name))) {
			return "";
		}
		return '<div class="ecran"><img src="'.$this->url($name).'" alt="'.$this->title->value.'" /></div>';
	}
	public function html_iframe() {
		return false;	//TODO Vérifier la pertinence
	}
	public function html_updateForm() {
		$path_iniFile = $this->path_iniFile;
		$ini = file_get_contents ($path_iniFile);
		$form = '';
		$form .= '<div id="form" style="position:fixed; left:0; top:0; right:0; bottom:0; background-color:rgba(0,0,0,.5);z-index:2000; line-height:2em;">';
		$form .= '<form id="modifier" method="post" action="?admin&amp;ajax" style="width:800px; margin:0 auto; background-color:white; padding:2em; color:black; margin-top:2em;box-shadow:0 0 1em;">';
		$form .= '<h2 style="margin:0; text-align:center;">Modifier un projet</h2>';
		//$form .= '<div><textarea name="ini" cols="100" rows="30">'.$ini.'</textarea></div>';
		$form .= implode("", $this->executeFunction('html_form'));
		$form .= '<div><input name="modifier" type="hidden" value="'.urlencode($this->url()).'" /><input type="submit" name="envoyer" /><input type="submit" name="annuler" value="Annuler"/></div>';
		$form .= '</form>';
		$form .= '</div>';
		return $form;
	}
	/**
	 * Retourne une liste de links html associés au directory
	 * @return array La liste de links
	 */
	public function createLinks() {
		//TODO Vérifier la précéance entre les links ds ini et la présence du fichier. Présemtement, le fichier l'emporte
		$result = [];
		// Link DIRECTIVES
		$result = array_replace($result, $this->directives->html_links());
		// Link FICHIERS
		$result = array_replace($result, $this->files->html_links());
		
		// $label = $this->files->label;
		// if (!isset($this->links->value[$label]) && ($link = $this->links->html_link()) !== "") {
		// 	$result[] = $link;
		// }
		// Link Solution
		$label = $this->solution->label;
		if (!isset($this->links->value[$label]) && ($link = $this->solution->html_link()) !== "") {
			$result[] = $link;
		}
		// Autres links
		foreach ($this->links->value as $label=>$address) {
			// C'est un link absolu : on ne vérifie pas la présence
			if (preg_match('#^/|^[a-z+]*:\/\/#', $address)) {
				$result[] = '<a href="'.$address.'" title="'.$label.'"></a>';
			} else {
				// $path = $this->path($this->url());
				$url = $this->url($address);
				//if (file_exists($path))
					$result[] = '<a href="'.$url.'" title="'.$label.'"></a>';
			}
		}
		return $result;
	}
	public function link_files($flags=0) {
		// Lien FICHIERS
		$type = ($flags & $this->PATH_SOLUTION) ? "solution" : "files";
		$label = $this->labels[$type];
		$data[$type] = $this->url();
		return $this->link_download($label, $data, $type);
	}
	public function link_download($label, $data, $class='') {
		$attrs = [];
		if ($class) {
			$attrs['class'] = 'telecharger '.$class.'';
		} else {
			$attrs['class'] = 'telecharger';
		}
		$attrs['href'] = 'telecharger.php?'.$data[0].'='.$data[1].'';
//		$attrs['href'] = 'telecharger.php?'.$this->encoder($data).'';
		$attrs['title'] = $label;
		$attrs = $this->attrString($attrs);
		return '<a '.$attrs.'></a>';
	}
	public function attrString($attrs) {
		$result = [];
		foreach ($attrs as $name=>$val) {
			$result[] = ''.$name.'="'.htmlspecialchars($val).'"';
		}
		$result = implode(" ", $result);
		return $result;
	}

	// METHODS //////////////////////////////////////////////////////////////////
	/** Ancienne version... AJUSTER */
	public function html_adminButtons(){
		$result = '<span class="admin">';

		//$result .= '<a style="" href="admin.php?a='.urlencode($this->url()).'">Paramètres</a>';
		$ajax = (Listfic::$ajaxMode) ? '&amp;ajax' : '';
		$commande = urlencode('['.urlencode($this->url()).']');
		$result .= '<a style="" href="?admin'.$ajax.'&amp;a'.$commande.'">Paramètres</a>';
		if ($this->visible) {
			$result .= '<a class="visibilite toggle on" href="?admin'.$ajax.'&amp;v'.$commande.'=false">Masquer le projet</a>';
		} else {
			$result .= '<a class="visibilite toggle off" href="?admin'.$ajax.'&amp;v'.$commande.'=true">Afficher le projet</a>';
		}
		if ($this->files) {
			$result .= '<a class="files toggle on" href="?admin'.$ajax.'&amp;f'.$commande.'=false">Retirer le directory de départ</a>';
		} else if (file_exists($this->files->path())||file_exists($this->path_zip())) {
			$result .= '<a class="files toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Publier le directory de départ</a>';
		} else {
			$result .= '<a class="files toggle off" href="?admin'.$ajax.'&amp;f'.$commande.'=true">Créer un directory de départ</a>';
		}
		if ($this->solution) {
			$result .= '<a class="solution toggle on" href="?admin'.$ajax.'&amp;s'.$commande.'=false">Retirer le directory de solution</a>';
		} else if (file_exists($this->solution->path())||file_exists($this->path_zip($this->solution_suffix))) {
			$result .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Publier le directory de solution</a>';
		} else {
			$result .= '<a class="solution toggle off" href="?admin'.$ajax.'&amp;s'.$commande.'=true">Créer un directory de solution</a>';
		}
		$result .= '</span>';
		return $result;
	}
	/** AJUSTER
	 * Retourne une liste de links html associés au directory
	 * @return string La liste de links
	 */
	public function createLinksButtons() {
		$links = $this->createLinks();
		$links = implode("", $links);
		if ($links) {
			return ' <span class="buttons-links">'.$links.'</span>';
		}
		else return '';
	}
}
