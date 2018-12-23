import { format, transports, createLogger } from 'winston';

const logger = createLogger({
  transports: [
    new transports.Console({
      format: format.splat(),
    }),
  ],
});

export const module = (name: any) => (level: string, message: string, ...any: any[]) =>
  logger.log(level, `[${name}] ${message}`, ...any);

export default { module };
