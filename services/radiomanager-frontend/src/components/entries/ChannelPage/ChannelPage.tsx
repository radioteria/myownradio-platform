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
import { getChannelTracks, ITEMS_PER_REQUEST_LIMIT } from '@/api/api.client'

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

  const [canInfinitelyScroll, setCanInfinitelyScroll] = useState(true)

  const handleInfiniteScroll = () => {
    getChannelTracks(channelId, trackEntries.length).then((tracks) => {
      const newEntries = tracks.map(toChannelTrackEntry)
      setTrackEntries((entries) => [...entries, ...newEntries])

      if (ITEMS_PER_REQUEST_LIMIT > newEntries.length) {
        setCanInfinitelyScroll(false)
      }
    })
  }

  return (
    <NowPlayingProvider channelId={channelId}>
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
    </NowPlayingProvider>
  )
}
