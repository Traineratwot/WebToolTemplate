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
					var method = form.attr('method') || 'POST';
					var action = form.attr('action');
					var dataType = form.data('type') || 'json';
					var id = form.attr('id') || Core.id();
					if (method && action) {
						e.preventDefault();
						$.ajax({
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
						}).done(function (e) {
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