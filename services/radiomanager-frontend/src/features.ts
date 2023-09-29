import makeDebug from 'debug'

const debug = makeDebug('BrowserFeatures')

export class BrowserFeatures {
  public readonly supportedAudioCodecs = {
    vorbis: MediaSource.isTypeSupported('audio/webm; codecs="vorbis"'),
    opus: MediaSource.isTypeSupported('audio/webm; codecs="opus"'),
  }

  constructor() {
    debug('supportedAudioCodecs', this.supportedAudioCodecs)
  }
}

export const browserFeatures = new BrowserFeatures()
