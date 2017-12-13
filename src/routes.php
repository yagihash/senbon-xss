<?php

use Slim\Http\Request;
use Slim\Http\Response;

define('HTTP403MSG', '403 Forbidden');
define('HTTP404MSG', '404 Not Found');
define('HTTP500MSG', '500 Internal Server Error');

/*************************************************************
 * Top menu
 *************************************************************/
$app->get('/', function (Request $req, Response $res, array $args) {
    return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
})->setName('root');

$app->get('/index', function (Request $req, Response $res, array $args) {
    if (Auth::isAuthed($_SESSION)) {
        $stages = new Stages();

        return $this->view->render($res, 'index.html', [
            'domain' => getenv('DOMAIN'),
            'pdo' => $this->pdo,
            'user' => $_SESSION['user'],
            'stages' => $stages->fetchAllStages($this->pdo),
        ]);
    } else {
        $messages = $this->flash->getMessages();

        return $this->view->render($res, 'login.html', [
            'message' => isset($messages['message']) ? $messages['message'][0] : null,
            'error' => isset($messages['error']) ? $messages['error'][0] : null,
            'csrf' => Auth::generateTokens($this),
        ]);
    }
})->setName('top');


/*************************************************************
 * Login, Logout, Register
 *************************************************************/
$app->post('/login', function (Request $req, Response $res, array $args) {
    $params = $req->getParsedBody();
    $username = filter_var($params['username']);
    $password = filter_var($params['password']);
    try {
        if ($user = Auth::authUser($this->pdo, $username, $password)) {
            $_SESSION['user'] = $user;
            session_regenerate_id(true);
            return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
        }
    } catch (Exception $e) {
        $this->flash->addMessage('error', $e->getMessage());
        return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
    }
})->setName('login');

$app->get('/logout', function (Request $req, Response $res, array $args) {
    unset($_SESSION['user']);
    $this->flash->addMessage('message', 'You\'ve successfully logged out.');
    return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
})->setName('logout');

$app->post('/register', function (Request $req, Response $res, array $args) {
    $params = $req->getParsedBody();
    $username = filter_var($params['username']);
    $password = filter_var($params['password']);
    try {
        if ($user = Auth::createUser($this->pdo, $username, $password)) {
            $_SESSION['user'] = $user;
            return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
        }
    } catch (Exception $e) {
        $this->flash->addMessage('error', $e->getMessage());
        return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
    }
})->setName('register');


/*************************************************************
 * Ranking
 *************************************************************/
$app->get('/ranking', function (Request $req, Response $res, array $args) {
    if (Auth::isAuthed($_SESSION)) {
        $tmp = new Ranking();
        $ranking = $tmp->fetchRanking($this->pdo);
        return $this->view->render($res, 'ranking.html', [
            'pdo' => $this->pdo,
            'user' => $_SESSION['user'],
            'ranking' => $ranking,
        ]);
    } else {
        $this->flash->addMessage('error', 'First of all, you have to login with your account.');
        return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
    }
})->setName('ranking');


/*************************************************************
 * Stages
 *************************************************************/
$app->group('/stage/{key:[a-f0-9]{40}}', function () {
    $this->get('', function (Request $req, Response $res, array $args) {
        try {
            $key = $req->getAttribute('key');
            $stage = new Stage($this->pdo, $key);
            if (Auth::isValidAccessToStage($_SESSION, $stage->getId(), $this->pdo)) {
                $messages = $this->flash->getMessages();

                return $this->view->render($res, 'stage.html', [
                    'pdo' => $this->pdo,
                    'user' => $_SESSION['user'],
                    'stage' => $stage,
                    'message' => isset($messages['message']) ? $messages['message'][0] : null,
                    'error' => isset($messages['error']) ? $messages['error'][0] : null,
                    'csrf' => Auth::generateTokens($this),
                ]);
            } else {
                return $res->withStatus(403)->write(HTTP403MSG);
            }
        } catch (Exception $e) {
            return $res->withStatus(404)->write(HTTP404MSG);
        }
    })->setName('stage');

    $this->post('/url', function (Request $req, Response $res, array $args) {
        try {
            $key = $req->getAttribute('key');
            $stage = new Stage($this->pdo, $key);

            if (Auth::isValidAccessToStage($_SESSION, $stage->getId(), $this->pdo)) {
                $params = $req->getParsedBody();

                $stage->logUrlSubmission($this->pdo, $_SESSION['user'], $params['url']);

                if (preg_match('/\Ahttps?:\/\//i', $params['url'])) {
                    /********************************
                     * URLアクセス関連の処理
                     ********************************/

                    $args = [
                        'cwd' => getenv('PATH4JS'),
                        'node' => getenv('PATH4NODE'),
                        'key' => $key,
                        'url' => $params['url'],
                        'flag' => $stage->getFlag(),
                    ];
                    Resque::enqueue('browse', $stage->getHandler(), $args);

                    /********************************
                     * URLアクセス関連の処理おわり
                     ********************************/

                    $this->flash->addMessage('message', 'Accessing the url. Wait a minute.');
                } else {
                    $this->flash->addMessage('error', 'Invalid URL.');
                }

                return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('stage', ['key' => $stage->getKey()]));
            } else {
                return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('top'));
            }
        } catch (Exception $e) {
            return $res->withStatus(404)->write(HTTP404MSG);
        }
    })->setName('stage-url');

    $this->post('/flag', function (Request $req, Response $res, array $args) {
        try {
            $key = $req->getAttribute('key');
            $stage = new Stage($this->pdo, $key);

            if (Auth::isValidAccessToStage($_SESSION, $stage->getId(), $this->pdo)) {
                $params = $req->getParsedBody();
                $pf = $stage->getFlag() === $params['flag'];

                $stage->logFlagSubmission($this->pdo, $_SESSION['user'], $params['flag'], $pf);
                if ($pf) {
                    $this->flash->addMessage('message', 'Congrats! You\'ve cleared this stage!');
                } else {
                    $this->flash->addMessage('error', 'Oops. It\'s not correct. Try harder!');
                }
            }
            return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('stage', ['key' => $stage->getKey()]));
        } catch (Exception $e) {
            return $res->withStatus(404)->write(HTTP404MSG);
        }
    })->setName('stage-flag');
});


/*************************************************************
 * Admin menu
 *************************************************************/
$app->group('/admin', function () {
    $this->get('', function (Request $req, Response $res, array $args) {
        if (Auth::isAuthedAdmin($_SESSION)) {
            return $this->view->render($res, 'admin.html', [
                'pdo' => $this->pdo,
                'user' => $_SESSION['user'],
            ]);
        } else {
            return $res->withStatus(403)->write(HTTP403MSG);
        }
    })->setName('admin');

    $this->group('/stage', function () {
        $this->get('', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    $stages = new Stages();
                    $genres = new Genres();
                    $modes = new Modes();

                    $messages = $this->flash->getMessages();
                    return $this->view->render($res, 'admin-stage.html', [
                        'pdo' => $this->pdo,
                        'user' => $_SESSION['user'],
                        'stages' => $stages->fetchAllStages($this->pdo),
                        'genres' => $genres->fetchAllGenres($this->pdo),
                        'modes' => $modes->fetchAllModes($this->pdo),
                        'message' => isset($messages['message']) ? $messages['message'][0] : null,
                        'error' => isset($messages['error']) ? $messages['error'][0] : null,
                        'csrf' => Auth::generateTokens($this),
                    ]);

                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (Exception $e) {
                return $res->withStatus(500)->write(HTTP500MSG);
            }
        })->setName('admin-stage');

        $this->post('/create', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    $params = $req->getParsedBody();
                    $stages = new Stages();

                    $stages->createStage($this->pdo, $params['name'], $params['qtext'], (int)$params['genreid'], $params['flag']);
                    $this->flash->addMessage('message', 'Successfully created the stage.');
                    return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('admin-stage'));
                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (PDOException $e) {
                $this->flash->addMessage('error', 'Something wrong...');
                return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('admin-stage'));
            } catch (Exception $e) {
                $this->flash->addMessage('error', $e->getMessage());
                return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('admin-stage'));
            }
        })->setName('stage-create');

        $this->group('/{key:[a-f0-9]{40}}', function () {
            $this->post('/edit', function (Request $req, Response $res, array $args) {
                try {
                    $key = $req->getAttribute('key');
                    $stage = new Stage($this->pdo, $key);

                    if (Auth::isAuthedAdmin($_SESSION)) {
                        $params = $req->getParsedBody();
                        $stage->edit($this->pdo, $params['name'], $params['flag'], $params['qtext'], $params['genreid'], $params['modeid']);

                        return $res->withJson([
                            'status' => 200,
                            'data' => 'Successfully edited the stage.',
                            'csrf' => Auth::generateTokens($this),
                        ]);
                    } else {
                        return $res->withStatus(403)->withJson([
                            'error' => HTTP403MSG,
                            'csrf' => Auth::generateTokens($this),
                        ]);
                    }
                } catch (PDOException $e) {
                    return $res->withStatus(500)->withJson([
                        'error' => '500 Internal Server Error',
                        'csrf' => Auth::generateTokens($this),
                    ]);
                } catch (Exception $e) {
                    return $res->withStatus(404)->withJson([
                        'error' => '404 Not Found',
                        'csrf' => Auth::generateTokens($this),
                    ]);
                }
            })->setName('stage-edit');

            $this->post('/delete', function (Request $req, Response $res, array $args) {
                try {
                    if (isset($_SESSION['user']) and
                        $_SESSION['user']->isAdmin()) {
                        $key = $req->getAttribute('key');
                        $stage = new Stage($this->pdo, $key);

                        $stage->delete($this->pdo);

                        $this->flash->addMessage('message', 'Successfully deleted the stage.');
                        return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('admin-stage'));
                    } else {
                        return $res->withStatus(403)->write(HTTP403MSG);
                    }
                } catch (PDOException $e) {
                    $this->flash->addMessage('error', 'Something wrong...');
                    return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('admin-stage'));
                } catch (Exception $e) {
                    $this->flash->addMessage('error', $e->getMessage());
                    return $res->withStatus(303)->withHeader('Location', $this->router->pathFor('admin-stage'));
                }
            })->setName('stage-delete');

        });
    });

    $this->group('/user', function () {
        $this->get('', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    $users = new Users();
                    $users = $users->fetchAllUsers($this->pdo);

                    return $this->view->render($res, 'admin-user.html', [
                        'pdo' => $this->pdo,
                        'user' => $_SESSION['user'],
                        'users' => $users,
                        'csrf' => Auth::generateTokens($this),
                    ]);
                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (Exception $e) {
                return $res->withStatus(404)->write(HTTP404MSG);
            }
        })->setName('admin-user');
    });

    $this->group('/export', function () {
        $this->get('', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    return $this->view->render($res, 'admin-export.html', [
                        'pdo' => $this->pdo,
                        'user' => $_SESSION['user'],
                    ]);
                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (Exception $e) {
                return $res->withStatus(500)->write(HTTP500MSG);
            }
        })->setName('admin-export');

        $this->get('/nginx', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    $stages = new Stages();
                    $stages = $stages->fetchAllStages($this->pdo);

                    return $this->view->render($res, 'nginx-conf.html', [
                        'domain' => getenv('DOMAIN'),
                        'stages' => $stages,
                    ])->withHeader('Content-Type', 'text/plain');
                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (Exception $e) {
                return $res->withStatus(500)->write(HTTP500MSG);
            }
        })->setName('admin-export-nginx');

        $this->get('/nginx-fpm', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    $stages = new Stages();
                    $stages = $stages->fetchAllStages($this->pdo);

                    return $this->view->render($res, 'nginx-conf-fpm.html', [
                        'domain' => getenv('DOMAIN'),
                        'stages' => $stages,
                    ])->withHeader('Content-Type', 'text/plain');
                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (Exception $e) {
                return $res->withStatus(500)->write(HTTP500MSG);
            }
        })->setName('admin-export-nginx-fpm');

        $this->get('/apache', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    $stages = new Stages();
                    $stages = $stages->fetchAllStages($this->pdo);

                    return $this->view->render($res, 'apache-conf.html', [
                        'stages' => $stages,
                    ])->withHeader('Content-Type', 'text/plain');
                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (Exception $e) {
                return $res->withStatus(500)->write(HTTP500MSG);
            }
        })->setName('admin-export-apache');

        $this->get('/stages', function (Request $req, Response $res, array $args) {
            try {
                if (Auth::isAuthedAdmin($_SESSION)) {
                    $stages = new Stages();
                    $stages = $stages->fetchAllStagesForJson($this->pdo);
                    return $res->withJson($stages);
                } else {
                    return $res->withStatus(403)->write(HTTP403MSG);
                }
            } catch (Exception $e) {
                return $res->withStatus(500)->write(HTTP500MSG);
            }
        })->
        setName('admin-export-stage');
    });
});

