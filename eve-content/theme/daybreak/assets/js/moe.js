define('model/core', [], function () {
    'use strict';
    return window.$;
});
define('model/debug', [
    'require',
    'exports',
    'module'
], function (require, module, exports) {
    'use strict';
    var globalLevel = 0;
    var userLevel = 0;
    var debugCache = {};
    var version = '0.0.1', Debug = function (params) {
            return new Debug.fn.init(params);
        };
    Debug.fn = Debug.prototype = {
        Debug: version,
        constructor: Debug
    };
    Debug.extend = Debug.fn.extend = function () {
        var options, name, src, copy, target = this;
        if ((options = arguments[0]) != null) {
            for (name in options) {
                src = target[name];
                copy = options[name];
                if (target === copy) {
                    continue;
                }
                if (copy !== undefined) {
                    target[name] = copy;
                }
            }
        }
        return target;
    };
    var init = Debug.fn.init = function (params) {
        switch (arguments.length) {
        case 1:
            Debug.extend(instance(params));
            break;
        default:
            Debug.extend(instance(0));
            return this;
        }
        return this;
    };
    init.prototype = Debug.fn;
    function instance(setLevel) {
        if (!(typeof setLevel === 'number' && setLevel >= 0 && setLevel <= 5)) {
            setLevel = 0;
        }
        userLevel = setLevel;
        return getDebug(userLevel || globalLevel);
    }
    function isFogy() {
        return navigator.appName.indexOf('Internet Explorer') > -1 && (navigator.appVersion.indexOf('MSIE 9') == -1 && navigator.appVersion.indexOf('MSIE 1') == -1);
    }
    function getDebug(level) {
        if (!debugCache[level]) {
            debugCache[level] = function (w, level) {
                var c = w.console || null, p = w.performance || null, v = function () {
                    }, d = {}, f = [
                        'count',
                        'error',
                        'warn',
                        'info',
                        'debug',
                        'log',
                        'time',
                        'timeEnd'
                    ];
                for (var i = 0, j = f.length; i < j; i++) {
                    (function (x, i) {
                        d[x] = c && c[x] ? function () {
                            level >= i && level <= 5 && (isFogy() ? Function.prototype.call.call(c[x], c, Array.prototype.slice.call(arguments)) : c[x].apply(c, arguments));
                        } : v;
                    }(f[i], i));
                }
                d['timeStamp'] = function () {
                    return +new Date();
                };
                d['performance'] = p && p.timing ? p.timing : null;
                return d;
            }(window, level);
        }
        return debugCache[level];
    }
    return Debug;
});
define('model/bg-star', [
    'require',
    'exports',
    'module',
    'model/core'
], function (require) {
    'use strict';
    var $ = require('./core');
    var win = $(window);
    function start(target) {
        function lineToAngle(x1, y1, length, radians) {
            var x2 = x1 + length * Math.cos(radians), y2 = y1 + length * Math.sin(radians);
            return {
                x: x2,
                y: y2
            };
        }
        function randomRange(min, max) {
            return min + Math.random() * (max - min);
        }
        function degreesToRads(degrees) {
            return degrees / 180 * Math.PI;
        }
        var particle = {
            x: 0,
            y: 0,
            vx: 0,
            vy: 0,
            radius: 0,
            create: function (x, y, speed, direction) {
                var obj = Object.create(this);
                obj.x = x;
                obj.y = y;
                obj.vx = Math.cos(direction) * speed;
                obj.vy = Math.sin(direction) * speed;
                return obj;
            },
            getSpeed: function () {
                return Math.sqrt(this.vx * this.vx + this.vy * this.vy);
            },
            setSpeed: function (speed) {
                var heading = this.getHeading();
                this.vx = Math.cos(heading) * speed;
                this.vy = Math.sin(heading) * speed;
            },
            getHeading: function () {
                return Math.atan2(this.vy, this.vx);
            },
            setHeading: function (heading) {
                var speed = this.getSpeed();
                this.vx = Math.cos(heading) * speed;
                this.vy = Math.sin(heading) * speed;
            },
            update: function () {
                this.x += this.vx;
                this.y += this.vy;
            }
        };
        var canvas = $(target).get(0), context = canvas.getContext('2d'), width = canvas.width = win.width(), height = canvas.height = win.height(), stars = [], shootingStars = [], layers = [
                {
                    speed: 0.015,
                    scale: 0.2,
                    count: 320
                },
                {
                    speed: 0.03,
                    scale: 0.5,
                    count: 50
                },
                {
                    speed: 0.05,
                    scale: 0.75,
                    count: 30
                }
            ], starsAngle = 145, shootingStarSpeed = {
                min: 15,
                max: 20
            }, shootingStarOpacityDelta = 0.01, trailLengthDelta = 0.01, shootingStarEmittingInterval = 2000, shootingStarLifeTime = 500, maxTrailLength = 300, starBaseRadius = 2, shootingStarRadius = 3, paused = false;
        for (var j = 0; j < layers.length; j += 1) {
            var layer = layers[j];
            for (var i = 0; i < layer.count; i += 1) {
                var star = particle.create(randomRange(0, width), randomRange(0, height), 0, 0);
                star.radius = starBaseRadius * layer.scale;
                star.setSpeed(layer.speed);
                star.setHeading(degreesToRads(starsAngle));
                stars.push(star);
            }
        }
        function createShootingStar() {
            var shootingStar = particle.create(randomRange(width / 2, width), randomRange(0, height / 2), 0, 0);
            shootingStar.setSpeed(randomRange(shootingStarSpeed.min, shootingStarSpeed.max));
            shootingStar.setHeading(degreesToRads(starsAngle));
            shootingStar.radius = shootingStarRadius;
            shootingStar.opacity = 0;
            shootingStar.trailLengthDelta = 0;
            shootingStar.isSpawning = true;
            shootingStar.isDying = false;
            shootingStars.push(shootingStar);
        }
        function killShootingStar(shootingStar) {
            setTimeout(function () {
                shootingStar.isDying = true;
            }, shootingStarLifeTime);
        }
        function update() {
            if (!paused) {
                context.clearRect(0, 0, width, height);
                context.fillStyle = '#282a3a';
                context.fillRect(0, 0, width, height);
                context.fill();
                for (var i = 0; i < stars.length; i += 1) {
                    var star = stars[i];
                    star.update();
                    drawStar(star);
                    if (star.x > width) {
                        star.x = 0;
                    }
                    if (star.x < 0) {
                        star.x = width;
                    }
                    if (star.y > height) {
                        star.y = 0;
                    }
                    if (star.y < 0) {
                        star.y = height;
                    }
                }
                for (i = 0; i < shootingStars.length; i += 1) {
                    var shootingStar = shootingStars[i];
                    if (shootingStar.isSpawning) {
                        shootingStar.opacity += shootingStarOpacityDelta;
                        if (shootingStar.opacity >= 1) {
                            shootingStar.isSpawning = false;
                            killShootingStar(shootingStar);
                        }
                    }
                    if (shootingStar.isDying) {
                        shootingStar.opacity -= shootingStarOpacityDelta;
                        if (shootingStar.opacity <= 0) {
                            shootingStar.isDying = false;
                            shootingStar.isDead = true;
                        }
                    }
                    shootingStar.trailLengthDelta += trailLengthDelta;
                    shootingStar.update();
                    if (shootingStar.opacity > 0) {
                        drawShootingStar(shootingStar);
                    }
                }
                for (i = shootingStars.length - 1; i >= 0; i--) {
                    if (shootingStars[i].isDead) {
                        shootingStars.splice(i, 1);
                    }
                }
            }
            requestAnimationFrame(update);
        }
        function drawStar(star) {
            context.fillStyle = 'rgb(255, 221, 157)';
            context.beginPath();
            context.arc(star.x, star.y, star.radius, 0, Math.PI * 2, false);
            context.fill();
        }
        function drawShootingStar(p) {
            var x = p.x, y = p.y, currentTrailLength = maxTrailLength * p.trailLengthDelta, pos = lineToAngle(x, y, -currentTrailLength, p.getHeading());
            context.fillStyle = 'rgba(255, 255, 255, ' + p.opacity + ')';
            var starLength = 5;
            context.beginPath();
            context.moveTo(x - 1, y + 1);
            context.lineTo(x, y + starLength);
            context.lineTo(x + 1, y + 1);
            context.lineTo(x + starLength, y);
            context.lineTo(x + 1, y - 1);
            context.lineTo(x, y + 1);
            context.lineTo(x, y - starLength);
            context.lineTo(x - 1, y - 1);
            context.lineTo(x - starLength, y);
            context.lineTo(x - 1, y + 1);
            context.lineTo(x - starLength, y);
            context.closePath();
            context.fill();
            context.fillStyle = 'rgba(255, 221, 157, ' + p.opacity + ')';
            context.beginPath();
            context.moveTo(x - 1, y - 1);
            context.lineTo(pos.x, pos.y);
            context.lineTo(x + 1, y + 1);
            context.closePath();
            context.fill();
        }
        update();
        setInterval(function () {
            if (paused)
                return;
            createShootingStar();
        }, shootingStarEmittingInterval);
        win.on('focus', function () {
            paused = false;
        });
        win.on('blur', function () {
            paused = true;
        });
    }
    return start;
});
define('page/home', [
    'require',
    'exports',
    'module',
    'model/core',
    'model/bg-star'
], function (require) {
    'use strict';
    return {
        init: function (container) {
            var $ = require('../model/core');
            var page = $(container);
            if (!page.length) {
                return false;
            }
            var bgStar = require('../model/bg-star');
            bgStar('.js-page-home .bg-star');
            $('.application-intro').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var scrollTop = page.find('.bg-star').height() + page.find('.bg-star').offset().top - $('body > navbar').height();
                if (scrollTop > $('body').scrollTop()) {
                    $('html,body').stop().animate({ scrollTop: scrollTop }, 800);
                }
            });
        }
    };
});
define('tpl/project-list', [], function () {
    'use strict';
    function render(it) {
        var out = '<table class="table table table-bordered table-hover js-table-project-list"> <thead> <tr> <th class="col-md-11">\u9879\u76EE\u540D\u79F0</th> <th class="col-md-1">\u5220\u9664\u9879\u76EE</th> </tr> </thead> <tbody> ';
        var arr1 = it;
        if (arr1) {
            var item, index = -1, l1 = arr1.length - 1;
            while (index < l1) {
                item = arr1[index += 1];
                out += ' <tr> <td>' + item + '</td> <td> <a class="button button-pill button-tiny button-circle button-caution js-remove-project" href="#"><i class="fa fa-minus"></i></a> </td> </tr> ';
            }
        }
        out += ' </tbody></table>';
        return out;
    }
    return render;
});
define('tpl/mock-list', [], function () {
    'use strict';
    function render(it) {
        var out = '<table class="table table table-bordered table-hover js-table-mock-list"> <thead> <tr> <th class="col-md-11">\u9879\u76EE\u540D\u79F0</th> <th class="col-md-1">\u5220\u9664\u9879\u76EE</th> </tr> </thead> <tbody> ';
        var arr1 = it;
        if (arr1) {
            var item, index = -1, l1 = arr1.length - 1;
            while (index < l1) {
                item = arr1[index += 1];
                out += ' <tr> <td>' + item.name + '</td> <td> ';
                if (item.mock) {
                    out += ' <a class="button button-pill button-tiny button-circle button-caution js-remove-mock" href="#"><i class="fa fa-minus"></i></a> ';
                }
                out += ' ';
                if (!item.mock) {
                    out += ' <a class="button button-pill button-tiny button-circle button-action js-create-mock" href="#"><i class="fa fa-plus"></i></a> ';
                }
                out += ' </td> </tr> ';
            }
        }
        out += ' </tbody></table>';
        return out;
    }
    return render;
});
define('model/template', [
    'require',
    'exports',
    'module',
    'tpl/project-list',
    'tpl/mock-list'
], function (require) {
    'use strict';
    var tplPackage = {
        'project-list': require('../tpl/project-list'),
        'mock-list': require('../tpl/mock-list')
    };
    var container = {
        'project-list': '.js-table-project-list',
        'mock-list': '.js-table-mock-list'
    };
    function Template(page) {
        this.page = page;
    }
    Template.prototype = {
        render: function (name, data) {
            this.page.find(container[name]).replaceWith(tplPackage[name](data));
        }
    };
    return function (page) {
        if (page) {
            return new Template(page);
        } else {
            return this;
        }
    };
});
define('model/config', [
    'require',
    'exports',
    'module',
    'model/core'
], function (require) {
    'use strict';
    var $ = require('./core');
    var host = '//' + location.host;
    var protocol = location.protocol;
    function makeUp(name, params) {
        var base = API[name], param = '';
        if (!base) {
            return '';
        }
        if (params) {
            param = '?' + $.param(params);
        }
        return {
            uri: base.uri + param,
            type: base.type
        };
    }
    var API = {
        'getProjectList': {
            uri: protocol + host + '/api/project-list',
            type: 'POST'
        },
        'createProject': {
            uri: protocol + host + '/api/create-project',
            type: 'POST'
        },
        'removeProject': {
            uri: protocol + host + '/api/remove-project',
            type: 'POST'
        },
        'getMockList': {
            uri: protocol + host + '/api/mock-list',
            type: 'POST'
        },
        'createMock': {
            uri: protocol + host + '/api/create-mock',
            type: 'POST'
        },
        'removeMock': {
            uri: protocol + host + '/api/remove-mock',
            type: 'POST'
        }
    };
    return makeUp;
});
define('model/network', [
    'require',
    'exports',
    'module',
    'model/debug',
    'model/config'
], function (require) {
    var $ = window.$;
    var debug = require('./debug');
    var config = require('./config');
    var dataStatus = 'data-network-status';
    debug('log');
    var failCode = {
        'NETWORK_ERROR': 500,
        'REQUEST_ERROR': 400
    };
    var body = $('body');
    function requestApi(type, uriParams, data, success, fail) {
        function innerSuccess(response) {
            body.attr(dataStatus, '');
            debug.debug('[\u8BF7\u6C42\u6210\u529F]\u5F53\u524D\u63A5\u53E3:', type, ' \u8FD4\u56DE\u5185\u5BB9:', response);
            if (response && response.status && response.status === 'success') {
                if (success) {
                    if (response.data) {
                        return success(response.data);
                    } else {
                        return success(response);
                    }
                }
                return true;
            } else {
                return innerFail(response);
            }
        }
        function innerFail(response) {
            body.attr(dataStatus, '');
            debug.debug('[\u8BF7\u6C42\u5931\u8D25]\u5F53\u524D\u63A5\u53E3:', type, ' \u8FD4\u56DE\u5185\u5BB9:', response);
            if (response && response.status && response.status === 'fail') {
                if (fail) {
                    if (response.data) {
                        return fail(response.data, failCode.REQUEST_ERROR);
                    } else {
                        return fail(response, failCode.REQUEST_ERROR);
                    }
                }
            } else {
                return fail(response, failCode.NETWORK_ERROR);
            }
        }
        var api = config(type, uriParams);
        var status = body.attr(dataStatus);
        if (status && status === 'locked') {
            debug.warn('\u6B63\u5728\u8BF7\u6C42\u63A5\u53E3\u4E2D\uFF0C\u8BF7\u52FF\u91CD\u590D\u63D0\u4EA4\u3002');
            return false;
        } else {
            body.attr(dataStatus, 'locked');
            $.ajax({
                type: api.type,
                url: api.uri,
                data: data,
                contentType: 'application/json',
                success: innerSuccess,
                error: innerFail
            });
        }
    }
    return { request: requestApi };
});
define('page/project', [
    'require',
    'exports',
    'module',
    'model/core',
    'model/debug',
    'model/template',
    'model/network'
], function (require) {
    'use strict';
    return {
        init: function (container) {
            var $ = require('../model/core');
            var page = $(container);
            if (!page.length) {
                return false;
            }
            var debug = require('../model/debug');
            debug('log');
            var Template = require('../model/template')(page);
            var Network = require('../model/network');
            function getList() {
                Network.request('getProjectList', '', '', function (response) {
                    debug.info(response, '\u83B7\u53D6\u9879\u76EE\u5217\u8868\u6210\u529F\u3002');
                    Template.render('project-list', response);
                }, function (response) {
                    debug.error(response, '\u83B7\u53D6\u9879\u76EE\u5217\u8868\u5931\u8D25');
                });
            }
            function pageLoaded() {
                getList();
            }
            page.delegate('.js-remove-project', 'click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                Network.request('removeProject', '', { 'domain': $(this).closest('tr').find('td:first-child').text() }, function (response) {
                    getList();
                    debug.log(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u6210\u529F\u3002');
                }, function (response) {
                    debug.error(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u5931\u8D25');
                });
            });
            page.delegate('.js-create-project-button', 'click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                Network.request('createProject', '', { 'domain': page.find('.input-domain').val() }, function (response) {
                    getList();
                    debug.log(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u6210\u529F\u3002');
                }, function (response) {
                    debug.error(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u5931\u8D25');
                });
            });
            pageLoaded();
        }
    };
});
define('page/mock', [
    'require',
    'exports',
    'module',
    'model/core',
    'model/debug',
    'model/template',
    'model/network'
], function (require) {
    'use strict';
    return {
        init: function (container) {
            var $ = require('../model/core');
            var page = $(container);
            if (!page.length) {
                return false;
            }
            var debug = require('../model/debug');
            debug('log');
            var Template = require('../model/template')(page);
            var Network = require('../model/network');
            function getList() {
                Network.request('getMockList', '', '', function (response) {
                    debug.info(response, '\u83B7\u53D6\u9879\u76EE\u5217\u8868\u6210\u529F\u3002');
                    Template.render('mock-list', response);
                }, function (response) {
                    debug.error(response, '\u83B7\u53D6\u9879\u76EE\u5217\u8868\u5931\u8D25');
                });
            }
            function pageLoaded() {
                getList();
            }
            page.delegate('.js-create-mock', 'click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                Network.request('createMock', '', { 'domain': $(this).closest('tr').find('td:first-child').text() }, function (response) {
                    getList();
                    debug.log(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u6210\u529F\u3002');
                }, function (response) {
                    debug.error(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u5931\u8D25');
                });
            });
            page.delegate('.js-remove-mock', 'click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                Network.request('removeMock', '', { 'domain': $(this).closest('tr').find('td:first-child').text() }, function (response) {
                    getList();
                    debug.log(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u6210\u529F\u3002');
                }, function (response) {
                    debug.error(response, '\u521B\u5EFA\u65B0\u7684\u9879\u76EE\u73AF\u5883\u5931\u8D25');
                });
            });
            pageLoaded();
        }
    };
});
define('moe', [
    'require',
    'exports',
    'module',
    'model/core',
    'model/debug',
    'page/home',
    'page/project',
    'page/mock'
], function (require) {
    'use strict';
    var $ = require('./model/core');
    var debug = require('./model/debug');
    debug('info');
    var page = {};
    function init() {
        $(function () {
            debug.log('Pantimos Start!');
            page.home = require('./page/home').init('.js-page-home');
            page.project = require('./page/project').init('.js-page-project');
            page.mock = require('./page/mock').init('.js-page-mock');
        });
    }
    return { init: init };
});