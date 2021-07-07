var Core = /** @class */ (function () {
    function Core() {
        this.events();
    }
    Core.prototype.id = function (length) {
        if (length === void 0) { length = 6; }
        length = length - 1;
        var result = 'a';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() *
                charactersLength));
        }
        return result;
    };
    Core.prototype.events = function () {
        var Core = this;
        $('form').on('submit', function (e) {
            if (this instanceof HTMLFormElement) {
                var form = $(this);
                var formData = new FormData(this);
                var c = form.trigger('beforeSubmit', formData);
                if (c) {
                    var method = form.attr('method') || 'POST';
                    var action = form.attr('action');
                    var dataType = form.data('type') || 'json';
                    var id = form.attr('id') || Core.id();
                    if (method && action) {
                        e.preventDefault();
                        $.ajax({
                            type: method,
                            url: action,
                            cache: false,
                            contentType: false,
                            processData: false,
                            data: formData,
                            dataType: dataType,
                            success: function (msg) {
                                msg.formId = id;
                                if (msg.success == true) {
                                    form.trigger('success', msg);
                                }
                                else {
                                    form.trigger('failure', msg);
                                }
                            },
                            error: function (e) {
                                // @ts-ignore
                                e.success = false;
                                form.trigger('failure', e);
                            }
                        }).done(function (e) {
                            e.success = false;
                            form.trigger('submit', e);
                        });
                    }
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
