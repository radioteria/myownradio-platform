'use client'

import { User, UserChannel, UserChannelTrack } from '@/api'
import { LibraryLayout, Header } from '@/layouts/LibraryLayout'
import { Sidebar } from '@/components/Sidebar'
import { ChannelTracksList } from './ChannelTracksList'
import { NowPlayingProvider } from '@/modules/NowPlaying'
import { MediaUploaderComponent } from '@/modules/MediaUploader'
import { useChannelPageStore } from './hooks/useChannelPageStore'
import { ChannelControls } from './ChannelTracksList/ChannelControls'
import { UserEventProvider } from '@/context/UserEventProvider'

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
            channelId={channelId}
            tracks={channelPageStore.trackEntries}
            onDeleteTracks={channelPageStore.handleDeletingTracks}
            onRemoveTracksFromChannel={channelPageStore.handleRemovingTracksFromChannel}
            loadMoreTracks={channelPageStore.loadMoreTracks}
          />
        }
        rightSidebar={
          <ChannelControls
            channelId={channelId}
            onPlayNext={channelPageStore.controls.playNext}
            onPlayPrev={channelPageStore.controls.playPrev}
          />
        }
      />
      <MediaUploaderComponent targetChannelId={channelId} />
    </>
  )
}

export const ChannelPageWithProviders: React.FC<Props> = (props) => {
  return (
    <UserEventProvider>
      <NowPlayingProvider channelId={props.channelId}>
        <ChannelPage {...props} />
      </NowPlayingProvider>
    </UserEventProvider>
  )
}
