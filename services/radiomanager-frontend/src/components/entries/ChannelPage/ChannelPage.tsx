'use client'

import { Header } from '@/components/Header/Header'
import { Sidebar } from '@/components/Sidebar/Sidebar'
import { ChannelTracksList } from '@/components/ChannelTracksList/ChannelTracksList'
import { StreamOverlay } from '@/components/StreamOverlay'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'
import { User, UserChannelTrack, UserChannel } from '@/api/api.types'

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
          <div>TODO</div>
        </>
      }
    />
  )
}
