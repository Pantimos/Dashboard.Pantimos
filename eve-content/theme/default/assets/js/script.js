if(document.getElementById("console-result")){
    var cm = CodeMirror.fromTextArea(document.getElementById("console-result"), {
        lineNumbers  : true,
        mode         : "shell",
        matchBrackets: true,
        theme        : "monokai"
    });

    console.log(cm)
}


$(function () {

    function initRoute() {
        var oRoute = location.search.split('?'), arr = {};
        oRoute = oRoute[oRoute.length - 1];
        if (oRoute) {
            for (var i = 0, d = oRoute.split('&'), j = d.length; i < j; i++) {
                if (d[i]) {
                    var t = d[i].split('=');
                    if (t.length === 2) {
                        arr[t[0]] = t[1];
                    }
                }
            }
        }
        return arr;
    }

    function initNav() {
        var route = initRoute(),
            nav = $('.masthead-nav');
        switch (route.mod) {
            case 'nginx':
                nav.find('li.nav-nginx').addClass('active');
                break;
            case 'redis':
                nav.find('li.nav-redis').addClass('active');
                break;
            case 'api':
                nav.find('li.nav-api').addClass('active');
                break;
            case 'hosts':
                nav.find('li.nav-hosts').addClass('active');
                break;
            case 'doc':
                nav.find('li.nav-doc').addClass('active');
                break;
            case 'build':
                nav.find('li.nav-build').addClass('active');
                break;
            default :
                nav.find('li.nav-home').addClass('active');
                break;
        }
    }

    initNav();

    function initEditHosts() {
        var route = initRoute();
        switch (route.mod) {
            case 'hosts':
                if (route.action === 'add' || route.action === 'remove') {
                    $('.panel-edit-hosts').removeClass('hide');
                }
                break;
        }
    }

    initEditHosts();

    var body = $('body');
    body.on('HOST:SWITCH', function () {
        $('.panel-edit-hosts').toggleClass('hide');
    }).on('HOST:ADD', function (e, target) {
        $.getJSON(target.attr('href'), {"data": $('.input-host-add').val()}, function (data) {
            if (data == 'ok') {
                location.href = '/?mod=hosts&action=view';
            } else {
                alert(data);
            }
        });
    }).on('HOST:REMOVE', function (e, target) {
        $.getJSON(target.attr('href'), {"data": $('.input-host-remove').val()}, function (data) {
            if (data == 'ok') {
                location.href = '/?mod=hosts&action=view';
            } else {
                alert(data);
            }
        });
    }).on('NGINX:RESTART', function () {
        $.getJSON($(this).attr('href')).always(function () {
            setTimeout(function () {
                location.reload()
            }, 500);
        });
    });

    $('.btn-edit-hosts').on('click', function () {
        body.trigger('HOST:SWITCH');
    });

    $('.btn-host-add').on('click', function (e) {
        e.preventDefault();
        body.trigger('HOST:ADD', [$(this)]);
    });

    $('.btn-host-remove').on('click', function (e) {
        e.preventDefault();
        body.trigger('HOST:REMOVE', [$(this)]);
    });

    $('.input-host-add').on('keyup', function (e) {
        if (e.keyCode === 13) {
            body.trigger('HOST:ADD', [$('.btn-host-add')]);
        }
    });

    $('.input-host-remove').on('keyup', function (e) {
        if (e.keyCode === 13) {
            body.trigger('HOST:REMOVE', [$('.btn-host-remove')]);
        }
    });

    $('.btn-nginx-restart').on('click', function (e) {
        e.preventDefault();
        body.trigger('NGINX:RESTART');
    });

    var fetchApi = $('#console-result').attr('data-url');
    fetchApi && $.ajax({
        type   : "GET",
        url    : fetchApi,
        success: function (response, status, xhr) {
            var ret = xhr.getResponseHeader("Page-Cache")|| "";
            $('#console-result').val($('#console-result').val()+"\n"+ret);
        }
    });


});