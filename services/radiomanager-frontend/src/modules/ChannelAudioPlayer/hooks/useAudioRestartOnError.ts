import { MutableRefObject, useEffect, useReducer } from 'react'
import makeDebug from 'debug'
import { Duration } from '@/utils/duration'

const debug = makeDebug('useAudioRestartOnError')

export const useAudioRestartOnError = (
  audioRef: MutableRefObject<HTMLAudioElement | null>,
  restartDelay = Duration.fromMillis(500),
) => {
  const [numRestarts, restart] = useReducer((n: number) => n + 1, 0)

  useEffect(() => {
    const audioElement = audioRef.current

    if (!audioElement) return

    let timeoutId: null | number = null

    const handleError = (ev: ErrorEvent) => {
      debug('Triggering restart: error happened on playing audio: %s', ev)

      window.setTimeout(() => restart(), restartDelay.toMillis())
    }

    audioElement.addEventListener('error', handleError)

    return () => {
      audioElement.removeEventListener('error', handleError)

      if (timeoutId) {
        window.clearTimeout(timeoutId)
        debug('Restart timer cancelled')
      }
    }
  }, [audioRef, restartDelay])

  return numRestarts
}
