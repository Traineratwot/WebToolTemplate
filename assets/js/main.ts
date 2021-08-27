class Wt {
	constructor() {
		this.events()
	}

	id(length = 6) {
		length = length - 1
		var result = 'a';
		var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() *
				charactersLength));
		}
		return result;
	}

	events() {
		var Core = this;
		$(document).on('submit', 'form', function (e) {
			e.preventDefault();
			if (this instanceof HTMLFormElement) {
				var form = $(this);
				var formData = new FormData(this);
				var c = form.trigger('beforeSubmit', formData)
				if (c) {
					var method: string = form.attr('method') || 'POST';
					var action: string = form.attr('action');
					var dataType: string = form.data('type') || 'json';
					var before: string | false = form.data('before') || false;
					var settings: JQuery.AjaxSettings = {
						type: method,
						url: '/index.php?a=' + action,
						cache: false,
						contentType: false,
						processData: false,
						data: formData,
						dataType: dataType,
						success: function (msg) {
							msg.formId = id
							if (msg.success == true) {
								form.trigger('success', msg)
							} else {
								form.trigger('failure', msg)
							}
						},
						error: function (e) {
							// @ts-ignore
							e.success = false;
							form.trigger('failure', e,)
						}
					}
					if (before !== false && window[before] instanceof Function) {
						// @ts-ignore
						var r: JQuery.AjaxSettings | false = window[before].call(this,formData, settings)
						if (r !== false) {
							if (r instanceof Object) {
								settings = r;
							}
						}else{
							return false;
						}
					}
					var id = form.attr('id') || Core.id();
					if (method && action) {
						e.preventDefault();
						$.ajax(settings).done(function (e) {
							e.success = false;
							form.trigger('afterSubmit', e,)
						})
					}
				}
			}
			return false;
		})
	}
}

// @ts-ignore
$(() => {
	window['wt'] = new Wt();
})