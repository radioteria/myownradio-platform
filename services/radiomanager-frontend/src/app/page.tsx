import { getLibraryTracks, getSelf } from '@/api'
import { LibraryPageWithProviders } from '@/components/entries/LibraryPage'
import { INITIAL_AUDIO_TRACKS_TO_LOAD } from '@/constants'

export default async function Library() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const userLibraryTracks = await getLibraryTracks({
    limit: INITIAL_AUDIO_TRACKS_TO_LOAD,
  })

  return (
    <LibraryPageWithProviders
      user={self.user}
      userTracks={userLibraryTracks}
      userChannels={self.streams}
    />
  )
}
