import { useCallback } from 'react'

export default function useFileSelect(
  accept: string,
  onSelect: (files: File[]) => void,
): () => void {
  return useCallback(() => {
    const input = document.createElement('input')
    input.setAttribute('multiple', '1')
    input.setAttribute('type', 'file')
    input.setAttribute('accept', accept)
    input.addEventListener('change', async (event: Event) => {
      if (event.target) {
        const eventTarget = event.target as HTMLInputElement
        if (eventTarget.files) {
          onSelect(Array.from(eventTarget.files))
        }
      }
    })
    input.click()
  }, [accept, onSelect])
}
