import { createContext, useContext, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { ContextMenuService, MenuContext } from '@/modules/ContextMenu/ContextMenuTypes'
import { ContextMenuComponent } from '@/modules/ContextMenu/ContextMenuComponent'

export const ContextMenuContext = createContext<ContextMenuService | null>(null)

export const useContextMenu = (): ContextMenuService => {
  const ctx = useContext(ContextMenuContext)

  if (!ctx) {
    throw new Error('Context menu service has not been found in the context')
  }

  return ctx
}

export const ContextMenuProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [context, setContext] = useState<MenuContext | null>(null)

  const hide = () => {
    setContext(null)
  }

  const contextMenuService: ContextMenuService = {
    show(ctx) {
      setContext(ctx)
    },
    hide() {
      hide()
    },
  }

  const handleBlur = () => {
    hide()
  }

  const handleEscapeKeyPressed = () => {
    hide()
  }

  const handleClick = () => {
    hide()
  }

  return (
    <ContextMenuContext.Provider value={contextMenuService}>
      {context &&
        (context.portalElement ? (
          createPortal(
            <ContextMenuComponent
              context={context}
              onBlur={handleBlur}
              onClick={handleClick}
              onEscapeKeyPressed={handleEscapeKeyPressed}
            />,
            context.portalElement,
          )
        ) : (
          <ContextMenuComponent
            context={context}
            onBlur={handleBlur}
            onClick={handleClick}
            onEscapeKeyPressed={handleEscapeKeyPressed}
          />
        ))}
      {children}
    </ContextMenuContext.Provider>
  )
}
