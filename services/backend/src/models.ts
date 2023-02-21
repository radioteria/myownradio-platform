import z from 'zod'

export const Channel = z.object({
  access: z.string(),
  bookmarked: z.number(),
  bookmarks_count: z.number(),
  cover: z.string(),
  cover_background: z.nullable(z.string()),
  created: z.number(),
  hashtags: z.string(),
  info: z.string(),
  is_featured: z.number(),
  listeners_count: z.number(),
  name: z.string(),
  permalink: z.string(),
  playbacks: z.string(),
  sid: z.string(),
  status: z.string(),
  uid: z.number(),
})

export const User = z.object({
  avatar: z.string(),
  country_id: z.nullable(z.number()),
  info: z.nullable(z.string()),
  login: z.string(),
  name: z.string(),
  permalink: z.nullable(z.string()),
  plan_id: z.number(),
  streams_count: z.number(),
  tracks_count: z.number(),
  uid: z.number(),
})

export const IcyMetadata = z.object({
  stream_title: z.string(),
})
export type IcyMetadata = z.TypeOf<typeof IcyMetadata>

const ICY_METADATA_REGEX = /^StreamTitle='(.+)';.*/

export function decodeIcyMetadata(rawMetadata: string): IcyMetadata | null {
  const matchResult = rawMetadata.match(ICY_METADATA_REGEX)

  if (matchResult === null || matchResult[1] === undefined) {
    return null
  }

  return {
    stream_title: matchResult[1],
  }
}
