'use client'

import { Channel, User, UserChannel, UserChannelTrack } from '@/api'
import { Header } from '@/components/Header'
import { Sidebar } from '@/components/Sidebar'
import { StreamOverlay } from '@/components/StreamOverlay'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { ChannelTracksList } from './ChannelTracksList'
import { ChannelControls } from './ChannelControls'
import { NowPlayingProvider } from '@/modules/NowPlaying'
import { MediaUploaderComponent } from '@/modules/MediaUploader'
import { useChannelPageStore } from './hooks/useChannelPageStore'

interface Props {
  readonly channelId: number
  readonly channel: UserChannel
  readonly user: User
  readonly userChannels: readonly UserChannel[]
}

export const ChannelPage: React.FC<Props> = ({ channelId, channel, user, userChannels }) => {
  const channelPageStore = useChannelPageStore(channelId)

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['channel', channelId]} />}
        content={
          <ChannelTracksList
            channelId={channelId}
            totalTracks={channel.tracksCount}
            tracks={channelPageStore.trackEntries}
            onDeleteTracks={channelPageStore.handleDeletingTracks}
            onRemoveTracksFromChannel={channelPageStore.handleRemovingTracksFromChannel}
            // onScrollTop={channelPageStore.handleOnScrollTop}
            // onScrollBottom={channelPageStore.handleOnScrollBottom}
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
