import { getSelf } from '@/api'
import { getUnusedUserTracksPage } from '@/api/radiomanager'
import { UnusedLibraryPageWithProviders } from '@/components/entries/LibraryPage'
import { INITIAL_AUDIO_TRACKS_TO_LOAD } from '@/constants'

export default async function UnusedTracks() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const data = await getUnusedUserTracksPage({
    limit: INITIAL_AUDIO_TRACKS_TO_LOAD,
  })

  return (
    <UnusedLibraryPageWithProviders
      user={self.user}
      tracks={data.items}
      totalTracks={data.totalCount}
      userChannels={self.streams}
    />
  )
}
