import { ChannelPage } from '@/components/entries/ChannelPage'
import { getChannelTracks, getSelf } from '@/api/api.client'

export default async function UserChannel({ params: { id } }: { params: { id: string } }) {
  const channelId = Number(id)
  const [self, channelTracks] = await Promise.all([getSelf(), getChannelTracks(channelId)])

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <ChannelPage
      channelId={channelId}
      user={self.user}
      userChannelTracks={channelTracks}
      userChannels={self.streams}
    />
  )
}
