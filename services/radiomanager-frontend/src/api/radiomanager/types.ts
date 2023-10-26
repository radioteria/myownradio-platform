import z from 'zod'

export const UserTrackSchema = z.object({
  tid: z.number(),
  filename: z.string(),
  artist: z.string(),
  title: z.string(),
  album: z.string(),
  trackNumber: z.string(),
  genre: z.string(),
  duration: z.number(),
})
export type UserTrack = z.infer<typeof UserTrackSchema>

export const ChannelEntrySchema = z.object({
  uniqueId: z.string(),
})
export type ChannelEntry = z.infer<typeof ChannelEntrySchema>

export const StreamDestinationSchema = z.object({
  id: z.number(),
  channelId: z.number(),
  destination: z.object({
    type: z.literal('RTMP'),
    rtmpUrl: z.string(),
    streamingKey: z.string(),
  }),
})
export type StreamDestination = z.infer<typeof StreamDestinationSchema>

export enum LiveStreamStatusEnum {
  Starting = 'Starting',
  Running = 'Running',
  Finished = 'Finished',
  Failed = 'Failed',
  Unknown = 'Unknown',
}

export const OutgoingStreamSchema = z.object({
  channelId: z.number(),
  streamId: z.string(),
  status: z.nativeEnum(LiveStreamStatusEnum),
})
export type OutgoingStream = z.infer<typeof OutgoingStreamSchema>
