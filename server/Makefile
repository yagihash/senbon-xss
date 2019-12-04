.DEFAULT_GOAL := build

BIN ?= server
REVISION ?= $(shell git describe --always)
LDFLAGS := '-w -s -X github.com/yagihash/senbon-xss/server/internal/model/revision.revision=$(REVISION)'

.PHONY: build
build:
	go build -ldflags $(LDFLAGS) -v -o bin/$(BIN) cmd/$(BIN)/main.go

.PHONY: run
run:
	go run -ldflags $(LDFLAGS) cmd/server/main.go

.PHONY: vet
vet:
	go vet ./...

.PHONY: test
test: vet
	richgo test -v -cover -race ./...

.PHONY: protoc
protoc:
	protoc -I ../proto/ --go_out=plugins=grpc:./internal/pb ../proto/*/*.proto
