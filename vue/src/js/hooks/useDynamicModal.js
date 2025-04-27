/**
 * Composition‐API hook
 */
export default function useDynamicModal() {
  const service = inject('modalService')

  if (!service) {
    throw new Error('[ModalService] not installed. Did you forget `app.use(ModalService)`?')
  }

  return service
}
