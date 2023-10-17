import { NowPlayingResponseSchema, SelfResponseSchema } from './apiTypes'
import {
  DeleteTracksResponseSchema,
  GetChannelsSchema,
  RemoveTracksFromChannelResponseSchema,
  UploadTrackResponseSchema,
  UploadTrackToChannelResponseSchema,
} from './httpSchemas'
import { BACKEND_BASE_URL } from './constants'
import { fetchAnyhow, fetchAnyhowWithDataSchema } from './fetchAnyhow'

export async function getChannels() {
  return fetchAnyhowWithDataSchema(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/`,
    { withCredentials: true },
    GetChannelsSchema,
  )
}

export async function getSelf() {
  return await fetchAnyhow(`${BACKEND_BASE_URL}/api/v2/self`, { withCredentials: true })
    .then((res) => res.json())
    .then((json) => {
      try {
        return SelfResponseSchema.parse(json).data
      } catch (error) {
        return null
      }
    })
}

export async function getNowPlaying(channelId: number, timestamp: number) {
  return await fetchAnyhowWithDataSchema(
    `${BACKEND_BASE_URL}/radio-manager/api/pub/v0/streams/${channelId}/now-playing`,
    {
      searchParams: [['ts', String(timestamp)]],
      withCredentials: true,
    },
    NowPlayingResponseSchema,
  )
}

export async function uploadTrackToLibrary(file: File, abortSignal: AbortSignal) {
  const form = new FormData()
  form.set('file', file)

  const {
    tracks: [track],
  } = await fetchAnyhowWithDataSchema(
    `${BACKEND_BASE_URL}/api/v2/track/upload`,
    {
      method: 'POST',
      signal: abortSignal,
      body: form,
      withCredentials: true,
    },
    UploadTrackResponseSchema,
  )

  if (!track) {
    throw new Error('Unable to upload track to library')
  }

  return track
}

export async function uploadTrackToChannel(
  channelId: number,
  file: File,
  abortSignal: AbortSignal,
) {
  const form = new FormData()
  form.set('file', file)
  form.set('stream_id', String(channelId))

  const {
    tracks: [track],
  } = await fetchAnyhowWithDataSchema(
    `${BACKEND_BASE_URL}/api/v2/track/upload`,
    {
      method: 'POST',
      signal: abortSignal,
      body: form,
      withCredentials: true,
    },
    UploadTrackToChannelResponseSchema,
  )

  if (!track) {
    throw new Error('Unable to upload track to channel')
  }

  return track
}

export async function deleteTracksById(trackIds: readonly number[]) {
  const form = new FormData()
  form.set('track_id', trackIds.join(','))

  const nullResult = await fetchAnyhowWithDataSchema(
    `${BACKEND_BASE_URL}/api/v2/track/delete`,
    {
      method: 'POST',
      body: form,
      withCredentials: true,
    },
    DeleteTracksResponseSchema,
  )
}

export async function removeTracksFromChannelById(uniqueIds: readonly string[], channelId: number) {
  const form = new FormData()
  form.set('stream_id', String(channelId))
  form.set('unique_ids', uniqueIds.join(','))

  return fetchAnyhowWithDataSchema(
    `${BACKEND_BASE_URL}/api/v2/stream/removeTracks`,
    {
      method: 'POST',
      body: form,
      withCredentials: true,
    },
    RemoveTracksFromChannelResponseSchema,
  )
}

export enum AudioFormat {
  AAC = 'aac',
  Vorbis = 'vorbis',
  Opus = 'opus',
}

export const getTrackTranscodeStream = async (
  trackId: number,
  initialPosition: number,
  audioFormat: AudioFormat | null,
  signal: AbortSignal,
): Promise<{ readonly stream: ReadableStream<Uint8Array>; readonly contentType: string }> => {
  const response = await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${trackId}/transcode`,
    {
      searchParams: [
        ['initialPosition', `${initialPosition}`],
        ['audioFormat', audioFormat ?? AudioFormat.Opus],
      ],
      signal,
      withCredentials: true,
    },
  )

  const contentType = response.headers.get('Content-Type')

  if (!contentType) {
    throw new Error('Content-Type is not defined')
  }

  if (!response.body) {
    throw new TypeError('Expected response body')
  }

  const stream = response.body

  return { stream, contentType }
}
