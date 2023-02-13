import makeDebug from 'debug'

const debug = makeDebug('IcyDemuxer')

export class IcyDemuxer extends ReadableStream<Uint8Array> {
  constructor(
    sourceStream: ReadableStream<Uint8Array>,
    private readonly icyMetaInt: number,
    private readonly onMetadata: (metadata: string) => void,
  ) {
    super({
      start: (controller) => {
        const sourceReader = sourceStream.getReader()
        const loop = icyMetaInt > 0 ? IcyDemuxer.demuxLoop : IcyDemuxer.bypassLoop

        loop(controller, sourceReader, icyMetaInt, onMetadata).catch((error) => {
          debug('Error happened in loop: %s', error)
        })
      },
    })
  }

  private static demuxLoop = async (
    controller: ReadableStreamDefaultController<Uint8Array>,
    sourceReader: ReadableStreamDefaultReader<Uint8Array>,
    icyMetaInt: number,
    onMetadata: (metadata: string) => void,
  ) => {
    let buffer = new Uint8Array()

    while (true) {
      const { value, done } = await sourceReader.read()

      if (done) {
        controller.close()
        break
      }

      buffer = IcyDemuxer.concatBuffers(buffer, value)

      while (buffer.length > icyMetaInt) {
        const bytesBeforeMetadata = buffer.slice(0, icyMetaInt)

        try {
          controller.enqueue(bytesBeforeMetadata)
        } catch {
          await sourceReader.cancel()
          return
        }

        const metadataSizeByte = buffer.at(icyMetaInt)
        if (metadataSizeByte === undefined) {
          debug('EOF on reading metadata size byte')
          break
        }
        const metadataSize = 1 + metadataSizeByte * 16
        const metadata = new TextDecoder().decode(
          buffer.slice(icyMetaInt + 1, icyMetaInt + metadataSize),
        )
        if (metadataSizeByte > 0) {
          debug('metadata: %s', metadata)
          onMetadata(metadata)
        }

        buffer = buffer.slice(icyMetaInt + metadataSize, buffer.length)
      }
    }
  }

  private static bypassLoop = async (
    controller: ReadableStreamDefaultController<Uint8Array>,
    sourceReader: ReadableStreamDefaultReader<Uint8Array>,
  ) => {
    while (true) {
      const { value, done } = await sourceReader.read()
      if (done) {
        controller.close()
        break
      }
      controller.enqueue(value)
    }
  }

  private static concatBuffers(buffer1: Uint8Array, buffer2: Uint8Array) {
    const newBuffer = new Uint8Array(buffer1.length + buffer2.length)
    newBuffer.set(buffer1, 0)
    newBuffer.set(buffer2, buffer1.length)
    return newBuffer
  }
}
