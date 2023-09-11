import exp from 'constants'

export const streamAsyncIterator = <T>(stream: ReadableStream<T>, signal?: AbortSignal) => ({
  async *[Symbol.asyncIterator]() {
    // Get a lock on the stream
    const reader = stream.getReader()

    // This function is used to interrupt the iterator when an abort signal is received
    const abortFn = () => {
      reader.cancel().catch(() => {
        // We don't need to handle the rejection here
        // since it's expected when the reader is cancelled.
      })
    }

    if (signal) {
      // Attach the abort function to the signal's abort event
      signal.addEventListener('abort', abortFn)
    }

    try {
      while (true) {
        // Read from the stream
        const { done, value } = await reader.read()
        // Exit if we're done
        if (done) return
        // Else yield the chunk
        yield value
      }
    } finally {
      if (signal) {
        // Remove the abort function from the signal's abort event
        signal.removeEventListener('abort', abortFn)
      }
      // Release the lock on the reader
      reader.releaseLock()
    }
  },
})

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

export function repeat<T>(value: T, times: number) {
  return new Array<T>(times).fill(value)
}

export function range(from = 0, to: number) {
  if (from > to) {
    return []
  }

  return new Array(to - from).fill(null).map((_, i) => i + from)
}
