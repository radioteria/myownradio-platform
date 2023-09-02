import makeDebug from 'debug'

const debug = makeDebug('audio.ts')

export function playAudio(htmlAudioElement: HTMLAudioElement, src: string) {
  debug('Starting audio playback: %s', src)
  htmlAudioElement.src = src
  htmlAudioElement.load()
  htmlAudioElement.play().catch((error) => {
    debug('Unable to start audio playback: %s', error)
  })
}

export function loadAudio(htmlAudioElement: HTMLAudioElement, src: string) {
  debug('Loading audio: %s', src)
  htmlAudioElement.src = src
  htmlAudioElement.load()
}

export function stopAudio(htmlAudioElement: HTMLAudioElement) {
  debug('Stopping audio playback')
  htmlAudioElement.pause()
  htmlAudioElement.load()
  htmlAudioElement.removeAttribute('src')
}

export function isAudioStopped(htmlAudioElement: HTMLAudioElement) {
  return htmlAudioElement.ended || htmlAudioElement.paused
}

export function advanceAudio(htmlAudioElement: HTMLAudioElement, amountSeconds: number) {
  debug('Advancing audio: %f', amountSeconds.toPrecision(2))
  htmlAudioElement.currentTime += amountSeconds
}
