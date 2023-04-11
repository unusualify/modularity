'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});

var _baseWrapper = require('./base-wrapper');

var _baseWrapper2 = _interopRequireDefault(_baseWrapper);

var _s = require('fine-uploader/lib/core/s3');

var _s2 = _interopRequireDefault(_s);

var _callbackNames = require('./callback-names');

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var FineUploaderS3 = function (_BaseWrapper) {
    _inherits(FineUploaderS3, _BaseWrapper);

    function FineUploaderS3(_ref) {
        var options = _ref.options;

        _classCallCheck(this, FineUploaderS3);

        return _possibleConstructorReturn(this, (FineUploaderS3.__proto__ || Object.getPrototypeOf(FineUploaderS3)).call(this, {
            callbackNames: _callbackNames.s3,
            options: options,
            qq: _s2.default,
            type: 's3'
        }));
    }

    return FineUploaderS3;
}(_baseWrapper2.default);

exports.default = FineUploaderS3;