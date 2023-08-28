import { useEffect, useRef } from 'react'
import { MenuContext, MenuItemType } from '@/modules/ContextMenu'
import { useMenuPosition } from '@/modules/ContextMenu/hooks/useMenuPosition'

interface Props {
  context: MenuContext
  onBlur: (ev: React.FocusEvent<HTMLElement>) => void
  onClick: (ev: React.MouseEvent<HTMLElement>) => void
}

export const ContextMenuComponent: React.FC<Props> = ({
  onBlur,
  onClick,
  context: { position, menuItems },
}) => {
  const menuRef = useRef<HTMLDivElement | null>(null)
  // TODO Fix position jumping
  const actualPosition = useMenuPosition(menuRef, position)

  useEffect(() => {
    menuRef.current?.focus()
  }, [])

  return (
    <div
      tabIndex={-1}
      ref={menuRef}
      onBlur={onBlur}
      className={'bg-morblue-400 py-2 outline-none rounded-md fixed text-sm cursor-pointer'}
      style={{
        left: `${actualPosition.x}px`,
        top: `${actualPosition.y}px`,
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
                    className={'px-4 py-2 hover:bg-morblue-300 text-gray-200 pointer truncate'}
                    onClick={(event) => {
                      menuItem.onClick(event)
                      onClick(event)
                    }}
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
