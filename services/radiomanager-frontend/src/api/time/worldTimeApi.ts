import z from 'zod'
import camelKeys from 'camelcase-keys'

const WORLD_TIME_API = 'http://worldtimeapi.org/api/'

const WorldTimeSchema = z
  .object({
    abbreviation: z.string(),
    client_ip: z.string(),
    datetime: z.string(),
    day_of_week: z.number(),
    day_of_year: z.number(),
    dst: z.boolean(),
    dst_from: z.string().nullable(),
    dst_offset: z.number(),
    dst_until: z.string().nullable(),
    raw_offset: z.number(),
    timezone: z.string(),
    unixtime: z.number(),
    utc_datetime: z.string(),
    utc_offset: z.string(),
    week_number: z.number(),
  })
  .transform((o) => camelKeys(o))

export const getWorldTime = () => {
  const url = `${WORLD_TIME_API}timezone/UTC` as const

  return fetch(url)
    .then((r) => r.json())
    .then((j) => WorldTimeSchema.parse(j))
}
