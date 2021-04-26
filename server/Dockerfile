FROM golang:1.16 as builder
WORKDIR /go/src/github.com/yagihash/senbon-xss/server
COPY . .
ARG revision
ENV CGO_ENABLED 0
ENV GOOS linux
RUN make build REVISION=$revision

FROM alpine
COPY --from=builder /go/src/github.com/yagihash/senbon-xss/server/bin/server /server
CMD ["/server"]
