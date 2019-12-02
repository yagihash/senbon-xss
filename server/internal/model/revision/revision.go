package revision

import (
	"net/http"

	"github.com/labstack/echo"
)

var revision string

type Revision struct {
	Revision string `json:"revision"`
}

func GetRevision(c echo.Context) error {
	r := &Revision{
		Revision: revision,
	}
	return c.JSON(http.StatusOK, r)
}
