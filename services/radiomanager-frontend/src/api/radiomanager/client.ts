import z from 'zod'
import { ChannelEntrySchema, UserTrackSchema } from './types'
import { fetchAnyhow, fetchAnyhowWithSchema } from '../fetchAnyhow'
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
