import { useEffect, useState } from 'react'
import { useNowPlaying } from './useNowPlaying'

/**
 * Hook to track the playback position of the current track.
 *
 * @param {number} interval - Interval time in milliseconds for updating the running time. Default is 1000 ms.
 * @returns {number} runningTime - The current playback position in milliseconds.
 */
export const usePlaybackPosition = (interval = 1_000) => {
  // Using the custom hook to get information about the currently playing track
  const { nowPlaying } = useNowPlaying()

  // Extracting the offset time of the current track, if available
  const currentTrackOffset = nowPlaying?.currentTrack.offset ?? null

  // State to hold the current running time of the track
  const [runningTime, setRunningTime] = useState(currentTrackOffset)

  /**
   * Effect to update the runningTime based on the offset and elapsed time.
   * This effect runs whenever currentTrackOffset or interval changes.
   */
  useEffect(() => {
    // If there's no offset for the current track, reset runningTime and exit the effect
    if (!currentTrackOffset) {
      setRunningTime(null)
      return
    }

    // Capture the current time
    const now = Date.now()

    // Set up an interval to update runningTime
    const intervalId = window.setInterval(() => {
      // Calculate the time elapsed since the effect started
      const runningDelay = Date.now() - now

      // Update runningTime based on the current track's offset and the elapsed time
      setRunningTime(currentTrackOffset + runningDelay)
    }, interval)

    // Cleanup: Clear the interval when the component unmounts or when the effect re-runs
    return () => {
      window.clearInterval(intervalId)
    }
  }, [currentTrackOffset, interval])

  return runningTime
}
