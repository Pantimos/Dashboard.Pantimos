$(function () {
    'use strict';


    function initConsole(){
        var consoleBox = document.getElementById('console-result');
        if (consoleBox) {
            CodeMirror.fromTextArea(consoleBox, {
                lineNumbers  : true,
                mode         : 'shell',
                matchBrackets: true,
                theme        : 'monokai'
            });
        }
    }

    initConsole();

    function initRoute () {
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

    var body = $('body');
    body
        .on('HOST:SWITCH', function () {
            $('.panel-edit-hosts').toggleClass('hide');
        })
        .on('HOST:ADD', function (e, target) {
            $.getJSON(target.attr('href'), {"data": $('.input-host-add').val()}, function (data) {
                if (data && data.code === 200) {
                    location.href = '/?pantimos_mod=hosts&pantimos_action=view';
                } else {
                    alert(data && data.desc);
                }
            });
        })
        .on('HOST:REMOVE', function (e, target) {
            $.getJSON(target.attr('href'), {"data": $('.input-host-remove').val()}, function (data) {
                if (data && data.code === 200) {
                    location.href = '/?pantimos_mod=hosts&pantimos_action=view';
                } else {
                    alert(data && data.desc);
                }
            });
        })
        .on('NGINX:RESTART', function () {
            $.getJSON($(this).attr('href')).always(function () {
                setTimeout(function () {
                    location.reload()
                }, 500);
            });
        })
        .on('PROJECT:SWITCH', function () {
            $('.panel-edit-project').toggleClass('hide');
        })
        .on('PROJECT:DO', function (e, target) {
            var route = initRoute(),
                data  = $('.input-project-name').val();
            if (!route['pantimos_action'] || !data) {
                return false;
            }
            $.getJSON(target.attr('href'), {"data": data, "do": route['pantimos_action']}, function (data) {
                if (data && data.code === 200) {
                    location.href = '/?pantimos_mod=project&pantimos_action=help';
                } else {
                    alert(data && data.desc);
                }
            });
        });


    // 初始化导航按钮
    (function initNav () {
        var route   = initRoute(),
            navBtns = $('.masthead-nav > li');
        var currentNav = 'home';
        for (var i = 0, j = navBtns.length; i < j; i++) {
            if (navBtns.eq(i).data('mod') === route['pantimos_mod']) {
                return navBtns.eq(i).addClass('active');
            }
        }
        $('.masthead-nav > .nav-home').addClass('active');
    }());
    // 初始化额外的编辑框 && etc.
    (function pageAction () {
        var route = initRoute();
        switch (route['pantimos_mod']) {
            case 'hosts':
                if (route['pantimos_action'] === 'add' || route['pantimos_action'] === 'remove') {
                    body.trigger('HOST:SWITCH');
                }
                break;
            case 'project':
                if (route['pantimos_action'] === 'create' || route['pantimos_action'] === 'destroy') {
                    body.trigger('PROJECT:SWITCH');
                }
                if (route['pantimos_action'] === 'help') {
                    $.getJSON('/?pantimos_mod=project', {'pantimos_action': 'list'}, function (data) {
                        if (data && data.code === 200) {
                            var ret = '';
                            for (var i = 0, j = data.data.length; i < j; i++) {
                                ret += data.data[i] + "\n";
                            }
                            $('#console-result').val(ret);
                            initConsole();

                        } else {
                            alert(data && data.desc);
                        }
                    })
                }
                break;
        }
    }());


    //HOST
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

    //NGINX
    $('.btn-nginx-restart').on('click', function (e) {
        e.preventDefault();
        body.trigger('NGINX:RESTART');
    });

    //PROJECT
    $('.btn-project-create').on('click', function (e) {
        e.preventDefault();
        body.trigger('PROJECT:SWITCH');
    });
    $('.btn-project-do').on('click', function (e) {
        e.preventDefault();
        body.trigger('PROJECT:DO', [$(this)]);
    });
    $('.input-project-name').on('keyup', function (e) {
        if (e.keyCode === 13) {
            body.trigger('PROJECT:DO', [$('.btn-project-do')]);
        }
    });


    var fetchApi = $('#console-result').attr('data-url');
    fetchApi && $.ajax({
        type   : "GET",
        url    : fetchApi,
        success: function (response, status, xhr) {
            var ret = xhr.getResponseHeader("Page-Cache") || "";
            $('#console-result').val($('#console-result').val() + "\n" + ret);
        }
    });


});