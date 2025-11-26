import { createApp } from './app.js';
import { env } from './config/env.js';
import { testConnection, disconnectDatabase } from './config/database.js';
import { logger } from './utils/logger.js';

async function main(): Promise<void> {
  // Test database connection
  const dbConnected = await testConnection();
  if (!dbConnected) {
    logger.error('Failed to connect to database. Exiting.');
    process.exit(1);
  }
  logger.info('Database connection established');

  // Create and start server
  const app = createApp();
  const server = app.listen(env.PORT, env.HOST, () => {
    logger.info(
      { host: env.HOST, port: env.PORT, env: env.NODE_ENV },
      `Server started at http://${env.HOST}:${env.PORT}`
    );
  });

  // Graceful shutdown
  const shutdown = async (signal: string): Promise<void> => {
    logger.info({ signal }, 'Shutdown signal received');

    server.close(async () => {
      logger.info('HTTP server closed');
      await disconnectDatabase();
      logger.info('Database connection closed');
      process.exit(0);
    });

    // Force exit after 10 seconds
    setTimeout(() => {
      logger.error('Forced shutdown after timeout');
      process.exit(1);
    }, 10000);
  };

  process.on('SIGTERM', () => shutdown('SIGTERM'));
  process.on('SIGINT', () => shutdown('SIGINT'));

  // Handle uncaught errors
  process.on('uncaughtException', (error) => {
    logger.fatal({ error }, 'Uncaught exception');
    process.exit(1);
  });

  process.on('unhandledRejection', (reason) => {
    logger.fatal({ reason }, 'Unhandled rejection');
    process.exit(1);
  });
}

main().catch((error) => {
  logger.fatal({ error }, 'Failed to start server');
  process.exit(1);
});
