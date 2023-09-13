import { getLibraryTracks, getSelf, getUnusedLibraryTracks } from '@/api'
import { UnusedLibraryPageWithProviders } from '@/components/entries/LibraryPage'
import { INITIAL_AUDIO_TRACKS_TO_LOAD } from '@/constants'

export default async function UnusedTracks() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  const unusedUserLibraryTracks = await getUnusedLibraryTracks({
    limit: INITIAL_AUDIO_TRACKS_TO_LOAD,
  })

  return (
    <UnusedLibraryPageWithProviders
      user={self.user}
      userTracks={unusedUserLibraryTracks}
      userChannels={self.streams}
    />
  )
}
