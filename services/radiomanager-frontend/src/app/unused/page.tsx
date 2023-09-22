import { getSelf } from '@/api'
import { getUnusedUserTracksPage } from '@/api/radiomanager'
import { UnusedLibraryPageWithProviders } from '@/components/entries/LibraryPage'
import { INITIAL_AUDIO_TRACKS_CHUNK_SIZE } from '@/constants'

export default async function UnusedTracks() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const data = await getUnusedUserTracksPage({
    limit: INITIAL_AUDIO_TRACKS_CHUNK_SIZE,
  })

  return (
    <UnusedLibraryPageWithProviders
      user={self.user}
      initialTracks={data.items}
      initialTotalCount={data.totalCount}
      userChannels={self.streams}
    />
  )
}
