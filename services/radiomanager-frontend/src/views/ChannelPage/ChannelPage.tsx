'use client'

import { User, Channel, UserChannelTrack } from '@/api'
import { LibraryLayout, Header } from '@/layouts/LibraryLayout'
import { Sidebar } from '@/components/Sidebar'
import { ChannelTracksList } from './ChannelTracksList'
import { NowPlayingProvider } from '@/modules/NowPlaying'
import { MediaUploaderComponent } from '@/modules/MediaUploader'
import { useChannelPageStore } from './hooks/useChannelPageStore'
import { ChannelControls } from './ChannelTracksList/ChannelControls'
import { UserEventProvider } from '@/context/UserEventProvider'
import { ChannelRtmpSettings } from '@/views/ChannelPage/ChannelRtmpSettings'
import { useRtmpSettingsStore } from '@/views/ChannelPage/hooks/useRtmpSettingsStore'
import { useLiveStatusStore } from '@/views/ChannelPage/hooks/useLiveStatusStore'

interface Props {
  readonly channelId: number
  readonly channel: Channel
  readonly tracks: readonly UserChannelTrack[]
  readonly totalTracks: number
  readonly user: User
  readonly channels: readonly Channel[]
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
  const rtmpSettingsStore = useRtmpSettingsStore(channel)
  const liveStatusStore = useLiveStatusStore(channelId, 'preview')

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
            onTrackItemDoubleClick={channelPageStore.controls.playFromIndex}
          />
        }
        rightSidebar={
          <>
            <ChannelControls
              channelId={channelId}
              onPlayNext={channelPageStore.controls.playNext}
              onPlayPrev={channelPageStore.controls.playPrev}
              onPlay={channelPageStore.controls.play}
              onPause={channelPageStore.controls.pause}
              onStop={channelPageStore.controls.stop}
              onSeek={channelPageStore.controls.seek}
            />
            <ChannelRtmpSettings
              channel={rtmpSettingsStore.channel}
              onUpdateRtmpSettings={rtmpSettingsStore.handleUpdateRtmpSettings}
              desiredLiveStatus={liveStatusStore.desiredLiveStatus}
              onToggleDesiredLiveStatus={liveStatusStore.handleToggleDesiredLiveStatus}
              actualLiveStatus={liveStatusStore.actualLiveStatus}
            />
          </>
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
