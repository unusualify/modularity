'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _objectAssign = require('object-assign');

var _objectAssign2 = _interopRequireDefault(_objectAssign);

var _callbackNames = require('./callback-names');

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var privateData = new WeakMap();
var callbacks = new WeakMap();

var CallbackProxy = function () {
    function CallbackProxy(name) {
        _classCallCheck(this, CallbackProxy);

        callbacks.set(this, []);

        privateData.set(this, {
            name: name,
            proxyFunction: getProxyFunction.call(this, { name: name })
        });
    }

    _createClass(CallbackProxy, [{
        key: 'add',
        value: function add(callback) {
            callbacks.get(this).push(callback);
        }
    }, {
        key: 'remove',
        value: function remove(callback) {
            var index = callbacks.get(this).indexOf(callback);
            if (index >= 0) {
                callbacks.get(this).splice(index, 1);
            }
        }
    }, {
        key: 'name',
        get: function get() {
            return privateData.get(this).name;
        }
    }, {
        key: 'proxyFunction',
        get: function get() {
            return privateData.get(this).proxyFunction;
        }
    }]);

    return CallbackProxy;
}();

exports.default = CallbackProxy;


var getProxyFunction = function getProxyFunction(_ref) {
    var name = _ref.name;

    var proxyClassContext = this;

    return function () {
        for (var _len = arguments.length, originalCallbackArguments = Array(_len), _key = 0; _key < _len; _key++) {
            originalCallbackArguments[_key] = arguments[_key];
        }

        var isThenable = _callbackNames.thenable.indexOf(name) >= 0;
        var registeredCallbacks = callbacks.get(proxyClassContext);
        var callbackReturnValue = void 0;

        if (isThenable) {
            callbackReturnValue = executeThenableCallbacks({ registeredCallbacks: registeredCallbacks, originalCallbackArguments: originalCallbackArguments });
        } else {
            (0, _objectAssign2.default)([], registeredCallbacks).every(function (callback) {
                var returnValue = callback.apply(null, originalCallbackArguments);

                callbackReturnValue = returnValue;

                return returnValue !== false;
            });
        }

        return callbackReturnValue;
    };
};

var executeThenableCallbacks = function executeThenableCallbacks(_ref2) {
    var registeredCallbacks = _ref2.registeredCallbacks,
        originalCallbackArguments = _ref2.originalCallbackArguments;

    if (registeredCallbacks.length) {
        return executeThenableCallback({
            registeredCallbacks: (0, _objectAssign2.default)([], registeredCallbacks).reverse(),
            originalCallbackArguments: originalCallbackArguments
        });
    }

    return Promise.resolve();
};

var getResultToPass = function getResultToPass(_ref3) {
    var newResult = _ref3.newResult,
        previousResult = _ref3.previousResult;

    if (newResult !== null && (typeof newResult === 'undefined' ? 'undefined' : _typeof(newResult)) === 'object') {
        return (0, _objectAssign2.default)({}, previousResult || {}, newResult);
    } else {
        return newResult || previousResult;
    }
};

var executeThenableCallback = function executeThenableCallback(_ref4) {
    var registeredCallbacks = _ref4.registeredCallbacks,
        originalCallbackArguments = _ref4.originalCallbackArguments,
        previousResult = _ref4.previousResult;

    return new Promise(function (resolve, reject) {
        var callback = registeredCallbacks.pop();

        var returnValue = callback.apply(null, originalCallbackArguments);

        if (returnValue && returnValue.then) {
            returnValue.then(function (result) {
                var resultToPass = getResultToPass({ newResult: result, previousResult: previousResult });

                if (registeredCallbacks.length) {
                    executeThenableCallback({ registeredCallbacks: registeredCallbacks, originalCallbackArguments: originalCallbackArguments, previousResult: resultToPass }).then(resolve, reject);
                } else {
                    resolve(resultToPass);
                }
            }).catch(function (error) {
                return reject(error);
            });
        } else if (returnValue === false) {
            reject();
        } else {
            var resultToPass = getResultToPass({ newResult: returnValue, previousResult: previousResult });

            if (registeredCallbacks.length) {
                executeThenableCallback({ registeredCallbacks: registeredCallbacks, originalCallbackArguments: originalCallbackArguments, previousResult: resultToPass }).then(resolve, reject);
            } else {
                resolve(resultToPass);
            }
        }
    });
};