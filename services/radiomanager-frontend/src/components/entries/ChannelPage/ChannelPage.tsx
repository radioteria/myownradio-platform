'use client'

import { useCallback, useEffect, useState } from 'react'
import { User, UserChannel, UserChannelTrack } from '@/api/api.types'
import { Header } from '@/components/Header'
import { Sidebar } from '@/components/Sidebar'
import { StreamOverlay } from '@/components/StreamOverlay'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { ChannelTracksList, toChannelTrackEntry } from './ChannelTracksList'
import { ChannelControls } from './ChannelControls'
import { NowPlayingProvider } from '@/modules/NowPlaying'
import {
  deleteTracksById,
  getChannelTracks,
  MAX_TRACKS_PER_REQUEST,
  removeTracksFromChannelById,
} from '@/api/api.client'
import { useMediaUploader, MediaUploaderComponent } from '@/modules/MediaUploader'
import { UploadedTrackType } from '@/modules/MediaUploader/MediaUploaderTypes'

interface Props {
  channelId: number
  user: User
  userChannelTracks: readonly UserChannelTrack[]
  userChannels: readonly UserChannel[]
}

export const ChannelPage: React.FC<Props> = ({
  channelId,
  user,
  userChannelTracks,
  userChannels,
}) => {
  const initialTrackEntries = userChannelTracks.map(toChannelTrackEntry)
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

  const { lastUploadedTrack } = useMediaUploader()
  useEffect(() => {
    if (!lastUploadedTrack) {
      return
    }

    // The last uploaded track is not related to that channel
    if (
      lastUploadedTrack.type !== UploadedTrackType.CHANNEL ||
      lastUploadedTrack.channelId !== channelId
    ) {
      return
    }

    // We haven't scrolled to the end of the tracks list
    if (canInfinitelyScroll) {
      return
    }

    addTrackEntry(lastUploadedTrack.track)
  }, [lastUploadedTrack, addTrackEntry, canInfinitelyScroll, channelId])

  const handleDeletingTracks = (trackIds: readonly number[]) => {
    const idsSet = new Set(trackIds)
    const updatedTrackEntries = trackEntries.filter(({ trackId }) => !idsSet.has(trackId))

    setTrackEntries(updatedTrackEntries)

    deleteTracksById(trackIds).catch((error) => {
      // Restore tracks after unsuccessful delete
      setTrackEntries(trackEntries)
    })
  }

  const handleRemovingTracksFromChannel = (uniqueIds: readonly string[]) => {
    const idsSet = new Set(uniqueIds)
    const updatedTrackEntries = trackEntries.filter(({ uniqueId }) => !idsSet.has(uniqueId))

    setTrackEntries(updatedTrackEntries)

    removeTracksFromChannelById(uniqueIds, channelId).catch((error) => {
      // Restore tracks after unsuccessful delete
      setTrackEntries(trackEntries)
    })
  }

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['channel', channelId]} />}
        content={
          <ChannelTracksList
            channelId={channelId}
            tracks={trackEntries}
            canInfinitelyScroll={canInfinitelyScroll}
            onInfiniteScroll={handleInfiniteScroll}
            onDeleteTracks={handleDeletingTracks}
            onRemoveTracksFromChannel={handleRemovingTracksFromChannel}
          />
        }
        rightSidebar={
          <>
            <StreamOverlay channelId={channelId} />
            <ChannelControls channelId={channelId} />
          </>
        }
      />
      <MediaUploaderComponent targetChannelId={channelId} />
    </>
  )
}

export const ChannelPageWithProviders: React.FC<Props> = (props) => {
  return (
    <NowPlayingProvider channelId={props.channelId}>
      <ChannelPage {...props} />
    </NowPlayingProvider>
  )
}
