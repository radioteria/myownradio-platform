export const getSupportedAudioFormats = () => ({
  aac: MediaSource.isTypeSupported('audio/aac'),
  vorbis: MediaSource.isTypeSupported('audio/webm'),
})
