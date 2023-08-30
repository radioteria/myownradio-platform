import { useCallback, useState } from 'react'
import { toChannelTrackEntry } from '@/components/entries/ChannelPage/ChannelTracksList'
import {
  MAX_TRACKS_PER_REQUEST,
  deleteTracksById,
  getChannelTracks,
  removeTracksFromChannelById,
} from '@/api'
import { useNowPlaying } from '@/modules/NowPlaying'
import { useHandleChannelLastUploadedTrack } from './useHandleChannelLastUploadedTrack'

import type { UserChannelTrack } from '@/api'

export const useChannelPageStore = (
  channelId: number,
  initialUserChannelTracks: readonly UserChannelTrack[],
) => {
  const { refresh: refreshNowPlaying } = useNowPlaying()

  const initialTrackEntries = initialUserChannelTracks.map(toChannelTrackEntry)
  const [trackEntries, setTrackEntries] = useState(initialTrackEntries)

  const addTrackEntry = useCallback((track: UserChannelTrack) => {
    setTrackEntries((entries) => [...entries, toChannelTrackEntry(track)])
  }, [])

  const initialCanInfinitelyScroll = initialTrackEntries.length === MAX_TRACKS_PER_REQUEST
  const [canInfinitelyScroll, setCanInfinitelyScroll] = useState(initialCanInfinitelyScroll)

  const handleInfiniteScroll = () => {
    getChannelTracks(channelId, trackEntries.length).then((tracks) => {
      const newEntries = tracks.map(toChannelTrackEntry)
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

    deleteTracksById(trackIds)
      .then(() => {
        refreshNowPlaying()
      })
      .catch((error) => {
        // Restore tracks after unsuccessful delete
        setTrackEntries(trackEntries)
      })
  }

  const handleRemovingTracksFromChannel = (uniqueIds: readonly string[]) => {
    const idsSet = new Set(uniqueIds)
    const updatedTrackEntries = trackEntries.filter(({ uniqueId }) => !idsSet.has(uniqueId))

    setTrackEntries(updatedTrackEntries)

    removeTracksFromChannelById(uniqueIds, channelId)
      .then(() => {
        refreshNowPlaying()
      })
      .catch((error) => {
        // Restore tracks after unsuccessful delete
        setTrackEntries(trackEntries)
      })
  }

  useHandleChannelLastUploadedTrack(channelId, canInfinitelyScroll, addTrackEntry)

  return {
    trackEntries,
    canInfinitelyScroll,
    handleInfiniteScroll,
    handleDeletingTracks,
    handleRemovingTracksFromChannel,
  }
}
