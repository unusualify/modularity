import qq from 'fine-uploader/lib/dnd'
import sanitizeFilename from '@/utils/sanitizeFilename.js'

/**
 * Extract a public image URL from common CMS / CKEditor upload responses.
 *
 * @param {import('axios').AxiosResponse|{ data?: Record<string, unknown> }} response
 * @returns {string|null}
 */
export function resolveUploadedImageUrl (response) {
  const data = response?.data ?? response

  if (!data || typeof data !== 'object') {
    return null
  }

  const media = data.media

  if (media && typeof media === 'object') {
    return media.original ?? media.medium ?? media.thumbnail ?? null
  }

  return data.url ?? data.default ?? data.location ?? null
}

/**
 * Read image dimensions before upload (required by the media library store endpoint).
 *
 * @param {File} file
 * @returns {Promise<{ width: number, height: number }|null>}
 */
export function readImageDimensions (file) {
  return new Promise((resolve) => {
    const objectUrl = URL.createObjectURL(file)
    const img = new Image()

    img.onload = () => {
      URL.revokeObjectURL(objectUrl)
      resolve({ width: img.width, height: img.height })
    }

    img.onerror = () => {
      URL.revokeObjectURL(objectUrl)
      resolve(null)
    }

    img.src = objectUrl
  })
}

/**
 * Upload an editor image through the Modularous media library endpoint.
 *
 * Uses the same field names as fine-uploader (`qqfile`, `qqfilename`, `unique_folder_name`).
 *
 * @param {File} file
 * @param {string} uploadUrl
 * @param {Object} [options]
 * @param {string|null} [options.uniqueFolderName]
 * @returns {Promise<string>}
 */
export async function uploadEditorImage (file, uploadUrl, { uniqueFolderName = null } = {}) {
  const folderName = uniqueFolderName ?? qq.getUniqueId()
  const filename = sanitizeFilename(file.name)
  const formData = new FormData()

  formData.append('qqfile', file)
  formData.append('qqfilename', filename)
  formData.append('unique_folder_name', folderName)

  const dimensions = await readImageDimensions(file)

  if (dimensions) {
    formData.append('width', String(dimensions.width))
    formData.append('height', String(dimensions.height))
  }

  const response = await window.axios.post(uploadUrl, formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
      'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
  })

  const url = resolveUploadedImageUrl(response)

  if (!url) {
    throw new Error('Upload response did not include an image URL.')
  }

  return url
}

/**
 * Create a stable upload folder id for multiple images in one editing session.
 *
 * @returns {string}
 */
export function createEditorUploadFolderName () {
  return qq.getUniqueId()
}
