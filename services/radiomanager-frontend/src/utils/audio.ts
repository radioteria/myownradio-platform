import makeDebug from 'debug'

const debug = makeDebug('audio.ts')

export function playAudio(htmlAudioElement: HTMLAudioElement, src: string) {
  debug('Starting audio playback: %s', src)
  if (src !== htmlAudioElement.currentSrc) {
    htmlAudioElement.src = src
    htmlAudioElement.load()
  }
  htmlAudioElement.play().catch((error) => {
    debug('Unable to start audio playback: %s', error)
  })
}

export function loadAudio(htmlAudioElement: HTMLAudioElement, src: string) {
  debug('Loading audio: %s', src)
  if (src !== htmlAudioElement.currentSrc) {
    htmlAudioElement.src = src
    htmlAudioElement.load()
  }
  htmlAudioElement.currentTime = 0
  htmlAudioElement.pause()
}

export function stopAudio(htmlAudioElement: HTMLAudioElement) {
  debug('Stopping audio playback')
  htmlAudioElement.pause()
  htmlAudioElement.load()
  htmlAudioElement.removeAttribute('src')
}

export function seekAudio(htmlAudioElement: HTMLAudioElement, amountSeconds: number) {
  debug('Advancing audio: %f', amountSeconds.toPrecision(2))
  htmlAudioElement.currentTime += amountSeconds
}

export async function appendBufferAsync(
  sourceBuffer: SourceBuffer,
  buffer: Uint8Array,
): Promise<void> {
  sourceBuffer.appendBuffer(buffer)

  if (sourceBuffer.updating) {
    await new Promise<void>((resolve) => {
      sourceBuffer.onupdateend = () => resolve()
    })
  }
}
