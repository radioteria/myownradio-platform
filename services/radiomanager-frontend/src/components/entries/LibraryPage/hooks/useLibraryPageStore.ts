import { useCallback, useEffect, useState } from 'react'
import { LibraryTrackEntry, toLibraryTrackEntry } from '@/components/LibraryTracksList'
import {
  deleteTracksById,
  getLibraryTracks,
  getUnusedLibraryTracks,
  MAX_TRACKS_PER_REQUEST,
} from '@/api'
import { useHandleLibraryLastUploadedTrack } from './useHandleLibraryLastUploadedTrack'

import type { UserTrack } from '@/api'

interface StoreConfig {
  readonly filterUnusedTracks?: boolean
}

export const useLibraryPageStore = (
  initialUserTracks: readonly UserTrack[],
  config?: StoreConfig,
) => {
  const [trackEntries, setTrackEntries] = useState<readonly LibraryTrackEntry[]>(() =>
    initialUserTracks.map(toLibraryTrackEntry),
  )

  const addTrackEntry = useCallback((track: UserTrack) => {
    setTrackEntries((entries) => [toLibraryTrackEntry(track), ...entries])
  }, [])

  const [isFetching, setIsFetching] = useState(true)
  useEffect(() => {
    if (!isFetching) return

    const abortController = new AbortController()

    const promise = config?.filterUnusedTracks
      ? getUnusedLibraryTracks({
          offset: trackEntries.length,
          signal: abortController.signal,
        })
      : getLibraryTracks({
          offset: trackEntries.length,
          signal: abortController.signal,
        })

    promise.then((tracks) => {
      const newEntries = tracks.map(toLibraryTrackEntry)
      setTrackEntries((entries) => [...entries, ...newEntries])
      setIsFetching(newEntries.length === MAX_TRACKS_PER_REQUEST)
    })

    return () => {
      abortController.abort()
    }
  }, [isFetching, trackEntries])

  useHandleLibraryLastUploadedTrack(addTrackEntry, config?.filterUnusedTracks ?? false)

  const handleDeletingTracks = (trackIds: readonly number[]) => {
    const idsSet = new Set(trackIds)
    const updatedTrackEntries = trackEntries.filter(({ trackId }) => !idsSet.has(trackId))

    setTrackEntries(updatedTrackEntries)

    deleteTracksById(trackIds).catch((error) => {
      // Restore tracks after unsuccessful delete
      setTrackEntries(trackEntries)
    })
  }

  return {
    trackEntries,
    handleDeletingTracks,
  }
}
