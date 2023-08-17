import { FocusEvent, useEffect, useRef } from 'react'
import { MenuContext, MenuItemType } from '@/modules/ContextMenu'

interface Props {
  context: MenuContext
  onBlur: (ev: FocusEvent<HTMLDivElement>) => void
}

export const ContextMenuComponent: React.FC<Props> = ({
  onBlur,
  context: { position, menuItems },
}) => {
  const menuRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    menuRef.current?.focus()
  }, [])

  return (
    <div
      tabIndex={0}
      ref={menuRef}
      onBlur={onBlur}
      className={'bg-gray-300 py-2 px-2 outline-none rounded-sm'}
      style={{
        left: `${position.x}px`,
        top: `${position.y}px`,
        position: 'fixed',
        zIndex: 99999999,
      }}
    >
      <ul>
        {menuItems.map((menuItem, index) =>
          ((menuItem) => {
            switch (menuItem.type) {
              case MenuItemType.Item:
                return (
                  <li key={index} className={'p-1 hover:bg-red-500'}>
                    {menuItem.label}
                  </li>
                )

              case MenuItemType.Separator:
                return null

              default:
                return null
            }
          })(menuItem),
        )}
      </ul>
    </div>
  )
}
