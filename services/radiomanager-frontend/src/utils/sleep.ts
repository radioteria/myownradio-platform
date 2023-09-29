export const sleep = async (timeout: number) => {
  return new Promise<void>((resolve) => {
    window.setTimeout(resolve, timeout)
  })
}
