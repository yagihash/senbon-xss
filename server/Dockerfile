FROM golang:1.13 as builder
WORKDIR /go/src/github.com/yagihash/senbon-xss/server
COPY . .
ENV CGO_ENABLED 0
ENV GOOS linux
RUN make build

FROM alpine
COPY --from=builder /go/src/github.com/yagihash/senbon-xss/server/bin/server /server
CMD ["/server"]
