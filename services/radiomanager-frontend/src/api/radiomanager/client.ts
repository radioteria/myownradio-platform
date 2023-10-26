import z from 'zod'
import {
  ChannelEntrySchema,
  LiveStreamStatusSchema,
  StreamDestination,
  StreamDestinationSchema,
  UserTrackSchema,
} from './types'
import { fetchAnyhow, fetchAnyhowWithDataSchema, fetchAnyhowWithSchema } from '../fetchAnyhow'
import { BACKEND_BASE_URL, MAX_TRACKS_PER_REQUEST } from '../constants'

interface PageRequestOptions {
  readonly offset?: number
  readonly limit?: number
  readonly signal?: AbortSignal
}

const GetUserTracksPageSchema = z.object({
  items: z.array(UserTrackSchema),
  totalCount: z.number().nonnegative(),
  paginationData: z.object({
    offset: z.number().nonnegative(),
    limit: z.number().nonnegative(),
  }),
})

export const getUserTracksPage = async (opts: PageRequestOptions = {}) => {
  return fetchAnyhowWithSchema(
    `${BACKEND_BASE_URL}/radio-manager/api/v1/tracks/all`,
    {
      withCredentials: true,
      searchParams: [
        ['offset', String(opts?.offset ?? 0)],
        ['limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST)],
      ],
      signal: opts?.signal,
    },
    GetUserTracksPageSchema,
  )
}

const GetUnusedUserTracksPageSchema = GetUserTracksPageSchema

export const getUnusedUserTracksPage = async (opts: PageRequestOptions = {}) => {
  return fetchAnyhowWithSchema(
    `${BACKEND_BASE_URL}/radio-manager/api/v1/tracks/unused`,
    {
      withCredentials: true,
      searchParams: [
        ['offset', String(opts?.offset ?? 0)],
        ['limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST)],
      ],
      signal: opts?.signal,
    },
    GetUnusedUserTracksPageSchema,
  )
}

const ChannelTracksEntrySchema = z.object({
  track: UserTrackSchema,
  entry: ChannelEntrySchema,
})
export type ChannelTrackEntry = z.TypeOf<typeof ChannelTracksEntrySchema>

const GetChannelTracksPageResponseSchema = z.object({
  items: z.array(ChannelTracksEntrySchema),
  totalCount: z.number().nonnegative(),
  paginationData: z.object({
    offset: z.number().nonnegative(),
    limit: z.number().nonnegative(),
  }),
})

export const getChannelTracksPage = async (channelId: number, opts: PageRequestOptions = {}) => {
  return fetchAnyhowWithSchema(
    `${BACKEND_BASE_URL}/radio-manager/api/v1/tracks/channel/${channelId}`,
    {
      withCredentials: true,
      searchParams: [
        ['offset', String(opts?.offset ?? 0)],
        ['limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST)],
      ],
      signal: opts?.signal,
    },
    GetChannelTracksPageResponseSchema,
  )
}

export const playNext = async (channelId: number): Promise<void> => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/controls/play-next`,
    {
      method: 'POST',
      withCredentials: true,
    },
  )
}

export const playPrev = async (channelId: number): Promise<void> => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/controls/play-prev`,
    {
      method: 'POST',
      withCredentials: true,
    },
  )
}

export const play = async (channelId: number): Promise<void> => {
  await fetchAnyhow(`${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/controls/play`, {
    method: 'POST',
    withCredentials: true,
  })
}

export const pause = async (channelId: number): Promise<void> => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/controls/pause`,
    {
      method: 'POST',
      withCredentials: true,
    },
  )
}

export const stop = async (channelId: number): Promise<void> => {
  await fetchAnyhow(`${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/controls/stop`, {
    method: 'POST',
    withCredentials: true,
  })
}

export const seek = async (channelId: number, position: number): Promise<void> => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/controls/seek/${position}`,
    {
      method: 'POST',
      withCredentials: true,
    },
  )
}

export const playFrom = async (channelId: number, playlistPosition: number): Promise<void> => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/controls/play-from/${playlistPosition}`,
    {
      method: 'POST',
      withCredentials: true,
    },
  )
}

export const getStreamDestinations = async (): Promise<readonly StreamDestination[]> => {
  return fetchAnyhowWithSchema(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/destinations/`,
    {
      withCredentials: true,
    },
    z.array(StreamDestinationSchema),
  )
}

export const createStreamDestination = async (channelId: number): Promise<void> => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/destinations/create-for-channel/${channelId}`,
    {
      method: 'POST',
      withCredentials: true,
    },
  )
}

export const deleteStreamDestination = async (id: number): Promise<void> => {
  await fetchAnyhow(`${BACKEND_BASE_URL}/radio-manager/api/v0/destinations/${id}`, {
    method: 'DELETE',
    withCredentials: true,
  })
}

export const updateStreamDestination = async (
  id: number,
  destination: StreamDestination,
): Promise<void> => {
  await fetchAnyhow(`${BACKEND_BASE_URL}/radio-manager/api/v0/destinations/${id}`, {
    method: 'PUT',
    withCredentials: true,
    body: JSON.stringify(destination),
    headers: [['Content-Type', 'application/json']],
  })
}

export const updateRtmpSettings = async (
  channelId: number,
  rtmpUrl: string,
  rtmpStreamingKey: string,
): Promise<void> => {
  await fetchAnyhow(`${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/rtmp-settings`, {
    method: 'POST',
    withCredentials: true,
    body: JSON.stringify({ rtmpUrl, rtmpStreamingKey }),
    headers: [['Content-Type', 'application/json']],
  })
}

export const startLiveStream = async (channelId: number) => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/outgoing-stream`,
    { method: 'POST', withCredentials: true },
  )
}

export const stopLiveStream = async (channelId: number) => {
  await fetchAnyhow(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/outgoing-stream`,
    { method: 'DELETE', withCredentials: true },
  )
}

export const getLiveStream = async (channelId: number) => {
  return fetchAnyhowWithSchema(
    `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/outgoing-stream`,
    { method: 'GET', withCredentials: true },
    LiveStreamStatusSchema,
  )
}
