export interface ContextMenuService {
  show(ctx: MenuContext): void
  hide(): void
}

export interface MenuContext {
  readonly position: {
    readonly x: number
    readonly y: number
  }
  menuItems: readonly unknown[]
}
