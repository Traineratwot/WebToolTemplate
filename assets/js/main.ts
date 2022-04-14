class Wt {
	public renderer: WtRenderer;

	constructor() {
		this.events()
		this.renderer = new WtRenderer(this)
	}

	id(length = 6) {
		length               = length - 1
		var result           = 'a';
		var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for (var i = 0; i < length; i++) {
			result += characters.charAt(Math.floor(Math.random() *
												   charactersLength));
		}
		return result;
	}

	canSee(elem: string, async = false) {

		var block_show = null
		var displays   = []
		$(elem).parents().each(function () {
			displays.push($(this).css('display'));
		})
		if (displays.indexOf('none') > 0) {
			console.log('Блок ' + elem + ' скрыт');
			block_show = false;
		} else {
			var wt = $(window).scrollTop();
			var wh = $(window).height();
			var et = $(elem).offset().top;
			var eh = $(elem).outerHeight();
			if (wt + wh >= et && wt + wh - eh * 2 <= et + (wh - eh)) {
				if (block_show == null || block_show == false) {
					console.log('Блок ' + elem + ' в области видимости');
				}
				block_show = true;
			} else {
				if (block_show == null || block_show == true) {
					console.log('Блок ' + elem + ' скрыт');
				}
				block_show = false;
			}
		}
		if (async) {
			return Promise.resolve([elem, block_show])
		} else {
			return block_show
		}
	}

	events() {
		var Core = this;
		$(document).on('submit', 'form:not(.default)', function (e) {
			e.preventDefault();
			if (this instanceof HTMLFormElement) {
				var form     = $(this);
				var formData = new FormData(this);
				var c        = form.trigger('beforeSubmit', formData)
				if (c) {
					var method: string                = form.attr('method') || 'POST';
					var action: string                = form.attr('action');
					var dataType: string              = form.data('type') || 'json';
					var before: string | false        = form.data('before') || false;
					var settings: JQuery.AjaxSettings = {
						type       : method,
						url        : '/index.php?a=' + action,
						cache      : false,
						contentType: false,
						processData: false,
						data       : formData,
						dataType   : dataType,
						success    : function (msg) {
							msg.formId = id
							if (msg.success == true) {
								form.trigger('success', msg)
							} else {
								form.trigger('failure', msg)
							}
						},
						error      : function (e) {
							// @ts-ignore
							e.success = false;
							form.trigger('failure', e,)
						}
					}
					if (before !== false && window[before] instanceof Function) {
						// @ts-ignore
						var r: JQuery.AjaxSettings | false = window[before].call(this, formData, settings)
						if (r !== false) {
							if (r instanceof Object) {
								settings = r;
							}
						} else {
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

	logout() {
		$.get('/user/logout').done(function () {
			document.location.href = '/'
		})
	}
}

class WtRenderer {
	private wt: Wt;
	private elem: any;

	constructor(wt) {
		this.wt = wt
	}

	async render(elem, alias, data = []) {
		$(elem).html("")
		var self = this
		await this.getData(alias, data, (data) => {
			self.elem = $(data);
			self.elem.appendTo(elem);
		})
		return true;
	}

	append(elem, alias, data = []) {
		var self = this
		this.getData(alias, data, (data) => {
			self.elem = $(data);
			self.elem.appendTo(elem);
		})
	}

	prepend(elem, alias, data = []) {
		var self = this
		this.getData(alias, data, (data) => {
			self.elem = $(data);
			self.elem.prependTo(elem);
		})
	}

	private getData(alias, data = [], callback) {
		var settings = {
			"url"    : "/?a=render",
			"method" : "POST",
			"timeout": 0,
			"headers": {
				"Content-Type": "application/json"
			},
			"data"   : JSON.stringify({
										  "alias": alias,
										  "data" : data
									  }),
		};

		$.ajax(settings).done(function (response) {
			callback(response)
		});
	}

}

// @ts-ignore
$(() => {
	window['wt'] = new Wt();
})