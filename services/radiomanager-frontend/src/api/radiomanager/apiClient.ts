import z from 'zod'
import { config } from '@/config'
import { isomorphicFetch } from '../isomorphicFetch'
import { ChannelEntrySchema, UserTrackSchema } from './apiTypes'

export const BACKEND_BASE_URL = config.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL

export const MAX_TRACKS_PER_REQUEST = 200

interface PageRequestOptions {
  readonly offset?: number
  readonly limit?: number
  readonly signal?: AbortSignal
}

const GetUserTracksPageSchema = z.object({
  items: z.array(UserTrackSchema),
  totalCount: z.number().positive(),
  paginationData: z.object({
    offset: z.number().positive(),
    limit: z.number().positive(),
  }),
})

export const getUserTracksPage = async (opts: PageRequestOptions = {}) => {
  const url = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v1/tracks/all`)

  url.searchParams.set('offset', String(opts?.offset ?? 0))
  url.searchParams.set('limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST))

  const res = await isomorphicFetch(url, { signal: opts?.signal })
  const json = await res.json()

  return GetUserTracksPageSchema.parse(json)
}

const GetUnusedUserTracksPageSchema = GetUserTracksPageSchema

export const getUnusedUserTracksPage = async (opts: PageRequestOptions = {}) => {
  const url = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v1/tracks/unused`)

  url.searchParams.set('offset', String(opts?.offset ?? 0))
  url.searchParams.set('limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST))

  const res = await isomorphicFetch(url, { signal: opts?.signal })
  const json = await res.json()

  return GetUnusedUserTracksPageSchema.parse(json)
}

const GetChannelTracksPageSchema = z.object({
  items: z.array(
    z.object({
      track: UserTrackSchema,
      entry: ChannelEntrySchema,
    }),
  ),
  totalCount: z.number().positive(),
  paginationData: z.object({
    offset: z.number().positive(),
    limit: z.number().positive(),
  }),
})

export const getChannelTracksPage = async (channelId: number, opts: PageRequestOptions = {}) => {
  const url = new URL(`${BACKEND_BASE_URL}/radio-manager/api/v1/tracks/channel/${channelId}`)

  url.searchParams.set('offset', String(opts?.offset ?? 0))
  url.searchParams.set('limit', String(opts?.limit ?? MAX_TRACKS_PER_REQUEST))

  const res = await isomorphicFetch(url, { signal: opts?.signal })
  const json = await res.json()

  return GetChannelTracksPageSchema.parse(json)
}
