class Wt {
    render;
    block_show = {};
    constructor() {
        this.events();
        this.render = new WtRender(this);
    }
    id(length = 6) {
        length = length - 1;
        var result = 'a';
        var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() *
                charactersLength));
        }
        return result;
    }
    canSee(elem, async = false) {
        if (!this.block_show.hasOwnProperty(elem)) {
            this.block_show[elem] = null;
        }
        var displays = [];
        $(elem).parents().each(function () {
            displays.push($(this).css('display'));
        });
        if (displays.indexOf('none') > 0) {
            console.log('Блок ' + elem + ' скрыт');
            this.block_show[elem] = false;
        }
        else {
            var wt = $(window).scrollTop();
            var wh = $(window).height();
            var et = $(elem).offset().top;
            var eh = $(elem).outerHeight();
            if (wt + wh >= et && wt + wh - eh * 2 <= et + (wh - eh)) {
                if (this.block_show[elem] == null || this.block_show[elem] == false) {
                    console.log('Блок ' + elem + ' в области видимости');
                }
                this.block_show[elem] = true;
            }
            else {
                if (this.block_show[elem] == null || this.block_show[elem] == true) {
                    console.log('Блок ' + elem + ' скрыт');
                }
                this.block_show[elem] = false;
            }
        }
        if (async) {
            return Promise.resolve(this.block_show[elem]);
        }
        else {
            return this.block_show[elem];
        }
    }
    events() {
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
    }
}
class WtRender {
    wt;
    elem;
    constructor(wt) {
        this.wt = wt;
    }
    getData(alias, data = [], callback) {
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
    }
    render(elem, alias, data = []) {
        $(elem).html("");
        var self = this;
        this.getData(alias, data, (data) => {
            self.elem = $(data);
            self.elem.appendTo(elem);
        });
    }
    append(elem, alias, data = []) {
        var self = this;
        this.getData(alias, data, (data) => {
            self.elem = $(data);
            self.elem.appendTo(elem);
        });
    }
    prepend(elem, alias, data = []) {
        var self = this;
        this.getData(alias, data, (data) => {
            self.elem = $(data);
            self.elem.prependTo(elem);
        });
    }
}
// @ts-ignore
$(() => {
    window['wt'] = new Wt();
});
