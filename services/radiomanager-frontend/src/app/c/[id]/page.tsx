import cn from 'classnames'
import { getChannelTracks, getNowPlaying, getSelf } from '@/api/api.client'
import { Sidebar } from '@/components/Sidebar/Sidebar'
import { Header } from '@/components/Header/Header'
import { ChannelTracksList } from '@/components/ChannelTracksList/ChannelTracksList'

export default async function UserChannel({ params: { id } }: { params: { id: string } }) {
  const channelId = Number(id)
  const [self, channelTracks] = await Promise.all([getSelf(), getChannelTracks(channelId)])

  if (!self) {
    return <h1>Unauthorized</h1>
  }

  return (
    <main className={cn('flex h-screen')}>
      <div className={cn('flex-1 flex flex-col overflow-hidden')}>
        <nav className={cn('h-16 bg-slate-800 text-gray-100 items-center')}>
          <Header user={self.user} />
        </nav>
        <div className={cn('flex h-full p-1')}>
          <aside className={cn('w-64 h-full p-1')}>
            <div
              className={cn(
                'w-full h-full rounded-lg',
                'from-gray-300 to-gray-100 bg-gradient-to-b',
              )}
            >
              <Sidebar channels={self.streams} activeItem={['channel', channelId]} />
            </div>
          </aside>
          <div className={cn('flex flex-col flex-1 p-1')}>
            <div
              className={cn(
                'w-full h-full rounded-lg overflow-y-auto',
                'from-gray-300 to-gray-100 bg-gradient-to-b',
              )}
            >
              <ChannelTracksList
                channelId={channelId}
                tracks={channelTracks}
                tracksCount={channelTracks.length}
              />
            </div>
          </div>
          <div className={cn('flex-col w-96 p-1')}>
            <div
              className={
                'bg-black aspect-video text-white flex items-center justify-center rounded-lg'
              }
            >
              OFFLINE
            </div>
            <div>TODO</div>
          </div>
        </div>
      </div>
    </main>
  )
}
