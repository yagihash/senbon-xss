package main

import (
	"context"
	"os"
	"os/signal"
	"time"

	"github.com/labstack/echo"
	"github.com/labstack/echo/middleware"
	"github.com/yagihash/senbon-xss/server/internal/model/revision"
)

const (
	exitOK = iota
	exitErr

	envPort = "PORT"
)

func main() {
	os.Exit(realMain())
}

func realMain() int {
	port, ok := os.LookupEnv(envPort)
	if !ok {
		port = "8080"
	}

	e := echo.New()
	e.Use(middleware.Logger())
	e.Use(middleware.Recover())

	e.GET("/", revision.GetRevision)

	go func() {
		if err := e.Start(":" + port); err != nil {
			e.Logger.Info("shutting down the server")
		}
	}()

	quit := make(chan os.Signal)
	signal.Notify(quit, os.Interrupt)
	<-quit
	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()
	if err := e.Shutdown(ctx); err != nil {
		e.Logger.Error(err)
		return exitErr
	}

	return exitOK
}
