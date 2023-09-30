import { useRef } from 'react'
import cn from 'classnames'
import { useIsVisible } from '@/hooks/useIsVisible'

import styles from './TrackListItemSkeleton.module.css'

export const TrackListItemSkeleton: React.FC = () => {
  const elementRef = useRef<HTMLDivElement>(null)
  const isVisible = useIsVisible(elementRef)

  return (
    <div
      ref={elementRef}
      className="flex items-center border-gray-800 h-12 relative cursor-pointer select-none"
    >
      <div className="p-2 pl-4 w-14 flex-shrink-0 z-10 text-right">
        <div
          className={cn('w-6 h-5 bg-morblue-800 rounded-md inline-block ', {
            [styles.skeletonAnimation]: isVisible,
          })}
        />
      </div>
      <div className="p-2 w-full z-10 min-w-0">
        <div
          className={cn('w-[75%] h-4 bg-morblue-800 rounded-md mb-1', {
            [styles.skeletonAnimation]: isVisible,
          })}
        />
        <div
          className={cn('w-[50%] h-3 bg-morblue-800 rounded-md', {
            [styles.skeletonAnimation]: isVisible,
          })}
        />
      </div>
      <div className="px-2 py-4 w-full hidden xl:block">
        <div
          className={cn('w-[75%] h-4 bg-morblue-800 rounded-md inline-block', {
            [styles.skeletonAnimation]: isVisible,
          })}
        />
      </div>
      <div className="p-2 w-20 flex-shrink-0 text-right z-10">
        <div
          className={cn('w-10 h-5 bg-morblue-800 rounded-md inline-block', {
            [styles.skeletonAnimation]: isVisible,
          })}
        />
      </div>
      <div className="pl-2 pr-4 py-4 w-10 flex-shrink-0 text-right" />
    </div>
  )
}
