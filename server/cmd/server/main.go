package main

import (
	"log"
	"net"
	"os"

	"github.com/yagihash/senbon-xss/server/internal/service"

	pb "github.com/yagihash/senbon-xss/server/internal/pb/v1/revision"
	"google.golang.org/grpc"
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
	lp, err := net.Listen("tcp", ":"+port)
	if err != nil {
		log.Println(err)
		return exitErr
	}

	server := grpc.NewServer()
	pb.RegisterRevisionServer(server, &service.RevisionService{})
	if err := server.Serve(lp); err != nil {
		log.Println(err)
		return exitErr
	}

	return exitOK
}
