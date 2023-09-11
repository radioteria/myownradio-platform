import { getLibraryTracks, getSelf } from '@/api'
import { LibraryPageWithProviders } from '@/components/entries/LibraryPage'

export default async function Library() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  // const tracks = await getLibraryTracks()

  return <LibraryPageWithProviders user={self.user} userTracks={[]} userChannels={self.streams} />
}
