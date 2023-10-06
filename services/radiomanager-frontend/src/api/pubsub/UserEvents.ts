import z from 'zod'

export enum UserEventType {
  RestartChannel = 'RestartChannel',
}

const RestartChannelUserEventSchema = z.object({
  eventType: z.literal(UserEventType.RestartChannel),
  channelId: z.number(),
})

export const UserEventSchema = RestartChannelUserEventSchema

export type UserEvent = z.TypeOf<typeof UserEventSchema>
