import { RefObject, useEffect } from 'react'

export function useClickOutside(ref: RefObject<HTMLElement>, onClickOutside: () => void): void {
  useEffect(() => {
    function handleClickOutside(event: MouseEvent): void {
      if (ref.current && !ref.current.contains(event.target as Node)) {
        onClickOutside()
      }
    }

    // Bind the event listener
    document.addEventListener('mousedown', handleClickOutside)

    return (): void => {
      // Unbind the event listener on clean up
      document.removeEventListener('mousedown', handleClickOutside)
    }
  }, [ref, onClickOutside])
}
