import z from 'zod'

const ConfigSchema = z.object({
  NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL: z.string(),
})

export type IConfig = z.infer<typeof ConfigSchema>

function fromEnv(env: { [k: string]: string | undefined }): IConfig {
  return ConfigSchema.parse(env)
}

export const config = fromEnv({
  NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL: process.env.NEXT_PUBLIC_RADIOMANAGER_BACKEND_URL,
})
