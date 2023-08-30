import z from 'zod'
import { ChannelSchema, UserChannelTrackSchema, UserTrackSchema } from './api.types'
import camelcaseKeys from 'camelcase-keys'

export const GetChannelsSchema = z.object({
  message: z.literal('OK'),
  code: z.literal(1),
  data: z.array(ChannelSchema),
})

export const UploadTrackResponseSchema = z.object({
  code: z.literal(1),
  message: z.literal('OK'),
  data: z.object({
    tracks: z.array(UserTrackSchema),
  }),
})

export const UploadTrackToChannelResponseSchema = z.object({
  code: z.literal(1),
  message: z.literal('OK'),
  data: z.object({
    tracks: z.array(UserChannelTrackSchema),
  }),
})

export const DeleteTracksResponseSchema = z.object({
  code: z.literal(1),
  message: z.literal('OK'),
  data: z.null(),
})

export const RemoveTracksFromChannelResponseSchema = z.object({
  code: z.literal(1),
  message: z.literal('OK'),
  data: z.null(),
})
