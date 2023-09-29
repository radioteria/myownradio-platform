import { config } from '@/config'
import {
  ChannelTracksResponseSchema,
  LibraryTracksResponseSchema,
  NowPlayingResponseSchema,
  SelfResponseSchema,
} from './apiTypes'
import { isomorphicFetch } from './isomorphicFetch'
import {
  DeleteTracksResponseSchema,
  GetChannelsSchema,
  RemoveTracksFromChannelResponseSchema,
  UploadTrackResponseSchema,
  UploadTrackToChannelResponseSchema,
} from './httpSchemas'

export const BACKEND_BASE_URL = config.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL

export const MAX_TRACKS_PER_REQUEST = 200

export async function getChannels() {
  const url = `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/`

  return await isomorphicFetch(url)
    .then((res) => res.json())
    .then((json) => GetChannelsSchema.parse(json).data)
}

export async function getSelf() {
  const url = `${BACKEND_BASE_URL}/api/v2/self`

  return await isomorphicFetch(url)
    .then((res) => res.json())
    .then((json) => {
      try {
        return SelfResponseSchema.parse(json).data
      } catch (error) {
        return null
      }
    })
}

interface GetLibraryTracksOpts {
  readonly offset?: number
  readonly limit?: number
  readonly signal?: AbortSignal
}

export async function getLibraryTracks(opts?: GetLibraryTracksOpts) {
  const url = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/`)
  url.searchParams.set('offset', String(opts?.offset ?? 0))
  url.searchParams.set('limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST))

  return await isomorphicFetch(url, { signal: opts?.signal })
    .then((res) => res.json())
    .then((json) => LibraryTracksResponseSchema.parse(json).data)
}

export async function getUnusedLibraryTracks(opts?: GetLibraryTracksOpts) {
  const url = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/`)
  url.searchParams.set('unused', 'true')
  url.searchParams.set('offset', String(opts?.offset ?? 0))
  url.searchParams.set('limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST))

  return await isomorphicFetch(url, { signal: opts?.signal })
    .then((res) => res.json())
    .then((json) => LibraryTracksResponseSchema.parse(json).data)
}

interface GetChannelTracksOpts {
  readonly offset?: number
  readonly limit?: number
  readonly signal?: AbortSignal
}

export async function getChannelTracks(channelId: number, opts?: GetChannelTracksOpts) {
  const url = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/tracks/`)
  url.searchParams.set('offset', String(opts?.offset ?? 0))
  url.searchParams.set('limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST))

  return await isomorphicFetch(url, { signal: opts?.signal })
    .then((res) => res.json())
    .then((json) => ChannelTracksResponseSchema.parse(json).data)
}

export async function getNowPlaying(channelId: number, timestamp: number) {
  const url = new URL(
    `${BACKEND_BASE_URL}/radio-manager/api/pub/v0/streams/${channelId}/now-playing`,
  )

  url.searchParams.set('ts', String(timestamp))

  return await isomorphicFetch(url.toString())
    .then((res) => res.json())
    .then((json) => NowPlayingResponseSchema.parse(json).data)
}

export async function uploadTrackToLibrary(file: File, abortSignal: AbortSignal) {
  const form = new FormData()
  form.set('file', file)

  const { tracks } = await fetch(`${BACKEND_BASE_URL}/api/v2/track/upload`, {
    signal: abortSignal,
    method: 'POST',
    body: form,
    credentials: 'include',
  })
    .then((res) => res.json())
    .then((json) => UploadTrackResponseSchema.parse(json).data)

  if (tracks.length === 0) {
    throw new Error('Unable to upload track to library')
  }

  return tracks[0]
}

export async function uploadTrackToChannel(
  channelId: number,
  file: File,
  abortSignal: AbortSignal,
) {
  const form = new FormData()
  form.set('file', file)
  form.set('stream_id', String(channelId))

  const { tracks } = await fetch(`${BACKEND_BASE_URL}/api/v2/track/upload`, {
    signal: abortSignal,
    method: 'POST',
    body: form,
    credentials: 'include',
  })
    .then((res) => res.json())
    .then((json) => UploadTrackToChannelResponseSchema.parse(json).data)

  if (tracks.length === 0) {
    throw new Error('Unable to upload track to channel')
  }

  return tracks[0]
}

export async function deleteTracksById(trackIds: readonly number[]) {
  const form = new FormData()
  form.set('track_id', trackIds.join(','))

  const nullResult = await fetch(`${BACKEND_BASE_URL}/api/v2/track/delete`, {
    method: 'POST',
    body: form,
    credentials: 'include',
  })
    .then((res) => res.json())
    .then((json) => DeleteTracksResponseSchema.parse(json).data)
}

export async function removeTracksFromChannelById(uniqueIds: readonly string[], channelId: number) {
  const form = new FormData()
  form.set('stream_id', String(channelId))
  form.set('unique_ids', uniqueIds.join(','))

  await fetch(`${BACKEND_BASE_URL}/api/v2/stream/removeTracks`, {
    method: 'POST',
    body: form,
    credentials: 'include',
  })
    .then((res) => res.json())
    .then((json) => RemoveTracksFromChannelResponseSchema.parse(json).data)
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
  const audioUrl = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v0/tracks/${trackId}/transcode`)

  if (initialPosition > 0) audioUrl.searchParams.set('initialPosition', `${initialPosition}`)
  if (audioFormat) audioUrl.searchParams.set('audioFormat', audioFormat)

  const response = await fetch(audioUrl, {
    credentials: 'include',
    signal,
  })
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
