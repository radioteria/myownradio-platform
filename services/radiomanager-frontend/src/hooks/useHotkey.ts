import { RefObject, useEffect, useMemo } from 'react'

interface Options {
  altKey?: boolean
  ctrlKey?: boolean
  metaKey?: boolean
  shiftKey?: boolean
}

export const useHotkey = <T extends string>(
  ref: RefObject<HTMLElement>,
  key: T,
  cb: () => void,
  options?: Options,
) => {
  useEffect(() => {
    const current = ref.current

    if (!current) {
      return
    }

    function handleKeyPressed(event: KeyboardEvent) {
      if (event.key !== key) {
        return
      }

      if (
        (options?.altKey && !event.altKey) ||
        (options?.ctrlKey && !event.ctrlKey) ||
        (options?.metaKey && !event.metaKey) ||
        (options?.shiftKey && !event.shiftKey)
      ) {
        return
      }

      cb()

      event.preventDefault()
    }

    current.addEventListener('keydown', handleKeyPressed)

    return () => {
      current.removeEventListener('keydown', handleKeyPressed)
    }
  }, [ref, key, cb, options?.altKey, options?.metaKey, options?.shiftKey, options?.ctrlKey])
}
