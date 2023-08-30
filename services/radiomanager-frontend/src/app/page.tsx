import { getSelf } from '@/api'
import { LibraryPageWithProviders } from '@/components/entries/LibraryPage'

export default async function Home() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <LibraryPageWithProviders
      user={self.user}
      userTracks={self.tracks}
      userChannels={self.streams}
    />
  )
}
