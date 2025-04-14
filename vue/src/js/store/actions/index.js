/* All the store actions are listed here */

/* Blocks */
export const MOVE_BLOCK_TO_EDITOR = 'moveBlockToEditor'
export const DUPLICATE_BLOCK = 'duplicateBlock'

/* Buckets */
export const GET_BUCKETS = 'getBucketsData'
export const SAVE_BUCKETS = 'saveBuckets'

/* Datatable */
export const GET_DATATABLE = 'getDatatableDatas'
export const SET_DATATABLE_NESTED = 'setDatatableNestedDatas'
export const SET_DATATABLE = 'setDatatableDatas'
export const TOGGLE_PUBLISH = 'togglePublishedData'
export const DELETE_ITEM = 'deleteData'
// export const DELETE_ITEM = 'deleteData'
export const DUPLICATE_ITEM = 'duplicateData'
export const RESTORE_ITEM = 'restoreData'
export const DESTROY_ITEM = 'destroyData'
export const TOGGLE_FEATURE = 'toggleFeaturedData'
export const BULK_PUBLISH = 'bulkPublishData'
export const BULK_FEATURE = 'bulkFeatureData'
export const BULK_EXPORT = 'bulkExportData'
export const BULK_DELETE = 'bulkDeleteData'
export const BULK_RESTORE = 'bulkRestoreData'
export const BULK_DESTROY = 'bulkDestroyData'

/* Form */
export const REPLACE_FORM = 'replaceFormData'
export const SAVE_FORM = 'saveFormData'
export const UPDATE_FORM_IN_LISTING = 'updateFormInListing'
export const CREATE_FORM_IN_MODAL = 'createFormInModal'

/* Alert */
export const SHOW_ALERT = 'showAlert'

/* Previews */
export const GET_ALL_PREVIEWS = 'getAllPreviews'
export const GET_PREVIEW = 'getPreview'

/* Revisions */
export const GET_REVISION = 'getRevisionContent'
export const GET_CURRENT = 'getCurrentContent'

/* Errors */
export const HANDLE_ERRORS = 'handleErrors'

export default {
  HANDLE_ERRORS,
  GET_BUCKETS,
  SAVE_BUCKETS,
  GET_DATATABLE,
  SET_DATATABLE_NESTED,
  SET_DATATABLE,
  TOGGLE_PUBLISH,
  DELETE_ITEM,
  // DELETE_ITEM,
  DUPLICATE_ITEM,
  RESTORE_ITEM,
  DESTROY_ITEM,
  TOGGLE_FEATURE,
  BULK_PUBLISH,
  BULK_FEATURE,
  BULK_EXPORT,
  BULK_DELETE,
  BULK_RESTORE,
  BULK_DESTROY,
  REPLACE_FORM,
  SAVE_FORM,
  UPDATE_FORM_IN_LISTING,
  CREATE_FORM_IN_MODAL,
  GET_ALL_PREVIEWS,
  GET_PREVIEW,
  GET_REVISION,
  GET_CURRENT,
  MOVE_BLOCK_TO_EDITOR,
  DUPLICATE_BLOCK,

  SHOW_ALERT,
}
