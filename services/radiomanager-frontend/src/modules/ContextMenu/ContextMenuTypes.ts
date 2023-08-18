export interface ContextMenuService {
  show(ctx: MenuContext): void
  hide(): void
}

export enum MenuItemType {
  Item = 'Item',
  Separator = 'Separator',
}

export type MenuItem =
  | {
      type: MenuItemType.Item
      label: string
      onClick: () => void
    }
  | {
      type: MenuItemType.Separator
    }

export interface MenuContext {
  readonly position: {
    readonly x: number
    readonly y: number
  }
  menuItems: readonly MenuItem[]
  portalElement?: HTMLElement
}
