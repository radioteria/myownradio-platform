import z from 'zod'
import { config } from '@/config'
import {
  ChannelTracksResponse,
  ChannelTracksResponseSchema,
  NowPlayingResponse,
  NowPlayingResponseSchema,
  SelfResponse,
  SelfResponseSchema,
} from '@/api/api.types'
import { isomorphicFetch } from '@/api/api.isomorphicFetch'

const BACKEND_BASE_URL = config.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL

const ChannelSchema = z.object({})
export type IChannel = z.infer<typeof ChannelSchema>

export const ITEMS_PER_REQUEST_LIMIT = 50

export const GetChannelsSchema = z.object({
  message: z.literal('OK'),
  code: z.literal(1),
  data: z.array(ChannelSchema),
})

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

export async function getChannelTracks(channelId: number, offset = 0) {
  const url = `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/${channelId}/tracks/?offset=${offset}&limit=${ITEMS_PER_REQUEST_LIMIT}`

  return await isomorphicFetch(url)
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

// Get Chunk
// http://localhost:40180/getchunk/380
