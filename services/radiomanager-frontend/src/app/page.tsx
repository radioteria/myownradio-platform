import cn from 'classnames'
import { getSelf } from '@/api'

export default async function Home() {
  const self = await getSelf()

  return (
    <main className={cn('flex h-screen')}>
      <div className={cn('flex-1 flex flex-col overflow-hidden')}>
        <nav className={cn('flex h-16 bg-slate-800 text-gray-100')}>
          <div className={cn('flex')}>Hello, {self.user.name || self.user.login}</div>
        </nav>
        <div className={cn('flex h-full')}>
          <aside className={cn('w-64 h-full from-gray-300 to-gray-100 bg-gradient-to-b')}>
            <h3 className={cn('text-xl')}>All Channels ({self.streams.length})</h3>
            <ul>
              {self.streams.map((stream) => (
                <li key={stream.sid}>{stream.name}</li>
              ))}
            </ul>
          </aside>
          <div className={cn('flex flex-col w-full')}>
            <h3 className={cn('text-xl')}>All tracks ({self.user.tracks_count})</h3>
            <ul>
              {self.tracks.map((track) => (
                <li key={track.tid}>{track.title}</li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </main>
  )
}
