import makeDebug from 'debug'
import { concatBuffers, createPromiseChannel, streamAsyncIterator } from './IcyDemuxer.utils'

const debug = makeDebug('IcyDemuxer')

export function makeIcyDemuxer(
  source: ReadableStream<Uint8Array>,
  icyMetaInt: number,
): readonly [ReadableStream<Uint8Array>, ReadableStream<string>] {
  const [mediaStreamControllerResolve, mediaStreamController] =
    createPromiseChannel<ReadableStreamDefaultController<Uint8Array>>()
  const mediaStream = new ReadableStream<Uint8Array>({
    start(controller) {
      mediaStreamControllerResolve(controller)
    },
  })

  const [metadataStreamControllerResolve, metadataStreamController] =
    createPromiseChannel<ReadableStreamDefaultController<string>>()
  const metadataStream = new ReadableStream<string>({
    start(controller) {
      metadataStreamControllerResolve(controller)
    },
  })

  Promise.all([mediaStreamController, metadataStreamController])
    .then(async ([mediaStreamController, metadataStreamController]) => {
      let buffer = new Uint8Array()

      try {
        for await (const value of streamAsyncIterator(source)) {
          buffer = concatBuffers(buffer, value)

          while (buffer.length > icyMetaInt) {
            const bytesBeforeMetadata = buffer.slice(0, icyMetaInt)

            mediaStreamController.enqueue(bytesBeforeMetadata)

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
              metadataStreamController.enqueue(metadata)
            }

            buffer = buffer.slice(icyMetaInt + metadataSize, buffer.length)
          }
        }
      } finally {
        await source.cancel()

        try {
          mediaStreamController.close()
        } catch {
          // NOP
        }

        try {
          metadataStreamController.close()
        } catch {
          // NOP
        }
      }
    })
    .catch((error) => {
      debug('Error happened during demuxing: %s', error)
    })

  return [mediaStream, metadataStream] as const
}
