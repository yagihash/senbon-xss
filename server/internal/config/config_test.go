package config

import (
	"os"
	"testing"

	"github.com/google/go-cmp/cmp"
)

func Test_env_validate(t *testing.T) {
	type fields struct {
		AppEnv  string
		Addr    string
		Port    int
		LogPath string
	}
	tests := []struct {
		name    string
		fields  fields
		wantErr bool
	}{
		{
			name: "ValidAppEnv",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "ValidPort",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "InvalidAppEnv",
			fields: fields{
				AppEnv:  "foobar",
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: true,
		},
		{
			name: "InvalidPositivePort",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    70000,
				LogPath: "stdout",
			},
			wantErr: true,
		},
		{
			name: "InvalidNegativePort",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    -80,
				LogPath: "stdout",
			},
			wantErr: true,
		},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			e := &env{
				AppEnv:  tt.fields.AppEnv,
				Addr:    tt.fields.Addr,
				Port:    tt.fields.Port,
				LogPath: tt.fields.LogPath,
			}
			if err := e.validate(); (err != nil) != tt.wantErr {
				t.Errorf("env.validate() error = %v, wantErr %v", err, tt.wantErr)
			}
		})
	}
}

func Test_env_validateAppEnv(t *testing.T) {
	type fields struct {
		AppEnv  string
		Addr    string
		Port    int
		LogPath string
	}
	tests := []struct {
		name    string
		fields  fields
		wantErr bool
	}{
		{
			name: "ValidAppEnvProd",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "ValidAppEnvDev",
			fields: fields{
				AppEnv:  AppEnvDev,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "ValidAppEnvLocal",
			fields: fields{
				AppEnv:  AppEnvLocal,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "InvalidAppEnv",
			fields: fields{
				AppEnv:  "foobar",
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: true,
		},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			e := &env{
				AppEnv:  tt.fields.AppEnv,
				Addr:    tt.fields.Addr,
				Port:    tt.fields.Port,
				LogPath: tt.fields.LogPath,
			}
			if err := e.validateAppEnv(); (err != nil) != tt.wantErr {
				t.Errorf("env.validateAppEnv() error = %v, wantErr %v", err, tt.wantErr)
			}
		})
	}
}

func Test_env_validatePort(t *testing.T) {
	type fields struct {
		AppEnv  string
		Addr    string
		Port    int
		LogPath string
	}
	tests := []struct {
		name    string
		fields  fields
		wantErr bool
	}{

		{
			name: "ValidPort",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "InvalidPositivePort",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    70000,
				LogPath: "stdout",
			},
			wantErr: true,
		},
		{
			name: "InvalidNegativePort",
			fields: fields{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    -80,
				LogPath: "stdout",
			},
			wantErr: true,
		},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			e := &env{
				AppEnv:  tt.fields.AppEnv,
				Addr:    tt.fields.Addr,
				Port:    tt.fields.Port,
				LogPath: tt.fields.LogPath,
			}
			if err := e.validatePort(); (err != nil) != tt.wantErr {
				t.Errorf("env.validatePort() error = %v, wantErr %v", err, tt.wantErr)
			}
		})
	}
}

func TestLoad(t *testing.T) {
	tests := []struct {
		name    string
		input   map[string]string
		want    *env
		wantErr bool
	}{
		{
			name: "Default",
			input: map[string]string{
				"ENV":     AppEnvLocal,
				"ADDR":    "127.0.0.1",
				"PORT":    "8080",
				"LOGPATH": "stdout",
			},
			want: &env{
				AppEnv:  AppEnvLocal,
				Addr:    "127.0.0.1",
				Port:    8080,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "ValidAppEnv",
			input: map[string]string{
				"ENV":     AppEnvProd,
				"ADDR":    "127.0.0.1",
				"PORT":    "8080",
				"LOGPATH": "stdout",
			},
			want: &env{
				AppEnv:  AppEnvProd,
				Addr:    "127.0.0.1",
				Port:    8080,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "ValidPort",
			input: map[string]string{
				"ENV":     AppEnvProd,
				"ADDR":    "127.0.0.1",
				"PORT":    "80",
				"LOGPATH": "stdout",
			},
			want: &env{
				AppEnv:  AppEnvProd,
				Addr:    "127.0.0.1",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "ValidAddr",
			input: map[string]string{
				"ENV":     AppEnvProd,
				"ADDR":    "0.0.0.0",
				"PORT":    "80",
				"LOGPATH": "stdout",
			},
			want: &env{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stdout",
			},
			wantErr: false,
		},
		{
			name: "ValidLogPathStderr",
			input: map[string]string{
				"ENV":     AppEnvProd,
				"ADDR":    "0.0.0.0",
				"PORT":    "80",
				"LOGPATH": "stderr",
			},
			want: &env{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "stderr",
			},
			wantErr: false,
		},
		{
			name: "ValidLogPathTmpServerLog",
			input: map[string]string{
				"ENV":     AppEnvProd,
				"ADDR":    "0.0.0.0",
				"PORT":    "80",
				"LOGPATH": "/tmp/server.log",
			},
			want: &env{
				AppEnv:  AppEnvProd,
				Addr:    "0.0.0.0",
				Port:    80,
				LogPath: "/tmp/server.log",
			},
			wantErr: false,
		},
		{
			name: "InvalidAppEnv",
			input: map[string]string{
				"ENV":     "foobar",
				"ADDR":    "0.0.0.0",
				"PORT":    "80",
				"LOGPATH": "/tmp/server.log",
			},
			want:    nil,
			wantErr: true,
		},
		{
			name: "InvalidPositivePort",
			input: map[string]string{
				"ENV":     "foobar",
				"ADDR":    "0.0.0.0",
				"PORT":    "70000",
				"LOGPATH": "/tmp/server.log",
			},
			want:    nil,
			wantErr: true,
		},
		{
			name: "InvalidNegativePort",
			input: map[string]string{
				"ENV":     "foobar",
				"ADDR":    "0.0.0.0",
				"PORT":    "-80",
				"LOGPATH": "/tmp/server.log",
			},
			want:    nil,
			wantErr: true,
		},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			if err := testLoadSetup(t, tt.input); err != nil {
				t.Error(err)
			}
			got, err := Load()
			if (err != nil) != tt.wantErr {
				t.Errorf("Load() error = %v, wantErr %v", err, tt.wantErr)
				return
			}
			if diff := cmp.Diff(got, tt.want); diff != "" {
				t.Errorf("Got unexpected value\n%s", diff)
			}
			if err := testLoadTeardown(t, tt.input); err != nil {
				t.Error(err)
			}
		})
	}
}

func testLoadSetup(t *testing.T, envvars map[string]string) error {
	t.Helper()
	for k, v := range envvars {
		if err := os.Setenv(k, v); err != nil {
			return err
		}
	}
	return nil
}

func testLoadTeardown(t *testing.T, envvars map[string]string) error {
	t.Helper()
	for k, _ := range envvars {
		if err := os.Unsetenv(k); err != nil {
			return err
		}
	}
	return nil
}
