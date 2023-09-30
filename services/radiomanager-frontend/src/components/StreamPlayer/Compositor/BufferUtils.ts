import { sleep } from '@/utils/sleep'

export const makeChunkTransform = (chunkSize: number): TransformStream<Uint8Array, Uint8Array> => {
  let buffer = new Uint8Array(chunkSize * 2) // Pre-allocate memory for the buffer
  let offset = 0 // Keep track of how much of the buffer is in use

  let isFlushed = false

  return new TransformStream(
    {
      async transform(chunk, controller) {
        // Resize buffer if needed
        if (offset + chunk.byteLength > buffer.length) {
          let newBuffer = new Uint8Array((offset + chunk.byteLength) * 2)
          newBuffer.set(buffer.subarray(0, offset), 0)
          buffer = newBuffer
        }

        // Copy the incoming chunk into the buffer
        buffer.set(chunk, offset)
        offset += chunk.byteLength

        // Send full-sized chunks downstream
        while (offset >= chunkSize) {
          if (!isFlushed && controller.desiredSize !== null && controller.desiredSize <= 0) {
            await sleep(50)
          }

          const chunkToSend = buffer.slice(0, chunkSize)
          controller.enqueue(chunkToSend)

          // Update the buffer and offset
          offset -= chunkSize
          buffer.copyWithin(0, chunkSize, offset + chunkSize)
        }
      },

      flush(controller) {
        // If there's remaining data in the buffer, send it downstream
        if (offset > 0) {
          controller.enqueue(buffer.slice(0, offset))
        }

        isFlushed = true
      },
    },
    {
      highWaterMark: 100,
    },
  )
}
