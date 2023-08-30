import { getLibraryTracks, getSelf } from '@/api'
import { LibraryPageWithProviders } from '@/components/entries/LibraryPage'

export default async function Library() {
  const [self, tracks] = await Promise.all([getSelf(), getLibraryTracks()])

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <LibraryPageWithProviders user={self.user} userTracks={tracks} userChannels={self.streams} />
  )
}
