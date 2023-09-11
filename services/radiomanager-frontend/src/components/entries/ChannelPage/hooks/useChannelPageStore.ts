import { useCallback, useEffect, useState } from 'react'
import {
  toChannelTrackEntry,
  ChannelTrackEntry,
} from '@/components/entries/ChannelPage/ChannelTracksList'
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

  const [trackEntries, setTrackEntries] = useState<readonly ChannelTrackEntry[]>(() =>
    initialUserChannelTracks.map(toChannelTrackEntry),
  )

  const addTrackEntry = useCallback((track: UserChannelTrack) => {
    setTrackEntries((entries) => [...entries, toChannelTrackEntry(track)])
  }, [])

  const [isFetching, setIsFetching] = useState(true)
  useEffect(() => {
    if (!isFetching) return

    const abortController = new AbortController()

    getChannelTracks(channelId, {
      offset: trackEntries.length,
      signal: abortController.signal,
    }).then((tracks) => {
      const newEntries = tracks.map(toChannelTrackEntry)
      setTrackEntries((entries) => [...entries, ...newEntries])
      setIsFetching(newEntries.length === MAX_TRACKS_PER_REQUEST)
    })

    return () => {
      abortController.abort()
    }
  }, [isFetching, channelId, trackEntries])

  const handleDeletingTracks = (trackIds: readonly number[]) => {
    const idsSet = new Set(trackIds)
    const updatedTrackEntries = trackEntries.filter((track) => track && !idsSet.has(track.trackId))

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
    const updatedTrackEntries = trackEntries.filter((track) => track && !idsSet.has(track.uniqueId))

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

  useHandleChannelLastUploadedTrack(channelId, isFetching, addTrackEntry)

  return {
    trackEntries,
    handleDeletingTracks,
    handleRemovingTracksFromChannel,
  }
}
