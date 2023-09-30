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
  readonly channel: UserChannel
  readonly tracks: readonly UserChannelTrack[]
  readonly totalTracks: number
  readonly user: User
  readonly channels: readonly UserChannel[]
}

export const ChannelPage: React.FC<Props> = ({
  channelId,
  channel,
  tracks,
  totalTracks,
  user,
  channels,
}) => {
  const channelPageStore = useChannelPageStore(channelId, tracks, totalTracks)

  return (
    <>
      <LibraryLayout
        header={<Header user={user} />}
        sidebar={<Sidebar channels={channels} activeItem={['channel', channelId]} />}
        content={
          <ChannelTracksList
            tracks={channelPageStore.trackEntries}
            onDeleteTracks={channelPageStore.handleDeletingTracks}
            onRemoveTracksFromChannel={channelPageStore.handleRemovingTracksFromChannel}
            loadMoreTracks={channelPageStore.loadMoreTracks}
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
