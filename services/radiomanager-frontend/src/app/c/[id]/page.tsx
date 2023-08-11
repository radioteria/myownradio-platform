import cn from 'classnames'
import { getChannelTracks, getNowPlaying, getSelf } from '@/api/api.client'
import { Sidebar } from '@/components/Sidebar/Sidebar'
import { Header } from '@/components/Header/Header'
import { ChannelTracksList } from '@/components/ChannelTracksList/ChannelTracksList'

export default async function UserChannel({ params: { id } }: { params: { id: string } }) {
  const [self, channelTracks] = await Promise.all([getSelf(), getChannelTracks(+id)])

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <main className={cn('flex h-screen')}>
      <div className={cn('flex-1 flex flex-col overflow-hidden')}>
        <nav className={cn('h-16 bg-slate-800 text-gray-100 items-center')}>
          <Header user={self.user} />
        </nav>
        <div className={cn('flex h-full')}>
          <aside className={cn('w-64 h-full from-gray-300 to-gray-100 bg-gradient-to-b shadow-md')}>
            <Sidebar channels={self.streams} />
          </aside>
          <div className={cn('flex flex-col flex-1 overflow-y-auto')}>
            <ChannelTracksList
              channelId={+id}
              tracks={channelTracks}
              tracksCount={channelTracks.length}
            />
          </div>
          <div
            className={cn('from-gray-200 to-gray-50 bg-gradient-to-b', 'flex-col w-96 shadow-md')}
          >
            <div className={'bg-black aspect-video text-white flex items-center justify-center'}>
              OFFLINE
            </div>
            <div>TODO</div>
          </div>
        </div>
      </div>
    </main>
  )
}
