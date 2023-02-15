import { makeIcyDemuxer } from './IcyDemuxer'

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

export async function makeIcyDemuxedStream(
  url: string,
): Promise<readonly [ReadableStream<Uint8Array>, ReadableStream<string>, string]> {
  const response = await window.fetch(url, {
    headers: { 'icy-metadata': '1' },
  })
  const contentType = response.headers.get('Content-Type')
  const icyMetaIntStr = response.headers.get('icy-metaint')
  const sourceStream = response.body ?? new ReadableStream()

  if (!contentType) {
    throw new Error('Content-Type is undefined')
  }

  if (!icyMetaIntStr) {
    return [sourceStream, new ReadableStream<string>(), contentType] as const
  }

  const icyMetaInt = parseInt(icyMetaIntStr, 10)

  return [...makeIcyDemuxer(sourceStream, icyMetaInt), contentType]
}

export const streamAsyncIterator = <T>(stream: ReadableStream<T>) => ({
  async *[Symbol.asyncIterator]() {
    // Get a lock on the stream
    const reader = stream.getReader()

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
      reader.releaseLock()
    }
  },
})
