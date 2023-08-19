'use client'

import cn from 'classnames'
import { ContextMenuProvider } from '@/modules/ContextMenu'

interface Props {
  header: React.ReactNode
  sidebar: React.ReactNode
  content: React.ReactNode
  rightSidebar?: React.ReactNode
}

export const LibraryLayout: React.FC<Props> = ({ header, sidebar, content, rightSidebar }) => {
  return (
    <main className={cn('h-screen w-screen')}>
      <ContextMenuProvider>
        <div className={cn('flex h-full w-full flex-col')}>
          <nav className={cn('h-16 bg-morblue-700 text-gray-100 items-center overflow-hidden')}>
            {header}
          </nav>
          <div className={cn('flex-1 h-full flex p-1 overflow-hidden')}>
            <aside className={cn('w-64 h-full p-1')}>
              <div
                className={cn(
                  'w-full h-full rounded-lg overflow-y-auto',
                  'from-gray-200 to-gray-50 bg-gradient-to-b',
                )}
              >
                {sidebar}
              </div>
            </aside>
            <div className={cn('flex flex-col flex-1 p-1 min-w-0')}>
              <div
                className={cn(
                  'w-full h-full rounded-lg overflow-y-auto',
                  'from-gray-200 to-gray-50 bg-gradient-to-b',
                )}
              >
                {content}
              </div>
            </div>
            {rightSidebar && <div className={cn('flex-col w-96 p-1')}>{rightSidebar}</div>}
          </div>
        </div>
      </ContextMenuProvider>
    </main>
  )
}
