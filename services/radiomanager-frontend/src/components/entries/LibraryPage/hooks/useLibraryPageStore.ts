import { useCallback, useMemo, useState } from 'react'
import { toLibraryTrackEntry } from '@/components/LibraryTracksList/LibraryTracksList'
import { UserTrack } from '@/api/api.types'
import { deleteTracksById, getLibraryTracks, MAX_TRACKS_PER_REQUEST } from '@/api/api.client'
import { useHandleLibraryLastUploadedTrack } from './useHandleLibraryLastUploadedTrack'

export const useLibraryPageStore = (initialUserTracks: readonly UserTrack[]) => {
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
    getLibraryTracks(trackEntries.length).then((tracks) => {
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
