$(function () {
    'use strict';

    var consoleBox = document.getElementById('console-result');
    if (consoleBox) {
        var cm = CodeMirror.fromTextArea(consoleBox, {
            lineNumbers  : true,
            mode         : 'shell',
            matchBrackets: true,
            theme        : 'monokai'
        });
    }

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

    // 初始化导航按钮
    (function initNav () {
        var route   = initRoute(),
            navBtns = $('.masthead-nav > li');
        var currentNav = 'home';
        for (var i = 0, j = navBtns.length; i < j; i++) {
            if (navBtns.eq(i).data('mod') === route.mod) {
                return navBtns.eq(i).addClass('active');
            }
        }
        $('.masthead-nav > .nav-home').addClass('active');
    }());
    // 初始化额外的编辑框
    (function editPanel () {
        var route = initRoute();
        switch (route.mod) {
            case 'hosts':
                if (route.action === 'add' || route.action === 'remove') {
                    $('.panel-edit-hosts').removeClass('hide');
                }
                break;
            case 'project':
                if (route.action === 'create' || route.action === 'destroy') {
                    $('.panel-edit-project').removeClass('hide');
                }
                break;
        }
    }())

    var body = $('body');
    body
        .on('HOST:SWITCH', function () {
            $('.panel-edit-hosts').toggleClass('hide');
        })
        .on('HOST:ADD', function (e, target) {
            $.getJSON(target.attr('href'), {"data": $('.input-host-add').val()}, function (data) {
                if (data && data.code === 200) {
                    location.href = '/?mod=hosts&action=view';
                } else {
                    alert(data && data.desc);
                }
            });
        })
        .on('HOST:REMOVE', function (e, target) {
            $.getJSON(target.attr('href'), {"data": $('.input-host-remove').val()}, function (data) {
                if (data && data.code === 200) {
                    location.href = '/?mod=hosts&action=view';
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
            if (!route.action || !data) {
                return false;
            }
            $.getJSON(target.attr('href'), {"data": data, "do": route.action}, function (data) {
                if (data && data.code === 200) {
                    location.href = '/?mod=project&action=help';
                } else {
                    alert(data && data.desc);
                }
            });
        });


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