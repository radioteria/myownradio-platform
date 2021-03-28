FROM migrate/migrate:v4.13.0

COPY images/migration/entrypoint.sh /entrypoint.sh
COPY migrations /migrations

ENTRYPOINT ["sh", "/entrypoint.sh"]
