	$(function() {
		$('span.admin a').click(evt.clicAdmin);
	})
	evt = {
		clicAdmin:function(e) {
			var dom = $(this).closest('li');
			e.preventDefault();
			var href = $(this).attr('href');
			if (href.substr(0,5)=="admin") {location = href; return;}
			var data = href.split('?');
			var adresse = data.shift();
			var data = data.join('');
			$.get(adresse, data, function(resultat, etat, ajax) {
				var nouveau = $(ajax.responseText);
				$('span.admin a', nouveau).click(evt.clicAdmin);
				if (nouveau.is('li')) dom.replaceWith(nouveau);
				else if (nouveau.is('#form')) {
					$(document.body).append(nouveau);
					$("#form > form").submit(function(e){
						e.preventDefault();
						var input=e.originalEvent.explicitOriginalTarget;
						if (!$(input).is("[name=annuler]")) {
							var obj = {};
							for (var i=0, size=this.elements.length; i<size; i++ ) {
								var element = this.elements[i];
								obj[element.name] = element.value;
							}
							delete(obj.annuler);
							$.post(this.action, obj, function(resultat, etat, ajax) {
								var nouveau = $(ajax.responseText);
								$('span.admin a', nouveau).click(evt.clicAdmin);
								if (nouveau.is('li')) dom.replaceWith(nouveau);
							});
						};
						$(this).parent().remove();
						return false;
					});
				}
			});
		},
	}
