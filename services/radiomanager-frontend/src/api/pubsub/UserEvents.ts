import z from 'zod'

export enum UserEventType {
  RestartChannel = 'RestartChannel',
  OutgoingStreamStarted = 'OutgoingStreamStarted',
  OutgoingStreamStats = 'OutgoingStreamStats',
  OutgoingStreamFinished = 'OutgoingStreamFinished',
  OutgoingStreamError = 'OutgoingStreamError',
}

const RestartChannelUserEventSchema = z.object({
  eventType: z.literal(UserEventType.RestartChannel),
  channelId: z.number(),
})

const OutgoingStreamStartedUserEventSchema = z.object({
  channelId: z.number(),
  eventType: z.literal(UserEventType.OutgoingStreamStarted),
})

const OutgoingStreamStatsUserEventSchema = z.object({
  channelId: z.number(),
  byteCount: z.number(),
  timePosition: z.number(),
  eventType: z.literal(UserEventType.OutgoingStreamStats),
})

const OutgoingStreamFinishedUserEventSchema = z.object({
  channelId: z.number(),
  eventType: z.literal(UserEventType.OutgoingStreamFinished),
})

const OutgoingStreamErrorUserEventSchema = z.object({
  channelId: z.number(),
  eventType: z.literal(UserEventType.OutgoingStreamError),
})

export const UserEventSchema = z.union([
  RestartChannelUserEventSchema,
  OutgoingStreamStartedUserEventSchema,
  OutgoingStreamStatsUserEventSchema,
  OutgoingStreamFinishedUserEventSchema,
  OutgoingStreamErrorUserEventSchema,
])

export type UserEvent = z.TypeOf<typeof UserEventSchema>
