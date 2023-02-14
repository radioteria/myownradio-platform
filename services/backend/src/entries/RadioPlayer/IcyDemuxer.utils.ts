export function createPromiseChannel<T>(): readonly [(t: T) => void, Promise<T>] {
  let resolve: ((t: T) => void) | undefined = undefined

  const promise = new Promise<T>((r) => {
    resolve = r
  })

  if (resolve === undefined) {
    throw new Error('Resolve function is undefined')
  }

  return [resolve, promise] as const
}

export function concatBuffers(buffer1: Uint8Array, buffer2: Uint8Array) {
  const newBuffer = new Uint8Array(buffer1.length + buffer2.length)
  newBuffer.set(buffer1, 0)
  newBuffer.set(buffer2, buffer1.length)
  return newBuffer
}
