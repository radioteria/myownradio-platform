'use client'

import { User, UserChannelTrack, UserChannel } from '@/api/api.types'
import { Header } from '@/components/Header'
import { Sidebar } from '@/components/Sidebar'
import { StreamOverlay } from '@/components/StreamOverlay'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { ChannelTracksList } from './ChannelTracksList'
import { ChannelControls } from './ChannelControls'
import { NowPlayingProvider } from '@/modules/NowPlaying'

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
  return (
    <NowPlayingProvider channelId={channelId}>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['channel', channelId]} />}
        content={
          <ChannelTracksList
            channelId={channelId}
            tracks={userChannelTracks}
            tracksCount={userChannelTracks.length}
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
