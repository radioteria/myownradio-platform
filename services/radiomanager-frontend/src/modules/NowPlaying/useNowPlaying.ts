import { useContext } from 'react'
import { NowPlayingContext } from './NowPlaying'

export const useNowPlaying = () => {
  const ctx = useContext(NowPlayingContext)

  if (!ctx) {
    throw new Error('Now playing data has not been found in the context')
  }

  return ctx
}
