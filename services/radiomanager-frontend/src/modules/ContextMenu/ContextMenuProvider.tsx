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
  const onHideRef = useRef<null | (() => void)>(null)

  const hide = () => {
    setContext(null)

    if (onHideRef.current) {
      onHideRef.current()
      onHideRef.current = null
    }
  }

  const contextMenuService: ContextMenuService = {
    show(ctx, onHide) {
      onHideRef.current = onHide
      setContext(ctx)
    },
    hide() {
      hide()
    },
  }

  const handleBlur = () => {
    hide()
  }

  return (
    <ContextMenuContext.Provider value={contextMenuService}>
      {context &&
        (context.portalElement ? (
          createPortal(
            <ContextMenuComponent context={context} onBlur={handleBlur} />,
            context.portalElement,
          )
        ) : (
          <ContextMenuComponent context={context} onBlur={handleBlur} />
        ))}
      {children}
    </ContextMenuContext.Provider>
  )
}
