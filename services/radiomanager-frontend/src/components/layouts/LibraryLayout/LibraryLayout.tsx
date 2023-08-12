import cn from 'classnames'

interface Props {
  header: React.ReactNode
  sidebar: React.ReactNode
  content: React.ReactNode
  rightSidebar?: React.ReactNode
}

export const LibraryLayout: React.FC<Props> = ({ header, sidebar, content, rightSidebar }) => {
  return (
    <main className={cn('flex h-screen w-screen')}>
      <div className={cn('flex flex-1 flex-col')}>
        <nav className={cn('h-16 bg-slate-800 text-gray-100 items-center overflow-hidden')}>
          {header}
        </nav>
        <div className={cn('flex-1 h-full flex p-1 overflow-hidden')}>
          <aside className={cn('w-64 h-full p-1')}>
            <div
              className={cn(
                'w-full h-full rounded-lg',
                'from-gray-300 to-gray-100 bg-gradient-to-b',
              )}
            >
              {sidebar}
            </div>
          </aside>
          <div className={cn('flex flex-col flex-1 p-1')}>
            <div
              className={cn(
                'w-full h-full rounded-lg overflow-y-auto',
                'from-gray-300 to-gray-100 bg-gradient-to-b',
              )}
            >
              {content}
            </div>
          </div>
          {rightSidebar && <div className={cn('flex-col w-96 p-1')}>{rightSidebar}</div>}
        </div>
      </div>
    </main>
  )
}
