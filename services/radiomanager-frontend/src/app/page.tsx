import { getSelf } from '@/api'
import { getUserTracksPage } from '@/api/radiomanager'
import { LibraryPageWithProviders } from '@/components/entries/LibraryPage'
import { INITIAL_AUDIO_TRACKS_TO_LOAD } from '@/constants'

export default async function Library() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const data = await getUserTracksPage({
    limit: INITIAL_AUDIO_TRACKS_TO_LOAD,
  })

  return (
    <LibraryPageWithProviders
      user={self.user}
      tracks={data.items}
      totalTracks={data.totalCount}
      channels={self.streams}
    />
  )
}
