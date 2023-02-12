import makeDebug from 'debug'

const debug = makeDebug('RadioPlayerStore:util')

export function playAudio(htmlAudioElement: HTMLAudioElement, src: string) {
  debug('Starting audio playback: %s', src)
  htmlAudioElement.src = src
  htmlAudioElement.load()
  htmlAudioElement.play().catch((error) => {
    debug('Unable to start audio playback: %s', error)
  })
}

export function playMediaSource(htmlAudioElement: HTMLAudioElement, source: MediaSource) {
  debug('Starting audio playback: %s', source)
  htmlAudioElement.src = URL.createObjectURL(source)
  htmlAudioElement.load()
  htmlAudioElement.play().catch((error) => {
    debug('Unable to start audio playback: %s', error)
  })
}

export function stopAudio(htmlAudioElement: HTMLAudioElement) {
  debug('Stopping audio playback')
  htmlAudioElement.pause()
  htmlAudioElement.load()
  htmlAudioElement.removeAttribute('src')
}
