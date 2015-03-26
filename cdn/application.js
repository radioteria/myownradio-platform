/* ng-infinite-scroll - v1.0.0 - 2013-02-23 */
var mod;

mod = angular.module('infinite-scroll', []);

mod.directive('infiniteScroll', [
    '$rootScope', '$window', '$timeout', function($rootScope, $window, $timeout) {
        return {
            link: function(scope, elem, attrs) {
                var checkWhenEnabled, handler, scrollDistance, scrollEnabled;
                $window = angular.element($window);
                scrollDistance = 0;
                if (attrs.infiniteScrollDistance != null) {
                    scope.$watch(attrs.infiniteScrollDistance, function(value) {
                        return scrollDistance = parseInt(value, 10);
                    });
                }
                scrollEnabled = true;
                checkWhenEnabled = false;
                if (attrs.infiniteScrollDisabled != null) {
                    scope.$watch(attrs.infiniteScrollDisabled, function(value) {
                        scrollEnabled = !value;
                        if (scrollEnabled && checkWhenEnabled) {
                            checkWhenEnabled = false;
                            return handler();
                        }
                    });
                }
                handler = function() {
                    var elementBottom, remaining, shouldScroll, windowBottom;
                    windowBottom = $window.height() + $window.scrollTop();
                    elementBottom = elem.offset().top + elem.height();
                    remaining = elementBottom - windowBottom;
                    shouldScroll = remaining <= $window.height() * scrollDistance;
                    if (shouldScroll && scrollEnabled) {
                        if ($rootScope.$$phase) {
                            return scope.$eval(attrs.infiniteScroll);
                        } else {
                            return scope.$apply(attrs.infiniteScroll);
                        }
                    } else if (shouldScroll) {
                        return checkWhenEnabled = true;
                    }
                };
                $window.on('scroll', handler);
                scope.$on('$destroy', function() {
                    return $window.off('scroll', handler);
                });
                return $timeout((function() {
                    if (attrs.infiniteScrollImmediateCheck) {
                        if (scope.$eval(attrs.infiniteScrollImmediateCheck)) {
                            return handler();
                        }
                    } else {
                        return handler();
                    }
                }), 0);
            }
        };
    }
]);

// Modifies $httpProvider for correct server communication (POST variable format)
angular.module('httpPostFix', [], function($httpProvider)
{
    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';

    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = [function(data)
    {
        /**
         * The workhorse; converts an object to x-www-form-urlencoded serialization.
         * @param {Object} obj
         * @return {String}
         */
        var param = function(obj)
        {
            var query = '';
            var name, value, fullSubName, subName, subValue, innerObj, i;

            for(name in obj)
            {
                value = obj[name];

                if(value instanceof Array)
                {
                    for(i=0; i<value.length; ++i)
                    {
                        subValue = value[i];
                        fullSubName = name + '[' + i + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value instanceof Object)
                {
                    for(subName in value)
                    {
                        subValue = value[subName];
                        fullSubName = name + '[' + subName + ']';
                        innerObj = {};
                        innerObj[fullSubName] = subValue;
                        query += param(innerObj) + '&';
                    }
                }
                else if(value !== undefined && value !== null)
                {
                    query += encodeURIComponent(name) + '=' + encodeURIComponent(value) + '&';
                }
            }

            return query.length ? query.substr(0, query.length - 1) : query;
        };

        return angular.isObject(data) && String(data) !== '[object File]' ? param(data) : data;
    }];
});

/*
 * angular-loading-bar
 *
 * intercepts XHR requests and creates a loading bar.
 * Based on the excellent nprogress work by rstacruz (more info in readme)
 *
 * (c) 2013 Wes Cruver
 * License: MIT
 */


(function() {

    'use strict';

// Alias the loading bar for various backwards compatibilities since the project has matured:
    angular.module('angular-loading-bar', ['cfp.loadingBarInterceptor']);
    angular.module('chieffancypants.loadingBar', ['cfp.loadingBarInterceptor']);


    /**
     * loadingBarInterceptor service
     *
     * Registers itself as an Angular interceptor and listens for XHR requests.
     */
    angular.module('cfp.loadingBarInterceptor', ['cfp.loadingBar'])
        .config(['$httpProvider', function ($httpProvider) {

            var interceptor = ['$q', '$cacheFactory', '$timeout', '$rootScope', 'cfpLoadingBar', function ($q, $cacheFactory, $timeout, $rootScope, cfpLoadingBar) {

                /**
                 * The total number of requests made
                 */
                var reqsTotal = 0;

                /**
                 * The number of requests completed (either successfully or not)
                 */
                var reqsCompleted = 0;

                /**
                 * The amount of time spent fetching before showing the loading bar
                 */
                var latencyThreshold = cfpLoadingBar.latencyThreshold;

                /**
                 * $timeout handle for latencyThreshold
                 */
                var startTimeout;


                /**
                 * calls cfpLoadingBar.complete() which removes the
                 * loading bar from the DOM.
                 */
                function setComplete() {
                    $timeout.cancel(startTimeout);
                    cfpLoadingBar.complete();
                    reqsCompleted = 0;
                    reqsTotal = 0;
                }

                /**
                 * Determine if the response has already been cached
                 * @param  {Object}  config the config option from the request
                 * @return {Boolean} retrns true if cached, otherwise false
                 */
                function isCached(config) {
                    var cache;
                    var defaultCache = $cacheFactory.get('$http');
                    var defaults = $httpProvider.defaults;

                    // Choose the proper cache source. Borrowed from angular: $http service
                    if ((config.cache || defaults.cache) && config.cache !== false &&
                        (config.method === 'GET' || config.method === 'JSONP')) {
                        cache = angular.isObject(config.cache) ? config.cache
                            : angular.isObject(defaults.cache) ? defaults.cache
                            : defaultCache;
                    }

                    var cached = cache !== undefined ?
                    cache.get(config.url) !== undefined : false;

                    if (config.cached !== undefined && cached !== config.cached) {
                        return config.cached;
                    }
                    config.cached = cached;
                    return cached;
                }


                return {
                    'request': function(config) {
                        // Check to make sure this request hasn't already been cached and that
                        // the requester didn't explicitly ask us to ignore this request:
                        if (!config.ignoreLoadingBar && !isCached(config)) {
                            $rootScope.$broadcast('cfpLoadingBar:loading', {url: config.url});
                            if (reqsTotal === 0) {
                                startTimeout = $timeout(function() {
                                    cfpLoadingBar.start();
                                }, latencyThreshold);
                            }
                            reqsTotal++;
                            cfpLoadingBar.set(reqsCompleted / reqsTotal);
                        }
                        return config;
                    },

                    'response': function(response) {
                        if (!response.config.ignoreLoadingBar && !isCached(response.config)) {
                            reqsCompleted++;
                            $rootScope.$broadcast('cfpLoadingBar:loaded', {url: response.config.url, result: response});
                            if (reqsCompleted >= reqsTotal) {
                                setComplete();
                            } else {
                                cfpLoadingBar.set(reqsCompleted / reqsTotal);
                            }
                        }
                        return response;
                    },

                    'responseError': function(rejection) {
                        if (!rejection.config.ignoreLoadingBar && !isCached(rejection.config)) {
                            reqsCompleted++;
                            $rootScope.$broadcast('cfpLoadingBar:loaded', {url: rejection.config.url, result: rejection});
                            if (reqsCompleted >= reqsTotal) {
                                setComplete();
                            } else {
                                cfpLoadingBar.set(reqsCompleted / reqsTotal);
                            }
                        }
                        return $q.reject(rejection);
                    }
                };
            }];

            $httpProvider.interceptors.push(interceptor);
        }]);


    /**
     * Loading Bar
     *
     * This service handles adding and removing the actual element in the DOM.
     * Generally, best practices for DOM manipulation is to take place in a
     * directive, but because the element itself is injected in the DOM only upon
     * XHR requests, and it's likely needed on every view, the best option is to
     * use a service.
     */
    angular.module('cfp.loadingBar', [])
        .provider('cfpLoadingBar', function() {

            this.includeSpinner = true;
            this.includeBar = true;
            this.latencyThreshold = 100;
            this.startSize = 0.02;
            this.parentSelector = 'body';
            this.spinnerTemplate = '<div id="loading-bar-spinner"><div class="spinner-icon"></div></div>';
            this.loadingBarTemplate = '<div id="loading-bar"><div class="bar"><div class="peg"></div></div></div>';

            this.$get = ['$injector', '$document', '$timeout', '$rootScope', function ($injector, $document, $timeout, $rootScope) {
                var $animate;
                var $parentSelector = this.parentSelector,
                    loadingBarContainer = angular.element(this.loadingBarTemplate),
                    loadingBar = loadingBarContainer.find('div').eq(0),
                    spinner = angular.element(this.spinnerTemplate);

                var incTimeout,
                    completeTimeout,
                    started = false,
                    status = 0;

                var includeSpinner = this.includeSpinner;
                var includeBar = this.includeBar;
                var startSize = this.startSize;

                /**
                 * Inserts the loading bar element into the dom, and sets it to 2%
                 */
                function _start() {
                    if (!$animate) {
                        $animate = $injector.get('$animate');
                    }

                    var $parent = $document.find($parentSelector).eq(0);
                    $timeout.cancel(completeTimeout);

                    // do not continually broadcast the started event:
                    if (started) {
                        return;
                    }

                    $rootScope.$broadcast('cfpLoadingBar:started');
                    started = true;

                    if (includeBar) {
                        $animate.enter(loadingBarContainer, $parent);
                    }

                    if (includeSpinner) {
                        $animate.enter(spinner, $parent);
                    }

                    _set(startSize);
                }

                /**
                 * Set the loading bar's width to a certain percent.
                 *
                 * @param n any value between 0 and 1
                 */
                function _set(n) {
                    if (!started) {
                        return;
                    }
                    var pct = (n * 100) + '%';
                    loadingBar.css('width', pct);
                    status = n;

                    // increment loadingbar to give the illusion that there is always
                    // progress but make sure to cancel the previous timeouts so we don't
                    // have multiple incs running at the same time.
                    $timeout.cancel(incTimeout);
                    incTimeout = $timeout(function() {
                        _inc();
                    }, 250);
                }

                /**
                 * Increments the loading bar by a random amount
                 * but slows down as it progresses
                 */
                function _inc() {
                    if (_status() >= 1) {
                        return;
                    }

                    var rnd = 0;

                    // TODO: do this mathmatically instead of through conditions

                    var stat = _status();
                    if (stat >= 0 && stat < 0.25) {
                        // Start out between 3 - 6% increments
                        rnd = (Math.random() * (5 - 3 + 1) + 3) / 100;
                    } else if (stat >= 0.25 && stat < 0.65) {
                        // increment between 0 - 3%
                        rnd = (Math.random() * 3) / 100;
                    } else if (stat >= 0.65 && stat < 0.9) {
                        // increment between 0 - 2%
                        rnd = (Math.random() * 2) / 100;
                    } else if (stat >= 0.9 && stat < 0.99) {
                        // finally, increment it .5 %
                        rnd = 0.005;
                    } else {
                        // after 99%, don't increment:
                        rnd = 0;
                    }

                    var pct = _status() + rnd;
                    _set(pct);
                }

                function _status() {
                    return status;
                }

                function _completeAnimation() {
                    status = 0;
                    started = false;
                }

                function _complete() {
                    if (!$animate) {
                        $animate = $injector.get('$animate');
                    }

                    $rootScope.$broadcast('cfpLoadingBar:completed');
                    _set(1);

                    $timeout.cancel(completeTimeout);

                    // Attempt to aggregate any start/complete calls within 500ms:
                    completeTimeout = $timeout(function() {
                        var promise = $animate.leave(loadingBarContainer, _completeAnimation);
                        if (promise && promise.then) {
                            promise.then(_completeAnimation);
                        }
                        $animate.leave(spinner);
                    }, 500);
                }

                return {
                    start            : _start,
                    set              : _set,
                    status           : _status,
                    inc              : _inc,
                    complete         : _complete,
                    includeSpinner   : this.includeSpinner,
                    latencyThreshold : this.latencyThreshold,
                    parentSelector   : this.parentSelector,
                    startSize        : this.startSize
                };


            }];     //
        });       // wtf javascript. srsly
})();       //

/*
 * ngDialog - easy modals and popup windows
 * http://github.com/likeastore/ngDialog
 * (c) 2013-2014 MIT License, https://likeastore.com
 */

(function (root, factory) {
    if (typeof module !== 'undefined' && module.exports) {
        // CommonJS
        module.exports = factory(require('angular'));
    } else if (typeof define === 'function' && define.amd) {
        // AMD
        define(['angular'], factory);
    } else {
        // Global Variables
        factory(root.angular);
    }
}(this, function (angular, undefined) {
    'use strict';

    var m = angular.module('ngDialog', []);

    var $el = angular.element;
    var isDef = angular.isDefined;
    var style = (document.body || document.documentElement).style;
    var animationEndSupport = isDef(style.animation) || isDef(style.WebkitAnimation) || isDef(style.MozAnimation) || isDef(style.MsAnimation) || isDef(style.OAnimation);
    var animationEndEvent = 'animationend webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend';
    var forceBodyReload = false;
    var scope;

    m.provider('ngDialog', function () {
        var defaults = this.defaults = {
            className: 'ngdialog-theme-default',
            plain: false,
            showClose: true,
            closeByDocument: true,
            closeByEscape: true,
            closeByNavigation: false,
            appendTo: false,
            preCloseCallback: false,
            overlay: true,
            cache: true
        };

        this.setForceBodyReload = function (_useIt) {
            forceBodyReload = _useIt || false;
        };

        this.setDefaults = function (newDefaults) {
            angular.extend(defaults, newDefaults);
        };

        var globalID = 0, dialogsCount = 0, closeByDocumentHandler, defers = {};

        this.$get = ['$document', '$templateCache', '$compile', '$q', '$http', '$rootScope', '$timeout', '$window', '$controller',
            function ($document, $templateCache, $compile, $q, $http, $rootScope, $timeout, $window, $controller) {
                var $body = $document.find('body');
                if (forceBodyReload) {
                    $rootScope.$on('$locationChangeSuccess', function () {
                        $body = $document.find('body');
                    });
                }

                var privateMethods = {
                    onDocumentKeydown: function (event) {
                        if (event.keyCode === 27) {
                            publicMethods.close('$escape');
                        }
                    },

                    setBodyPadding: function (width) {
                        var originalBodyPadding = parseInt(($body.css('padding-right') || 0), 10);
                        $body.css('padding-right', (originalBodyPadding + width) + 'px');
                        $body.data('ng-dialog-original-padding', originalBodyPadding);
                    },

                    resetBodyPadding: function () {
                        var originalBodyPadding = $body.data('ng-dialog-original-padding');
                        if (originalBodyPadding) {
                            $body.css('padding-right', originalBodyPadding + 'px');
                        } else {
                            $body.css('padding-right', '');
                        }
                    },

                    performCloseDialog: function ($dialog, value) {
                        var id = $dialog.attr('id');

                        if (typeof $window.Hammer !== 'undefined') {
                            var hammerTime = scope.hammerTime;
                            hammerTime.off('tap', closeByDocumentHandler);
                            hammerTime.destroy && hammerTime.destroy();
                            delete scope.hammerTime;
                        } else {
                            $dialog.unbind('click');
                        }

                        if (dialogsCount === 1) {
                            $body.unbind('keydown');
                        }

                        if (!$dialog.hasClass("ngdialog-closing")){
                            dialogsCount -= 1;
                        }

                        $rootScope.$broadcast('ngDialog.closing', $dialog);

                        if (animationEndSupport) {
                            scope.$destroy();
                            $dialog.unbind(animationEndEvent).bind(animationEndEvent, function () {
                                $dialog.remove();
                                if (dialogsCount === 0) {
                                    $body.removeClass('ngdialog-open');
                                    privateMethods.resetBodyPadding();
                                }
                                $rootScope.$broadcast('ngDialog.closed', $dialog);
                            }).addClass('ngdialog-closing');
                        } else {
                            scope.$destroy();
                            $dialog.remove();
                            if (dialogsCount === 0) {
                                $body.removeClass('ngdialog-open');
                                privateMethods.resetBodyPadding();
                            }
                            $rootScope.$broadcast('ngDialog.closed', $dialog);
                        }
                        if (defers[id]) {
                            defers[id].resolve({
                                id: id,
                                value: value,
                                $dialog: $dialog,
                                remainingDialogs: dialogsCount
                            });
                            delete defers[id];
                        }
                    },

                    closeDialog: function ($dialog, value) {
                        var preCloseCallback = $dialog.data('$ngDialogPreCloseCallback');

                        if (preCloseCallback && angular.isFunction(preCloseCallback)) {

                            var preCloseCallbackResult = preCloseCallback.call($dialog, value);

                            if (angular.isObject(preCloseCallbackResult)) {
                                if (preCloseCallbackResult.closePromise) {
                                    preCloseCallbackResult.closePromise.then(function () {
                                        privateMethods.performCloseDialog($dialog, value);
                                    });
                                } else {
                                    preCloseCallbackResult.then(function () {
                                        privateMethods.performCloseDialog($dialog, value);
                                    }, function () {
                                        return;
                                    });
                                }
                            } else if (preCloseCallbackResult !== false) {
                                privateMethods.performCloseDialog($dialog, value);
                            }
                        } else {
                            privateMethods.performCloseDialog($dialog, value);
                        }
                    }
                };

                var publicMethods = {

                    /*
                     * @param {Object} options:
                     * - template {String} - id of ng-template, url for partial, plain string (if enabled)
                     * - plain {Boolean} - enable plain string templates, default false
                     * - scope {Object}
                     * - controller {String}
                     * - className {String} - dialog theme class
                     * - showClose {Boolean} - show close button, default true
                     * - closeByEscape {Boolean} - default true
                     * - closeByDocument {Boolean} - default true
                     * - preCloseCallback {String|Function} - user supplied function name/function called before closing dialog (if set)
                     *
                     * @return {Object} dialog
                     */
                    open: function (opts) {
                        var self = this;
                        var options = angular.copy(defaults);

                        opts = opts || {};
                        angular.extend(options, opts);

                        globalID += 1;

                        self.latestID = 'ngdialog' + globalID;

                        var defer;
                        defers[self.latestID] = defer = $q.defer();

                        scope = angular.isObject(options.scope) ? options.scope.$new() : $rootScope.$new();
                        var $dialog, $dialogParent;

                        $q.when(loadTemplate(options.template || options.templateUrl)).then(function (template) {

                            $templateCache.put(options.template || options.templateUrl, template);

                            if (options.showClose) {
                                template += '<div class="ngdialog-close"></div>';
                            }

                            self.$result = $dialog = $el('<div id="ngdialog' + globalID + '" class="ngdialog"></div>');
                            $dialog.html((options.overlay ?
                            '<div class="ngdialog-overlay"></div><div class="ngdialog-content">' + template + '</div>' :
                            '<div class="ngdialog-content">' + template + '</div>'));

                            if (options.data && angular.isString(options.data)) {
                                var firstLetter = options.data.replace(/^\s*/, '')[0];
                                scope.ngDialogData = (firstLetter === '{' || firstLetter === '[') ? angular.fromJson(options.data) : options.data;
                            } else if (options.data && angular.isObject(options.data)) {
                                scope.ngDialogData = options.data;
                            }

                            if (options.controller && (angular.isString(options.controller) || angular.isArray(options.controller) || angular.isFunction(options.controller))) {
                                var controllerInstance = $controller(options.controller, {
                                    $scope: scope,
                                    $element: $dialog
                                });
                                $dialog.data('$ngDialogControllerController', controllerInstance);
                            }

                            if (options.className) {
                                $dialog.addClass(options.className);
                            }

                            if (options.appendTo && angular.isString(options.appendTo)) {
                                $dialogParent = angular.element(document.querySelector(options.appendTo));
                            } else {
                                $dialogParent = $body;
                            }

                            if (options.preCloseCallback) {
                                var preCloseCallback;

                                if (angular.isFunction(options.preCloseCallback)) {
                                    preCloseCallback = options.preCloseCallback;
                                } else if (angular.isString(options.preCloseCallback)) {
                                    if (scope) {
                                        if (angular.isFunction(scope[options.preCloseCallback])) {
                                            preCloseCallback = scope[options.preCloseCallback];
                                        } else if (scope.$parent && angular.isFunction(scope.$parent[options.preCloseCallback])) {
                                            preCloseCallback = scope.$parent[options.preCloseCallback];
                                        } else if ($rootScope && angular.isFunction($rootScope[options.preCloseCallback])) {
                                            preCloseCallback = $rootScope[options.preCloseCallback];
                                        }
                                    }
                                }

                                if (preCloseCallback) {
                                    $dialog.data('$ngDialogPreCloseCallback', preCloseCallback);
                                }
                            }

                            scope.closeThisDialog = function (value) {
                                privateMethods.closeDialog($dialog, value);
                            };

                            $timeout(function () {
                                $compile($dialog)(scope);
                                var widthDiffs = $window.innerWidth - $body.prop('clientWidth');
                                $body.addClass('ngdialog-open');
                                var scrollBarWidth = widthDiffs - ($window.innerWidth - $body.prop('clientWidth'));
                                if (scrollBarWidth > 0) {
                                    privateMethods.setBodyPadding(scrollBarWidth);
                                }
                                $dialogParent.append($dialog);

                                if (options.name) {
                                    $rootScope.$broadcast('ngDialog.opened', {dialog: $dialog, name: options.name});
                                } else {
                                    $rootScope.$broadcast('ngDialog.opened', $dialog);
                                }
                            });

                            if (options.closeByEscape) {
                                $body.bind('keydown', privateMethods.onDocumentKeydown);
                            }

                            if (options.closeByNavigation) {
                                $rootScope.$on('$locationChangeSuccess', function () {
                                    privateMethods.closeDialog($dialog);
                                });
                            }

                            closeByDocumentHandler = function (event) {
                                var isOverlay = options.closeByDocument ? $el(event.target).hasClass('ngdialog-overlay') : false;
                                var isCloseBtn = $el(event.target).hasClass('ngdialog-close');

                                if (isOverlay || isCloseBtn) {
                                    publicMethods.close($dialog.attr('id'), isCloseBtn ? '$closeButton' : '$document');
                                }
                            };

                            if (typeof $window.Hammer !== 'undefined') {
                                var hammerTime = scope.hammerTime = $window.Hammer($dialog[0]);
                                hammerTime.on('tap', closeByDocumentHandler);
                            } else {
                                $dialog.bind('click', closeByDocumentHandler);
                            }

                            dialogsCount += 1;

                            return publicMethods;
                        });

                        return {
                            id: 'ngdialog' + globalID,
                            closePromise: defer.promise,
                            close: function (value) {
                                privateMethods.closeDialog($dialog, value);
                            }
                        };

                        function loadTemplateUrl (tmpl, config) {
                            return $http.get(tmpl, (config || {})).then(function(res) {
                                return res.data || '';
                            });
                        }

                        function loadTemplate (tmpl) {
                            if (!tmpl) {
                                return 'Empty template';
                            }

                            if (angular.isString(tmpl) && options.plain) {
                                return tmpl;
                            }

                            if (typeof options.cache === 'boolean' && !options.cache) {
                                return loadTemplateUrl(tmpl, {cache: false});
                            }

                            return $templateCache.get(tmpl) || loadTemplateUrl(tmpl, {cache: true});
                        }
                    },

                    /*
                     * @param {Object} options:
                     * - template {String} - id of ng-template, url for partial, plain string (if enabled)
                     * - plain {Boolean} - enable plain string templates, default false
                     * - name {String}
                     * - scope {Object}
                     * - controller {String}
                     * - className {String} - dialog theme class
                     * - showClose {Boolean} - show close button, default true
                     * - closeByEscape {Boolean} - default false
                     * - closeByDocument {Boolean} - default false
                     * - preCloseCallback {String|Function} - user supplied function name/function called before closing dialog (if set); not called on confirm
                     *
                     * @return {Object} dialog
                     */
                    openConfirm: function (opts) {
                        var defer = $q.defer();

                        var options = {
                            closeByEscape: false,
                            closeByDocument: false
                        };
                        angular.extend(options, opts);

                        options.scope = angular.isObject(options.scope) ? options.scope.$new() : $rootScope.$new();
                        options.scope.confirm = function (value) {
                            defer.resolve(value);
                            var $dialog = $el(document.getElementById(openResult.id));
                            privateMethods.performCloseDialog($dialog, value);
                        };

                        var openResult = publicMethods.open(options);
                        openResult.closePromise.then(function (data) {
                            if (data) {
                                return defer.reject(data.value);
                            }
                            return defer.reject();
                        });

                        return defer.promise;
                    },

                    /*
                     * @param {String} id
                     * @return {Object} dialog
                     */
                    close: function (id, value) {
                        var $dialog = $el(document.getElementById(id));

                        if ($dialog.length) {
                            privateMethods.closeDialog($dialog, value);
                        } else {
                            publicMethods.closeAll(value);
                        }

                        return publicMethods;
                    },

                    closeAll: function (value) {
                        var $all = document.querySelectorAll('.ngdialog');

                        angular.forEach($all, function (dialog) {
                            privateMethods.closeDialog($el(dialog), value);
                        });
                    },

                    getDefaults: function () {
                        return defaults;
                    }
                };

                return publicMethods;
            }];
    });

    m.directive('ngDialog', ['ngDialog', function (ngDialog) {
        return {
            restrict: 'A',
            scope : {
                ngDialogScope : '='
            },
            link: function (scope, elem, attrs) {
                elem.on('click', function (e) {
                    e.preventDefault();

                    var ngDialogScope = angular.isDefined(scope.ngDialogScope) ? scope.ngDialogScope : 'noScope';
                    angular.isDefined(attrs.ngDialogClosePrevious) && ngDialog.close(attrs.ngDialogClosePrevious);

                    var defaults = ngDialog.getDefaults();

                    ngDialog.open({
                        template: attrs.ngDialog,
                        className: attrs.ngDialogClass || defaults.className,
                        controller: attrs.ngDialogController,
                        scope: ngDialogScope,
                        data: attrs.ngDialogData,
                        showClose: attrs.ngDialogShowClose === 'false' ? false : (attrs.ngDialogShowClose === 'true' ? true : defaults.showClose),
                        closeByDocument: attrs.ngDialogCloseByDocument === 'false' ? false : (attrs.ngDialogCloseByDocument === 'true' ? true : defaults.closeByDocument),
                        closeByEscape: attrs.ngDialogCloseByEscape === 'false' ? false : (attrs.ngDialogCloseByEscape === 'true' ? true : defaults.closeByEscape),
                        preCloseCallback: attrs.ngDialogPreCloseCallback || defaults.preCloseCallback
                    });
                });
            }
        };
    }]);
}));


/**
 * ng-context-menu - v1.0.1 - An AngularJS directive to display a context menu
 * when a right-click event is triggered
 *
 * @author Ian Kennington Walter (http://ianvonwalter.com)
 */
angular
    .module('ng-context-menu', [])
    .factory('ContextMenuService', function() {
        return {
            element: null,
            menuElement: null
        };
    })
    .directive('contextMenu', [
        '$document',
        'ContextMenuService',
        '$compile',
        function($document, ContextMenuService) {
            return {
                restrict: 'A',
                scope: {
                    'callback': '&contextMenu',
                    'disabled': '&contextMenuDisabled',
                    'closeCallback': '&contextMenuClose'
                },
                link: function($scope, $element, $attrs) {
                    var opened = false;

                    function open(event, menuElement) {
                        menuElement.addClass('open');

                        var doc = $document[0].documentElement;
                        var docLeft = (window.pageXOffset || doc.scrollLeft) -
                                (doc.clientLeft || 0),
                            docTop = (window.pageYOffset || doc.scrollTop) -
                                (doc.clientTop || 0),
                            elementWidth = menuElement[0].scrollWidth,
                            elementHeight = menuElement[0].scrollHeight;
                        var docWidth = doc.clientWidth + docLeft,
                            docHeight = doc.clientHeight + docTop,
                            totalWidth = elementWidth + event.pageX,
                            totalHeight = elementHeight + event.pageY,
                            left = Math.max(event.pageX - docLeft, 0),
                            top = Math.max(event.pageY - docTop, 0);

                        if (totalWidth > docWidth) {
                            left = left - (totalWidth - docWidth);
                        }

                        if (totalHeight > docHeight) {
                            top = top - (totalHeight - docHeight);
                        }

                        menuElement.css('margin-top', top + 'px');
                        menuElement.css('margin-left', left + 'px');
                        opened = true;
                    }

                    function close(menuElement) {
                        menuElement.removeClass('open');

                        if (opened) {
                            $scope.closeCallback();
                        }

                        opened = false;
                    }

                    $element.on('contextmenu', function(event) {

                        if (!$scope.disabled() && event.button == 2) {
                            if (ContextMenuService.menuElement !== null) {
                                close(ContextMenuService.menuElement);
                            }

                            ContextMenuService.menuElement = angular.element(
                                document.getElementById($attrs.target)
                            );

                            ContextMenuService.element = event.target;

                            event.preventDefault();
                            event.stopPropagation();
                            $scope.$apply(function() {
                                $scope.callback({ $event: event });
                            });
                            $scope.$apply(function() {
                                open(event, ContextMenuService.menuElement);
                            });

                        }

                    });

                    function handleKeyUpEvent(event) {
                        //console.log('keyup');
                        if (!$scope.disabled() && opened && event.keyCode === 27) {
                            $scope.$apply(function() {
                                close(ContextMenuService.menuElement);
                            });
                        }
                    }

                    function handleClickEvent(event) {
                        if (!$scope.disabled() &&
                            opened &&
                            (event.button !== 2 ||
                            event.target !== ContextMenuService.element)) {
                            $scope.$apply(function() {
                                close(ContextMenuService.menuElement);
                            });
                        }
                    }

                    $document.bind('keyup', handleKeyUpEvent);
                    // Firefox treats a right-click as a click and a contextmenu event
                    // while other browsers just treat it as a contextmenu event
                    $document.bind('click', handleClickEvent);
                    $document.bind('contextmenu', handleClickEvent);

                    $scope.$on('$destroy', function() {
                        //console.log('destroy');
                        $document.unbind('keyup', handleKeyUpEvent);
                        $document.unbind('click', handleClickEvent);
                        $document.unbind('contextmenu', handleClickEvent);
                    });
                }
            };
        }
    ]);


/**
 * @license Angulartics v0.17.0
 * (c) 2013 Luis Farzati http://luisfarzati.github.io/angulartics
 * License: MIT
 */
!function(a){"use strict";var b=window.angulartics||(window.angulartics={});b.waitForVendorCount=0,b.waitForVendorApi=function(a,c,d,e,f){f||b.waitForVendorCount++,e||(e=d,d=void 0),!Object.prototype.hasOwnProperty.call(window,a)||void 0!==d&&void 0===window[a][d]?setTimeout(function(){b.waitForVendorApi(a,c,d,e,!0)},c):(b.waitForVendorCount--,e(window[a]))},a.module("angulartics",[]).provider("$analytics",function(){var c={pageTracking:{autoTrackFirstPage:!0,autoTrackVirtualPages:!0,trackRelativePath:!1,autoBasePath:!1,basePath:""},eventTracking:{},bufferFlushDelay:1e3,developerMode:!1},d=["pageTrack","eventTrack","setAlias","setUsername","setAlias","setUserProperties","setUserPropertiesOnce","setSuperProperties","setSuperPropertiesOnce"],e={},f={},g=function(a){return function(){b.waitForVendorCount&&(e[a]||(e[a]=[]),e[a].push(arguments))}},h=function(b,c){return f[b]||(f[b]=[]),f[b].push(c),function(){var c=arguments;a.forEach(f[b],function(a){a.apply(this,c)},this)}},i={settings:c},j=function(a,b){b?setTimeout(a,b):a()},k={$get:function(){return i},api:i,settings:c,virtualPageviews:function(a){this.settings.pageTracking.autoTrackVirtualPages=a},firstPageview:function(a){this.settings.pageTracking.autoTrackFirstPage=a},withBase:function(b){this.settings.pageTracking.basePath=b?a.element("base").attr("href").slice(0,-1):""},withAutoBase:function(a){this.settings.pageTracking.autoBasePath=a},developerMode:function(a){this.settings.developerMode=a}},l=function(b,d){i[b]=h(b,d);var f=c[b],g=f?f.bufferFlushDelay:null,k=null!==g?g:c.bufferFlushDelay;a.forEach(e[b],function(a,b){j(function(){d.apply(this,a)},b*k)})},m=function(a){return a.replace(/^./,function(a){return a.toUpperCase()})},n=function(a){var b="register"+m(a);k[b]=function(b){l(a,b)},i[a]=h(a,g(a))};return a.forEach(d,n),k}).run(["$rootScope","$window","$analytics","$injector",function(b,c,d,e){d.settings.pageTracking.autoTrackFirstPage&&e.invoke(["$location",function(a){var b=!0;if(e.has("$route")){var f=e.get("$route");for(var g in f.routes){b=!1;break}}else if(e.has("$state")){var h=e.get("$state");for(var i in h.get()){b=!1;break}}if(b)if(d.settings.pageTracking.autoBasePath&&(d.settings.pageTracking.basePath=c.location.pathname),d.settings.trackRelativePath){var j=d.settings.pageTracking.basePath+a.url();d.pageTrack(j,a)}else d.pageTrack(a.absUrl(),a)}]),d.settings.pageTracking.autoTrackVirtualPages&&e.invoke(["$location",function(a){d.settings.pageTracking.autoBasePath&&(d.settings.pageTracking.basePath=c.location.pathname+"#"),e.has("$route")&&b.$on("$routeChangeSuccess",function(b,c){if(!c||!(c.$$route||c).redirectTo){var e=d.settings.pageTracking.basePath+a.url();d.pageTrack(e,a)}}),e.has("$state")&&b.$on("$stateChangeSuccess",function(){var b=d.settings.pageTracking.basePath+a.url();d.pageTrack(b,a)})}]),d.settings.developerMode&&a.forEach(d,function(a,b){"function"==typeof a&&(d[b]=function(){})})}]).directive("analyticsOn",["$analytics",function(b){function c(a){return["a:","button:","button:button","button:submit","input:button","input:submit"].indexOf(a.tagName.toLowerCase()+":"+(a.type||""))>=0}function d(a){return c(a)?"click":"click"}function e(a){return c(a)?a.innerText||a.value:a.id||a.name||a.tagName}function f(a){return"analytics"===a.substr(0,9)&&-1===["On","Event","If","Properties","EventType"].indexOf(a.substr(9))}function g(a){var b=a.slice(9);return"undefined"!=typeof b&&null!==b&&b.length>0?b.substring(0,1).toLowerCase()+b.substring(1):b}return{restrict:"A",link:function(c,h,i){var j=i.analyticsOn||d(h[0]),k={};a.forEach(i.$attr,function(a,b){f(b)&&(k[g(b)]=i[b],i.$observe(b,function(a){k[g(b)]=a}))}),a.element(h[0]).bind(j,function(d){var f=i.analyticsEvent||e(h[0]);k.eventType=d.type,(!i.analyticsIf||c.$eval(i.analyticsIf))&&(i.analyticsProperties&&a.extend(k,c.$eval(i.analyticsProperties)),b.eventTrack(f,k))})}}}])}(angular);

/**
 * @license Angulartics v0.17.0
 * (c) 2013 Luis Farzati http://luisfarzati.github.io/angulartics
 * Universal Analytics update contributed by http://github.com/willmcclellan
 * License: MIT
 */
!function(a){"use strict";a.module("angulartics.google.analytics",["angulartics"]).config(["$analyticsProvider",function(a){a.settings.trackRelativePath=!0,a.registerPageTrack(function(a){window._gaq&&_gaq.push(["_trackPageview",a]),window.ga&&ga("send","pageview",a)}),a.registerEventTrack(function(a,b){if(b&&b.category){if(b.value){var c=parseInt(b.value,10);b.value=isNaN(c)?0:c}if(window._gaq)_gaq.push(["_trackEvent",b.category,a,b.label,b.value,b.noninteraction]);else if(window.ga){for(var d={eventCategory:b.category||null,eventAction:a||null,eventLabel:b.label||null,eventValue:b.value||null,nonInteraction:b.noninteraction||null},e=1;20>=e;e++)b["dimension"+e.toString()]&&(d["dimension"+e.toString()]=b["dimension"+e.toString()]),b["metric"+e.toString()]&&(d["metric"+e.toString()]=b["metric"+e.toString()]);ga("send","event",d)}}})}])}(angular);

!function(window, document, undefined) {
    var getModule = function(angular) {
        return angular.module('seo', [])
            .run([
                '$rootScope',
                function($rootScope) {
                    $rootScope.htmlReady = function() {
                        $rootScope.$evalAsync(function() { // fire after $digest
                            setTimeout(function() { // fire after DOM rendering
                                if (typeof window.callPhantom == 'function') {
                                    window.callPhantom();
                                }
                            }, 0);
                        });
                    };
                }
            ]);
    };
    if (typeof define == 'function' && define.amd)
        define(['angular'], getModule);
    else
        getModule(angular);
}(window, document);


/*
 jQuery UI Sortable plugin wrapper

 @param [ui-sortable] {object} Options to pass to $.fn.sortable() merged onto ui.config
 */
angular.module('ui.sortable', [])
    .value('uiSortableConfig',{})
    .directive('uiSortable', [
        'uiSortableConfig', '$timeout', '$log',
        function(uiSortableConfig, $timeout, $log) {
            return {
                require: '?ngModel',
                scope: {
                    ngModel: '=',
                    uiSortable: '='
                },
                link: function(scope, element, attrs, ngModel) {
                    var savedNodes;

                    function combineCallbacks(first,second){
                        if(second && (typeof second === 'function')) {
                            return function() {
                                first.apply(this, arguments);
                                second.apply(this, arguments);
                            };
                        }
                        return first;
                    }

                    function getSortableWidgetInstance(element) {
                        // this is a fix to support jquery-ui prior to v1.11.x
                        // otherwise we should be using `element.sortable('instance')`
                        var data = element.data('ui-sortable');
                        if (data && typeof data === 'object' && data.widgetFullName === 'ui-sortable') {
                            return data;
                        }
                        return null;
                    }

                    function hasSortingHelper (element, ui) {
                        var helperOption = element.sortable('option','helper');
                        return helperOption === 'clone' || (typeof helperOption === 'function' && ui.item.sortable.isCustomHelperUsed());
                    }

                    // thanks jquery-ui
                    function isFloating (item) {
                        return (/left|right/).globalTop(item.css('float')) || (/inline|table-cell/).globalTop(item.css('display'));
                    }

                    function getElementScope(elementScopes, element) {
                        var result = null;
                        for (var i = 0; i < elementScopes.length; i++) {
                            var x = elementScopes[i];
                            if (x.element[0] === element[0]) {
                                result = x.scope;
                                break;
                            }
                        }
                        return result;
                    }

                    function afterStop(e, ui) {
                        ui.item.sortable._destroy();
                    }

                    var opts = {};

                    // directive specific options
                    var directiveOpts = {
                        'ui-floating': undefined
                    };

                    var callbacks = {
                        receive: null,
                        remove:null,
                        start:null,
                        stop:null,
                        update:null
                    };

                    var wrappers = {
                        helper: null
                    };

                    angular.extend(opts, directiveOpts, uiSortableConfig, scope.uiSortable);

                    if (!angular.element.fn || !angular.element.fn.jquery) {
                        $log.error('ui.sortable: jQuery should be included before AngularJS!');
                        return;
                    }

                    if (ngModel) {

                        // When we add or remove elements, we need the sortable to 'refresh'
                        // so it can find the new/removed elements.
                        scope.$watch('ngModel.length', function() {
                            // Timeout to let ng-repeat modify the DOM
                            $timeout(function() {
                                // ensure that the jquery-ui-sortable widget instance
                                // is still bound to the directive's element
                                if (!!getSortableWidgetInstance(element)) {
                                    element.sortable('refresh');
                                }
                            }, 0, false);
                        });

                        callbacks.start = function(e, ui) {
                            if (opts['ui-floating'] === 'auto') {
                                // since the drag has started, the element will be
                                // absolutely positioned, so we check its siblings
                                var siblings = ui.item.siblings();
                                var sortableWidgetInstance = getSortableWidgetInstance(angular.element(e.target));
                                sortableWidgetInstance.floating = isFloating(siblings);
                            }

                            // Save the starting position of dragged item
                            ui.item.sortable = {
                                model: ngModel.$modelValue[ui.item.index()],
                                index: ui.item.index(),
                                source: ui.item.parent(),
                                sourceModel: ngModel.$modelValue,
                                cancel: function () {
                                    ui.item.sortable._isCanceled = true;
                                },
                                isCanceled: function () {
                                    return ui.item.sortable._isCanceled;
                                },
                                isCustomHelperUsed: function () {
                                    return !!ui.item.sortable._isCustomHelperUsed;
                                },
                                _isCanceled: false,
                                _isCustomHelperUsed: ui.item.sortable._isCustomHelperUsed,
                                _destroy: function () {
                                    angular.forEach(ui.item.sortable, function(value, key) {
                                        ui.item.sortable[key] = undefined;
                                    });
                                }
                            };
                        };

                        callbacks.activate = function(e, ui) {
                            // We need to make a copy of the current element's contents so
                            // we can restore it after sortable has messed it up.
                            // This is inside activate (instead of start) in order to save
                            // both lists when dragging between connected lists.
                            savedNodes = element.contents();

                            // If this list has a placeholder (the connected lists won't),
                            // don't inlcude it in saved nodes.
                            var placeholder = element.sortable('option','placeholder');

                            // placeholder.element will be a function if the placeholder, has
                            // been created (placeholder will be an object).  If it hasn't
                            // been created, either placeholder will be false if no
                            // placeholder class was given or placeholder.element will be
                            // undefined if a class was given (placeholder will be a string)
                            if (placeholder && placeholder.element && typeof placeholder.element === 'function') {
                                var phElement = placeholder.element();
                                // workaround for jquery ui 1.9.x,
                                // not returning jquery collection
                                phElement = angular.element(phElement);

                                // exact match with the placeholder's class attribute to handle
                                // the case that multiple connected sortables exist and
                                // the placehoilder option equals the class of sortable items
                                var excludes = element.find('[class="' + phElement.attr('class') + '"]:not([ng-repeat], [data-ng-repeat])');

                                savedNodes = savedNodes.not(excludes);
                            }

                            // save the directive's scope so that it is accessible from ui.item.sortable
                            var connectedSortables = ui.item.sortable._connectedSortables || [];

                            connectedSortables.push({
                                element: element,
                                scope: scope
                            });

                            ui.item.sortable._connectedSortables = connectedSortables;
                        };

                        callbacks.update = function(e, ui) {
                            // Save current drop position but only if this is not a second
                            // update that happens when moving between lists because then
                            // the value will be overwritten with the old value
                            if(!ui.item.sortable.received) {
                                ui.item.sortable.dropindex = ui.item.index();
                                var droptarget = ui.item.parent();
                                ui.item.sortable.droptarget = droptarget;

                                var droptargetScope = getElementScope(ui.item.sortable._connectedSortables, droptarget);
                                ui.item.sortable.droptargetModel = droptargetScope.ngModel;

                                // Cancel the sort (let ng-repeat do the sort for us)
                                // Don't cancel if this is the received list because it has
                                // already been canceled in the other list, and trying to cancel
                                // here will mess up the DOM.
                                element.sortable('cancel');
                            }

                            // Put the nodes back exactly the way they started (this is very
                            // important because ng-repeat uses comment elements to delineate
                            // the start and stop of repeat sections and sortable doesn't
                            // respect their order (even if we cancel, the order of the
                            // comments are still messed up).
                            if (hasSortingHelper(element, ui) && !ui.item.sortable.received &&
                                element.sortable( 'option', 'appendTo' ) === 'parent') {
                                // restore all the savedNodes except .ui-sortable-helper element
                                // (which is placed last). That way it will be garbage collected.
                                savedNodes = savedNodes.not(savedNodes.last());
                            }
                            savedNodes.appendTo(element);

                            // If this is the target connected list then
                            // it's safe to clear the restored nodes since:
                            // update is currently running and
                            // stop is not called for the target list.
                            if(ui.item.sortable.received) {
                                savedNodes = null;
                            }

                            // If received is true (an item was dropped in from another list)
                            // then we add the new item to this list otherwise wait until the
                            // stop event where we will know if it was a sort or item was
                            // moved here from another list
                            if(ui.item.sortable.received && !ui.item.sortable.isCanceled()) {
                                scope.$apply(function () {
                                    ngModel.$modelValue.splice(ui.item.sortable.dropindex, 0,
                                        ui.item.sortable.moved);
                                });
                            }
                        };

                        callbacks.stop = function(e, ui) {
                            // If the received flag hasn't be set on the item, this is a
                            // normal sort, if dropindex is set, the item was moved, so move
                            // the items in the list.
                            if(!ui.item.sortable.received &&
                                ('dropindex' in ui.item.sortable) &&
                                !ui.item.sortable.isCanceled()) {

                                scope.$apply(function () {
                                    ngModel.$modelValue.splice(
                                        ui.item.sortable.dropindex, 0,
                                        ngModel.$modelValue.splice(ui.item.sortable.index, 1)[0]);
                                });
                            } else {
                                // if the item was not moved, then restore the elements
                                // so that the ngRepeat's comment are correct.
                                if ((!('dropindex' in ui.item.sortable) || ui.item.sortable.isCanceled()) &&
                                    !hasSortingHelper(element, ui)) {
                                    savedNodes.appendTo(element);
                                }
                            }

                            // It's now safe to clear the savedNodes
                            // since stop is the last callback.
                            savedNodes = null;
                        };

                        callbacks.receive = function(e, ui) {
                            // An item was dropped here from another list, set a flag on the
                            // item.
                            ui.item.sortable.received = true;
                        };

                        callbacks.remove = function(e, ui) {
                            // Workaround for a problem observed in nested connected lists.
                            // There should be an 'update' event before 'remove' when moving
                            // elements. If the event did not fire, cancel sorting.
                            if (!('dropindex' in ui.item.sortable)) {
                                element.sortable('cancel');
                                ui.item.sortable.cancel();
                            }

                            // Remove the item from this list's model and copy data into item,
                            // so the next list can retrive it
                            if (!ui.item.sortable.isCanceled()) {
                                scope.$apply(function () {
                                    ui.item.sortable.moved = ngModel.$modelValue.splice(
                                        ui.item.sortable.index, 1)[0];
                                });
                            }
                        };

                        wrappers.helper = function (inner) {
                            if (inner && typeof inner === 'function') {
                                return function (e, item) {
                                    var innerResult = inner.apply(this, arguments);
                                    item.sortable._isCustomHelperUsed = item !== innerResult;
                                    return innerResult;
                                };
                            }
                            return inner;
                        };

                        scope.$watch('uiSortable', function(newVal /*, oldVal*/) {
                            // ensure that the jquery-ui-sortable widget instance
                            // is still bound to the directive's element
                            var sortableWidgetInstance = getSortableWidgetInstance(element);
                            if (!!sortableWidgetInstance) {
                                angular.forEach(newVal, function(value, key) {
                                    // if it's a custom option of the directive,
                                    // handle it approprietly
                                    if (key in directiveOpts) {
                                        if (key === 'ui-floating' && (value === false || value === true)) {
                                            sortableWidgetInstance.floating = value;
                                        }

                                        opts[key] = value;
                                        return;
                                    }

                                    if (callbacks[key]) {
                                        if( key === 'stop' ){
                                            // call apply after stop
                                            value = combineCallbacks(
                                                value, function() { scope.$apply(); });

                                            value = combineCallbacks(value, afterStop);
                                        }
                                        // wrap the callback
                                        value = combineCallbacks(callbacks[key], value);
                                    } else if (wrappers[key]) {
                                        value = wrappers[key](value);
                                    }

                                    opts[key] = value;
                                    element.sortable('option', key, value);
                                });
                            }
                        }, true);

                        angular.forEach(callbacks, function(value, key) {
                            opts[key] = combineCallbacks(value, opts[key]);
                            if( key === 'stop' ){
                                opts[key] = combineCallbacks(opts[key], afterStop);
                            }
                        });

                    } else {
                        $log.info('ui.sortable: ngModel not provided!', element);
                    }

                    // Create sortable
                    element.sortable(opts);
                }
            };
        }
    ]);

/**
 * @license AngularJS v1.3.9
 * (c) 2010-2014 Google, Inc. http://angularjs.org
 * License: MIT
 */
(function(window, angular, undefined) {'use strict';

    /**
     * @ngdoc module
     * @name ngTouch
     * @description
     *
     * # ngTouch
     *
     * The `ngTouch` module provides touch events and other helpers for touch-enabled devices.
     * The implementation is based on jQuery Mobile touch event handling
     * ([jquerymobile.com](http://jquerymobile.com/)).
     *
     *
     * See {@link ngTouch.$swipe `$swipe`} for usage.
     *
     * <div doc-module-components="ngTouch"></div>
     *
     */

// define ngTouch module
    /* global -ngTouch */
    var ngTouch = angular.module('ngTouch', []);

    /* global ngTouch: false */

    /**
     * @ngdoc service
     * @name $swipe
     *
     * @description
     * The `$swipe` service is a service that abstracts the messier details of hold-and-drag swipe
     * behavior, to make implementing swipe-related directives more convenient.
     *
     * Requires the {@link ngTouch `ngTouch`} module to be installed.
     *
     * `$swipe` is used by the `ngSwipeLeft` and `ngSwipeRight` directives in `ngTouch`, and by
     * `ngCarousel` in a separate component.
     *
     * # Usage
     * The `$swipe` service is an object with a single method: `bind`. `bind` takes an element
     * which is to be watched for swipes, and an object with four handler functions. See the
     * documentation for `bind` below.
     */

    ngTouch.factory('$swipe', [function() {
        // The total distance in any direction before we make the call on swipe vs. scroll.
        var MOVE_BUFFER_RADIUS = 10;

        var POINTER_EVENTS = {
            'mouse': {
                start: 'mousedown',
                move: 'mousemove',
                end: 'mouseup'
            },
            'touch': {
                start: 'touchstart',
                move: 'touchmove',
                end: 'touchend',
                cancel: 'touchcancel'
            }
        };

        function getCoordinates(event) {
            var touches = event.touches && event.touches.length ? event.touches : [event];
            var e = (event.changedTouches && event.changedTouches[0]) ||
                (event.originalEvent && event.originalEvent.changedTouches &&
                event.originalEvent.changedTouches[0]) ||
                touches[0].originalEvent || touches[0];

            return {
                x: e.clientX,
                y: e.clientY
            };
        }

        function getEvents(pointerTypes, eventType) {
            var res = [];
            angular.forEach(pointerTypes, function(pointerType) {
                var eventName = POINTER_EVENTS[pointerType][eventType];
                if (eventName) {
                    res.push(eventName);
                }
            });
            return res.join(' ');
        }

        return {
            /**
             * @ngdoc method
             * @name $swipe#bind
             *
             * @description
             * The main method of `$swipe`. It takes an element to be watched for swipe motions, and an
             * object containing event handlers.
             * The pointer types that should be used can be specified via the optional
             * third argument, which is an array of strings `'mouse'` and `'touch'`. By default,
             * `$swipe` will listen for `mouse` and `touch` events.
             *
             * The four events are `start`, `move`, `end`, and `cancel`. `start`, `move`, and `end`
             * receive as a parameter a coordinates object of the form `{ x: 150, y: 310 }`.
             *
             * `start` is called on either `mousedown` or `touchstart`. After this event, `$swipe` is
             * watching for `touchmove` or `mousemove` events. These events are ignored until the total
             * distance moved in either dimension exceeds a small threshold.
             *
             * Once this threshold is exceeded, either the horizontal or vertical delta is greater.
             * - If the horizontal distance is greater, this is a swipe and `move` and `end` events follow.
             * - If the vertical distance is greater, this is a scroll, and we let the browser take over.
             *   A `cancel` event is sent.
             *
             * `move` is called on `mousemove` and `touchmove` after the above logic has determined that
             * a swipe is in progress.
             *
             * `end` is called when a swipe is successfully completed with a `touchend` or `mouseup`.
             *
             * `cancel` is called either on a `touchcancel` from the browser, or when we begin scrolling
             * as described above.
             *
             */
            bind: function(element, eventHandlers, pointerTypes) {
                // Absolute total movement, used to control swipe vs. scroll.
                var totalX, totalY;
                // Coordinates of the start position.
                var startCoords;
                // Last event's position.
                var lastPos;
                // Whether a swipe is active.
                var active = false;

                pointerTypes = pointerTypes || ['mouse', 'touch'];
                element.on(getEvents(pointerTypes, 'start'), function(event) {
                    startCoords = getCoordinates(event);
                    active = true;
                    totalX = 0;
                    totalY = 0;
                    lastPos = startCoords;
                    eventHandlers['start'] && eventHandlers['start'](startCoords, event);
                });
                var events = getEvents(pointerTypes, 'cancel');
                if (events) {
                    element.on(events, function(event) {
                        active = false;
                        eventHandlers['cancel'] && eventHandlers['cancel'](event);
                    });
                }

                element.on(getEvents(pointerTypes, 'move'), function(event) {
                    if (!active) return;

                    // Android will send a touchcancel if it thinks we're starting to scroll.
                    // So when the total distance (+ or - or both) exceeds 10px in either direction,
                    // we either:
                    // - On totalX > totalY, we send preventDefault() and treat this as a swipe.
                    // - On totalY > totalX, we let the browser handle it as a scroll.

                    if (!startCoords) return;
                    var coords = getCoordinates(event);

                    totalX += Math.abs(coords.x - lastPos.x);
                    totalY += Math.abs(coords.y - lastPos.y);

                    lastPos = coords;

                    if (totalX < MOVE_BUFFER_RADIUS && totalY < MOVE_BUFFER_RADIUS) {
                        return;
                    }

                    // One of totalX or totalY has exceeded the buffer, so decide on swipe vs. scroll.
                    if (totalY > totalX) {
                        // Allow native scrolling to take over.
                        active = false;
                        eventHandlers['cancel'] && eventHandlers['cancel'](event);
                        return;
                    } else {
                        // Prevent the browser from scrolling.
                        event.preventDefault();
                        eventHandlers['move'] && eventHandlers['move'](coords, event);
                    }
                });

                element.on(getEvents(pointerTypes, 'end'), function(event) {
                    if (!active) return;
                    active = false;
                    eventHandlers['end'] && eventHandlers['end'](getCoordinates(event), event);
                });
            }
        };
    }]);

    /* global ngTouch: false */

    /**
     * @ngdoc directive
     * @name ngClick
     *
     * @description
     * A more powerful replacement for the default ngClick designed to be used on touchscreen
     * devices. Most mobile browsers wait about 300ms after a tap-and-release before sending
     * the click event. This version handles them immediately, and then prevents the
     * following click event from propagating.
     *
     * Requires the {@link ngTouch `ngTouch`} module to be installed.
     *
     * This directive can fall back to using an ordinary click event, and so works on desktop
     * browsers as well as mobile.
     *
     * This directive also sets the CSS class `ng-click-active` while the element is being held
     * down (by a mouse click or touch) so you can restyle the depressed element if you wish.
     *
     * @element ANY
     * @param {expression} ngClick {@link guide/expression Expression} to evaluate
     * upon tap. (Event object is available as `$event`)
     *
     * @example
     <example module="ngClickExample" deps="angular-touch.js">
     <file name="index.html">
     <button ng-click="count = count + 1" ng-init="count=0">
     Increment
     </button>
     count: {{ count }}
     </file>
     <file name="script.js">
     angular.module('ngClickExample', ['ngTouch']);
     </file>
     </example>
     */

    ngTouch.config(['$provide', function($provide) {
        $provide.decorator('ngClickDirective', ['$delegate', function($delegate) {
            // drop the default ngClick directive
            $delegate.shift();
            return $delegate;
        }]);
    }]);

    ngTouch.directive('ngClick', ['$parse', '$timeout', '$rootElement',
        function($parse, $timeout, $rootElement) {
            var TAP_DURATION = 750; // Shorter than 750ms is a tap, longer is a taphold or drag.
            var MOVE_TOLERANCE = 12; // 12px seems to work in most mobile browsers.
            var PREVENT_DURATION = 2500; // 2.5 seconds maximum from preventGhostClick call to click
            var CLICKBUSTER_THRESHOLD = 25; // 25 pixels in any dimension is the limit for busting clicks.

            var ACTIVE_CLASS_NAME = 'ng-click-active';
            var lastPreventedTime;
            var touchCoordinates;
            var lastLabelClickCoordinates;


            // TAP EVENTS AND GHOST CLICKS
            //
            // Why tap events?
            // Mobile browsers detect a tap, then wait a moment (usually ~300ms) to see if you're
            // double-tapping, and then fire a click event.
            //
            // This delay sucks and makes mobile apps feel unresponsive.
            // So we detect touchstart, touchmove, touchcancel and touchend ourselves and determine when
            // the user has tapped on something.
            //
            // What happens when the browser then generates a click event?
            // The browser, of course, also detects the tap and fires a click after a delay. This results in
            // tapping/clicking twice. We do "clickbusting" to prevent it.
            //
            // How does it work?
            // We attach global touchstart and click handlers, that run during the capture (early) phase.
            // So the sequence for a tap is:
            // - global touchstart: Sets an "allowable region" at the point touched.
            // - element's touchstart: Starts a touch
            // (- touchmove or touchcancel ends the touch, no click follows)
            // - element's touchend: Determines if the tap is valid (didn't move too far away, didn't hold
            //   too long) and fires the user's tap handler. The touchend also calls preventGhostClick().
            // - preventGhostClick() removes the allowable region the global touchstart created.
            // - The browser generates a click event.
            // - The global click handler catches the click, and checks whether it was in an allowable region.
            //     - If preventGhostClick was called, the region will have been removed, the click is busted.
            //     - If the region is still there, the click proceeds normally. Therefore clicks on links and
            //       other elements without ngTap on them work normally.
            //
            // This is an ugly, terrible hack!
            // Yeah, tell me about it. The alternatives are using the slow click events, or making our users
            // deal with the ghost clicks, so I consider this the least of evils. Fortunately Angular
            // encapsulates this ugly logic away from the user.
            //
            // Why not just put click handlers on the element?
            // We do that too, just to be sure. If the tap event caused the DOM to change,
            // it is possible another element is now in that position. To take account for these possibly
            // distinct elements, the handlers are global and care only about coordinates.

            // Checks if the coordinates are close enough to be within the region.
            function hit(x1, y1, x2, y2) {
                return Math.abs(x1 - x2) < CLICKBUSTER_THRESHOLD && Math.abs(y1 - y2) < CLICKBUSTER_THRESHOLD;
            }

            // Checks a list of allowable regions against a click location.
            // Returns true if the click should be allowed.
            // Splices out the allowable region from the list after it has been used.
            function checkAllowableRegions(touchCoordinates, x, y) {
                for (var i = 0; i < touchCoordinates.length; i += 2) {
                    if (hit(touchCoordinates[i], touchCoordinates[i + 1], x, y)) {
                        touchCoordinates.splice(i, i + 2);
                        return true; // allowable region
                    }
                }
                return false; // No allowable region; bust it.
            }

            // Global click handler that prevents the click if it's in a bustable zone and preventGhostClick
            // was called recently.
            function onClick(event) {
                if (Date.now() - lastPreventedTime > PREVENT_DURATION) {
                    return; // Too old.
                }

                var touches = event.touches && event.touches.length ? event.touches : [event];
                var x = touches[0].clientX;
                var y = touches[0].clientY;
                // Work around desktop Webkit quirk where clicking a label will fire two clicks (on the label
                // and on the input element). Depending on the exact browser, this second click we don't want
                // to bust has either (0,0), negative coordinates, or coordinates equal to triggering label
                // click event
                if (x < 1 && y < 1) {
                    return; // offscreen
                }
                if (lastLabelClickCoordinates &&
                    lastLabelClickCoordinates[0] === x && lastLabelClickCoordinates[1] === y) {
                    return; // input click triggered by label click
                }
                // reset label click coordinates on first subsequent click
                if (lastLabelClickCoordinates) {
                    lastLabelClickCoordinates = null;
                }
                // remember label click coordinates to prevent click busting of trigger click event on input
                if (event.target.tagName.toLowerCase() === 'label') {
                    lastLabelClickCoordinates = [x, y];
                }

                // Look for an allowable region containing this click.
                // If we find one, that means it was created by touchstart and not removed by
                // preventGhostClick, so we don't bust it.
                if (checkAllowableRegions(touchCoordinates, x, y)) {
                    return;
                }

                // If we didn't find an allowable region, bust the click.
                event.stopPropagation();
                event.preventDefault();

                // Blur focused form elements
                event.target && event.target.blur();
            }


            // Global touchstart handler that creates an allowable region for a click event.
            // This allowable region can be removed by preventGhostClick if we want to bust it.
            function onTouchStart(event) {
                var touches = event.touches && event.touches.length ? event.touches : [event];
                var x = touches[0].clientX;
                var y = touches[0].clientY;
                touchCoordinates.push(x, y);

                $timeout(function() {
                    // Remove the allowable region.
                    for (var i = 0; i < touchCoordinates.length; i += 2) {
                        if (touchCoordinates[i] == x && touchCoordinates[i + 1] == y) {
                            touchCoordinates.splice(i, i + 2);
                            return;
                        }
                    }
                }, PREVENT_DURATION, false);
            }

            // On the first call, attaches some event handlers. Then whenever it gets called, it creates a
            // zone around the touchstart where clicks will get busted.
            function preventGhostClick(x, y) {
                if (!touchCoordinates) {
                    $rootElement[0].addEventListener('click', onClick, true);
                    $rootElement[0].addEventListener('touchstart', onTouchStart, true);
                    touchCoordinates = [];
                }

                lastPreventedTime = Date.now();

                checkAllowableRegions(touchCoordinates, x, y);
            }

            // Actual linking function.
            return function(scope, element, attr) {
                var clickHandler = $parse(attr.ngClick),
                    tapping = false,
                    tapElement,  // Used to blur the element after a tap.
                    startTime,   // Used to check if the tap was held too long.
                    touchStartX,
                    touchStartY;

                function resetState() {
                    tapping = false;
                    element.removeClass(ACTIVE_CLASS_NAME);
                }

                element.on('touchstart', function(event) {
                    tapping = true;
                    tapElement = event.target ? event.target : event.srcElement; // IE uses srcElement.
                    // Hack for Safari, which can target text nodes instead of containers.
                    if (tapElement.nodeType == 3) {
                        tapElement = tapElement.parentNode;
                    }

                    element.addClass(ACTIVE_CLASS_NAME);

                    startTime = Date.now();

                    var touches = event.touches && event.touches.length ? event.touches : [event];
                    var e = touches[0].originalEvent || touches[0];
                    touchStartX = e.clientX;
                    touchStartY = e.clientY;
                });

                element.on('touchmove', function(event) {
                    resetState();
                });

                element.on('touchcancel', function(event) {
                    resetState();
                });

                element.on('touchend', function(event) {
                    var diff = Date.now() - startTime;

                    var touches = (event.changedTouches && event.changedTouches.length) ? event.changedTouches :
                        ((event.touches && event.touches.length) ? event.touches : [event]);
                    var e = touches[0].originalEvent || touches[0];
                    var x = e.clientX;
                    var y = e.clientY;
                    var dist = Math.sqrt(Math.pow(x - touchStartX, 2) + Math.pow(y - touchStartY, 2));

                    if (tapping && diff < TAP_DURATION && dist < MOVE_TOLERANCE) {
                        // Call preventGhostClick so the clickbuster will catch the corresponding click.
                        preventGhostClick(x, y);

                        // Blur the focused element (the button, probably) before firing the callback.
                        // This doesn't work perfectly on Android Chrome, but seems to work elsewhere.
                        // I couldn't get anything to work reliably on Android Chrome.
                        if (tapElement) {
                            tapElement.blur();
                        }

                        if (!angular.isDefined(attr.disabled) || attr.disabled === false) {
                            element.triggerHandler('click', [event]);
                        }
                    }

                    resetState();
                });

                // Hack for iOS Safari's benefit. It goes searching for onclick handlers and is liable to click
                // something else nearby.
                element.onclick = function(event) { };

                // Actual click handler.
                // There are three different kinds of clicks, only two of which reach this point.
                // - On desktop browsers without touch events, their clicks will always come here.
                // - On mobile browsers, the simulated "fast" click will call this.
                // - But the browser's follow-up slow click will be "busted" before it reaches this handler.
                // Therefore it's safe to use this directive on both mobile and desktop.
                element.on('click', function(event, touchend) {
                    scope.$apply(function() {
                        clickHandler(scope, {$event: (touchend || event)});
                    });
                });

                element.on('mousedown', function(event) {
                    element.addClass(ACTIVE_CLASS_NAME);
                });

                element.on('mousemove mouseup', function(event) {
                    element.removeClass(ACTIVE_CLASS_NAME);
                });

            };
        }]);

    /* global ngTouch: false */

    /**
     * @ngdoc directive
     * @name ngSwipeLeft
     *
     * @description
     * Specify custom behavior when an element is swiped to the left on a touchscreen device.
     * A leftward swipe is a quick, right-to-left slide of the finger.
     * Though ngSwipeLeft is designed for touch-based devices, it will work with a mouse click and drag
     * too.
     *
     * To disable the mouse click and drag functionality, add `ng-swipe-disable-mouse` to
     * the `ng-swipe-left` or `ng-swipe-right` DOM Element.
     *
     * Requires the {@link ngTouch `ngTouch`} module to be installed.
     *
     * @element ANY
     * @param {expression} ngSwipeLeft {@link guide/expression Expression} to evaluate
     * upon left swipe. (Event object is available as `$event`)
     *
     * @example
     <example module="ngSwipeLeftExample" deps="angular-touch.js">
     <file name="index.html">
     <div ng-show="!showActions" ng-swipe-left="showActions = true">
     Some list content, like an email in the inbox
     </div>
     <div ng-show="showActions" ng-swipe-right="showActions = false">
     <button ng-click="reply()">Reply</button>
     <button ng-click="delete()">Delete</button>
     </div>
     </file>
     <file name="script.js">
     angular.module('ngSwipeLeftExample', ['ngTouch']);
     </file>
     </example>
     */

    /**
     * @ngdoc directive
     * @name ngSwipeRight
     *
     * @description
     * Specify custom behavior when an element is swiped to the right on a touchscreen device.
     * A rightward swipe is a quick, left-to-right slide of the finger.
     * Though ngSwipeRight is designed for touch-based devices, it will work with a mouse click and drag
     * too.
     *
     * Requires the {@link ngTouch `ngTouch`} module to be installed.
     *
     * @element ANY
     * @param {expression} ngSwipeRight {@link guide/expression Expression} to evaluate
     * upon right swipe. (Event object is available as `$event`)
     *
     * @example
     <example module="ngSwipeRightExample" deps="angular-touch.js">
     <file name="index.html">
     <div ng-show="!showActions" ng-swipe-left="showActions = true">
     Some list content, like an email in the inbox
     </div>
     <div ng-show="showActions" ng-swipe-right="showActions = false">
     <button ng-click="reply()">Reply</button>
     <button ng-click="delete()">Delete</button>
     </div>
     </file>
     <file name="script.js">
     angular.module('ngSwipeRightExample', ['ngTouch']);
     </file>
     </example>
     */

    function makeSwipeDirective(directiveName, direction, eventName) {
        ngTouch.directive(directiveName, ['$parse', '$swipe', function($parse, $swipe) {
            // The maximum vertical delta for a swipe should be less than 75px.
            var MAX_VERTICAL_DISTANCE = 75;
            // Vertical distance should not be more than a fraction of the horizontal distance.
            var MAX_VERTICAL_RATIO = 0.3;
            // At least a 30px lateral motion is necessary for a swipe.
            var MIN_HORIZONTAL_DISTANCE = 30;

            return function(scope, element, attr) {
                var swipeHandler = $parse(attr[directiveName]);

                var startCoords, valid;

                function validSwipe(coords) {
                    // Check that it's within the coordinates.
                    // Absolute vertical distance must be within tolerances.
                    // Horizontal distance, we take the current X - the starting X.
                    // This is negative for leftward swipes and positive for rightward swipes.
                    // After multiplying by the direction (-1 for left, +1 for right), legal swipes
                    // (ie. same direction as the directive wants) will have a positive delta and
                    // illegal ones a negative delta.
                    // Therefore this delta must be positive, and larger than the minimum.
                    if (!startCoords) return false;
                    var deltaY = Math.abs(coords.y - startCoords.y);
                    var deltaX = (coords.x - startCoords.x) * direction;
                    return valid && // Short circuit for already-invalidated swipes.
                        deltaY < MAX_VERTICAL_DISTANCE &&
                        deltaX > 0 &&
                        deltaX > MIN_HORIZONTAL_DISTANCE &&
                        deltaY / deltaX < MAX_VERTICAL_RATIO;
                }

                var pointerTypes = ['touch'];
                if (!angular.isDefined(attr['ngSwipeDisableMouse'])) {
                    pointerTypes.push('mouse');
                }
                $swipe.bind(element, {
                    'start': function(coords, event) {
                        startCoords = coords;
                        valid = true;
                    },
                    'cancel': function(event) {
                        valid = false;
                    },
                    'end': function(coords, event) {
                        if (validSwipe(coords)) {
                            scope.$apply(function() {
                                element.triggerHandler(eventName);
                                swipeHandler(scope, {$event: event});
                            });
                        }
                    }
                }, pointerTypes);
            };
        }]);
    }

// Left is negative X-coordinate, right is positive.
    makeSwipeDirective('ngSwipeLeft', -1, 'swipeleft');
    makeSwipeDirective('ngSwipeRight', 1, 'swiperight');



})(window, window.angular);

/**
 * Created by Roman on 08.12.2014.
 */

(function () {

    var md = angular.module("application", [

        "ngRoute", "ngAnimate", "ngDialog", "ngTouch",
        "angular-loading-bar", 'angulartics', 'angulartics.google.analytics',
        "httpPostFix", "infinite-scroll", "ng-context-menu", "ui.sortable", 'seo', "mor-popup",

        "Account", "Site", "Catalog", "RadioPlayer", "Search", "Profile", "Library", "AudioInfo", "mor-loader", "Dialogs",

        "mor.stream.scheduler", "mor.tools"

    ]);

    md.controller("MainController", [function () { }]);

    var settings = {
        REST_LOCATION: "http://myownradio.biz/api/v2",
        STREAMS_PER_PAGE: 50
    };

    var routes = {
        /* Home Page */
        PATH_HOME: ["/", {
            templateUrl: "/views/home.html",
            rootClass: "image"
        }],

        /* Streams List */
        PATH_STREAMS_CATALOG: ["/streams/", {
            templateUrl: "/views/streams.html",
            controller: 'ListStreamsController',
            resolve: {
                channelData: ["$route", "Resolvers", "STREAMS_PER_SCROLL",
                    function ($route, Resolvers, STREAMS_PER_SCROLL) {
                        var category = $route.current.params.category;
                        return Resolvers.getChannelList(null, category, 0, STREAMS_PER_SCROLL);
                    }
                ]
            }
        }],

        PATH_STREAMS_SEARCH: ["/search/:query", {
            templateUrl: "/views/search-results.html",
            controller: "ListSearchController",
            resolve: {
                channelData: ["$route", "Resolvers", "STREAMS_PER_SCROLL",
                    function ($route, Resolvers, STREAMS_PER_SCROLL) {
                        var category = $route.current.params.category,
                            filter = $route.current.params.query;
                        return Resolvers.getChannelList(filter, category, 0, STREAMS_PER_SCROLL);
                    }
                ]
            }
        }],

        /* Streams List */
        PATH_STREAMS_BOOKMARKS: ["/bookmarks/", {
            templateUrl: "/views/streams.html",
            controller: 'BookmarksController',
            resolve: {
                channelData: ["Resolvers", "STREAMS_PER_SCROLL",
                    function (Resolvers, STREAMS_PER_SCROLL) {
                        return Resolvers.getBookmarks(0, STREAMS_PER_SCROLL);
                    }
                ]
            }
        }],

        /* Streams List */
        PATH_STREAMS_USER: ["/user/:key", {
            templateUrl: "/views/streams.html",
            controller: 'UserStreamsController'
        }],

        /* Single Stream View */
        PATH_STREAM: ["/streams/:id", {
            templateUrl: "/views/stream.html",
            controller: "OneStreamController",
            resolve: {
                streamData: ["Streams", "$route", function (Streams, $route) {
                    return Streams.getByIdWithSimilar($route.current.params.id);
                }]
            }
        }],

        /* Streams Search List */
        PATH_USERS_CATALOG: ["/user", {
            redirectTo: "/"
        }],

        PATH_LOGIN: ["/login/", {
            templateUrl: "/views/login.html",
            controller: "LoginForm"
        }],

        PATH_RECOVER_PASSWORD1: ["/recover", {
            templateUrl: "/views/forms/recoverPassword1.html",
            controller: "PasswordResetBeginForm"
        }],

        PATH_RECOVER_PASSWORD2: ["/recover/:code", {
            templateUrl: "/views/forms/recoverPassword2.html",
            controller: "PasswordResetCompleteForm"
        }],

        PATH_SIGN_UP_BEGIN: ["/signup", {
            templateUrl: "/views/forms/signUpBegin.html",
            controller: "SignUpBeginForm"
        }],

        PATH_SIGN_UP_COMPLETE: ["/signup/:code", {
            templateUrl: "/views/forms/signUpComplete.html",
            controller: "SignUpCompleteForm"
        }],

        PATH_REG_LETTER_SENT: ["/static/registrationLetterSent", {
            templateUrl: "/views/static/registrationLetterSent.html"
        }],

        PATH_REG_COMPLETE: ["/static/registrationCompleted", {
            templateUrl: "/views/static/registrationCompleted.html"
        }],

        PATH_RECOVER_LETTER_SENT: ["/static/resetLetterSent", {
            templateUrl: "/views/static/resetLetterSent.html"
        }],

        PATH_RECOVER_COMPLETED: ["/static/resetPasswordCompleted", {
            templateUrl: "/views/static/resetPasswordCompleted.html"
        }],

        PATH_PROFILE_HOME: ["/profile/", {
            templateUrl: "/views/auth/profile.html",
            needsAuth: true
        }],

        PATH_PROFILE_EDIT: ["/profile/edit", {
            templateUrl: "/views/auth/editprofile.html",
            needsAuth: true
        }],

        PATH_PROFILE_CHANGE_PASSWORD: ["/profile/password", {
            templateUrl: "/views/auth/change-password.html",
            needsAuth: true
        }],

        PATH_PROFILE_CHANGE_PLAN: ["/profile/plan", {
            templateUrl: "/views/auth/change-plan.html",
            needsAuth: true
        }],

        PATH_PROFILE_TRACKS: ["/profile/tracks/", {
            templateUrl: "/views/auth/tracks.html",
            needsAuth: true
        }],

        PATH_UNUSED_TRACKS: ["/profile/tracks/unused", {
            templateUrl: "/views/auth/tracks.html",
            unused: true,
            needsAuth: true
        }],

        PATH_PROFILE_STREAMS: ["/profile/streams/", {
            templateUrl: "/views/auth/streams.html",
            needsAuth: true
        }],

        PATH_PROFILE_STREAM: ["/profile/streams/:id", {
            templateUrl: "/views/auth/stream.html",
            needsAuth: true
        }],

        PATH_EDIT_STREAM: ["/profile/edit-stream/:id", {
            templateUrl: "/views/auth/edit-stream.html",
            needsAuth: true
        }],

        PATH_NEW_STREAM: ["/profile/new-stream", {
            templateUrl: "/views/auth/new-stream.html",
            needsAuth: true
        }],

        PATH_CATEGORIES_LIST: ["/categories/", {
            templateUrl: "/views/categories.html"
        }]

    };

    md.constant("ROUTES", routes);
    md.constant("SETTINGS", settings);
    md.constant("SITE_TITLE", "myownradio.biz - create your own web radio station for free");

    md.config([
        '$routeProvider', '$locationProvider', 'ROUTES', 'cfpLoadingBarProvider',
        '$sceDelegateProvider', '$httpProvider',
        function ($routeProvider, $locationProvider, ROUTES, cfpLoadingBarProvider,
                  $sceDelegateProvider, $httpProvider) {

            cfpLoadingBarProvider.includeSpinner = false;
            $locationProvider.html5Mode(true).hashPrefix('!');
            $sceDelegateProvider.resourceUrlWhitelist([
                'self',
                'http://myownradio.biz:7778/**'
            ]);

            for (key in ROUTES) {
                if (ROUTES.hasOwnProperty(key)) {
                    $routeProvider.when(ROUTES[key][0], ROUTES[key][1])
                }
            }

            /* Otherwise */
            $routeProvider.otherwise({
                redirectTo: "/"
            });

            $httpProvider.defaults.cache = false;


        }]);

    md.run(["$rootScope", "$location", "$route", "$document", "SITE_TITLE", "$analytics", "Response", "$http",

        function ($rootScope, $location, $route, $document, SITE_TITLE, $analytics, Response, $http) {

        $rootScope.lib = {
            countries: [],
            categories: []
        };

        $rootScope.go = function (path) {
            $location.path(path);
        };

        $rootScope.reload = function () {
            Response($http({
                method: "GET",
                url: "/api/v2/getCollection"
            })).onSuccess(function (data) {
                $rootScope.lib = data;
            });
        };

        $rootScope.reload();

        $("a").live("click", function () {
            $analytics.eventTrack('followLink', {category: 'Application', label: this.href});
            if (this.href == $location.absUrl()) {
                $route.reload();
            }
        });

        initHelpers();

        $rootScope.$on("$routeChangeSuccess", function (event, currentRoute) {

            //$rootScope.account.init();
            $document[0].title = SITE_TITLE;
            $rootScope.rootClass = currentRoute.rootClass;
            $rootScope.url = $location.url();

        });

        $rootScope.openedDialogs = 0;

        $rootScope.$on("ngDialog.opened", function () {
            $rootScope.openedDialogs++;
            $rootScope.$apply();
        });

        $rootScope.$on("ngDialog.closed", function () {
            $rootScope.openedDialogs--;
            $rootScope.$apply();
        });


        $rootScope.meta = {
            title: SITE_TITLE,
            image: "",
            url: "",
            description: ""
        };


    }

    ]);

    md.directive("toggle", ["$document", function ($document) {
        return {
            scope: {
                toggle: "="
            },
            link: function ($scope, $element, attrs) {
                $scope.toggle = false;
                $element.on("click", function () {
                    $scope.toggle = !$scope.toggle;
                });
                var callback = function (event) {
                    if (!$element.is(event.target) && $element.find(event.target).length == 0) {
                        $scope.$applyAsync(function () {
                            $scope.toggle = false;
                        });
                    }
                };
                $document.on("click", callback);
                $scope.$on("$destroy", function () {
                    $document.unbind("click", callback);
                });
            }
        }
    }]);


    md.directive('footer', [function () {
        return {
            restrict: 'E',
            templateUrl: '/views/footer.html'
        };
    }]);

    md.directive('tagsList', [function () {
        return {
            restrict: 'A',
            template: '<ul class="taglist">' +
            '<li ng-repeat="tag in tags">' +
            '<a href="/search/%23{{tag | escape}}">{{tag}}</a>' +
            '</li>' +
            '</ul>',
            scope: {
                tags: "=tagsList"
            }
        }
    }]);

    md.directive('header', [function () {
        return {
            restrict: 'E',
            templateUrl: "/views/blocks/header.html"
        }
    }]);

})();


(function () {

    var app = angular.module("application");

    app.filter("escape", function () {
        return window.encodeURI;
    });

    app.filter("usersCatalog", ['ROUTES', function (ROUTES) {
        return function (key) {
            return ROUTES.PATH_USERS_CATALOG[0] + "/" + key;
        }
    }])

})();


(function () {
    var module = angular.module("mor-context-menu", []);
    module.directive("contextMenu", [
        function () {
            return {
                restrict: "A",
                scope: {
                    callback: "&contextMenu",
                    disabled: "&contextMenuDisabled"
                },
                link: function ($scope, $element, $attrs) {
                    $element.bind('contextmenu', function(event) {
                        if (!$scope.disabled()) {
                            event.preventDefault();
                            event.stopPropagation();
                            $scope.$apply(function() {
                                $scope.callback({ $event: event, $element: $element });
                            });
                        }
                    });
                }
            }
        }
    ]);
})();

/**
 * Created by roman on 31.12.14.
 */
(function () {

    var site = angular.module("Site", []);


    site.filter("streamsCatalog", [function () {
        return function (key) {
            return "/streams/" + key;
        }
    }]);

    site.filter("msToTime", [function () {
        return function (ms) {
            var seconds = parseInt(ms / 1000);
            var out_hours = parseInt(seconds / 3600);
            var out_minutes = parseInt(seconds / 60) % 60;
            var out_seconds = seconds % 60;
            return (out_hours ? (out_hours.toString() + ":") : "") + (out_minutes < 10 ? "0" : "")
                + out_minutes.toString() + ":" + (out_seconds < 10 ? "0" : "") + out_seconds.toString();
        }
    }]);

    site.filter("msToSmallTime", [function () {
        return function (ms) {
            var sign = (ms < 0 ? "-" : "+");
            var abs = Math.abs(ms);
            if (abs < 60000) {
                return sign + Math.floor(abs / 1000) + "s";
            } else {
                return sign + Math.floor(abs / 60000) + "m";
            }
        }
    }]);

    site.filter("humanTime", [function () {
        return function (ms) {

            var totalSeconds = parseInt(Math.abs(ms / 1000));
            var days = parseInt(totalSeconds / 86400);
            var hours = parseInt(totalSeconds / 3600) % 24;
            var minutes = parseInt(totalSeconds / 60) % 60;

            if (ms < 0) {
                return "Overused (-" + days + " days " + hours + " hours " + minutes + " minutes)";
            } else {
                return days + " days " + hours + " hours " + minutes + " minutes";
            }


        }
    }]);

    site.filter("lighten", [function () {
        return function (color, rate) {
            var ex = new RegExp("^\\#[a-f0-9]{6}$"),
                r, g, b, rr, gg, bb, result;

            if (angular.isString(color) && color.match(ex)) {
                r = parseInt(color.substr(1, 2), 16);
                g = parseInt(color.substr(3, 2), 16);
                b = parseInt(color.substr(5, 2), 16);

                rr = Math.floor(r + b / 100 * rate);
                gg = Math.floor(g + b / 100 * rate);
                bb = Math.floor(b + b / 100 * rate);

                if (rr > 255) {
                    rr = 255
                }
                if (gg > 255) {
                    gg = 255
                }
                if (bb > 255) {
                    bb = 255
                }

                result = "#" +
                    ((rr < 16) ? "0" : "") + rr.toString(16) +
                    ((gg < 16) ? "0" : "") + gg.toString(16) +
                    ((bb < 16) ? "0" : "") + bb.toString(16);

            } else {
                result = null;
            }

            return result;
        }
    }]);

    site.directive("dBackgroundImage", [function () {
        return {
            scope: {
                dBackgroundImage: "@"
            },
            link: function ($scope, $element, $attrs) {
                $scope.$watch("dBackgroundImage", function (newUrl) {
                    $element.css({opacity: 0});
                    if (angular.isDefined(newUrl) && (!window.mobileCheck())) {
                        angular.element("<img>").on('load', function () {
                            $element.css("background-image", "url(" + newUrl + ")");
                            $element.animate({opacity: 1}, 500);
                        }).attr("src", newUrl);
                    } else {
                        $element.css("background-image", "");
                    }
                });
            }
        }
    }]);

    site.directive("morBackgroundColor", [function () {
        return {
            scope: {
                morBackgroundColor: "="
            },
            link: function ($scope, $element, $attributes) {
                $scope.$watch("morBackgroundColor", function (newColor) {
                    if (angular.isDefined(newColor)) {
                        $element.css("background-color", newColor + " !important");
                    } else {
                        $element.css("background-color", "");
                    }
                });
            }
        }
    }]);

    site.directive("morColor", [function () {
        return {
            scope: {
                morColor: "="
            },
            link: function ($scope, $element, $attributes) {
                $scope.$watch("morColor", function (newColor) {
                    if (angular.isDefined(newColor)) {
                        $element.css("color", newColor + " !important");
                    } else {
                        $element.css("color", "");
                    }
                });
            }
        }
    }]);

    site.directive("activeTab", ["$location", function ($location) {
        return {
            scope: {
                activeTab: "@"
            },
            link: function ($scope, $element, $attributes) {
                var CLASS = "active";
                $element.toggleClass(CLASS, $location.url().match($scope.activeTab) !== null);
            }
        };
    }]);

    site.directive("multipleSelect", ["$parse", "$document", function ($parse, $document) {
        return {
            link: function ($scope, $element, $attr) {

                var CURRENT_CLASS = $attr["currentClass"] || "current";
                var SELECTED_CLASS = $attr["selectedClass"] || "selected";
                var DO_ON_TICK = $parse($attr["msTick"]);
                var SOURCE = $parse($attr["msSource"]);
                var DESTINATION = $parse($attr["msDestination"]);

                var selectNothing = function (event) {
                    if ($element.find(event.target).length == 0
                        && $(".select-persistent").find(event.target).length == 0
                        && $(".ngdialog").find(event.target).length == 0) {

                        $element.children().removeClass(SELECTED_CLASS).removeClass(CURRENT_CLASS);
                        updateSelection();
                    }
                };

                $scope.unSelect = function () {
                    $element.children().removeClass(SELECTED_CLASS).removeClass(CURRENT_CLASS);
                    updateSelection();
                };

                var updateSelection = function () {

                    var obj;

                    DESTINATION($scope).splice(0, DESTINATION($scope).length);

                    $element.children("." + SELECTED_CLASS).each(function () {
                        obj = SOURCE($scope)[$(this).index()];
                        DESTINATION($scope).push(obj);
                    });

                    $scope.$applyAsync(DO_ON_TICK);

                };

                $document.on("click", selectNothing);

                $scope.$on("$destroy", function () {
                    $document.unbind("click", selectNothing);
                });

                $element.live("mousedown", function (event) {

                    var ctrlPressed = event.metaKey || event.ctrlKey;
                    var shiftPressed = event.shiftKey;
                    var activeElement = $element.children().filter("." + CURRENT_CLASS);
                    var that = $(event.target).parents().filter($element.children());

                    var action = function () {

                        // Selection manipulation
                        if (ctrlPressed) {
                            $(this).toggleClass(SELECTED_CLASS);
                        } else if (shiftPressed) {
                            var fromIndex = activeElement.length ? activeElement.index() : 0;
                            var newIndex = $(this).index();
                            if (fromIndex < newIndex) {
                                $element.children().slice(fromIndex, newIndex).addClass(SELECTED_CLASS);
                            } else {
                                $element.children().slice(newIndex, fromIndex).addClass(SELECTED_CLASS);
                            }
                            $(this).addClass(SELECTED_CLASS);
                        } else {
                            $element.children().removeClass(SELECTED_CLASS);
                            $(this).addClass(SELECTED_CLASS);
                        }

                        // Change current
                        activeElement.removeClass(CURRENT_CLASS);
                        $(this).addClass(CURRENT_CLASS);

                        // Do on Tick
                        updateSelection();

                    };

                    var right = function () {
                        if (activeElement.length == 0) {
                            $(this).addClass(CURRENT_CLASS).addClass(SELECTED_CLASS);
                        }
                        updateSelection();
                    };


                    if (event.button == 0) {
                        action.call(that);
                    } else if (event.button == 2) {
                        right.call(that);
                    }

                    event.stopPropagation();
                    event.preventDefault();

                    return false;

                });

            }
        };
    }]);

    site.directive("ensureUnique", ["$http", function ($http) {
        var timer = false;
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                scope.$watch(attrs.ngModel, function (n) {

                    if (typeof timer == "number") {
                        clearTimeout(timer);
                        timer = false;
                    }

                    if (!n) {
                        return false;
                    }

                    timer = setTimeout(function () {
                        $http({
                            method: "POST",
                            url: "/api/check/" + attrs.ensureUnique,
                            data: {field: elem.val()}
                        }).success(function (res) {
                            c.$setValidity('unique', res.data.available);
                        }).error(function () {
                            c.$setValidity('unique', false);
                        });
                    }, 250);
                })
            }
        }
    }]);


    site.directive("mustExist", ["$http", function ($http) {
        var timer = false;
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                scope.$watch(attrs.ngModel, function (n) {

                    if (typeof timer == "number") {
                        clearTimeout(timer);
                        timer = false;
                    }

                    if (!n) {
                        return false;
                    }

                    timer = setTimeout(function () {
                        $http({
                            method: "POST",
                            url: "/api/exists/" + attrs.mustExist,
                            data: {field: elem.val()}
                        }).success(function (res) {
                            c.$setValidity('exists', res.data.exists);
                        }).error(function () {
                            c.$setValidity('exists', true);
                        });
                    }, 250);
                })
            }
        }
    }]);

    site.directive("isAvailable", ["$http", function ($http) {
        var timer = false;
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                scope.$watch(attrs.ngModel, function (n) {

                    if (timer !== false) {
                        clearTimeout(timer);
                        timer = false;
                    }

                    if (!n) {
                        return false;
                    }

                    timer = setTimeout(function () {
                        $http({
                            method: "POST",
                            url: "/api/check/" + attrs.isAvailable,
                            data: {field: elem.val(), context: attrs.morContext}
                        }).success(function (res) {
                            c.$setValidity('available', res.data.available);
                        }).error(function () {
                            c.$setValidity('available', false);
                        });
                    }, 250);
                })
            }
        }
    }]);

    site.directive("ngFocus", [function () {
        var FOCUS_CLASS = "ng-focused";
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function (scope, elem, attrs, c) {
                c.$focused = false;
                elem.bind("focus", function () {
                    elem.addClass(FOCUS_CLASS);
                    scope.$apply(function () {
                        c.$focused = true;
                    });
                }).bind("blur", function () {
                    elem.removeClass(FOCUS_CLASS);
                    scope.$apply(function () {
                        c.$focused = false;
                    })
                })
            }
        }
    }]);

    site.directive('ngEnter', [function () {
        return function (scope, element, attrs) {
            element.bind("keydown keypress", function (event) {
                if (event.which === 13) {
                    scope.$apply(function () {
                        scope.$eval(attrs.ngEnter);
                    });

                    event.preventDefault();
                }
            });
        };
    }]);

    site.filter("repeat", [function () {
        return function (input) {

        };
    }]);

    site.filter('bytes', [function () {
        return function (bytes, precision) {
            if (isNaN(parseFloat(bytes)) || !isFinite(bytes)) return '-';
            if (typeof precision === 'undefined') precision = 1;
            var units = ['bytes', 'kB', 'MB', 'GB', 'TB', 'PB'],
                number = Math.floor(Math.log(bytes) / Math.log(1024));
            return (bytes / Math.pow(1024, Math.floor(number))).toFixed(precision) + ' ' + units[number];
        }
    }]);

    site.filter('userDisplayName', [function () {
        return function (user) {
            return (typeof user != "undefined") ? (user.name ? user.name : user.login) : undefined;
        }
    }]);


    site.filter('regExpEscape', [function () {
        return function (str) {
            return str.toString().replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
        }
    }]);


    site.directive("compareTo", [function () {
        return {
            require: "ngModel",
            scope: {
                otherModelValue: "=compareTo"
            },
            link: function (scope, element, attributes, ngModel) {

                ngModel.$validators.compareTo = function (modelValue) {
                    return modelValue == scope.otherModelValue;
                };

                scope.$watch("otherModelValue", function () {
                    ngModel.$validate();
                });
            }
        };
    }]);

    site.directive("popup", [function () {
        return {
            link: function (scope, element, attrs) {
                var link = attrs.href;
                var size = attrs.popup.split("x");

                element.on("click", function (event) {
                    window.open(link, "", "width=".concat(size[0]).concat(", ").concat("height=").concat(size[1]));
                    event.stopPropagation();
                    event.preventDefault();
                    return false;
                });
            }
        };
    }]);

    site.directive("alignVertical", [function () {
        return {
            link: function ($scope, $element, $attr) {

            }
        }
    }]);

    site.directive("morSuggest", ["$rootScope", function ($rootScope) {
        return {
            scope: {
                morSuggest: "=",
                ngModel: "="
            },
            require: "ngModel",
            link: function ($scope, $element, $attributes) {
                var old;
                $element.on("keyup", function (event) {
                    if (event.which < 32) return;
                    if (old != event.target.value) {
                        var position = event.target.selectionStart;
                        var temp = $rootScope.lib.genres;
                        for (var i = 0, length = temp.length; i < length; i += 1) {
                            if (temp[i].genre_name.toLowerCase().indexOf(event.target.value.toLowerCase()) == 0) {
                                $scope.ngModel = temp[i].genre_name;
                                $scope.$apply();
                                event.target.selectionStart = position;
                                break;
                            }
                        }
                    }
                    old = event.target.value;
                })
            }
        }
    }]);

    site.directive("sync", [function () {
        return {
            scope: {
                source: "=",
                destination: "="
            },
            link: function ($scope) {
                $scope.$watch("source", function (value) {
                    if (angular.isArray(value) && angular.isArray($scope.destination)) {
                        copyArrayValues(value, $scope.destination);
                    } else if (angular.isObject(value) && angular.isObject($scope.destination)) {
                        copyObjectValues(value, $scope.destination);
                    } else {
                        $scope.destination = value;
                    }
                });
            }
        }
    }]);

    site.directive("followVertical", ["$document", function ($document) {
        return {
            link: function ($scope, $element, $attributes) {
                var scrollFollow = function (event) {
                    $element.css("padding-top", $document.scrollTop());
                };
                $document.on("scroll", scrollFollow);
                $scope.$on("$destroy", function () {
                    $document.unbind("scroll", scrollFollow)
                });

            }
        }
    }]);

    site.directive("morEdit", ["$timeout", "$document", function ($timeout, $document) {
        return {
            scope: {
                morEdit: "=",
                morEditSubmit: "&"
            },
            link: function ($scope, $element, $attributes) {

            }
        }
    }]);

    site.factory("Response", [function () {
        return function (promise) {
            return {
                onSuccess: function (onSuccess, onError) {
                    onSuccess = onSuccess || function () {
                    };
                    onError = onError || function () {
                    };
                    promise.then(function (res) {
                        var response = res.data;
                        if (response.code == 1) {
                            onSuccess(response.data, response.message);
                        } else {
                            onError(response.message);
                        }
                    });
                }
            }
        }
    }]);

    site.factory("$body", [function () {
        return angular.element("body");
    }]);

    site.factory("$dialog", ["ngDialog", function (ngDialog) {
        return {
            question: function (question, callback) {
                if (typeof question != "string")
                    throw new Error("Question must be a STRING");

                if (typeof callback != "function")
                    throw new Error("Callback must be a FUNCTION");

                ngDialog.openConfirm({
                    template: '\
                    <div class="dialog-wrap">\
                        <i class="big-icon icon-question"></i>\
                        <div class="dialog-body">' + question + '</div>\
                        <div class="buttons">\
                            <span class="button" ng-click="confirm(1)">Yes</span>\
                            <span class="button" ng-click="closeThisDialog()">No</span>\
                        </div>\
                    </div>',
                    plain: true,
                    showClose: false
                }).then(function () {
                    callback.call();
                });

            },
            info: function (info) {
                if (typeof info != "string")
                    throw new Error("Info must be a STRING");

                ngDialog.openConfirm({
                    template: '\
                    <div class="dialog-wrap">\
                        <div class="dialog-body">' + info + '</div>\
                        <div class="buttons">\
                            <span class="button" ng-click="closeThisDialog()">OK</span>\
                        </div>\
                    </div>',
                    plain: true,
                    showClose: false
                });
            }

        };
    }]);

    site.directive("ngChangeAction", ["$timeout", function ($timeout) {
        var timer;
        return {
            require: 'ngModel',
            link: function (scope, element, attr, ctrl) {
                ctrl.$viewChangeListeners.push(function () {
                    if (timer) $timeout.cancel(timer);
                    timer = $timeout(function () {
                        scope.$eval(attr.ngChangeAction);
                    }, attr.ngChangeDelay || 0);
                });
            }
        }
    }])

})();

(function () {
    var tools = angular.module("mor.tools", []);

    tools.directive("ngClickOutside", ["$document", "$parse", function ($document, $parse) {
            return {
                restrict: 'A',
                multiElement: true,
                link: function (scope, element, attrs) {

                    var action = function (event) {
                        if (element.find(event.target).length == 0) {
                            scope.$apply(function () {
                                var action = $parse(attrs.ngClickOutside);
                                action(scope);
                            });
                        }
                    };

                    $document.on("click", action);

                    scope.$on("$destroy", function () {
                        $document.unbind("click", action);
                    });

                }
            };
        }
    ]);


    tools.directive("ngVisible", [function () {
        return {
            restrict: "A",
            scope: {
                ngVisible: "="
            },
            link: function (scope, element, attrs) {
                scope.$watch("ngVisible", function (value) {
                    element.css("visibility", value ? "visible" : "hidden");
                });
            }
        }
    }]);

    tools.factory("ResponseData", [function () {
        return function (res) {
            var response = res.data;
            return {
                onSuccess: function (onSuccess, onError) {
                    onSuccess = onSuccess || function () {
                    };
                    onError = onError || function () {
                    };
                    if (response.code == 1) {
                        onSuccess(response.data, response.message);
                    } else {
                        onError(response.message);
                    }
                }
            }
        }
    }]);


})();

(function () {
    var tools = angular.module("mor.tools");

    tools.run(["$rootScope", "Defaults", function ($rootScope, Defaults) {

        $rootScope.defaults = {
            formats: Defaults.getFormatsList(),
            format: Defaults.getDefaultFormat()
        };

        $rootScope.setDefaultFormat = function (format) {
            $rootScope.defaults.format = format;
            Defaults.setDefaultFormat(format);
            $rootScope.player.controls.reload();
        };

    }]);

    tools.factory("Defaults", [function () {
            return {
                getDefaultFormat: function () {
                    return $.cookie("af") || "mp3_128k";
                },
                setDefaultFormat: function (format) {
                    $.cookie("af", format, { expires: 365, path: "/" });
                },
                getFormatsList: function () {
                    return {
                        aac: [
                            { key: "aacplus_24k", bitrate: "24K" },
                            { key: "aacplus_32k", bitrate: "32K" },
                            { key: "aacplus_64k", bitrate: "64K" },
                            { key: "aacplus_128k", bitrate: "128K"}
                        ],
                        mp3: [
                            { key: "mp3_128k", bitrate: "128K" },
                            { key: "mp3_256k", bitrate: "256K" }
                        ]
                    }
                }
            }
        }
    ]);
})();

(function () {
    var tools = angular.module("mor.tools");

    tools.controller("StreamShareController", ["$scope", "$timeout", function ($scope, $timeout) {

        $scope.$watch("maxSize", function (width) {
            $scope.code = '<iframe ng-src="https://myownradio.biz/widget/?stream_id=' + $scope.streamObject.sid +
                '" width="' + $scope.embed.maxSize + '" height="' + $scope.embed.maxSize + '"></iframe>';
        });

        $scope.embed = {
            url: "/widget/?stream_id=" + $scope.streamObject.sid,
            maxSize: 400
        };

        $timeout(function () {
            stButtons.locateElements();
        }, 100);

    }]);

    tools.directive("share", [function () {
        return {
            scope: {
                ngModel: "="
            },
            restrict: "E",
            required: "ngModel",
            template: "<i class='icon-share-alt' mor-tooltip='Share this radio channel' ng-click='share()'></i>",
            controller: ["$scope", "ngDialog", function ($scope, ngDialog) {
                $scope.share = function () {
                    if (angular.isDefined($scope.ngModel)) {
                        var scope = $scope.$new();
                        scope.streamObject = $scope.ngModel;
                        scope.streamObject.url = "https://myownradio.biz/streams/" + scope.streamObject.key;
                        ngDialog.open({
                            templateUrl: "/views/blocks/share.html",
                            controller: "StreamShareController",
                            scope: scope
                        });
                    }
                }
            }]
        }
    }]);

})();

/**
 * Module Account
 */
(function () {

    var account = angular.module("Account", ["Site"]);

    account.run(["$rootScope", "User", "$location", "$route", "$cacheFactory", "$http",
        function ($rootScope, User, $location, $route, $cacheFactory, $http) {

            // Initial account state
            $rootScope.account = {authorized: false, user: null, pending: true, streams: null};

            $rootScope.$watch("account.pending", function (value) {

                if (typeof $route.current == "undefined") return;
                if (typeof $route.current.needsAuth == "undefined") return;
                if (value == true) return;

                if ($rootScope.account.authorized == false && $route.current.needsAuth === true) {
                    $location.url("/login");
                }

            });

            $rootScope.$on("$routeChangeSuccess", function (event, currentRoute) {

                $rootScope.account.init();

            });

            // Function handlers
            $rootScope.account.init = function (go) {

                $rootScope.account.pending = true;

                $cacheFactory.get('$http').removeAll();

                var cookie = $.cookie();

                User.whoAmI().onSuccess(function (data) {
                    $rootScope.account.authorized = true;
                    $rootScope.account.user = data.user;
                    $rootScope.account.streams = data.streams;
                    $rootScope.account.pending = false;
                    if (cookie.PHPSESSID) {
                        $http.defaults.headers.common.Security = cookie.PHPSESSID;
                    }

                    if (typeof go == "string") {
                        $location.url(go);
                    }
                }, function () {
                    $rootScope.account.authorized = false;
                    $rootScope.account.user = null;
                    $rootScope.account.streams = null;
                    $rootScope.account.pending = false;
                    $http.defaults.headers.common.Security = undefined;
                });
            };

            $rootScope.account.logout = function () {
                $cacheFactory.get('$http').removeAll();
                User.logout().onSuccess(function () {
                    //$rootScope.account.init();
                    $route.reload();
                });
            };

        }]);

    /*
     Login Controller
     */
    account.controller("LoginForm", ["$scope", "$rootScope", "User", "$location", "Popup", "Account",

        function ($scope, $rootScope, User, $location, Popup, Account) {

            // Init variables
            $scope.credentials = {login: "", password: "", save: false};
            $scope.status = "";
            $scope.submit = function () {
                User.login($scope.credentials)
                    .onSuccess(function (data) {
                        Popup.message("Welcome, " + data.name + "!<br>You're successfully logged in!");
                        $scope.status = "";
                        $rootScope.account.init("/profile/");
                    }, function (err) {
                        $scope.status = err;
                    }
                )
            };
            $scope.signUpFacebook = function () {
                FB.login(function (response) {
                    if (response.status === "connected") {
                        Account.loginByFacebook(response.authResponse).onSuccess(function(data) {
                            Popup.message("Welcome, " + data.name + "!<br>You're successfully logged in!");
                            $scope.status = "";
                            $scope.account.init("/profile/");
                        }, function (error) {
                            $scope.status = error;
                        });
                    }
                }, {
                    scope: "email"
                });
            };
        }

    ]);

    account.controller("ChangePlanController", ["$scope", "User", "$location", "Popup",

        function ($scope, User, $location, Popup) {
            $scope.data = {
                code: "",
                error: ""
            };
            $scope.submit = function () {
                User.enterPromoCode($scope.data.code).onSuccess(function (data) {
                    Popup.message("You have successfully changed your current account plan to" + htmlEscape(data));
                    $location.url("/profile/");
                }, function (message) {
                    $scope.data.error = message;
                })
            }
        }

    ]);

    /*
     Sign Up Controllers
     */
    account.controller("SignUpBeginForm", ["$scope", "$location", "Account", "Popup",

        function ($scope, $location, Account, Popup) {

            // Init variables
            $scope.signup = {email: "", code: ""};
            $scope.status = "";
            $scope.submit = function () {
                Account.signUpRequest($scope.signup).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/registrationLetterSent");
                }, function (err) {
                    $scope.status = err;
                });
            };

            $scope.signUpFacebook = function () {
                FB.login(function (response) {
                    if (response.status === "connected") {
                        Account.loginByFacebook(response.authResponse).onSuccess(function (data) {
                            Popup.message("Welcome, " + data.name + "!<br>You're successfully logged in!");
                            $scope.account.init("/profile/");
                        }, function (error) {
                            console.log(error);
                        });
                    }
                }, {
                    scope: "email"
                });
            };

        }

    ]);

    account.controller("SignUpCompleteForm", ["$scope", "$location", "Account", "$routeParams",

        function ($scope, $location, Account, $routeParams) {
            // Init variables
            $scope.signup = {code: $routeParams.code, login: "", password: "", name: "", info: "", permalink: "", country_id: null};
            $scope.status = "";
            $scope.submit = function () {
                Account.signUp($scope.signup).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/registrationCompleted");
                }, function (err) {
                    $scope.status = err;
                });
            }
        }

    ]);

    /*
     Password Recovery Controllers
     */
    account.controller("PasswordResetBeginForm", ["$scope", "$location", "Account",

        function ($scope, $location, Account) {
            // Init variables
            $scope.reset = {login: ""};
            $scope.status = "";
            $scope.submit = function () {
                Account.resetRequest($scope.reset).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/resetLetterSent"); // todo: add email hint
                }, function (err) {
                    $scope.status = err;
                });
            }
        }

    ]);

    account.controller("PasswordResetCompleteForm", ["$scope", "$location", "Account", "$routeParams",

        function ($scope, $location, Account, $routeParams) {
            // Init variables
            $scope.reset = {code: $routeParams.code, password: ""};
            $scope.check = "";
            $scope.status = "";
            $scope.submit = function () {
                Account.resetPassword($scope.reset).onSuccess(function () {
                    $scope.status = "";
                    $location.url("/static/resetPasswordCompleted");
                }, function (err) {
                    $scope.status = err;
                });
            }
        }

    ]);

    account.factory("Account", ["$http", "Response", function ($http, Response) {
        return {
            loginByFacebook: function (authResponse) {
                var action = $http({
                    method: 'POST',
                    url: "/api/v2/user/fbLogin",
                    data: {
                        token: authResponse.accessToken
                    }
                });
                return Response(action);
            },
            signUpRequest: function (params) {
                var action = $http({
                    method: 'POST',
                    url: "/api/v2/user/signUpBegin",
                    data: params
                });
                return Response(action);
            },
            signUp: function (params) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/signUpComplete",
                    data: {
                        code: params.code,
                        login: params.login,
                        password: params.password,
                        name: params.name,
                        info: params.info,
                        permalink: params.permalink,
                        country_id: params.country_id
                    }
                });
                return Response(action);
            },
            resetRequest: function (params) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/passwordResetBegin",
                    data: {
                        login: params.login
                    }
                });
                return Response(action);
            },
            resetPassword: function (params) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/passwordResetComplete",
                    data: {
                        code: params.code,
                        password: params.password
                    }
                });
                return Response(action);
            }
        };
    }]);

    /**
     * @class User
     * @constructor
     */
    account.factory("User", ["$http", "Response", function ($http, Response) {
        return {
            /**
             * @method whoAmI
             * @returns {*}
             */
            whoAmI: function () {
                var action = $http({
                    method: "GET",
                    url: "/api/v2/self"
                });
                return Response(action);
            },
            /**
             * @method changeInfo
             * @param name
             * @param info
             * @param permalink
             * @param country
             * @returns {*}
             */
            changeInfo: function (name, info, permalink, country) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/self",
                    data: {
                        name: name,
                        info: info,
                        permalink: permalink,
                        country_id: country
                    }
                });
                return Response(action);
            },
            /**
             * @method changePassword
             * @param new_password
             * @param old_password
             * @returns {*}
             */
            changePassword: function (new_password, old_password) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/self/changePassword",
                    data: {
                        new_password: new_password,
                        old_password: old_password
                    }
                });
                return Response(action);
            },
            /**
             * @method deleteAccount
             * @param password
             * @returns {*}
             */
            deleteAccount: function (password) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/self/delete",
                    data: {
                        password: password
                    }
                });
                return Response(action);
            },
            /**
             * @method login
             * @param data
             * @returns {*}
             */
            login: function (data) {
                var action = $http({
                    method: "POST",
                    url: "/api/v2/user/login",
                    data: data
                });
                return Response(action);
            },
            /**
             * @method logout
             * @returns {*}
             */
            logout: function () {
                var action = $http({
                    method: "DELETE",
                    url: "/api/v2/self"
                });
                return Response(action);
            },
            /**
             * @method enterPromoCode
             * @param code
             * @returns {*}
             */
            enterPromoCode: function (code) {
                return Response($http({
                    method: "POST",
                    url: "/api/v2/self/promoCode",
                    data: {
                        code: code
                    }
                }))
            }
        }
    }]);

    account.factory("Bookmarks", ["$http", "Response", function ($http, Response) {
        return {
            add: function (streamObject) {
                var action = $http({
                    method: "PUT",
                    url: "/api/v2/bookmark",
                    params: {
                        stream_id: streamObject.sid
                    }
                });
                return Response(action);
            },
            remove: function (streamObject) {
                var action = $http({
                    method: "DELETE",
                    url: "/api/v2/bookmark",
                    params: {
                        stream_id: streamObject.sid
                    }
                });
                return Response(action);
            }
        }
    }]);

    account.directive("bookmark", ["Bookmarks", "Popup", "$rootScope",

        function (Bookmarks, Popup, $rootScope) {

            var buttonTemplate = '<i ng-if="!stream.bookmarked" class="icon-heart-o" mor-tooltip="Add radio station to bookmarks"></i>\
                                  <i ng-if=" stream.bookmarked" class="icon-heart" mor-tooltip="Remove radio station from bookmarks"></i>';

            return {
                restrict: "E",
                scope: {
                    stream: "=ngModel"
                },
                require: "ngModel",
                template: buttonTemplate,
                link: function ($scope, $element, $attrs) {
                    $element.on("click", function (event) {
                        if ($scope.stream.bookmarked) {
                            Bookmarks.remove($scope.stream).onSuccess(function () {
                                $rootScope.$broadcast("BOOKMARK", {id: $scope.stream.sid, bookmarked: false});
                                Popup.message("<b>" + htmlEscape($scope.stream.name) + "</b> successfully removed from your bookmarks");
                            }, function (message) {
                                Popup.message(message, "Error");
                            });
                        } else {
                            Bookmarks.add($scope.stream).onSuccess(function () {
                                $rootScope.$broadcast("BOOKMARK", {id: $scope.stream.sid, bookmarked: true});
                                Popup.message("<b>" + htmlEscape($scope.stream.name) + "</b> successfully added to your bookmarks");
                            }, function (message) {
                                Popup.message(message, "Error");
                            });
                        }
                    });
                },
                controller: ["$scope", function ($scope) {
                        $scope.$on("BOOKMARK", function (broadcast, data) {
                            if ($scope.stream.sid == data.id) {
                                $scope.stream.bookmarked = data.bookmarked;
                            }
                        })
                }]
            }
        }

    ]);

})();



/**
 * Created by roman on 31.12.14.
 */

(function () {
    var player = angular.module("RadioPlayer", ['Site']);

    player.run(["$rootScope", "$http", "Response", "Streams", "$timeout", "$location", "Popup",

        function ($rootScope, $http, Response, Streams, $timeout, $location, Popup) {

            var handle = false;

            $rootScope.player = {
                isPlaying: false,
                isLoaded: false,
                isBuffering: false,
                nowPlaying: null,
                currentID: null,
                currentStream: null,
                url: null,
                page: undefined,
                visible: true,
                goCurrent: function () {
                    $location.url($rootScope.player.page);
                },
                controls: {
                    reload: function () {
                        var $stream = $rootScope.player.currentStream;
                        if ($rootScope.player.isPlaying === true) {
                            console.log("Reload", $stream.sid, $rootScope.defaults.format);
                            $rootScope.player.url = "http://myownradio.biz:7778/audio?s=" + $stream.sid + "&f=" + $rootScope.defaults.format;
                            $rootScope.player.controls.play();
                        }
                    },
                    loadStream: function ($stream) {
                        $rootScope.player.url = "/flow?s=" + $stream.sid + "&f=" + $rootScope.defaults.format;
                        $rootScope.player.currentID = $stream.sid;
                        $rootScope.player.controls.play();
                        $rootScope.player.currentStream = $stream;
                        $rootScope.player.page = "/streams/" + $stream.key;
                        $rootScope.player.isLoaded = true;
                    },
                    play: function () {
                        $rootScope.player.isBuffering = true;
                        realPlayer.play($rootScope.player.url);
                        $rootScope.player.isPlaying = true;
                    },
                    stop: function () {
                        realPlayer.stop();
                        $timeout.cancel(handle);
                        $rootScope.player.isBuffering = false;
                        $rootScope.player.nowPlaying = null;
                        $rootScope.player.isPlaying = false;
                    },
                    switch: function () {
                        $rootScope.player.isPlaying ?
                            $rootScope.player.controls.stop() :
                            $rootScope.player.controls.play();
                    },
                    playSwitchStream: function ($stream) {
                        if ($rootScope.player.currentID == $stream.sid) {
                            $rootScope.player.controls.switch();
                        } else {
                            $rootScope.player.controls.stop();
                            $rootScope.player.controls.loadStream($stream);
                        }
                    },
                    unload: function () {
                        $rootScope.player.controls.stop();
                        $rootScope.player.currentID = null;
                        $rootScope.player.currentStream = null;
                        $rootScope.player.page = null;
                        $rootScope.player.isLoaded = false;
                    }
                }
            };

            $rootScope.$watch("player.nowPlaying.unique_id", function (newValue) {
                if (newValue && $rootScope.player.isPlaying) {
                    Popup.message("<b>" + htmlEscape($rootScope.player.nowPlaying.caption) + "</b><br>now on <b>" + htmlEscape($rootScope.player.currentStream.name) + "</b>", 5000);
                }
            });

            var realHandle = null;
            var realPlayer = {
                play: function (url, onPlay) {

                    onPlay = onPlay || function () {
                    };

                    realPlayer.stop();
                    realHandle = new Audio5js({
                        swf_path: "/swf/audio5js.swf",
                        codecs: ['mp3'],
                        ready: function () {
                            this.on("timeupdate", function () {
                                if ($rootScope.player.isBuffering == true) {
                                    $rootScope.player.isBuffering = false;
                                    $rootScope.$digest();
                                }

                            });
                            this.on("error", function () {
                                $rootScope.player.isBuffering = true;
                                $timeout(function () {
                                    realPlayer.play(url)
                                }, 1000);
                            });

                            this.load(url);
                            this.play();
                        }
                    });
                },
                stop: function () {
                    if (realHandle instanceof Audio5js) {
                        realHandle.destroy();
                    }
                    realHandle = null;
                }
            };

        }

    ]);

    player.directive("play", [function () {
        return {
            scope: {
                obj: "="
            },
            template: '<div class="play-pause"><div class="toggle" ng-click="playRadio(obj)" mor-tooltip="Play/Stop">\
                            <i ng-show="player.isPlaying && player.currentID == obj.sid" class="icon-stop"></i>\
                            <i ng-hide="player.isPlaying && player.currentID == obj.sid" class="icon-play-arrow"></i>\
                            </div></div>',
            controller: ["$scope", "$rootScope", function ($scope, $rootScope) {
                $scope.playRadio = function ($stream) {
                    $rootScope.player.controls.playSwitchStream($stream);
                };
                $scope.player = $rootScope.player;
            }]
        }
    }]);

    player.directive("preview", ["TrackPreviewService", function (TrackPreviewService) {
        return {
            template: '<span class="only-first-element" mor-tooltip="Click to preview track">' +
                '<i ng-if="!isPlaying" class="icon-play-circle-fill"></i>' +
                '<i ng-if="isPlaying" class="icon-pause-circle-fill"></i>' +
                '</span>',
            restrict: "E",
            require: "ngModel",
            scope: {
                ngModel: "="
            },
            link: function ($scope, $element, $attrs) {
                $scope.isPlaying = false;
                $element.on("mousedown", function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    TrackPreviewService.play($scope.ngModel);
                    $scope.ngModel.is_new = 0;
                });
                $scope.$on("preview.start", function (event, track) {
                    if ($scope.ngModel == null) return;
                    if (track.tid == $scope.ngModel.tid) {
                        $scope.isPlaying = true;
                        $scope.$applyAsync();
                    }
                });
                $scope.$on("preview.stop", function (event, track) {
                    $scope.isPlaying = false;
                    $scope.$applyAsync();
                });
                TrackPreviewService.ifSomethingIsPlaying(function () {
                    if ($scope.ngModel == null) return;
                    if (this.tid == $scope.ngModel.tid) {
                        $scope.isPlaying = true;
                    }
                });
            }
        };
    }]);

    player.factory("TrackPreviewService", ["$rootScope", "Popup",

        function ($rootScope, Popup) {

            var jPlayer = $("<div></div>").appendTo("body").jPlayer({
                swfPath: "jplayer",
                supplied: "mp3",
                play: function (event) {
                    Popup.message("Preview of <b>" + htmlEscape(currentTrack.artist + " - " + currentTrack.title) + "</b> is started");
                    $rootScope.$broadcast("preview.start", currentTrack);
                },
                ended: function (event) {
                    Popup.message("Preview of <b>" + htmlEscape(currentTrack.artist + " - " + currentTrack.title) + "</b> is finished");
                    $rootScope.$broadcast("preview.stop");
                    currentTrack = null;
                },
                error: function (event) {
                    Popup.message("Error<br>" + htmlEscape(event.jPlayer.error.message));
                    $rootScope.$broadcast("preview.stop");
                    currentTrack = null;
                },
                solution: "html, flash",
                volume: 1,
                wmode: 'window'
            });

            var currentTrack = null;

            var service = {
                play: function (object) {
                    if (currentTrack != null && currentTrack.tid == object.tid) {
                        service.stop();
                    } else {
                        service.stop();
                        jPlayer.jPlayer("setMedia", { mp3: "/content/audio/".concat(object.tid) });
                        jPlayer.jPlayer("play");
                        currentTrack = object;
                    }
                },
                stop: function () {
                    jPlayer.jPlayer("clearMedia");
                    $rootScope.$broadcast("preview.stop");
                    currentTrack = null;
                },
                ifSomethingIsPlaying: function (callback) {
                    if (currentTrack != null)
                        callback.call(currentTrack);
                }
            };

            return service;

        }

    ]);

})();



/**
 * Module Catalog
 */
(function () {

    var catalog = angular.module("Catalog", ["Site"]);

    catalog.constant("STREAMS_PER_SCROLL", 20);
    catalog.constant("TIMELINE_RESOLUTION", 1800000);


    catalog.controller("StreamEditorController", ["$scope", "$rootScope", "$routeParams", "Streams", "$location", "$dialog",

        function ($scope, $rootScope, $routeParams, Streams, $location, $dialog) {

            var id = $routeParams.id;
            $scope.error = "";
            $scope.stream = {};
            $scope.init = function () {
                Streams.getByID(id).onSuccess(function (response) {
                    $scope.stream = response;
                }, function () {
                    $location.url("/profile/streams/");
                });
            };
            $scope.submit = function () {
                $scope.error = "";
                Streams.updateStreamInfo($scope.stream).onSuccess(function () {
                    $rootScope.reload();
                    $rootScope.account.init("/profile/streams/" + $scope.stream.sid);
                }, function (err) {
                    $scope.error = err;
                });
            };

            $scope.init();

        }

    ]);

    catalog.controller("NewStreamController", ["$scope", "Streams", "$rootScope", "Response", "$http",

        function ($scope, Streams, $rootScope, Response, $http) {
            $scope.status = "";
            $scope.error = "";
            $scope.stream = {
                name: "",
                info: "",
                hashtags: "",
                permalink: "",
                category: 0,
                access: "PUBLIC"
            };

            $scope.cover = null;
            $scope.preview = null;

            $scope.submit = function () {
                $scope.status = "";
                $scope.error = "";

                var fd = new FormData();

                if ($scope.cover) {
                    fd.append("file", $scope.cover);
                }

                fd.append("name", $scope.stream.name);
                fd.append("info", $scope.stream.info);
                fd.append("tags", $scope.stream.hashtags);
                fd.append("permalink", $scope.stream.permalink);
                fd.append("category", $scope.stream.category);
                fd.append("access", $scope.stream.access);

                var uploader = Response($http({
                    method: "POST",
                    url: "/api/v2/stream/create",
                    data: fd,
                    transformRequest: angular.identity,
                    headers: {'Content-Type': undefined}
                }));

                uploader.onSuccess(function (data) {
                    $rootScope.reload();
                    $rootScope.account.init("/profile/streams/" + data.sid);
                }, function (resp) {
                    $scope.error = resp;
                });

            };

        }

    ]);

    catalog.controller("StreamCoverController", ["$scope", "$http", "Response", "$routeParams",

        function ($scope, $http, Response, $routeParams) {

            var id = $routeParams.id;
            $scope.upload = function () {

                var file = $("<input>");

                file.attr("type", "file");
                file.attr("accept", "image/jpeg,image/png")
                file.on("change", function (event) {
                    if (this.files.length == 0) return;

                    var fd = new FormData();
                    fd.append("file", this.files[0]);
                    fd.append("stream_id", id);

                    var uploader = Response($http({
                        method: "POST",
                        url: "/api/v2/stream/changeCover",
                        data: fd,
                        transformRequest: angular.identity,
                        headers: {'Content-Type': undefined}
                    }));

                    uploader.onSuccess(function (response) {
                        $scope.stream.cover_url = response.url;
                        $scope.stream.cover = response.name;
                    });

                });
                file.click();
            };

            $scope.remove = function () {

                var uploader = Response($http({
                    method: "POST",
                    url: "/api/v2/stream/removeCover",
                    data: {stream_id: id}
                }));

                uploader.onSuccess(function () {
                    $scope.stream.cover_url = null;
                    $scope.stream.cover = null;
                });

            };
        }

    ]);

    catalog.controller("NewStreamCoverController", ["$scope", function ($scope) {

        var reader = new FileReader();

        reader.onload = function (e) {
            $scope.$parent.preview = e.target.result;
            $scope.$digest();
        };


        $scope.upload = function () {

            var file = $("<input>");

            file.attr("type", "file");
            file.attr("accept", "image/jpeg,image/png");
            file.on("change", function () {
                if (this.files.length == 0) return;
                $scope.$parent.cover = this.files[0];
                reader.readAsDataURL(this.files[0]);
            });
            file.click();
        };

        $scope.remove = function () {

            $scope.$parent.cover = null;
            $scope.$parent.preview = null;

        };

    }]);

    catalog.controller("SearchFormController", ["$element", "$scope", "$location", "Streams", "$document",

        function ($element, $scope, $location, Streams, $document) {
            $scope.filter = "";
            $scope.streams = [];
            $scope.focused = false;

            $document.bind("click", function (event) {
                $scope.$apply(function () {
                    $scope.focused = ($element.find(event.target).length > 0);
                });
            });

            $element.on("keyup", function (event) {
                $scope.$apply(function () {
                    $scope.focused = true;
                });
            });

            $scope.goSearch = function () {
                $location.url("/search/".concat(encodeURIComponent($scope.filter)));
                $scope.filter = "";
            };
            $scope.goStream = function (key) {
                $location.url("/streams/".concat(key));
                $scope.filter = "";
            };
            $scope.$watch("filter", function (value) {
                if (typeof value == "string" && value.length > 0) {
                    Streams.getList($scope.filter, "", 0, 5).onSuccess(function (data) {
                        $scope.streams = data.streams;
                    });
                } else {
                    $scope.streams = [];
                }
            });
        }

    ]);


    catalog.controller("ListStreamsController", ["$scope", "$location", "Streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, Streams, STREAMS_PER_SCROLL, channelData) {

            var category = $location.search().category;

            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data.streams;
                $scope.content.empty = data.streams.length > 0;
            });

            $scope.busy = false;

            $scope.load = function () {
                $scope.busy = true;
                Streams.getList(null, category, $scope.content.streams.length, STREAMS_PER_SCROLL, false)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response.streams);
                        if (response.streams.length > 0) {
                            $scope.busy = false;
                        }
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    });
            };

        }

    ]);

    catalog.controller("ListSearchController", ["$scope", "$location", "$routeParams", "Streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, $routeParams, Streams, STREAMS_PER_SCROLL, channelData) {

            var category = $location.search().category;

            $scope.query = decodeURI($routeParams.query);

            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data.streams;
                $scope.content.empty = data.streams.length > 0;
            });

            $scope.busy = false;

            $scope.load = function () {
                $scope.busy = true;
                Streams.getList($scope.query, category, $scope.content.streams.length, STREAMS_PER_SCROLL, $scope.content.streams.length === 0)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response.streams);
                        if (response.streams.length > 0) {
                            $scope.busy = false;
                        }
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    });
            };

        }

    ]);

    catalog.controller("BookmarksController", ["$scope", "$location", "Streams", "STREAMS_PER_SCROLL", "channelData",

        function ($scope, $location, Streams, STREAMS_PER_SCROLL, channelData) {
            $scope.content = {
                streams: [],
                empty: false
            };

            channelData.onSuccess(function (data) {
                $scope.content.streams = data;
                $scope.content.empty = data.length > 0;
            });

            $scope.busy = false;

            $scope.load = function () {
                $scope.busy = true;
                Streams.getBookmarks($scope.content.streams.length, $scope.content.streams.length === 0)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response);
                        if (response.length > 0) {
                            $scope.busy = false;
                        }
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    });
            };

        }

    ]);

    catalog.controller("UserStreamsController", ["$scope", "$routeParams", "Streams", "STREAMS_PER_SCROLL",

        function ($scope, $routeParams, Streams, STREAMS_PER_SCROLL) {
            $scope.user = $routeParams.key;
            $scope.content = {
                streams: [],
                owner: null,
                loaded: false,
                empty: false
            };
            $scope.busy = false;
            $scope.load = function () {
                $scope.busy = true;
                Streams.getByUser($scope.user, $scope.content.streams.length, $scope.content.streams.length === 0)
                    .onSuccess(function (response) {
                        $scope.content.streams = $scope.content.streams.concat(response.streams);
                        $scope.content.owner = response.user;
                        $scope.htmlReady();
                        if (response.streams.length == STREAMS_PER_SCROLL) {
                            $scope.busy = false;
                        }
                        $scope.content.loaded = true;
                        if ($scope.content.streams.length == 0) {
                            $scope.content.empty = true;
                        }
                    }, function (err) {
                        console.log(err);
                        $scope.busy = false;
                    });
            };

        }

    ]);

    catalog.controller("MyStreamsController", ["$rootScope", "$scope", "$dialog", "Streams", "StreamWorks",

        function ($rootScope, $scope, $dialog, Streams, StreamWorks) {

            $scope.deleteStream = function (stream) {
                $dialog.question("Are you sure want to delete stream<br><b>" + htmlEscape(stream.name) + "</b>?", function () {
                    Streams.deleteStream(stream).onSuccess(function () {
                        deleteMatching($rootScope.account.streams, function (obj) {
                            return stream.sid == obj.sid
                        });
                        $rootScope.account.user.streams_count = $rootScope.account.user.streams_count - 1;
                    });
                });
            };

            $scope.changeStreamState = function (stream) {
                if (stream.status == 0) {
                    StreamWorks.startStream(stream).onSuccess(function () {
                        $rootScope.account.init();
                    });
                } else {
                    $dialog.question("Are you sure want to <b>shut down</b> stream<br><b>" + htmlEscape(stream.name) + "</b>?", function () {
                        StreamWorks.stopStream(stream).onSuccess(function () {
                            $rootScope.account.init();
                        });
                    });
                }
            }

        }

    ]);

    catalog.controller("OneStreamController", ["$rootScope", "$scope", "$location", "Streams", "$routeParams",
        "$document", "SITE_TITLE", "AudioInfoEditor", "TrackAction", "StreamWorks", "streamData",

        function ($rootScope, $scope, $location, Streams, $routeParams, $document, SITE_TITLE, AudioInfoEditor, TrackAction, StreamWorks, streamData) {
            $scope.content = {
                id: $routeParams.id,
                streamData: null,
                similarStreams: null
            };
            if (streamData.data.code == 1) {
                $scope.content.streamData = streamData.data.data.stream;
                $scope.content.similarStreams = streamData.data.data.similar.streams;
                $document.get(0).title = streamData.data.data.stream.name.toString().concat(" @ ").concat(SITE_TITLE);
            } else {
                $location.url("/streams/");
            }
            $scope.play = function () {
                $scope.content.streamData.listeners_count += 1;
                $rootScope.player.controls.loadStream($scope.content.streamData);
            };
            $scope.stop = function () {
                $scope.content.streamData.listeners_count = Math.max($scope.content.streamData.listeners_count - 1, 0);
                $rootScope.player.controls.stop();
            };
            $scope.toggle = function () {
                (($rootScope.player.isPlaying == true && $rootScope.player.currentID == $scope.content.streamData.sid) ? $scope.stop : $scope.play)();
            };
            $scope.$on("BOOKMARK", function (obj, data) {
                if (data.id == $scope.content.streamData.sid) {
                    if (data.bookmarked == true) {
                        $scope.content.streamData.bookmarks_count++;
                    } else {
                        $scope.content.streamData.bookmarks_count--;
                    }
                }
            });
            $scope.edit = function () {
                AudioInfoEditor.show([$rootScope.player.nowPlaying], $scope);
            };
            $scope.editTrack = function (track) {
                AudioInfoEditor.show([track], $scope);
            };
            $scope.remove = function () {
                TrackAction.removeTracksFromStream($scope.content.streamData, [$rootScope.player.nowPlaying]);
            };
            $scope.removeTrack = function (track) {
                TrackAction.removeTracksFromStream($scope.content.streamData, [track]);
            };

            $scope.$watch("scheduleTracks", function (data) {
                if (data !== null && $scope.content.streamData !== null) {
                    $scope.content.streamData.listeners_count = data.listeners_count;
                    $scope.content.streamData.bookmarks_count = data.bookmarks_count;
                }
            });

            $scope.sort = function (uniqueId, newIndex) {
                StreamWorks.sort($scope.content.streamData.sid, uniqueId, newIndex + 1).onSuccess(function () {

                });
            };

            $scope.sortableOptions = {
                axis: 'y',
                items: ".track-row",
                stop: function (event, ui) {
                    var thisElement = angular.element(ui.item).scope();
                    var thisIndex = angular.element(ui.item);
                    console.log(thisElement, thisIndex);
                    //$scope.sort(thisElement.track.unique_id, thisIndex);
                },
                helper: function (e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function (index) {
                        $(this).width($originals.eq(index).width())
                    });
                    return $helper;
                }
            };

        }

    ]);

    catalog.directive("trackItem", ["TIMELINE_RESOLUTION", function (TIMELINE_RESOLUTION) {
        return {
            link: function (scope, elem, attrs) {
                var width, diff, isFirst = false, isLast = false, isOutside = false, isInside = false;

                /* Define time ranges */
                var leftRange = scope.schedule.position - (TIMELINE_RESOLUTION >> 1),
                    rightRange = scope.schedule.position + (TIMELINE_RESOLUTION >> 1),
                    marginSize = (TIMELINE_RESOLUTION >> 1) - scope.schedule.position;

                if (scope.track.time_offset + scope.track.duration < leftRange || scope.track.time_offset > rightRange) {
                    isOutside = true;
                }

                if (scope.track.time_offset <= leftRange && scope.track.time_offset + scope.track.duration > leftRange) {
                    isFirst = true;
                }

                if (scope.track.time_offset >= leftRange && scope.track.time_offset + scope.track.duration <= rightRange) {
                    isInside = true;
                }

                if (scope.track.time_offset < rightRange && scope.track.time_offset + scope.track.duration >= rightRange) {
                    isLast = true;
                }

                (scope.reDraw = function () {
                    if (isFirst && isLast) {
                        width = Math.min(elem.parent().width() / scope.rate, scope.track.duration);
                        elem.css("margin-left", Math.max(0, marginSize * scope.rate).toString().concat("px"));
                    } else if (isFirst) {
                        width = Math.max(0, scope.track.duration - (leftRange - scope.track.time_offset));
                    } else if (isLast) {
                        var end = scope.track.time_offset + scope.track.duration;
                        width = Math.min(scope.track.duration - (end - rightRange), scope.track.duration);
                    } else if (isInside) {
                        width = scope.track.duration;
                        if (scope.$first) {
                            elem.css("margin-left", Math.max(0, marginSize * scope.rate).toString().concat("px"));
                        }
                    } else if (isOutside) {
                        width = 0;
                        elem.css("display", "none");
                    }
                    elem.width((width * scope.rate).toString().concat("px"));
                })();

            },
            template: '<div class="track-title">{{track.caption}}</div>'
        };
    }]);

    catalog.directive("timeLine", ["TIMELINE_RESOLUTION", function (TIMELINE_RESOLUTION) {
        return {
            scope: {
                schedule: "=timeLine"
            },
            template: '<div class="timeline-container">\
               <div class="timeline-wrap">\
                  <div ng-repeat="track in schedule.tracks" class="timeline-track" ng-class="(track.tid === current.tid) ? \'current\' : \'\'\" track-item mor-tooltip="{{track.caption}}"></div>\
               </div>\
            </div>\
            <div class="canvas-wrap"><canvas id="grid"></canvas></div>',
            controller: function ($element, $scope, $parse, $attrs) {

                $scope.rate = $element.find(".timeline-container").width() / TIMELINE_RESOLUTION;

                $scope.$watch("schedule", function (response) {

                    if (!response) return false;

                    $scope.current = response.tracks[response.current];
                    $scope.schedule = response;
                    $scope.schedule.clientTime = new Date().getTime();
                    $scope.drawGrid();

                });

                $scope.drawGrid = function () {
                    var leftEdgeTime = $scope.schedule.time - (TIMELINE_RESOLUTION >> 1);
                    var cut = leftEdgeTime % 60000;
                    var canvas = $element.find("#grid").get(0);

                    if (typeof canvas == "undefined") return;

                    canvas.height = $(canvas).height();
                    canvas.width = $(canvas).width();

                    var rangeUSeconds = TIMELINE_RESOLUTION;

                    var resolution = canvas.width / rangeUSeconds;

                    var context = canvas.getContext("2d");
                    context.font = "10px sans-serif";
                    context.translate(0.5, 0.5);
                    context.clearRect(0, 0, canvas.width, canvas.height);
                    context.globalAlpha = 1;
                    context.strokeStyle = "#000000";
                    context.beginPath();
                    var fix;
                    for (var n = -cut; n < rangeUSeconds; n += 30000) {
                        var thisDate = new Date(leftEdgeTime + n);
                        fix = parseInt(n * resolution);
                        context.moveTo(fix, 0);

                        if (thisDate.getMinutes() % 5 == 0 && thisDate.getSeconds() == 0) {
                            context.lineTo(fix, 6);
                            var time = thisDate.toTimeString().replace(/.*(\d{2}:\d{2}):\d{2}.*/, "$1");
                            context.fillText(time, fix - 12, 16);
                        } else if (thisDate.getSeconds() == 0) {
                            context.lineTo(fix, 4);
                        } else {
                            context.lineTo(fix, 2);
                        }
                    }
                    context.stroke();
                    context.globalAlpha = 0.5;
                    context.fillStyle = "#FF0000";
                    context.beginPath();
                    context.moveTo(canvas.width / 2, 0);
                    context.lineTo(canvas.width / 2 + 5, 10);
                    context.lineTo(canvas.width / 2 - 5, 10);
                    context.lineTo(canvas.width / 2, 0);
                    context.fill();
                };
            }
        }
    }]);

    catalog.factory("Categories", ["$http", "Response", function ($http, Response) {
        return {
            list: function () {
                var action = $http({
                    method: "GET",
                    url: "/api/v2/categories"
                });
                return Response(action);
            }
        }
    }]);

    catalog.factory("Resolvers", ["$http", "ResponseData", function ($http, ResponseData) {
        return {
            getChannelList: function (filter, category, offset, limit) {
                return $http.get("/api/v2/streams/getList", {
                    cache: true,
                    busy: true,
                    params: {
                        filter: filter,
                        category: category,
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getBookmarks: function (offset, limit) {
                return $http.get("/api/v2/streams/getBookmarks", {
                    busy: true,
                    params: {
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getByIdWithSimilar: function (streamID) {
                return $http.get("/api/v2/streams/getOneWithSimilar", {
                    busy: true,
                    params: {
                        stream_id: streamID
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getChannelsByUser: function (userID, offset, limit) {
                return $http.get("/api/v2/streams/getStreamsByUser", {
                    busy: true,
                    params: {
                        user: userID,
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            },
            getChannelsBySelf: function (offset, limit) {
                return $http.get("/api/v2/streams/getStreamsByUser", {
                    busy: true,
                    params: {
                        offset: offset,
                        limit: limit
                    }
                }).then(function (data) {
                    return ResponseData(data);
                });
            }
        }
    }]);

    catalog.factory("Streams", ["$http", "Response", "ResponseData",

        function ($http, Response, ResponseData) {
            return {
                getByID: function (streamID) {
                    var action = $http({
                        method: "GET",
                        url: "/api/v2/streams/getOne",
                        busy: false,
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                getList: function (filter, category, offset, limit, busy) {
                    var action = $http({
                        method: "GET",
                        cache: true,
                        url: "/api/v2/streams/getList",
                        busy: busy,
                        params: {
                            filter: filter,
                            category: category,
                            offset: offset,
                            limit: limit
                        }
                    });
                    return Response(action);
                },
                getSimilarTo: function (streamID) {
                    var action = $http({
                        method: "GET",
                        cache: true,
                        url: "/api/v2/streams/getSimilarTo",
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                getByIdWithSimilar: function (streamID) {
                    var promise = $http.get("/api/v2/streams/getOneWithSimilar", {
                        cache: true,
                        url: "/api/v2/streams/getOneWithSimilar",
                        busy: true,
                        params: {
                            stream_id: streamID
                        }
                    }).success(function (data) {
                        return ResponseData(data);
                    });

                    return promise;
                },
                getByUser: function (userID, offset, busy) {
                    var action = $http({
                        method: "GET",
                        url: "/api/v2/streams/getStreamsByUser",
                        busy: busy,
                        params: {
                            user: userID,
                            offset: offset
                        }
                    });
                    return Response(action);
                },
                getBySelf: function () {
                    var action = $http({
                        method: "GET",
                        url: "/api/v2/streams/getStreamsByUser"
                    });
                    return Response(action);
                },
                getBookmarks: function (offset, busy) {
                    var action = $http({
                        method: "GET",
                        cache: true,
                        url: "/api/v2/streams/getBookmarks",
                        busy: busy,
                        params: {
                            offset: offset
                        }
                    });
                    return Response(action);
                },
                getSchedule: function (streamID) {
                    var action = $http({
                        method: "GET",
                        ignoreLoadingBar: true,
                        url: "/api/v2/streams/getSchedule",
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                getNowPlaying: function (streamID) {
                    var action = $http({
                        method: "GET",
                        ignoreLoadingBar: true,
                        url: "/api/v2/streams/getNowPlaying",
                        params: {
                            stream_id: streamID
                        }
                    });
                    return Response(action);
                },
                updateStreamInfo: function (object) {
                    var action = $http({
                        method: "POST",
                        url: "/api/v2/stream/modify",
                        data: {
                            stream_id: object.sid,
                            name: object.name,
                            info: object.info,
                            tags: object.hashtags,
                            permalink: object.permalink,
                            category: object.category,
                            access: object.access
                        }
                    });
                    return Response(action);
                },
                createNewStream: function (object) {
                    var action = $http({
                        method: "POST",
                        url: "/api/v2/stream/create",
                        data: {
                            name: object.name,
                            info: object.info,
                            tags: object.hashtags,
                            permalink: object.permalink,
                            category: object.category,
                            access: object.access
                        }
                    });
                    return Response(action);
                },
                deleteStream: function (stream) {
                    var action = $http({
                        method: "POST",
                        url: "/api/v2/stream/delete",
                        data: {
                            stream_id: stream.sid
                        }
                    });
                    return Response(action);
                }
            }
        }
    ]);

})();


(function () {

    var search = angular.module("Search", ['Site']);

    search.directive("navigableList", ["$document", function ($document) {
        return {
            link: function ($scope, $element, $attributes) {
                var SELECTED_CLASS = "selected",
                    bindHandle = function (event) {
                        if (event.which == 40) { // down
                            next();
                            event.stopPropagation();
                            return false;
                        } else if (event.which == 38) { // up
                            prev();
                            event.stopPropagation();
                            return false;
                        } else if (event.which == 13) {
                            $element.children("." + SELECTED_CLASS).trigger("click");
                            event.stopPropagation();
                            return false;
                        }
                    },
                    handle = $document.bind("keydown", bindHandle);

                $element.on('$destroy', function () {
                    handle.unbind("keydown", bindHandle);
                });

                $element.bind("mouseover", function (event) {
                    if ($element.children(event.target).length > 0) {
                        var li = $(event.target).parents("li");
                        select(li.index());
                    }
                });

                var next = function () {
                    var prevElem = $element.children("." + SELECTED_CLASS);
                    if (prevElem.length == 0) {
                        $element.children().eq(0).addClass(SELECTED_CLASS);
                    } else if (prevElem.next().length > 0) {
                        prevElem.removeClass(SELECTED_CLASS);
                        prevElem.next().addClass(SELECTED_CLASS);
                    }
                };

                var prev = function () {
                    var nextElem = $element.children("." + SELECTED_CLASS);
                    if (nextElem.prev().length > 0) {
                        nextElem.removeClass(SELECTED_CLASS);
                        nextElem.prev().addClass(SELECTED_CLASS);
                    }
                };

                var select = function (index) {
                    $element.children("." + SELECTED_CLASS).removeClass(SELECTED_CLASS);
                    $element.children().eq(index).addClass(SELECTED_CLASS);
                };

                select(0);
            }
        }
    }]);

})();

(function () {

    var profile = angular.module("Profile", ["Site"]);

    profile.controller("ProfileController", ["$rootScope", "$scope", "User",

        function ($rootScope, $scope, User) {

            $scope.status = "";
            $scope.error = "";

            var watcher = $rootScope.$watch("account.user", function (value) {
                $scope.details = value;
            });

            $scope.submit = function () {
                $scope.status = "";
                $scope.error = "";
                User.changeInfo($scope.details.name, $scope.details.info, $scope.details.permalink, $scope.details.country_id)
                    .onSuccess(function () {
                        $scope.status = "Profile updated";
                    }, function (err) {
                        $scope.error = err;
                    });
            };

            $scope.$on("$destroy", function () {
                watcher();
            });

        }

    ]);

    profile.controller("ChangePasswordController", ["$scope", "User", function ($scope, User) {

        $scope.status = "";
        $scope.error = "";

        $scope.passwords = {
            current: "",
            password1: ""
        };
        $scope.submit = function () {
            $scope.status = "";
            $scope.error = "";
            User.changePassword($scope.passwords.password1, $scope.passwords.current)
                .onSuccess(function () {
                    $scope.status = "Password successfully changed";
                }, function (err) {
                    $scope.error = err;
                });
        };
    }]);

    profile.controller("UserAvatarController", ["$rootScope", "$scope", "$http", "Response",

        function ($rootScope, $scope, $http, Response) {

            $scope.avatarUrl = null;

            var watcher = $rootScope.$watch("account.user.avatar_url", function (url) {
                $scope.avatarUrl = url;
            });

            $scope.$on("$destroy", function () {
                watcher();
            });

            $scope.upload = function () {
                var file = $("<input>");
                file.attr("type", "file");
                file.attr("accept", "image/jpeg,image/png")
                file.on("change", function (event) {
                    if (this.files.length == 0) return;

                    var fd = new FormData();
                    fd.append('file', this.files[0]);

                    var uploader = Response($http({
                        method: "POST",
                        url: "/api/v2/avatar",
                        data: fd,
                        transformRequest: angular.identity,
                        headers: {'Content-Type': undefined}
                    }));

                    uploader.onSuccess(function (url) {
                        $scope.avatarUrl = url;
                        $rootScope.account.init();
                    });

                });
                file.click();
            };

            $scope.remove = function () {

                var uploader = Response($http({
                    method: "DELETE",
                    url: "/api/v2/avatar"
                }));

                uploader.onSuccess(function () {
                    $scope.avatarUrl = null;
                    $rootScope.account.init();
                });

            };
        }

    ]);

    profile.controller("NavigationController", ["$scope", function ($scope) {

    }]);

    profile.directive("mainNavigation", [function () {
        return {
            templateUrl: "/views/blocks/nav.html",
            controller: "NavigationController"
        }
    }]);

})();

(function () {

    var lib = angular.module("Library", ["Site"]);

    lib.controller("StreamLibraryController", ["$scope", "$rootScope", "TrackWorks", "StreamWorks",
        "Streams", "$routeParams", "AudioInfoEditor", "TrackAction", "Popup", "TrackPreviewService",
        "ngDialog", "$location", "TracksScopeActions",

        function ($scope, $rootScope, TrackWorks, StreamWorks, Streams,
                  $routeParams, AudioInfoEditor, TrackAction, Popup, TrackPreviewService,
                  ngDialog, $location, TracksScopeActions) {

            $scope.tracksPending = true;
            $scope.tracks = [];
            $scope.stream = {};
            $scope.target = [];
            $scope.filter = "";
            $scope.busy = false;
            $scope.empty = false;

            $scope.$watch("tracks.length", function () {
                $scope.empty = $scope.tracks.length == 0;
            });

            $scope.clear = function () {
                $scope.filter = "";
                $scope.load(true);
            };

            $scope.numberTracks = function () {
                for (var i = 0, length = $scope.tracks.length; i < length; i += 1) {
                    $scope.tracks[i].t_order = i + 1;
                }
                $scope.$apply();
            };

            $scope.sortableOptions = {
                axis: 'y',
                items: ".item:visible",
                stop: function (event, ui) {
                    var thisElement = angular.element(ui.item).scope(),
                        thisIndex = thisElement.$index;
                    $scope.sort(thisElement.track.unique_id, thisIndex);
                },
                helper: function (e, tr) {
                    var $originals = tr.children();
                    var $helper = tr.clone();
                    $helper.children().each(function (index) {
                        $(this).width($originals.eq(index).width())
                    });
                    return $helper;
                }
            };

            var streamId = $routeParams.id;

            $scope.readStream = function () {
                Streams.getByID(streamId).onSuccess(function (res) {
                    $scope.stream = res;
                });
            };

            /* Tracks Manipulation */
            $scope.upload = function () {
                $scope.options = {
                    target: $scope.stream.sid,
                    append: true,
                    onFinish: function () { $scope.load(true); }
                };
                ngDialog.open({
                    templateUrl: "/views/auth/upload.html",
                    controller: "UploadController",
                    scope: $scope,
                    showClose: false,
                    closeByDocument: false
                });
            };

            $scope.load = function (clear) {

                clear = clear || false;
                var offset = clear ? 0 : $scope.tracks.length;

                $scope.busy = true;

                TrackWorks.getByStreamID(streamId, offset, $scope.filter).onSuccess(function (data) {

                    $scope.tracks = clear ? data : $scope.tracks.concat(data);
                    $scope.tracksPending = false;

                    if (data.length > 0) {
                        $scope.busy = false;
                    } else if ($scope.tracks.length == 0) {
                        $scope.empty = true;
                    }

                }, function () {
                    $location.url("/profile/streams/");
                });

                $scope.readStream();

            };

            $scope.deleteSelected = function () {

                TrackPreviewService.stop();
                TrackAction.removeTracksFromStream($scope.stream, $scope.target, function () {
                    Popup.message($scope.target.length + " track(s) successfully removed from stream <b>" + htmlEscape($scope.stream.name) + "</b>");
                    deleteMatching($scope.tracks, function (track) {
                        return $scope.target.indexOf(track) != -1;
                    });
                    truncateArray($scope.target);
                    $rootScope.account.init();
                    $scope.readStream();
                });

            };

            $scope.deleteCompletelySelected = function () {

                TrackPreviewService.stop();
                TrackAction.removeTracksFromAccount($scope.target, function () {
                    Popup.message($scope.target.length + " track(s) successfully removed from your account");
                    deleteMatching($scope.tracks, function (track) {
                        return $scope.target.indexOf(track) != -1;
                    });
                    truncateArray($scope.target);
                    $rootScope.account.init();
                    $scope.readStream();
                });

            };

            $scope.shuffle = function () {
                StreamWorks.shuffle(streamId).onSuccess(function () {
                    $scope.load(true);
                });
            };

            $scope.sort = function (uniqueId, newIndex) {
                StreamWorks.sort(streamId, uniqueId, newIndex + 1).onSuccess(function () {

                });
            };

            $scope.addToStream = function (stream) {
                TrackAction.addTracksToStream(stream, $scope.target, function () {
                    Popup.message($scope.target.length + " track(s) successfully added to stream <b>" + htmlEscape(stream.name) + "</b>");
                    if (stream.sid == $scope.stream.sid) {
                        $scope.load(true);
                    }
                    $rootScope.account.init();
                    $scope.readStream();
                });
            };

            $scope.moveToStream = function (stream) {
                TrackAction.addTracksToStream(stream, $scope.target, function () {
                    TrackAction.removeTracksFromStream($scope.stream, $scope.target, function () {
                        deleteMatching($scope.tracks, function (track) {
                            return $scope.target.indexOf(track) != -1;
                        });
                        truncateArray($scope.target);
                        $rootScope.account.init();
                        $scope.readStream();
                    });
                });
            };

            $scope.changeGroup = function (groupObject) {
                TrackAction.changeTracksColor(groupObject, $scope.target, function () {
                    for (var n = 0; n < $scope.target.length; n += 1) {
                        $scope.target[n].color = groupObject.color_id;
                    }
                });
            };

            $scope.playFrom = function () {

                var id = $scope.target[0].unique_id;

                StreamWorks.play(id, $scope.stream);

            };

            $scope.edit = function () {
                AudioInfoEditor.show($scope.target, $scope);
            };

            $scope.remove = function () {
                TrackAction.deleteStream($scope.stream);
            };

            $scope.readStream();

        }
    ]);

    lib.controller("TracksLibraryController",["$rootScope", "$scope", "TrackWorks", "StreamWorks",
        "ngDialog", "$route", "$dialog", "AudioInfoEditor", "TrackAction", "Popup", "TrackPreviewService",

        function ($rootScope, $scope, TrackWorks, StreamWorks, ngDialog, $route,
                  $dialog, AudioInfoEditor, TrackAction, Popup, TrackPreviewService) {

            $scope.tracksPending = true;
            $scope.tracks = [];
            $scope.target = [];
            $scope.filter = "";
            $scope.busy = false;

            $scope.sorting = {
                row: 0,
                order: 0,
                change: function (row, order) {
                    if (typeof order == "number") {
                        $scope.sorting.row = row;
                        $scope.sorting.order = order;
                    } else if (row == $scope.sorting.row) {
                        $scope.sorting.order = 1 - $scope.sorting.order;
                    } else {
                        $scope.sorting.order = 0;
                        $scope.sorting.row = row;
                    }
                    $scope.load(true, true);
                }
            };

            $scope.clear = function () {
                $scope.filter = "";
                $scope.load(true);
            };

            $scope.load = function (clear, busy) {

                clear = clear || false;

                $scope.busy = true;

                if (clear) {
                    $scope.tracksPending = true;
                    $scope.tracks = [];
                }

                TrackWorks.getAllTracks($scope.tracks.length, $scope.filter, $route.current.unused === true, $scope.sorting.row, $scope.sorting.order, busy)
                    .onSuccess(function (data) {

                    $scope.tracks = $scope.tracks.concat(data);
                    $scope.tracksPending = false;

                    if (data.length > 0) {
                        $scope.busy = false;
                    }

                });

            };

            /* Tracks Manipulation */
            $scope.upload = function () {
                ngDialog.open({
                    templateUrl: "/views/auth/upload.html",
                    controller: "UploadController",
                    scope: $scope,
                    showClose: false,
                    closeByDocument: false
                });
            };

            $scope.deleteSelected = function () {
                TrackPreviewService.stop();
                TrackAction.removeTracksFromAccount($scope.target, function () {
                    Popup.message($scope.target.length + " track(s) successfully removed from your account");
                    deleteMatching($scope.tracks, function (track) {
                        return $scope.target.indexOf(track) != -1;
                    });
                    truncateArray($scope.target);
                    $rootScope.account.init();
                });
            };

            $scope.addToStream = function (streamObject) {
                TrackAction.addTracksToStream(streamObject, $scope.target, function () {
                    Popup.message($scope.target.length + " track(s) successfully added to stream <b>" + htmlEscape(streamObject.name) + "</b>");
                    if ($route.current.unused === true) {
                        deleteMatching($scope.tracks, function (track) {
                            return $scope.target.indexOf(track) != -1
                        });
                        truncateArray($scope.target);
                    }
                    $rootScope.account.init();
                });
            };

            $scope.changeGroup = function (groupObject) {
                TrackAction.changeTracksColor(groupObject, $scope.target, function () {
                    for (var n = 0; n < $scope.target.length; n += 1) {
                        $scope.target[n].color = groupObject.color_id;
                    }
                });
            };

            $scope.edit = function () {
                AudioInfoEditor.show($scope.target, $scope);
            };

        }

    ]);

    lib.controller("UploadController", ["$scope", "$rootScope", "TrackWorks", "StreamWorks",
        "Response", "$http", "$q", "Popup",

        function ($scope, $rootScope, TrackWorks, StreamWorks, Response, $http, $q, Popup) {

        $scope.upNext = false;
        $scope.progress = {
            status: false,
            file: null,
            percent: 0
        };
        $scope.uploadQueue = [];

        $scope.options = $scope.options || {
            target: null,
            append: false,
            unique: false,
            onFinish: function () {}
        };

        var canceller = $q.defer();

        $scope.browse = function () {
            var selector = $("<input>");
            selector.attr("type", "file");
            selector.attr("accept", "audio/*");
            selector.attr("multiple", "multiple");
            selector.attr("name", "file");
            selector.on("change", function () {
                if (this.files.length == 0) return;
                var that = this;
                $scope.$applyAsync(function () {
                    for (var i = 0; i < that.files.length; i++) {
                        $scope.uploadQueue.push(that.files[i]);
                    }
                });
            });
            selector.click();
        };

        $scope.cancel = function () {
            $scope.options.onFinish.call();
            $scope.closeThisDialog();
        };

        $scope.$on("$destroy", function () {
            canceller.resolve("Upload aborted by user");
        });

        $scope.upload = function () {
            if ($scope.uploadQueue.length == 0) {
                $scope.cancel();
                return;
            }
            var file = $scope.uploadQueue.shift();
            var form = new FormData();
            form.append('file', file);

            if ($scope.options.target)
                form.append("stream_id", $scope.options.target);

            if ($scope.options.unique)
                form.append("skip_copies", 1);

            $scope.progress.status = true;
            $scope.progress.file = file.name;

            var uploader = Response($http({
                method: "POST",
                url: "/api/v2/track/upload",
                data: form,
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined},
                timeout: canceller.promise
            }));

            uploader.onSuccess(function (data) {
                var i;
                if ($scope.options.append === true) {
                    for (i = 0; i < data.tracks.length; i++) {
                        $scope.$parent.tracks.push(data.tracks[i]);
                        $rootScope.account.user.tracks_count += 1;
                    }
                } else {
                    for (i = data.tracks.length - 1; i >= 0; i--) {
                        $scope.$parent.tracks.unshift(data.tracks[i]);
                        $rootScope.account.user.tracks_count += 1;
                    }
                }
                $scope.upload();
            }, function (message) {
                Popup.message(message);
                $scope.upload();
            });
        };

    }

    ]);

    lib.factory("TracksScopeActions", [function () {
        return {
            removeTracksFromStream: function($stream, $tracks, $callback) {
                TrackPreviewService.stop();

            }
        }
    }]);

    lib.factory("StreamWorks", ["$http", "Response", function ($http, Response) {
        return {
            getMyStreams: function () {
                var result = $http({
                    method: "GET",
                    url: "/api/v2/streams/getStreamsByUser"
                });
                return Response(result);
            },
            deleteTracks: function (streamId, track_id) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/stream/removeTracks",
                    data: {
                        stream_id: streamId,
                        unique_ids: track_id
                    }
                });
                return Response(result);
            },
            addTracks: function (streamId, trackId) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/stream/addTracks",
                    data: {
                        stream_id: streamId,
                        tracks: trackId
                    }
                });
                return Response(result);
            },
            shuffle: function (streamId) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/control/shuffle",
                    busy: true,
                    data: {
                        stream_id: streamId
                    }
                });
                return Response(result);
            },
            sort: function (streamId, uniqueId, index) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/stream/moveTrack",
                    data: {
                        stream_id: streamId,
                        unique_id: uniqueId,
                        new_index: index
                    }
                });
                return Response(result);
            },
            startStream: function (object) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/control/play",
                    data: {
                        stream_id: object.sid
                    }
                });
                return Response(result);
            },
            stopStream: function (object) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/control/stop",
                    data: {
                        stream_id: object.sid
                    }
                });
                return Response(result);
            },
            play: function (unique_id, object) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/control/setCurrentTrack",
                    data: {
                        stream_id: object.sid,
                        unique_id: unique_id
                    }
                });
                return Response(result);
            }
        }
    }]);

    lib.factory("TrackWorks", ["$http", "Response", function ($http, Response) {
        return {
            getAllTracks: function (offset, filter, unused, row, order, busy) {
                var result = $http({
                    method: "GET",
                    url: "/api/v2/tracks/getAll",
                    busy: busy || false,
                    params: {
                        offset: offset,
                        filter: filter,
                        unused: unused ? 1 : 0,
                        row: row,
                        order: order
                    }
                });
                return Response(result);
            },
            getByStreamID: function (stream_id, offset, filter, color_id) {
                var result = $http({
                    method: "GET",
                    url: "/api/v2/tracks/getByStream",
                    busy: false,
                    params: {
                        stream_id: stream_id,
                        color_id: color_id || "",
                        offset: offset,
                        filter: filter
                    }
                });
                return Response(result);
            },
            getTrackDetails: function (track_id) {
                var result = $http({
                    method: "GET",
                    url: "/api/v2/tracks/getTrackDetails",
                    params: {
                        track_id: track_id
                    }
                });
                return Response(result);
            },
            updateTrackInfo: function (track) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/track/edit",
                    data: {
                        track_id: track.tid,
                        artist: track.artist,
                        title: track.title,
                        album: track.album,
                        track_number: track.track_number,
                        genre: track.genre,
                        date: track.date,
                        color_id: track.color
                    }
                });
                return Response(result);
            },
            updateColor: function (tracks, colorId) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/track/changeColor",
                    data: {
                        track_id: tracks,
                        color_id: colorId
                    }
                });
                return Response(result);
            },
            deleteTracks: function (track_id) {
                var result = $http({
                    method: "POST",
                    url: "/api/v2/track/delete",
                    data: {
                        track_id: track_id
                    }
                });
                return Response(result);
            }
        };
    }]);

})();

(function () {

    angular.module("AudioInfo", ["Site", "ngDialog"])

        .factory("AudioInfoEditor", ["$rootScope", "$http", "Response", "ngDialog",

            function ($rootScope, $http, Response, ngDialog) {

                return {
                    save: function (metadata) {
                        return Response($http({
                            method: "POST",
                            url: "/api/v2/track/edit",
                            data: metadata
                        }))
                    },
                    show: function (source, $scope) {
                        var scope = $scope.$new();
                        scope.source = source;
                        ngDialog.open({
                            templateUrl: "/views/auth/metadata.html",
                            controller: "TrackInfoController",
                            scope: scope,
                            showClose: false
                        });
                    }
                }

            }

        ])

        .controller("TrackInfoController", ["$scope", "AudioInfoEditor", "$dialog",

            function ($scope, AudioInfoEditor, $dialog) {

                var tracks = $scope.source.map(function (o) { return o.tid; }).join(",");

                $scope.metadata = {
                    title:          "",     saveTitle: true,
                    artist:         "",     saveArtist: true,
                    album:          "",     saveAlbum: true,
                    trackNumber:    "",     saveTrackNumber: true,
                    genre:          "",     saveGenre: true,
                    date:           "",     saveDate: true,
                    colorId:        "",     saveColorId: true,
                    cue:            "",     saveCue: true,
                    buy:            "",     saveBuy: true
                };

                $scope.save = function () {

                    var data = {
                        track_id: tracks
                    };

                    if ($scope.metadata.saveTitle)
                        data.title = $scope.metadata.title;
                    if ($scope.metadata.saveArtist)
                        data.artist = $scope.metadata.artist;
                    if ($scope.metadata.saveAlbum)
                        data.album = $scope.metadata.album;
                    if ($scope.metadata.saveTrackNumber)
                        data.track_number = $scope.metadata.trackNumber;
                    if ($scope.metadata.saveGenre)
                        data.genre = $scope.metadata.genre;
                    if ($scope.metadata.saveDate)
                        data.date = $scope.metadata.date;
                    if ($scope.metadata.saveColorId)
                        data.color_id = $scope.metadata.colorId;
                    if ($scope.metadata.saveCue)
                        data.cue = $scope.metadata.cue;
                    if ($scope.metadata.saveBuy)
                        data.buy = $scope.metadata.buy;

                    AudioInfoEditor.save(data).onSuccess(function () {
                        for (var i = 0, length = $scope.source.length; i < length; i += 1) {
                            if ($scope.metadata.saveTitle)
                                $scope.source[i].title = $scope.metadata.title;
                            if ($scope.metadata.saveArtist)
                                $scope.source[i].artist = $scope.metadata.artist;
                            if ($scope.metadata.saveAlbum)
                                $scope.source[i].album = $scope.metadata.album;
                            if ($scope.metadata.saveTrackNumber)
                                $scope.source[i].track_number = $scope.metadata.trackNumber;
                            if ($scope.metadata.saveGenre)
                                $scope.source[i].genre = $scope.metadata.genre;
                            if ($scope.metadata.saveDate)
                                $scope.source[i].date = $scope.metadata.date;
                            if ($scope.metadata.saveColorId)
                                $scope.source[i].color_id = $scope.metadata.colorId;
                            if ($scope.metadata.saveCue)
                                $scope.source[i].cue = $scope.metadata.cue;
                            if ($scope.metadata.saveBuy)
                                $scope.source[i].buy = $scope.metadata.buy;
                        }
                        $scope.closeThisDialog();
                    }, function (error) {
                        $dialog.info(error);
                    });

                };

                for (var i = 0, length = $scope.source.length; i < length; i += 1) {
                    if (i == 0) {
                        $scope.metadata.title       = $scope.source[i].title;
                        $scope.metadata.artist      = $scope.source[i].artist;
                        $scope.metadata.album       = $scope.source[i].album;
                        $scope.metadata.trackNumber = $scope.source[i].track_number;
                        $scope.metadata.genre       = $scope.source[i].genre;
                        $scope.metadata.date        = $scope.source[i].date;
                        $scope.metadata.colorId     = $scope.source[i].color;
                        $scope.metadata.cue         = $scope.source[i].cue;
                        $scope.metadata.buy         = $scope.source[i].buy;
                    } else {
                        if ($scope.metadata.title != $scope.source[i].title) {
                            $scope.metadata.saveTitle = false;
                            $scope.metadata.title = ""
                        }
                        if ($scope.metadata.artist != $scope.source[i].artist) {
                            $scope.metadata.saveArtist = false;
                            $scope.metadata.artist = ""
                        }
                        if ($scope.metadata.album != $scope.source[i].album) {
                            $scope.metadata.saveAlbum = false;
                            $scope.metadata.album = ""
                        }
                        if ($scope.metadata.trackNumber != $scope.source[i].track_number) {
                            $scope.metadata.saveTrackNumber = false;
                            $scope.metadata.trackNumber = ""
                        }
                        if ($scope.metadata.genre != $scope.source[i].genre) {
                            $scope.metadata.saveGenre = false;
                            $scope.metadata.genre = ""
                        }
                        if ($scope.metadata.date != $scope.source[i].date) {
                            $scope.metadata.saveDate = false;
                            $scope.metadata.date = ""
                        }
                        if ($scope.metadata.colorId != $scope.source[i].color) {
                            $scope.metadata.saveColorId = false;
                            $scope.metadata.colorId = ""
                        }
                        if ($scope.metadata.cue != $scope.source[i].cue) {
                            $scope.metadata.saveCue = false;
                            $scope.metadata.cue = ""
                        }
                        if ($scope.metadata.buy != $scope.source[i].buy) {
                            $scope.metadata.saveBuy = false;
                            $scope.metadata.buy = ""
                        }
                    }
                }

            }
        ])

})();

(function () {
    angular.module("mor-loader", ["Site"])
        .config([
            '$httpProvider',
            function ($httpProvider) {

                var interceptor = [
                    '$q',
                    '$rootScope',
                    '$timeout',
                    function ($q, $rootScope, $timeout) {

                        var requestsTotal = 0,
                            requestsCompleted = 0;

                        var timeoutHandle;

                        var action = {
                            startBusy: function () {
                                $timeout.cancel(timeoutHandle);
                                timeoutHandle = $timeout(function () {
                                    $rootScope.loader.busy = true;
                                }, 200);
                            },
                            completeBusy: function () {
                                $timeout.cancel(timeoutHandle);
                                $rootScope.loader.busy = false;
                            }
                        };


                        return {
                            'request': function(config) {
                                if (config.busy === true) {
                                    requestsTotal ++;
                                }
                                if (requestsCompleted < requestsTotal) {
                                    action.startBusy();
                                }
                                return config;
                            },

                            'response': function(response) {
                                if (response.config.busy === true) {
                                    requestsCompleted ++;
                                }
                                if (requestsCompleted == requestsTotal) {
                                    action.completeBusy();
                                }
                                return response;
                            },

                            'responseError': function(rejection) {
                                if (rejection.config.busy === true) {
                                    requestsCompleted ++;
                                }
                                if (requestsCompleted == requestsTotal) {
                                    action.completeBusy();
                                }
                                return $q.reject(rejection);
                            }
                        };
                    }
                ];
                $httpProvider.interceptors.push(interceptor);
            }
        ])
        .run([
            '$rootScope',
            function ($rootScope) {
                $rootScope.loader = { busy: false }
            }
        ]);
})();

(function () {
angular.module("Dialogs", [])
    .factory("TrackAction", [
        "TrackWorks",
        "StreamWorks",
        "Streams",
        "$dialog",
        "Popup",
        "$rootScope",
        function (TrackWorks, StreamWorks, Streams, $dialog, Popup, $rootScope) {

            var getFileName = function (array) {
                return array.length == 1 ?
                    "track <b>" + array[0].filename + "</b>" :
                    "<b>" + array.length.toString() + " track(s)</b>";
            };

            return {
                deleteStream: function ($stream) {
                    $dialog.question("Are you sure want to delete radio channel <b>" + $stream.name + "</b>?", function () {
                        Streams.deleteStream($stream).onSuccess(function () {
                            $rootScope.account.init("/profile/streams/");
                        });
                    });
                },
                removeTracksFromStream: function (streamObject, tracksArray, successCallback) {
                    $dialog.question("Delete " + getFileName(tracksArray) + " from stream?", function () {
                        var trackIds = tracksArray.map(function (track) { return track.unique_id }).join(",");
                        StreamWorks.deleteTracks(streamObject.sid, trackIds).onSuccess(function () {
                            if (typeof successCallback == "function") {
                                successCallback.call();
                            }
                        });
                    }, function (message) {
                        Popup.message(message);
                    });
                },
                removeTracksFromAccount: function (tracksArray, successCallback) {
                    $dialog.question("Delete " + getFileName(tracksArray) +  " from your account?", function () {
                        var trackIds = tracksArray.map(function (track) { return track.tid; }).join(",");
                        TrackWorks.deleteTracks(trackIds).onSuccess(function () {
                            if (typeof successCallback == "function") {
                                successCallback.call();
                            }
                        });
                    }, function (message) {
                        Popup.message(message);
                    });
                },
                addTracksToStream: function (streamObject, tracksArray, successCallback) {
                    var trackIds = tracksArray.map(function (track) { return track.tid; }).join(",");
                    StreamWorks.addTracks(streamObject.sid, trackIds).onSuccess(function () {
                        if (typeof successCallback == "function") {
                            successCallback.call();
                        }
                    }, function (message) {
                        Popup.message(message);
                    });
                },
                changeTracksColor: function (colorObject, tracksArray, successCallback) {
                    var trackIds = tracksArray.map(function (track) { return track.tid; }).join(",");
                    TrackWorks.updateColor(trackIds, colorObject.color_id).onSuccess(function () {
                        if (typeof successCallback == "function") {
                            successCallback.call();
                        }
                    }, function (message) {
                        Popup.message(message);
                    });
                }
            };
        }
    ]);
})();


(function () {

    var POPUP_TIMEOUT_DEFAULT = 5000;

    angular.module("mor-popup", [])

        .directive("morPopupFrame", ["$timeout", function ($timeout) {

            var timerHandle;

            return {
                restrict: 'E',
                templateUrl: "/views/blocks/popup.html",
                replace: true,
                scope: {
                    morPopupText: "@",
                    hideAfter: "="
                },
                link: function (scope, element) {
                    element.css('left', '400px').animate({left: '0'}, 200);

                    if (scope.hideAfter) {
                        timerHandle = $timeout(function () {
                            element.animate({left: '400px'}, 400, function () {
                                $(this).remove();
                            });
                        }, scope.hideAfter)
                    }
                    // Listen for scope destroy event
                    scope.$on("$destroy", function () {
                        $timeout.cancel(timerHandle);
                    });

                }
            }

        }])

        .directive("bindHtml", [function () {
            return {
                restrict: "A",
                scope: {
                    bindHtml: "="
                },
                link: function ($scope, $element, $attrs) {
                    $scope.$watch("bindHtml", function (newValue) {
                        $element.html(newValue);
                    })
                }
            }
        }])

        .factory("Popup", ["$body", "$compile", "$rootScope", function ($body, $compile, $rootScope) {

            var popupBackgroundElement = angular.element('<div class="popup-background"></div>').prependTo($body);

            return {
                message: function (message, timeout) {
                    var elem = angular.element("<mor-popup-frame>");
                    elem.attr("mor-popup-text", message);
                    if (timeout) {
                        elem.attr("hide-after", timeout);
                    } else {
                        elem.attr("hide-after", POPUP_TIMEOUT_DEFAULT);
                    }
                    elem.on("click", function () {
                        elem.remove();
                    });
                    $compile(elem)($rootScope);
                    elem.appendTo(popupBackgroundElement);
                }
            }
        }])

})();

/**
 * Created by Roman on 05.03.2015.
 */

(function () {

    var DEFAULT_INTERVAL = 5000,

        scheduler;

    scheduler = angular.module("mor.stream.scheduler", ["Site"]);

    scheduler.run(["$rootScope", function ($rootScope) {
        $rootScope.callOrSet = function (key, value, context) {
            if (angular.isUndefined(context[key])) {
                return false;
            }
            $rootScope.$applyAsync(function () {
                if (typeof context[key] === "function") {
                    context[key].call(this, value);
                } else {
                    context[key] = value;
                }
            });
        }
    }]);

    scheduler.factory("scheduler.rest", ["$http", "Response", function ($http, Response) {
        return {
            getNowPlaying: function (stream) {
                var action = $http({
                    method: "GET",
                    ignoreLoadingBar: true,
                    url: "/api/v2/streams/getNowPlaying",
                    params: {
                        stream_id: stream.sid
                    }
                });
                return Response(action);
            },
            getSchedule: function (stream) {
                var action = $http({
                    method: "GET",
                    ignoreLoadingBar: true,
                    url: "/api/v2/streams/getSchedule",
                    params: {
                        stream_id: stream.sid
                    }
                });
                return Response(action);
            }
        }
    }]);

    scheduler.directive("now", [function () {
        return {
            require: "ngModel",
            restrict: "AE",
            scope: {
                ngModel: "=",
                onInterval: "=",
                onTrackChange: "="
            },
            controller: [
                "$scope",
                "$timeout",
                "scheduler.rest",
                function ($scope, $timeout, rest) {
                    var delay,
                        prevUniqueId,
                        update = function () {

                            $timeout.cancel(delay);

                            if ($scope.ngModel.sid === undefined) {
                                return
                            }

                            rest.getNowPlaying($scope.ngModel).onSuccess(
                                function (response) {
                                    $scope.$root.callOrSet("onInterval", response, $scope);
                                    if (prevUniqueId !== response.current.unique_id) {
                                        prevUniqueId = response.current.unique_id;
                                        $scope.$root.callOrSet("onTrackChange", response.current, $scope);
                                    }
                                    var end = response.current.duration + response.current.time_offset - response.position;
                                    delay = $timeout(update, Math.min(DEFAULT_INTERVAL, end))
                                }, function () {
                                    $scope.$root.callOrSet("onInterval", undefined, $scope);
                                    if (prevUniqueId !== undefined) {
                                        prevUniqueId = undefined;
                                        $scope.$root.callOrSet("onTrackChange", undefined, $scope);
                                    }
                                    delay = $timeout(update, DEFAULT_INTERVAL)
                                }
                            )
                        },
                        stop = function () {

                            $timeout.cancel(delay);

                            if (prevUniqueId !== undefined) {
                                $scope.$root.callOrSet("onTrackChange", undefined, $scope);
                                prevUniqueId = undefined;
                            }

                            $scope.$root.callOrSet("onInterval", undefined, $scope);

                        };

                    $scope.$watch("ngModel", function () {

                        (($scope.ngModel && $scope.ngModel.sid) ? update : stop)();

                    });

                    $scope.$on("$destroy", function () {

                        $timeout.cancel(delay)

                    });

                }
            ]
        }
    }]);

    scheduler.directive("schedule", [function () {
        return {
            require: "ngModel",
            restrict: "AE",
            scope: {
                ngModel: "=",
                onInterval: "=",
                onTrackUpdate: "="
            },
            link: function ($scope, $element, $attributes) {
                //$scope.onInterval = $compile($attributes.onInterval)($scope);
                //$scope.onTrackChange = $compile($attributes.onTrackChange)($scope);
                //console.log($scope.onInterval);
            },
            controller: [
                "$scope",
                "$timeout",
                "scheduler.rest",
                function ($scope, $timeout, rest) {
                    var delay,
                        previousUniqueId,
                        update = function () {
                            if (angular.isUndefined($scope.ngModel.sid)) {
                                return false;
                            }

                            rest.getSchedule($scope.ngModel).onSuccess(
                                function (response) {
                                    var currentTrack = response.tracks[response.current];
                                    $scope.$root.callOrSet("onInterval", response, $scope);

                                    if (previousUniqueId != currentTrack.unique_id) {
                                        $scope.$root.callOrSet("onTrackChange", response, $scope);
                                        previousUniqueId = currentTrack.unique_id;
                                    }

                                    var end = currentTrack.duration + currentTrack.time_offset - response.position;
                                    delay = $timeout(update, Math.min(DEFAULT_INTERVAL, end))
                                }, function () {
                                    $scope.$root.callOrSet("onInterval", null, $scope);

                                    if (previousUniqueId != null) {
                                        $scope.$root.callOrSet("onTrackChange", null, $scope);
                                        previousUniqueId = null;
                                    }

                                    delay = $timeout(update, DEFAULT_INTERVAL)
                                }
                            )
                        },
                        stop = function () {
                            $timeout.cancel(delay);
                            if (previousUniqueId != null) {
                                $scope.$root.callOrSet("onTrackChange", null, $scope);
                                previousUniqueId = null;
                            }
                            $scope.$root.callOrSet("onInterval", null, $scope);
                        };

                    $scope.$watch("ngModel", function (value) {

                        (value ? update : stop)();

                    });

                    $scope.$on("$destroy", function () {

                        $timeout.cancel(delay)

                    });

                }
            ]
        }
    }]);

})();

/*
 MOR Tooltip plugin v0.1
 */

(function () {

    var FAST_TOOLTIP_DELAY = 50;

    $(document).ready(function () {
        var tipTemplate = $("<div>")
                .addClass("mortip")
                .append($("<div>").addClass("corner"))
                .append($("<div>").addClass("content"))
                .prependTo("body"),
            tipDelay = null,
            hideOn = new Date().getTime(),
            showTip = function ($target, contents, delay, raw) {

                getRealWidth($target);

                var targetW = $target.outerWidth(),
                    targetH = $target.outerHeight(),
                    targetX1 = $target.offset().left,
                    targetY1 = $target.offset().top,
                    targetY2 = $target.offset().top + targetH,
                    scrollTop = $(window).scrollTop(),
                    windowHeight = $(window).height(),
                    documentWidth = $(document).width();

                if (raw === true) {
                    tipTemplate.find(".content").html(contents);
                } else {
                    tipTemplate.find(".content").text(contents);
                }


                var width = tipTemplate.outerWidth(true),
                    newLeft = targetX1 + (targetW / 2) - (width / 2),
                    leftShift = Math.max(0, newLeft + width - documentWidth),
                    rightShift = Math.max(0, -newLeft);

                tipTemplate.css("left", newLeft - leftShift + rightShift);

                if (targetY1 + (targetH / 2) - scrollTop > (windowHeight / 2)) {
                    tipTemplate.css("top", (targetY1 - tipTemplate.outerHeight(true) - 8).toString().concat("px"));
                    tipTemplate.addClass("top");
                } else {
                    tipTemplate.css("top", targetY2.toString().concat("px"));
                    tipTemplate.addClass("bottom");
                }

                tipTemplate.find(".corner").css({left: (width / 2 + leftShift - rightShift).toString().concat("px")});

                showSlow(delay || 500);

            },
            getRealWidth = function (element) {


            },
            showFast = function () {
                tipTemplate.addClass("visible");
                tipTemplate.css({opacity: 1});
            },
            showSlow = function (delay) {
                resetDelay();
                tipDelay = window.setTimeout(function () {
                    tipTemplate.addClass("visible");
                    tipTemplate.animate({opacity: 1}, 250);
                }, delay);
            },
            resetDelay = function () {
                if (tipDelay !== null) {
                    window.clearInterval(tipDelay);
                    tipDelay = null;
                }
            },
            hideTip = function () {
                hideOn = new Date().getTime();
                resetDelay();
                tipTemplate
                    .stop()
                    .removeClass("visible top bottom")
                    .css({
                        top: "",
                        left: "",
                        bottom: "",
                        right: "",
                        opacity: 0
                    });
            },
            initPlugin = function () {

                var ajaxHandle = null,
                    timerHandle = null,
                    stop = function () {
                        if(ajaxHandle && ajaxHandle.readystate != 4){
                            ajaxHandle.abort();
                        }
                        if(timerHandle) {
                            window.clearInterval(timerHandle);
                        }
                    };

                $("[mor-tooltip]").livequery(function () {

                    var $this = $(this);

                    $this.on("mouseover", function () {
                        showTip($this, $this.attr("mor-tooltip"), undefined, false);
                    }).on("mouseleave click", function () {
                        hideTip();
                    });


                });

                $("[mor-tooltip-url]").livequery(function () {

                    var $this = $(this);

                    $this.on("mouseover", function () {

                        stop();
                        timerHandle = window.setTimeout(function () {
                            ajaxHandle = $.get($this.attr("mor-tooltip-url"));
                            ajaxHandle.then(function (data) {
                                showTip($this, data, 0, true);
                            });
                        }, 250);

                    }).on("mouseleave click", function () {
                        stop();
                        hideTip()
                    });

                });

            };

        initPlugin();

    });

})();

