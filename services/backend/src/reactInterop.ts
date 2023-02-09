import { ReactElement } from 'react'
import { createRoot } from 'react-dom/client'
import { IDirectiveFactory } from 'angular'

export const makeReactApp = (reactElement: ReactElement): IDirectiveFactory => {
  return () => {
    return {
      restrict: 'E',
      scope: false,
      link: (_scope, element, _attrs) => {
        const container = element.get(0) as HTMLElement
        const root = createRoot(container)

        root.render(reactElement)

        element.on('$destroy', () => root.unmount())
      },
    }
  }
}
