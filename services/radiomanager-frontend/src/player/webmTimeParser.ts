import { EbmlDataTag, EbmlStreamDecoder, EbmlTagId } from 'ebml-web-stream'
import makeDebug from 'debug'

const debug = makeDebug('TimestampParser')

/**
 * Creates a TransformStream for parsing WebM Timecodes from EBML streams.
 *
 * @returns {TransformStream<Uint8Array, [Uint8Array, number]>} The TransformStream that takes Uint8Array chunks as input and outputs a tuple containing the original chunk and the last parsed timecode.
 */
export const createWebmTimeParser = (): TransformStream<Uint8Array, [Uint8Array, number]> => {
  // Create a readable/writable pair using the EbmlStreamDecoder
  const { readable, writable } = new EbmlStreamDecoder()

  const reader = readable.getReader()
  const writer = writable.getWriter()

  // Variable to hold the last parsed Timecode
  let lastTimeCode = 0

  /**
   * Asynchronously loops through the readable stream to look for Timecode tags.
   */
  const loop = async () => {
    while (true) {
      const { value, done } = await reader.read()

      if (done) break

      // If the tag is an EbmlDataTag and its ID matches the Timecode ID, update the lastTimeCode
      if (value instanceof EbmlDataTag && value.id === EbmlTagId.Timecode) {
        lastTimeCode = value.data as number
      }
    }
  }

  const transformStream = new TransformStream({
    /**
     * The transform method responsible for writing data to the writable stream
     * and enqueueing the output to the readable stream.
     */
    async transform(chunk, controller) {
      await writer.write(chunk)
      controller.enqueue([chunk, lastTimeCode])
    },

    /**
     * The flush method that cancels the reader when the stream is done.
     */
    async flush() {
      await reader.cancel()
    },

    /**
     * The start method that begins the asynchronous loop for reading the readable stream.
     */
    start() {
      debug('Start Loop')
      loop().finally(() => debug('Loop Ended'))
    },
  })

  // Store the original cancel method
  const cancelCallback = transformStream.readable.cancel

  /**
   * Override the cancel method to also cancel the internal reader.
   */
  transformStream.readable.cancel = async function () {
    await cancelCallback.apply(this)
    await reader.cancel()
  }

  return transformStream
}
