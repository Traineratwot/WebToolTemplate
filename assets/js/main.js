var Wt = /** @class */ (function () {
    function Wt() {
        this.events();
        this.render = new WtRender(this);
    }
    Wt.prototype.id = function (length) {
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
    Wt.prototype.events = function () {
        var Core = this;
        $(document).on('submit', 'form', function (e) {
            e.preventDefault();
            if (this instanceof HTMLFormElement) {
                var form = $(this);
                var formData = new FormData(this);
                var c = form.trigger('beforeSubmit', formData);
                if (c) {
                    var method = form.attr('method') || 'POST';
                    var action = form.attr('action');
                    var dataType = form.data('type') || 'json';
                    var before = form.data('before') || false;
                    var settings = {
                        type: method,
                        url: '/index.php?a=' + action,
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
                    };
                    if (before !== false && window[before] instanceof Function) {
                        // @ts-ignore
                        var r = window[before].call(this, formData, settings);
                        if (r !== false) {
                            if (r instanceof Object) {
                                settings = r;
                            }
                        }
                        else {
                            return false;
                        }
                    }
                    var id = form.attr('id') || Core.id();
                    if (method && action) {
                        e.preventDefault();
                        $.ajax(settings).done(function (e) {
                            e.success = false;
                            form.trigger('afterSubmit', e);
                        });
                    }
                }
            }
            return false;
        });
    };
    return Wt;
}());
var WtRender = /** @class */ (function () {
    function WtRender(wt) {
        this.wt = wt;
    }
    WtRender.prototype.getData = function (alias, data, callback) {
        if (data === void 0) { data = []; }
        var settings = {
            "url": "/?a=render",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "Content-Type": "application/json"
            },
            "data": JSON.stringify({
                "alias": alias,
                "data": data
            }),
        };
        $.ajax(settings).done(function (response) {
            callback(response);
        });
    };
    WtRender.prototype.render = function (elem, alias, data) {
        if (data === void 0) { data = []; }
        $(elem).html("");
        var self = this;
        this.getData(alias, data, function (data) {
            self.elem = $(data);
            self.elem.appendTo(elem);
        });
    };
    WtRender.prototype.append = function (elem, alias, data) {
        if (data === void 0) { data = []; }
        var self = this;
        this.getData(alias, data, function (data) {
            self.elem = $(data);
            self.elem.appendTo(elem);
        });
    };
    WtRender.prototype.prepend = function (elem, alias, data) {
        if (data === void 0) { data = []; }
        var self = this;
        this.getData(alias, data, function (data) {
            self.elem = $(data);
            self.elem.prependTo(elem);
        });
    };
    return WtRender;
}());
// @ts-ignore
$(function () {
    window['wt'] = new Wt();
});
