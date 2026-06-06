FROM node:20-alpine AS builder
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM node:20-alpine AS runner
WORKDIR /app

ENV NODE_ENV=production
ENV PORT=10000

RUN npm install -g serve@14.2.4

COPY --from=builder /app/dist ./dist

EXPOSE 10000
CMD ["sh", "-c", "serve -s dist -l ${PORT}"]
