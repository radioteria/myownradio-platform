import z from 'zod'
import { cookies } from 'next/headers'
import { config } from '@/config'
import { SelfResponseSchema } from '@/api.types'

const SESSION_COOKIE_NAME = 'secure_session'
const BACKEND_BASE_URL = config.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL

const ChannelSchema = z.object({})
export type IChannel = z.infer<typeof ChannelSchema>

function getSessionCookieHeader(): string {
  const sessionCookie = cookies().get(SESSION_COOKIE_NAME)

  return `${sessionCookie?.name}=${sessionCookie?.value};`
}

export const GetChannelsSchema = z.object({
  message: z.literal('OK'),
  code: z.literal(1),
  data: z.array(ChannelSchema),
})

export async function getChannels(): Promise<readonly IChannel[]> {
  const url = `${BACKEND_BASE_URL}/radio-manager/api/v0/streams/`

  return await fetch(url, { headers: { Cookie: getSessionCookieHeader() } })
    .then((res) => res.json())
    .then((json) => GetChannelsSchema.parse(json).data)
}

export async function getSelf(): Promise<SelfResponseSchema['data'] | null> {
  const url = `${BACKEND_BASE_URL}/api/v2/self`

  return await fetch(url, { headers: { Cookie: getSessionCookieHeader() } })
    .then((res) => res.json())
    .then((json) => {
      try {
        return SelfResponseSchema.parse(json).data
      } catch (error) {
        return null
      }
    })
}
