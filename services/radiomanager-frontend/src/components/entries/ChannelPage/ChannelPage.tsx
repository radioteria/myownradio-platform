'use client'

import { User, UserChannel, UserChannelTrack } from '@/api'
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
  readonly userChannel: UserChannel
  readonly userChannelTracks: readonly UserChannelTrack[]
  readonly user: User
  readonly userChannels: readonly UserChannel[]
}

export const ChannelPage: React.FC<Props> = ({
  channelId,
  userChannel,
  userChannelTracks,
  user,
  userChannels,
}) => {
  const channelPageStore = useChannelPageStore(channelId, userChannelTracks)

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['channel', channelId]} />}
        content={
          <ChannelTracksList
            channelId={channelId}
            totalTracks={userChannel.tracksCount}
            tracks={channelPageStore.trackEntries}
            onDeleteTracks={channelPageStore.handleDeletingTracks}
            onRemoveTracksFromChannel={channelPageStore.handleRemovingTracksFromChannel}
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
