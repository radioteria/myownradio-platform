'use client'

import { User, UserChannel, UserChannelTrack } from '@/api/apiTypes'
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
  const channelPageStore = useChannelPageStore(channelId, userChannelTracks)

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={userChannels} activeItem={['channel', channelId]} />}
        content={
          <ChannelTracksList
            channelId={channelId}
            tracks={channelPageStore.trackEntries}
            canInfinitelyScroll={channelPageStore.canInfinitelyScroll}
            onInfiniteScroll={channelPageStore.handleInfiniteScroll}
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
