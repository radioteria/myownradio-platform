import styles from './ListItemSkeleton.module.css'
import cn from 'classnames'
import { useEffect, useRef, useState } from 'react'

interface Props {}

export const ListItemSkeleton: React.FC<Props> = () => {
  return (
    <div className="flex items-center border-gray-800 h-12 relative cursor-pointer select-none">
      <div className="p-2 pl-4 w-14 flex-shrink-0 z-10 text-right">
        <div
          className={cn('w-4 h-5 bg-gray-300 rounded-md inline-block ', styles.skeletonAnimation)}
        />
      </div>
      <div className="p-2 w-full z-10 min-w-0">
        <div className={cn('w-[75%] h-4 bg-gray-300 rounded-md mb-1', styles.skeletonAnimation)} />
        <div className={cn('w-[50%] h-3 bg-gray-300 rounded-md', styles.skeletonAnimation)} />
      </div>
      <div className="px-2 py-4 w-full hidden xl:block">
        <div
          className={cn(
            'w-[75%] h-4 bg-gray-300 rounded-md inline-block',
            styles.skeletonAnimation,
          )}
        />
      </div>
      <div className="p-2 w-20 flex-shrink-0 text-right z-10">
        <div
          className={cn('w-10 h-5 bg-gray-300 rounded-md inline-block', styles.skeletonAnimation)}
        />
      </div>
      <div className="pl-2 pr-4 py-4 w-10 flex-shrink-0 text-right" />
    </div>
  )
}
