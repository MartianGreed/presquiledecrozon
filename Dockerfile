FROM oven/bun:1.3.14 AS bun
FROM node:24.18.0-bookworm-slim AS build
COPY --from=bun /usr/local/bin/bun /usr/local/bin/bun
WORKDIR /app
COPY package.json bun.lock ./
RUN bun install --frozen-lockfile
COPY angular.json tsconfig.json ./
COPY apps/web ./apps/web
COPY packages ./packages
RUN bun run build

FROM oven/bun:1.3.14-debian AS runtime
WORKDIR /app
ENV PORT=3000 WEB_DIRECTORY=/app/dist/web/browser UPLOAD_DIRECTORY=/app/var/uploads
COPY package.json bun.lock ./
RUN bun install --frozen-lockfile --production
COPY apps/api ./apps/api
COPY packages ./packages
COPY scripts/legacy-data.ts scripts/promote-admin.ts ./scripts/
COPY --from=build /app/dist/web ./dist/web
RUN mkdir -p /app/var/uploads && chown -R bun:bun /app/var
USER bun
EXPOSE 3000
STOPSIGNAL SIGTERM
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s CMD bun -e 'const r=await fetch("http://127.0.0.1:3000/health/ready");process.exit(r.ok?0:1)'
CMD ["bun", "apps/api/src/main.ts"]
