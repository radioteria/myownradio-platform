import express from 'express';
import { createClient } from '@redis/client';
import { z, ZodError } from 'zod';

async function main() {
    // Create an Express application
    const app = express();
    app.use(express.json());

    // Initialize Redis client
    const redisClient = createClient({
        url: `redis://${process.env.REDIS_HOST}`
    });

    // Zod schema for request body validation
    const PostSchema = z.object({
        channel: z.string(),
        message: z.string(),
    });

    // POST endpoint
    app.post('/post', async (req, res) => {
        const userId = req.headers['user-id'];
        if (!userId || Array.isArray(userId)) {
            return res.status(400).send('User-Id header is missing or invalid.');
        }

        try {
            const validatedData = PostSchema.parse(req.body);
            const channel = `${validatedData.channel}_${userId}`;

            await redisClient.publish(channel, validatedData.message);

            res.status(200).send('Message published successfully.');
        } catch (e) {
            if (e instanceof ZodError) {
            res.status(400).send(e.errors);
            } else {
            res.status(500).send('An error occurred.');
            }
        }
    });

    // Server-Sent Events endpoint
    app.get('/events', async (req, res) => {
        const userId = req.headers['user-id'];
        if (!userId || Array.isArray(userId)) {
            return res.status(400).send('User-Id header is missing or invalid.');
        }

        const channel = `${req.query.channel}_${userId}`;

        res.set({
            'Content-Type': 'text/event-stream',
            'Cache-Control': 'no-cache',
            'Connection': 'keep-alive',
        });

        const subscription = redisClient.duplicate();
        await subscription.connect();

        await subscription.subscribe(channel, (message) => {
            res.write(`data: ${message}\n\n`);
        });

        req.on('close', async () => {
            await subscription.unsubscribe(channel);
            await subscription.disconnect();
        });
    });


    // Start the server
    const PORT = process.env.PORT || 3000;
    const server = app.listen(PORT, () => {
        console.log(`Server is running on port ${PORT}`);
    });

    // Handle graceful shutdown
    const gracefulShutdown = async () => {
        await new Promise<void>((resolve) => server.close(() => resolve()));
        await redisClient.disconnect();
        process.exit(0);
    };

    process.on('SIGTERM', gracefulShutdown);
    process.on('SIGINT', gracefulShutdown);
}

main().catch(error => {
    console.error(error);
    process.exit(1);
});
