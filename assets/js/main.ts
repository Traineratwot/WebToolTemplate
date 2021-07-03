class Core {
	constructor() {
		this.events()
	}

	events() {
		$('form').on('submit', function (e) {
			if (this instanceof HTMLFormElement) {
				var formData = new FormData(this);
				var form = $(this);
				var method = form.attr('method') ?? 'POST';
				var action = form.attr('action');
				var id = form.attr('id');
				if (method && action && id) {
					e.preventDefault();
					$.ajax({
						type: method,
						url: action,
						cache: false,
						contentType: false,
						processData: false,
						data: formData,
						dataType: 'json',
						success: function (msg) {
							msg.formId = id
							if (msg.success == true) {
								form.trigger('success', msg)
							} else {
								form.trigger('failure', msg)
							}
						},
					})
				}
			}
		})
	}
}


// @ts-ignore
$(() => {
	window['core'] = new Core();
})