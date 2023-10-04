import z, { ZodTypeDef } from "zod"

type EnvMap = { [key: string]: string | undefined }

const stringifiedNumber = z.preprocess<z.ZodNumber>(Number, z.number())

const getEnvValue = <T>(env: EnvMap, name: string, zodType: z.ZodType<T, ZodTypeDef, unknown>, defaultValue?: T) => {
  if (name in env) {
    const result = zodType.safeParse(env[name])
    if (!result.success) {
      throw new Error(`Environment variable ${name} value is not compatible with type ${zodType}`)
    }

    return result.data
  }

  if (defaultValue !== undefined) {
    return defaultValue
  }

  throw new Error(`Environment variable ${name} is required`)
}

const getEnvStringValue = (env: EnvMap, name: string, defaultValue?: string) => {
  return getEnvValue(env, name, z.string(), defaultValue)
}

const getEnvNumberValue = (env: EnvMap, name: string, defaultValue?: number) => {
  return getEnvValue(env, name, stringifiedNumber, defaultValue)
}

export class Config {
  public readonly httpPort = getEnvNumberValue(this.env, "PORT")
  public readonly redisHost = getEnvStringValue(this.env, "REDIS_HOST")

  constructor(private readonly env: EnvMap) {}

  public static fromEnv(env: EnvMap) {
    return new Config(env)
  }
}
