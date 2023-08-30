import z from 'zod'
import { ChannelSchema, UserChannelTrackSchema, UserTrackSchema } from './apiTypes'

const ok = <T extends z.ZodType<unknown>>(dataSchema: T) => {
  return z.object({
    message: z.literal('OK'),
    code: z.literal(1),
    data: dataSchema,
  })
}

export const GetChannelsSchema = ok(z.array(ChannelSchema))

export const UploadTrackResponseSchema = ok(
  z.object({
    tracks: z.array(UserTrackSchema),
  }),
)

export const UploadTrackToChannelResponseSchema = ok(
  z.object({
    tracks: z.array(UserChannelTrackSchema),
  }),
)

export const DeleteTracksResponseSchema = ok(z.null())

export const RemoveTracksFromChannelResponseSchema = ok(z.null())
