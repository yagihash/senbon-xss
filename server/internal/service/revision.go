package service

import (
	"context"

	pb "github.com/yagihash/senbon-xss/server/internal/pb/v1/revision"
)

var revision string

type RevisionService struct{}

func (s *RevisionService) GetRevision(ctx context.Context, req *pb.GetRevisionRequest) (*pb.RevisionResponse, error) {
	return &pb.RevisionResponse{Revision: revision}, nil
}
