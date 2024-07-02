FROM migrate/migrate:v4.17.1

COPY images/migration/entrypoint.sh /entrypoint.sh
COPY migrations /migrations

ENV DUMMY=0

ENTRYPOINT ["sh", "/entrypoint.sh"]
