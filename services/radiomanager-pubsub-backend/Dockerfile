FROM node:18 as devenv

WORKDIR /code


FROM node:18 as build

WORKDIR /code

COPY package.json /code/package.json
COPY package-lock.json /code/package-lock.json
COPY tsconfig.json /code/tsconfig.json
COPY src /code/src

RUN npm ci

RUN npm run build

RUN npm prune --production


FROM node:18

USER nobody:nogroup

COPY --from=build /code/node_modules /code/node_modules
COPY --from=build /code/dist /code/dist
COPY --from=build /code/package.json /code/package.json

CMD ["node", "/code/dist/index.js"]
