var Core = /** @class */ (function () {
    function Core() {
        this.events();
    }
    Core.prototype.events = function () {
        $('form').on('submit', function (e) {
            var _a;
            if (this instanceof HTMLFormElement) {
                var formData = new FormData(this);
                var form = $(this);
                var method = (_a = form.attr('method')) !== null && _a !== void 0 ? _a : 'POST';
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
                            msg.formId = id;
                            if (msg.success == true) {
                                form.trigger('success', msg);
                            }
                            else {
                                form.trigger('failure', msg);
                            }
                        }
                    });
                }
            }
        });
    };
    return Core;
}());
// @ts-ignore
$(function () {
    window['core'] = new Core();
});
