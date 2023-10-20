import { getChannels, getSelf } from '@/api'
import { getUserTracksPage } from '@/api/radiomanager'
import { LibraryPageWithProviders } from '@/views/LibraryPage'
import { INITIAL_AUDIO_TRACKS_CHUNK_SIZE } from '@/constants'

export default async function Library() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const channels = await getChannels()

  const data = await getUserTracksPage({
    limit: INITIAL_AUDIO_TRACKS_CHUNK_SIZE,
  })

  return (
    <LibraryPageWithProviders
      user={self.user}
      initialTracks={data.items}
      initialTotalCount={data.totalCount}
      channels={channels}
    />
  )
}
