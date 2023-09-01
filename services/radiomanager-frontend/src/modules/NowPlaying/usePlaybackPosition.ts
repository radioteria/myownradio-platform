import { useEffect, useState } from 'react'
import { useNowPlaying } from './useNowPlaying'
import { Duration } from '@/utils/duration'

/**
 * Hook to track the playback position of the current track.
 *
 * @param interval - Interval time in milliseconds for updating the running time. Default is 1000 ms.
 * @returns runningTime - The current playback position.
 */
export const usePlaybackPosition = (interval: number = 1_000): Duration | null => {
  // Using the custom hook to get information about the currently playing track
  const { nowPlaying, updatedAt } = useNowPlaying()
  const timeSince = Duration.fromMillis(new Date().getTime() - updatedAt.getTime())

  // Extracting the offset time of the current track, if available
  const currentTrackOffset = nowPlaying ? Duration.fromMillis(nowPlaying.currentTrack.offset) : null

  // State to hold the current running time of the track
  const [runningTime, setRunningTime] = useState(currentTrackOffset?.add(timeSince) ?? null)

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

    const updateRunningDelay = () => {
      // Calculate the time elapsed since the effect started
      const runningDelay = Duration.fromMillis(Date.now() - updatedAt.getTime())

      // Update runningTime based on the current track's offset and the elapsed time
      setRunningTime(currentTrackOffset.add(runningDelay))
    }

    // Set up an interval to update runningTime
    const intervalId = window.setInterval(updateRunningDelay, interval)

    // Cleanup: Clear the interval when the component unmounts or when the effect re-runs
    return () => {
      window.clearInterval(intervalId)
    }
  }, [currentTrackOffset, interval])

  return runningTime
}
