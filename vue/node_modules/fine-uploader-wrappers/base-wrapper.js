'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _objectAssign = require('object-assign');

var _objectAssign2 = _interopRequireDefault(_objectAssign);

var _callbackProxy = require('./callback-proxy');

var _callbackProxy2 = _interopRequireDefault(_callbackProxy);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var callbackProxies = new WeakMap();

var BaseWrapper = function () {
    function BaseWrapper(_ref) {
        var callbackNames = _ref.callbackNames,
            options = _ref.options,
            qq = _ref.qq,
            type = _ref.type;

        _classCallCheck(this, BaseWrapper);

        var callbacks = options.callbacks || {};

        var optionsSansCallbacks = (0, _objectAssign2.default)({}, options);
        delete optionsSansCallbacks.callbacks;
        this.options = optionsSansCallbacks;

        callbackProxies.set(this, createCallbackProxies(callbackNames));

        registerOptionsCallbacks({ callbacks: callbacks, callbackProxies: callbackProxies.get(this) });

        this.methods = createFineUploader({
            callbackProxies: callbackProxies.get(this),
            options: optionsSansCallbacks,
            qq: qq,
            type: type
        });

        this.qq = qq;
    }

    _createClass(BaseWrapper, [{
        key: 'off',
        value: function off(name, callback) {
            var normalizedName = normalizeCallbackName(name);
            var proxy = callbackProxies.get(this)[normalizedName];

            proxy.remove(callback);
        }
    }, {
        key: 'on',
        value: function on(name, callback) {
            var normalizedName = normalizeCallbackName(name);
            var proxy = callbackProxies.get(this)[normalizedName];

            proxy.add(callback);
        }
    }]);

    return BaseWrapper;
}();

exports.default = BaseWrapper;


var createCallbackProxies = function createCallbackProxies(names) {
    var proxyMap = {};

    names.forEach(function (callbackName) {
        proxyMap[callbackName] = new _callbackProxy2.default(callbackName);
    });

    return proxyMap;
};

var createFineUploader = function createFineUploader(_ref2) {
    var callbackProxies = _ref2.callbackProxies,
        options = _ref2.options,
        qq = _ref2.qq,
        type = _ref2.type;

    var optionsCopy = (0, _objectAssign2.default)({ callbacks: {} }, options);

    Object.keys(callbackProxies).forEach(function (callbackName) {
        var proxy = callbackProxies[callbackName];

        optionsCopy.callbacks[callbackName] = proxy.proxyFunction;
    });

    if (type === 'traditional') {
        return new qq.FineUploaderBasic(optionsCopy);
    } else {
        return new qq[type].FineUploaderBasic(optionsCopy);
    }
};

var normalizeCallbackName = function normalizeCallbackName(name) {
    if (!name.match(/^on[A-Z]/)) {
        return 'on' + name[0].toUpperCase() + name.slice(1);
    }

    return name;
};

var registerOptionsCallbacks = function registerOptionsCallbacks(_ref3) {
    var callbacks = _ref3.callbacks,
        callbackProxies = _ref3.callbackProxies;

    Object.keys(callbacks).forEach(function (callbackName) {
        var callbackProxy = callbackProxies[callbackName];

        callbackProxy.add(callbacks[callbackName]);
    });
};