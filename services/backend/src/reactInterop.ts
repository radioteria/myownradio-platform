import { ReactElement } from 'react'
import { createRoot } from 'react-dom/client'
import { IAttributes, IDirectiveFactory } from 'angular'

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

export const makeReactAppWithAttrs = (
  renderFunction: (attrs: IAttributes) => ReactElement,
  dependencies: readonly string[] = [],
): IDirectiveFactory => {
  return () => {
    return {
      restrict: 'E',
      scope: false,
      link: (_scope, element, attrs) => {
        const container = element.get(0) as HTMLElement
        const root = createRoot(container)

        const render = () => {
          root.render(renderFunction(attrs))
        }

        render()

        for (const dependency of dependencies) {
          attrs.$observe(dependency, render)
        }

        element.on('$destroy', () => root.unmount())
      },
    }
  }
}
