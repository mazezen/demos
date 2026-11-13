package main

import (
	"context"
	"fmt"
	"net/http"
	"time"

	"github.com/labstack/echo/v5"
	"github.com/labstack/echo/v5/middleware"
	"github.com/redis/go-redis/v9"
)

// RateLimiter Struct
type RateLimiter struct {
	limit       int
	interval    time.Duration
	redisClient *redis.Client
	luaScriptID string
}

// Custom RateLimiter Middleware
func (limiter *RateLimiter) RateLimiter() echo.MiddlewareFunc {
	return func(next echo.HandlerFunc) echo.HandlerFunc {
		return func(c *echo.Context) error {
			cIP := c.RealIP()

			raterLimited, err := limiter.isRateLimiter(cIP)
			if err != nil {
				return c.JSON(http.StatusInternalServerError, "Internal Server Error")
			}

			if raterLimited {
				return c.JSON(http.StatusTooManyRequests, "Rate Limit exceeded")
			}

			if err := limiter.redisClient.EvalSha(context.Background(), limiter.luaScriptID, []string{cIP}, limiter.interval.Seconds()).Err(); err != nil {
				return c.JSON(http.StatusInternalServerError, "Internal Server Error")
			}

			return next(c)
		}
	}
}

func (limiter *RateLimiter) isRateLimiter(clientIp string) (bool, error) {
	counter, err := limiter.redisClient.Get(context.Background(), clientIp).Int64()
	if err != nil {
		if err == redis.Nil {
			return false, nil
		}
		return false, err
	}

	if counter > int64(limiter.limit) {
		return true, nil
	}
	return false, nil
}

// init redis connection
func rc() *redis.Client {
	return redis.NewClient(&redis.Options{
		Addr:     "localhost:7379",
		Password: "123456",
		DB:       0,
	})
}

func main() {
	// Echo Instance
	e := echo.New()

	// Middleware
	e.Use(middleware.RequestLogger())
	e.Use(middleware.Recover())

	redisClient := rc()

	rateLimiterScript := `
		-- KEYS[1] is the key name
		-- ARGV[1] is the timeToLeaveInSeconds

		local counter = redis.call("incr",KEYS[1])
		if counter == 1 then
			redis.call("expire",KEYS[1],ARGV[1])
		end
		return counter
		`
	luaScriptID, err := redisClient.ScriptLoad(context.Background(), rateLimiterScript).Result()
	if err != nil {
		panic(err)
	}
	limiter := &RateLimiter{
		limit:       5,
		interval:    time.Minute,
		redisClient: redisClient,
		luaScriptID: luaScriptID,
	}

	// Router => handler
	e.GET("/", func(c *echo.Context) error {
		return c.String(http.StatusOK, "Hello World!\n")
	}, limiter.RateLimiter())

	// Start Server
	// Visit http://localhost:1323
	fmt.Println("Visit http://localhost:1323")
	sc := echo.StartConfig{Address: ":1323"}
	if err := sc.Start(context.Background(), e); err != nil {
		e.Logger.Error("Failed to start server", "error", err)
	}
}
