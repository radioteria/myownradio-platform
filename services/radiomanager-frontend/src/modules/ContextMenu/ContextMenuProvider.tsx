import { createContext, useContext, useState } from 'react'
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

  const contextMenuService: ContextMenuService = {
    show(ctx: MenuContext) {
      setContext(ctx)
    },
    hide() {
      setContext(null)
    },
  }

  return (
    <ContextMenuContext.Provider value={contextMenuService}>
      {context && <ContextMenuComponent context={context} onBlur={() => setContext(null)} />}
      {children}
    </ContextMenuContext.Provider>
  )
}
