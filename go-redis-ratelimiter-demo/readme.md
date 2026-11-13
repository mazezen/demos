# go-redis-ratelimiter-demo

A small Echo server that rate limits requests per client IP using Redis and a Lua script.

Each IP gets a counter key in Redis. The first request in a window sets the TTL, and every request after that increments the counter. Once the counter passes the limit, the middleware returns `429 Too Many Requests` until the key expires.

## Requirements

- Go 1.26+
- A running Redis instance

## Run

Start Redis, either on the host or in Docker:

```bash
docker run -d --name redis -p 7379:6379 redis:7 redis-server --requirepass 123456
```

Then start the server:

```bash
go run ./main.go
```

The server listens on `http://localhost:1323`.

## Test

```bash
curl http://localhost:1323/
```

Send requests past the limit and the server starts returning `429` for that IP. The block is released when the Redis key expires (one minute).

## Configuration

Redis connection is set in `rc()` in `main.go`:

```go
func rc() *redis.Client {
	return redis.NewClient(&redis.Options{
		Addr:     "localhost:7379",
		Password: "123456",
		DB:       0,
	})
}
```

Limit and window are set where the limiter is created:

```go
limiter := &RateLimiter{
	limit:       5,
	interval:    time.Minute,
	redisClient: redisClient,
	luaScriptID: luaScriptID,
}
```

- `limit` – requests allowed per IP per window
- `interval` – how long the counter key lives, in seconds passed to the Lua script

If you change the Redis password or port, update `rc()` to match.

## Notes

- The limiter is applied per route. The middleware is attached to the handler in `main.go`:

  ```go
  e.GET("/", handler, limiter.RateLimiter())
  ```

- Client IP comes from `c.RealIP()`. Behind a proxy, make sure Echo is configured to trust the forwarding headers.
- The Lua script is loaded once at startup with `SCRIPT LOAD` and called with `EVALSHA`. If Redis restarts, the server needs a restart too.
