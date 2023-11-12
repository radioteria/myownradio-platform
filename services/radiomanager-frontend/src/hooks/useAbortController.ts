import { useEffect, useMemo } from 'react'

export const useAbortController = () => {
  const abortController = useMemo(() => new AbortController(), [])

  useEffect(() => {
    return () => {
      abortController.abort()
    }
  }, [abortController])

  return abortController
}
