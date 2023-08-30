import { getSelf, getUnusedLibraryTracks } from '@/api'
import { UnusedLibraryPageWithProviders } from '@/components/entries/LibraryPage'

export default async function UnusedTracks() {
  const [self, unusedTracks] = await Promise.all([getSelf(), getUnusedLibraryTracks()])

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <UnusedLibraryPageWithProviders
      user={self.user}
      userTracks={unusedTracks}
      userChannels={self.streams}
    />
  )
}
