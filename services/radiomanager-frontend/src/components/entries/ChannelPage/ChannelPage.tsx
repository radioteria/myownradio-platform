'use client'

import { useCallback, useState } from 'react'
import { User, UserChannelTrack, UserChannel } from '@/api/api.types'
import { Header } from '@/components/Header'
import { Sidebar } from '@/components/Sidebar'
import { StreamOverlay } from '@/components/StreamOverlay'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { ChannelTracksList, toChannelTrackEntry } from './ChannelTracksList'
import { ChannelControls } from './ChannelControls'
import { NowPlayingProvider } from '@/modules/NowPlaying'
import { getChannelTracks, MAX_TRACKS_PER_REQUEST } from '@/api/api.client'
import { MediaUploaderProvider } from '@/modules/MediaUploader'

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

  const removeTrackEntry = useCallback((indexToRemove: number) => {
    setTrackEntries((entries) => entries.filter((_, index) => index !== indexToRemove))
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

  return (
    <LibraryLayout
      header={<Header user={user} />}
      sidebar={<Sidebar channels={userChannels} activeItem={['channel', channelId]} />}
      content={
        <ChannelTracksList
          channelId={channelId}
          tracks={trackEntries}
          tracksCount={userChannelTracks.length}
          canInfinitelyScroll={canInfinitelyScroll}
          onInfiniteScroll={handleInfiniteScroll}
        />
      }
      rightSidebar={
        <>
          <StreamOverlay channelId={channelId} />
          <ChannelControls channelId={channelId} />
        </>
      }
    />
  )
}

export const ChannelPageWithProviders: React.FC<Props> = (props) => {
  return (
    <MediaUploaderProvider>
      <NowPlayingProvider channelId={props.channelId}>
        <ChannelPage {...props} />
      </NowPlayingProvider>
    </MediaUploaderProvider>
  )
}
