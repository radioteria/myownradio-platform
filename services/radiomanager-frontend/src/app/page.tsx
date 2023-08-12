import { getSelf } from '@/api/api.client'
import { Sidebar } from '@/components/Sidebar/Sidebar'
import { LibraryTracksList } from '@/components/LibraryTracksList/LibraryTracksList'
import { Header } from '@/components/Header/Header'
import { LibraryLayout } from '@/components/layouts/LibraryLayout'

export default async function Home() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <LibraryLayout
      header={<Header user={self.user} />}
      sidebar={<Sidebar channels={self.streams} activeItem={['library']} />}
      content={<LibraryTracksList tracks={self.tracks} tracksCount={self.user.tracksCount} />}
    />
  )
}
