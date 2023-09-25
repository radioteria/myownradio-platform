import { streamAsyncIterator } from '@/utils/iterators'
import makeDebug from 'debug'

export const getSupportedAudioFormats = () => ({
  vorbis: MediaSource.isTypeSupported('audio/webm; codecs="vorbis"'),
  opus: MediaSource.isTypeSupported('audio/webm; codecs="opus"'),
})

const debug = makeDebug('Piper')

export class Piper<In, Out> {
  private readonly writer = this.writable.getWriter()

  private buffer: Out[] = []

  constructor(
    private writable: WritableStream<In>,
    private readable: ReadableStream<Out>,
  ) {
    this.loop().catch()
  }

  private loop = async () => {
    debug('Start')
    let iterator = streamAsyncIterator(this.readable)
    for await (const chunk of iterator) {
      this.buffer.push(chunk)
    }
    debug('End')
  }

  public push = async (chunk: In): Promise<Out[]> => {
    let received = this.buffer.splice(0)
    await this.writer.write(chunk)
    return received
  }

  public close = async () => {
    await this.writer.close()
  }
}

export const makeBufferTransform = (
  bufferSize: number,
): ReadableWritablePair<Uint8Array, Uint8Array> => {
  let buffer = new Uint8Array(0)

  return new TransformStream({
    transform(chunk, controller) {
      const newBuffer = new Uint8Array(buffer.byteLength + chunk.byteLength)
      newBuffer.set(buffer, 0)
      newBuffer.set(new Uint8Array(chunk), buffer.byteLength)
      buffer = newBuffer

      while (buffer.byteLength >= bufferSize) {
        const chunkToSend = buffer.slice(0, bufferSize)
        controller.enqueue(chunkToSend)
        buffer = buffer.slice(bufferSize)
      }
    },
    flush(controller) {
      if (buffer.byteLength > 0) {
        controller.enqueue(buffer)
      }
    },
  })
}
