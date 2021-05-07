export interface UIModule {
  // What should be done after mounting.
  mount(element: HTMLElement): Promise<void>

  // What should be done after unmounting.
  unmount(element: HTMLElement): Promise<void>
}
