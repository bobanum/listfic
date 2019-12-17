Gerer = (function(self) {
	self = self || function() {}
	self.load = function(e) {
		self.bulle("Opération réussie");
	}
	self.fade = function() {
		this.parentNode.removeChild(this);
	}
	self.clicmenu = function(e) {
		if (e.ctrlKey || e.metaKey) return true;
		e.preventDefault();
		var ajax = XMLHttpRequest();
		ajax.open("get", this.href);
		var td = e.target.parentNode.parentNode.parentNode;
		debugger;
		ajax.onreadystatechange = function(e) {
			if (this.readyState !== 4) return;
			var reponse = this.responseText;
			var ct = this.getResponseHeader("Content-type");
			if (ct.indexOf("html") > 0) {
				if (reponse.substr(0,3) === "<td") {
					var tr = document.createElement("tr");
					tr.innerHTML = reponse;
					td.parentNode.replaceChild(tr.firstChild, td);
				} else {
					// Ajouter un editeur
					document.body.appendChild(self.overlay(this.responseText));
				}
			}
		};
		ajax.send(null);
		return false;
	}
	self.clicreadonly = function(e) {
		e.preventDefault();
		var form = this.parentNode;
		form.removeChild(this);
		form.texte.readOnly=false;
		form.envoyer.disabled=false;
		return false;
	}
	self.bulle = function(html) {
		var resultat = document.createElement("div");
		resultat.setAttribute("class","bulle");
		resultat.innerHTML = html;
		resultat.addEventListener("animationend", self.fade,true);
		resultat.addEventListener("transitionend", self.fade,true);
		document.body.appendChild(resultat);
		return resultat;
	}
	self.overlay = function(html) {
		var resultat = document.createElement("div");
		resultat.setAttribute("id","ecran");
		resultat.innerHTML = html;
		return resultat;
	}
	self.editeur = function() {
		var resultat = document.createElement("div");
		resultat.setAttribute("id","ecran");
		var zone = document.createElement("div");
		resultat.appendChild(zone);
		var zone = document.createElement("div");
		resultat.appendChild(zone);
		return resultat;
	}
	self.submitEditeur = function(e) {
		//return true;
		e.preventDefault();
		if (this.clickedButton && this.clickedButton.name === "annuler") {
			var ecran = document.getElementById("ecran");
			ecran.parentNode.removeChild(ecran);
			return false;
		}
		var params = "action=sauvegarder";
		params += "&directory="+encodeURIComponent(this.directory.value)+"";
		params += "&fichier="+encodeURIComponent(this.fichier.value)+"";
		if (this.suffix) params += "&suffix="+encodeURIComponent(this.suffix.value)+"";
		params += "&texte="+encodeURIComponent(this.texte.value)+"";
		console.log(params);
		var ajax = XMLHttpRequest();
		ajax.open("post", this.getAttribute("action"), 1);
		ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
		ajax.setRequestHeader("Content-length", params.length);
		ajax.setRequestHeader("Connection", "close");
		ajax.onreadystatechange = function(e) {
			var reponse = this.responseText;
			self.bulle("Opération réussie");
		};
		ajax.send(params);
		var ecran = document.getElementById("ecran");
		ecran.parentNode.removeChild(ecran);
		return false;
	}
	return self;
})()
window.addEventListener('load', Gerer.load, true);
