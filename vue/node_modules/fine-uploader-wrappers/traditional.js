'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _baseWrapper = require('./base-wrapper');

var _baseWrapper2 = _interopRequireDefault(_baseWrapper);

var _core = require('fine-uploader/lib/core');

var _core2 = _interopRequireDefault(_core);

var _callbackNames = require('./callback-names');

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var FineUploaderTraditional = function (_BaseWrapper) {
    _inherits(FineUploaderTraditional, _BaseWrapper);

    function FineUploaderTraditional(_ref) {
        var options = _ref.options;

        _classCallCheck(this, FineUploaderTraditional);

        return _possibleConstructorReturn(this, (FineUploaderTraditional.__proto__ || Object.getPrototypeOf(FineUploaderTraditional)).call(this, {
            callbackNames: _callbackNames.traditional,
            options: options,
            qq: _core2.default,
            type: 'traditional'
        }));
    }

    return FineUploaderTraditional;
}(_baseWrapper2.default);

exports.default = FineUploaderTraditional;