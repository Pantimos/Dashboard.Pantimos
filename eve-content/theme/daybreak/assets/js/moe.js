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
    'module'
], function (require) {
    var $ = window.$;
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
    'model/bg-star'
], function (require, module, exports) {
    'use strict';
    var $ = window.$;
    var bgStar = require('../model/bg-star');
    var homePage = $('.js-page-home');
    function init() {
        if (homePage.length) {
            bgStar('.js-page-home .bg-star');
            $('.application-intro').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var scrollTop = homePage.find('.bg-star').height() + homePage.find('.bg-star').offset().top - $('body > navbar').height();
                $('html,body').animate({ scrollTop: scrollTop }, 800);
            });
        }
    }
    return { init: init };
});
define('page/project', [
    'require',
    'exports',
    'module'
], function (require, module, exports) {
    'use strict';
    var $ = window.$;
    var projectPage = $('.js-page-project');
    function getList() {
        if (projectPage.length) {
            $.post('/project-list', null, function (response) {
                if (response) {
                    switch (response.code) {
                    case 200:
                        console.log(response.data);
                        break;
                    case 400:
                        console.log(response.data);
                        break;
                    }
                }
            });
        }
    }
    function pageLoaded() {
        getList();
    }
    function init() {
        if (projectPage.length) {
            projectPage.find('.js-create-project-button').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $.post('/create-project', { 'domain': projectPage.find('.input-domain').val() }, function (resp) {
                    console.log(resp);
                });
            });
        }
        pageLoaded();
    }
    return { init: init };
});
define('moe', [
    'require',
    'exports',
    'module',
    'model/debug',
    'page/home',
    'page/project'
], function (require, module, exports) {
    'use strict';
    var $ = window.$;
    var debug = require('./model/debug');
    debug(5);
    var home = require('./page/home');
    var project = require('./page/project');
    function initTheme() {
        $(function () {
            debug.log('this is demo.');
            home.init();
            project.init();
        });
    }
    return { init: initTheme };
});