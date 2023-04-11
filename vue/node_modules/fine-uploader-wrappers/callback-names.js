'use strict';

Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.s3 = exports.traditional = exports.thenable = undefined;

var _objectAssign = require('object-assign');

var _objectAssign2 = _interopRequireDefault(_objectAssign);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var thenable = exports.thenable = ['onCancel', 'onPasteReceived', 'onSubmit', 'onSubmitDelete', 'onUpload', 'onUploadChunk', 'onValidate', 'onValidateBatch'];

var traditional = exports.traditional = ['onAutoRetry', 'onCancel', 'onComplete', 'onAllComplete', 'onDelete', 'onDeleteComplete', 'onError', 'onManualRetry', 'onPasteReceived', 'onProgress', 'onResume', 'onSessionRequestComplete', 'onStatusChange', 'onSubmit', 'onSubmitDelete', 'onSubmitted', 'onTotalProgress', 'onUpload', 'onUploadChunk', 'onUploadChunkSuccess', 'onValidate', 'onValidateBatch'];

var s3 = exports.s3 = (0, _objectAssign2.default)([], traditional, ['onCredentialsExpired']);