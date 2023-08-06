import { config } from '@/config'
import z from 'zod'

const radioManagerBackendUrl = config.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL

export const RadioManagerResponse = <T>(data: z.ZodType<T>) =>
  z.object({
    code: z.literal(1),
    message: z.literal('OK'),
    data,
  })

export const ChannelSchema = z.object({})
export type IChannel = z.infer<typeof ChannelSchema>

export const GetChannelsResponseSchema = RadioManagerResponse(z.array(ChannelSchema))

export async function getChannels(): Promise<readonly IChannel[]> {
  const response = await fetch(`${radioManagerBackendUrl}/radio-manager/api/v0/streams/`)
  const json = await response.json()

  return GetChannelsResponseSchema.parse(json).data
}
