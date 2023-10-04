import { createClient } from "@redis/client"
import express from "express"
import makeDebug from "debug"
import { z, ZodError } from "zod"
import { Config } from "./config.js"

const debug = makeDebug("main")

async function main() {
  const config = Config.fromEnv(process.env)

  // Initialize Redis client
  const redisClient = createClient({
    url: `redis://${process.env.REDIS_HOST}`,
  })
  await redisClient.connect()
  const app = express()

  app.use(express.json())

  const PublishSchema = z.object({
    channel: z.string(),
    message: z.string(),
  })

  app.post("/publish", async (req, res) => {
    const userId = req.headers["user-id"]

    if (!userId || Array.isArray(userId)) {
      return res.status(400).send("User-Id header is missing or invalid.")
    }

    try {
      const validatedData = PublishSchema.parse(req.body)
      const redisChannel = `${userId}_${validatedData.channel}`

      await redisClient.publish(redisChannel, validatedData.message)

      res.status(200).send("OK")
    } catch (e) {
      if (e instanceof ZodError) {
        res.status(400).send(e.errors)
      } else {
        debug("Error on publishing: %s", e)
        res.status(500).send("Internal server error")
      }
    }
  })

  app.get("/subscribe/:channelId", async (req, res) => {
    const userId = req.headers["user-id"]
    const channelId = req.params.channelId

    if (!userId || Array.isArray(userId)) {
      return res.status(400).send("User-Id header is missing or invalid.")
    }

    const redisChannel = `${userId}_${channelId}`

    const subscription = redisClient.duplicate()
    await subscription.connect()

    res.set({
      "Content-Type": "text/event-stream",
      "Cache-Control": "no-cache",
      Connection: "keep-alive",
    })

    await subscription.subscribe(redisChannel, (message) => {
      res.write(`data: ${message}\n\n`)
    })

    req.on("close", async () => {
      await subscription.unsubscribe(redisChannel)
      await subscription.disconnect()
    })
  })

  app.listen(config.httpPort, () => {
    debug(`Server is running on port ${config.httpPort}`)
  })
}

main().catch((error) => {
  debug("Error on starting the server: %s", error)
  process.exit(1)
})
