import { FocusEvent, useEffect, useRef } from 'react'
import { MenuContext, MenuItemType } from '@/modules/ContextMenu'
import { useInViewport } from '@/hooks/useInViewport'

interface Props {
  context: MenuContext
  onBlur: (ev: FocusEvent<HTMLDivElement>) => void
}

export const ContextMenuComponent: React.FC<Props> = ({
  onBlur,
  context: { position, menuItems },
}) => {
  const menuRef = useRef<HTMLDivElement | null>(null)

  useInViewport(menuRef)

  useEffect(() => {
    menuRef.current?.focus()
  }, [])

  return (
    <div
      tabIndex={0}
      ref={menuRef}
      onBlur={onBlur}
      className={'bg-gray-700 py-2 outline-none rounded-md fixed text-sm'}
      style={{
        left: `${position.x}px`,
        top: `${position.y}px`,
        zIndex: 99999999,
      }}
    >
      <ul>
        {menuItems.map((menuItem, index) =>
          ((menuItem) => {
            switch (menuItem.type) {
              case MenuItemType.Item:
                return (
                  <li
                    key={index}
                    className={'px-4 py-2 hover:bg-gray-600 text-gray-200 pointer truncate'}
                  >
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
