#!/bin/sh
set -e

exec /migrate -path /migrations/ -database "mysql://${MYSQL_USER}:${MYSQL_PASSWORD}@tcp(${MYSQL_HOST}:3306)/${MYSQL_DB}?multiStatements=true" up
