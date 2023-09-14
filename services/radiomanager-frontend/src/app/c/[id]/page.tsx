import { ChannelPageWithProviders } from '@/components/entries/ChannelPage'
import { getSelf } from '@/api'
import { INITIAL_AUDIO_TRACKS_TO_LOAD } from '@/constants'
import { getChannelTracksPage } from '@/api/radiomanager'

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

  const data = await getChannelTracksPage(channelId, {
    limit: INITIAL_AUDIO_TRACKS_TO_LOAD,
  })

  return (
    <ChannelPageWithProviders
      channelId={channelId}
      channel={userChannel}
      tracks={data.items.map(({ track, entry }) => ({ ...track, ...entry }))}
      totalTracks={data.totalCount}
      user={self.user}
      channels={self.streams}
    />
  )
}
