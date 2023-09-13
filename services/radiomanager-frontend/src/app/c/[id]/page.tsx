import { ChannelPageWithProviders } from '@/components/entries/ChannelPage'
import { getChannelTracks, getNowPlaying, getSelf, MAX_TRACKS_PER_REQUEST } from '@/api'
import { INITIAL_AUDIO_TRACKS_TO_LOAD } from '@/constants'

export default async function UserChannel({ params: { id } }: { params: { id: string } }) {
  const channelId = Number(id)

  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const userChannel = self.streams.find((c) => c.sid === channelId)

  if (!userChannel) {
    return <h1>Channel not found</h1>
  }

  const userChannelTracks = await getChannelTracks(channelId, {
    limit: INITIAL_AUDIO_TRACKS_TO_LOAD,
  })

  return (
    <ChannelPageWithProviders
      channelId={channelId}
      userChannel={userChannel}
      userChannelTracks={userChannelTracks}
      user={self.user}
      userChannels={self.streams}
    />
  )
}
