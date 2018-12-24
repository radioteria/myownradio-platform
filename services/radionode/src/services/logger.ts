import { createLogger, transports, format } from 'winston';

const logger = createLogger({
  transports: [
    new transports.Console({
      format: format.simple(),
      level: 'verbose',
    }),
  ],
});

export default logger;
