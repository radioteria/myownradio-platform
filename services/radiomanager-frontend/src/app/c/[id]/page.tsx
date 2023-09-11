import { ChannelPageWithProviders } from '@/components/entries/ChannelPage'
import { getChannelTracks, getNowPlaying, getSelf, MAX_TRACKS_PER_REQUEST } from '@/api'

export default async function UserChannel({ params: { id } }: { params: { id: string } }) {
  const channelId = Number(id)

  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const channel = self.streams.find((c) => c.sid === channelId)

  if (!channel) {
    return <h1>Channel not found</h1>
  }

  return (
    <ChannelPageWithProviders
      channelId={channelId}
      channel={channel}
      user={self.user}
      userChannels={self.streams}
    />
  )
}
