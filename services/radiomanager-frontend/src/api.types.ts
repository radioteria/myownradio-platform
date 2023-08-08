import z from 'zod'

const PlanDataSchema = z.object({
  plan_id: z.number(),
  plan_name: z.string(),
  plan_duration: z.number().nullable(),
  plan_period: z.string().nullable(),
  plan_value: z.number(),
  limit_id: z.number(),
  streams_max: z.number().nullable(),
  time_max: z.number(),
  min_track_length: z.number(),
  max_listeners: z.number(),
})
export type PlanDataSchema = z.infer<typeof PlanDataSchema>

const UserSchema = z.object({
  uid: z.number(),
  name: z.string(),
  permalink: z.string().nullable(),
  avatar: z.string(),
  streams_count: z.number(),
  tracks_count: z.number(),
  info: z.number().nullable(),
  plan_id: z.number(),
  country_id: z.number().nullable(),
  login: z.string(),
  tracks_duration: z.number(),
  plan_expires: z.number().nullable(),
  avatar_url: z.string(),
  key: z.number(),
  plan_data: PlanDataSchema,
})
export type UserSchema = z.infer<typeof UserSchema>

export const UserStream = z.object({
  sid: z.number(),
  uid: z.number(),
  name: z.string(),
  permalink: z.string(),
  info: z.string().optional(),
  hashtags: z.string().optional(),
  category: z.number(),
  status: z.number(),
  access: z.enum(['PUBLIC', 'UNLISTED', 'PRIVATE'] as const),
  cover: z.string(),
  cover_background: z.string().nullable(),
  created: z.number(),
  bookmarks_count: z.number(),
  listeners_count: z.number(),
  tracks_count: z.number(),
  tracks_duration: z.number(),
  bookmarked: z.boolean(),
  cover_url: z.string(),
  key: z.string(),
  hashtags_array: z.array(z.string()).nullable(),
  url: z.string(),
})
export type UserStream = z.infer<typeof UserStream>

export const UserTrack = z.object({
  tid: z.number(),
  filename: z.string(),
  artist: z.string().optional(),
  title: z.string(),
  album: z.string().optional(),
  track_number: z.number().optional(),
  genre: z.string().optional(),
  date: z.number().optional(),
  buy: z.nullable(z.any()),
  duration: z.number(),
  color: z.number(),
  can_be_shared: z.number().int(),
  likes: z.number().int(),
  dislikes: z.number().int(),
})

export const SelfResponseSchema = z.object({
  code: z.literal(1),
  message: z.literal('OK'),
  data: z.object({
    user: UserSchema,
    streams: z.array(UserStream),
    tracks: z.array(UserTrack),
    client_id: z.string(),
  }),
})
export type SelfResponseSchema = z.infer<typeof SelfResponseSchema>
