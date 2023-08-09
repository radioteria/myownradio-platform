import cn from 'classnames'
import { getSelf } from '@/api'
import { Sidebar } from '@/components/Sidebar/Sidebar'
import { MediaTracksList } from '@/components/MediaTracksList/MediaTracksList'
import { Header } from '@/components/Header/Header'

export default async function Home() {
  const self = await getSelf()

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <main className={cn('flex h-screen')}>
      <div className={cn('flex-1 flex flex-col overflow-hidden')}>
        <nav className={cn('h-16 bg-slate-800 text-gray-100')}>
          <Header user={self.user} />
        </nav>
        <div className={cn('flex h-full')}>
          <aside className={cn('w-64 h-full from-gray-300 to-gray-100 bg-gradient-to-b')}>
            <Sidebar channels={self.streams} />
          </aside>
          <div className={cn('flex flex-col w-full')}>
            <MediaTracksList tracks={self.tracks} tracksCount={self.user.tracks_count} />
          </div>
        </div>
      </div>
    </main>
  )
}
