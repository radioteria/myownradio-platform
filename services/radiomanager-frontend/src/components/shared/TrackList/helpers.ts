export const isModifierKeyPressed = (event: React.MouseEvent) => {
  const isMac = window.navigator.userAgent.includes('Mac')
  const key = isMac ? 'metaKey' : 'ctrlKey'

  return event[key]
}
