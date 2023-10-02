import { useEffect, useRef } from 'react'
import cn from 'classnames'
import { MenuContext, MenuItemType } from '@/modules/ContextMenu'
import { useMenuPosition } from '@/modules/ContextMenu/hooks/useMenuPosition'
import styles from './ContextMenuComponent.module.css'
import { useHotkey } from '@/hooks/useHotkey'

interface Props {
  context: MenuContext
  onBlur: (ev: React.FocusEvent<HTMLElement>) => void
  onClick: (ev: React.MouseEvent<HTMLElement>) => void
  onEscapeKeyPressed: () => void
}

export const ContextMenuComponent: React.FC<Props> = ({
  onBlur,
  onClick,
  onEscapeKeyPressed,
  context: { position, menuItems },
}) => {
  const menuRef = useRef<HTMLDivElement | null>(null)
  // TODO Fix position jumping
  const actualPosition = useMenuPosition(menuRef, position)

  useHotkey(menuRef, 'Escape', onEscapeKeyPressed)

  useEffect(() => {
    menuRef.current?.focus()
  }, [])

  return (
    <div
      tabIndex={-1}
      ref={menuRef}
      onBlur={onBlur}
      className={cn([
        'bg-morblue-400 py-2 outline-none rounded-md fixed cursor-pointer',
        styles.menuAnimation,
      ])}
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
