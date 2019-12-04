package config

import (
	"fmt"

	"github.com/kelseyhightower/envconfig"
	"github.com/pkg/errors"
)

const (
	AppEnvLocal = "local"
	AppEnvDev   = "development"
	AppEnvProd  = "production"
)

type Option func(*env)

type env struct {
	AppEnv  string `envconfig:"ENV" default:"local"`
	Addr    string `envconfig:"ADDR" default:"127.0.0.1"`
	Port    int    `envconfig:"PORT" default:"8080"`
	LogPath string `envconfig:"LOGPATH" default:"stdout"`
}

func (e *env) validate() error {
	if err := e.validateAppEnv(); err != nil {
		return errors.Wrap(err, "failed to load env vars")
	}

	if err := e.validatePort(); err != nil {
		return errors.Wrap(err, "failed to load env vars")
	}

	return nil
}

func (e *env) validateAppEnv() error {
	switch e.AppEnv {
	case AppEnvLocal:
		// pass
	case AppEnvDev:
		// pass
	case AppEnvProd:
		// pass
	default:
		return fmt.Errorf("invalid app env [%s]", e.AppEnv)
	}
	return nil
}

func (e *env) validatePort() error {
	if 0 < e.Port && e.Port < 65536 {
		// pass
	} else {
		return fmt.Errorf("invalid port [%d]", e.Port)
	}
	return nil
}

func Load() (*env, error) {
	var e env
	if err := envconfig.Process("", &e); err != nil {
		return nil, err
	}

	if err := e.validate(); err != nil {
		return nil, err
	}

	return &e, nil
}
