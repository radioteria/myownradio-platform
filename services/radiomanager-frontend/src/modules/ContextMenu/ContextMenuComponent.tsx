import { FocusEvent, useEffect, useRef } from 'react'
import { MenuContext } from '@/modules/ContextMenu'
import cn from 'classnames'

interface Props {
  context: MenuContext
  onBlur: (ev: FocusEvent<HTMLDivElement>) => void
}

export const ContextMenuComponent: React.FC<Props> = ({ onBlur, context: { position } }) => {
  const menuRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    menuRef.current?.focus()
  }, [])

  return (
    <div
      tabIndex={0}
      ref={menuRef}
      onBlur={onBlur}
      className={'bg-gray-300 py-2 px-2 outline-none rounded-lg'}
      style={{
        left: `${position.x}px`,
        top: `${position.y}px`,
        position: 'fixed',
        zIndex: 99999999,
      }}
    >
      Menu
    </div>
  )
}
