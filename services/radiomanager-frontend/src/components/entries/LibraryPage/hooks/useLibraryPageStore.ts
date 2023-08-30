import { useCallback, useMemo, useState } from 'react'
import { toLibraryTrackEntry } from '@/components/LibraryTracksList/LibraryTracksList'
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
  const initialTrackEntries = useMemo(
    () => initialUserTracks.map(toLibraryTrackEntry),
    [initialUserTracks],
  )
  const [trackEntries, setTrackEntries] = useState(initialTrackEntries)

  const addTrackEntry = useCallback((track: UserTrack) => {
    setTrackEntries((entries) => [toLibraryTrackEntry(track), ...entries])
  }, [])

  useHandleLibraryLastUploadedTrack(addTrackEntry)

  const initialCanInfinitelyScroll = initialTrackEntries.length === MAX_TRACKS_PER_REQUEST
  const [canInfinitelyScroll, setCanInfinitelyScroll] = useState(initialCanInfinitelyScroll)
  const handleInfiniteScroll = () => {
    const promise = config?.filterUnusedTracks
      ? getUnusedLibraryTracks(trackEntries.length)
      : getLibraryTracks(trackEntries.length)

    promise.then((tracks) => {
      const newEntries = tracks.map(toLibraryTrackEntry)
      setTrackEntries((entries) => [...entries, ...newEntries])

      if (MAX_TRACKS_PER_REQUEST > newEntries.length) {
        setCanInfinitelyScroll(newEntries.length === MAX_TRACKS_PER_REQUEST)
      }
    })
  }

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
    canInfinitelyScroll,
    handleInfiniteScroll,
    handleDeletingTracks,
  }
}
