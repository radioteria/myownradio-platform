import React, { ElementType } from 'react'
import { createRoot } from 'react-dom/client'
import { IDirectiveFactory, IScope } from 'angular'

interface MyReactScope extends IScope {
  props: Record<string, unknown>
}

export const makeReactDirective = (Component: ElementType): IDirectiveFactory<MyReactScope> => {
  return () => {
    return {
      restrict: 'E',
      scope: {
        props: '=',
      },
      link: (scope, element, _attrs) => {
        const container = element.get(0) as HTMLElement
        const root = createRoot(container)

        scope.$watchCollection('props', () => root.render(<Component {...scope.props} />))

        element.on('$destroy', () => {
          root.unmount()
        })
      },
    }
  }
}
