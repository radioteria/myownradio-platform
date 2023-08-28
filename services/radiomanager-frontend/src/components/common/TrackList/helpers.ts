export const isMac = window.navigator.userAgent.includes('Mac')

export const isModifierKeyPressed = (event: React.MouseEvent) => {
  const key = isMac ? 'metaKey' : 'ctrlKey'

  return event[key]
}
