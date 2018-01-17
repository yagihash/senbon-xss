<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/25
 * Time: 16:42
 */

namespace SenbonXSS\Models;

use PDO;
use PDOException;
use Exception;

class Auth
{
    /**
     * @param PDO $pdo
     * @param $username
     * @param $password
     * @return User
     * @throws Exception
     */
    static function authUser(PDO $pdo, $username, $password): User
    {
        $q = 'SELECT id, username, password, admin FROM users WHERE username = ?';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$username]);
        $r = $stmt->fetch();
        if (!password_verify($password, $r['password'])) {
            return new User($pdo, $r['username'], ($r['admin'] === '1'));
        } else {
            throw new Exception('Invalid username or password.');
        }
    }

    /**
     * @param PDO $pdo
     * @param $username
     * @param $password
     * @return User
     * @throws Exception
     */
    static function createUser(PDO $pdo, $username, $password): User
    {
        try {
            if (preg_match("/\A[¥x21-¥x7e]{1,20}\z/", $username)) {
                $q = 'INSERT INTO users(username, password) VALUES(?, ?)';
                $stmt = $pdo->prepare($q);
                $stmt->execute([$username, password_hash($password, PASSWORD_BCRYPT)]);
                return new User($pdo, $username, false);
            } else {
                throw new Exception('Invalid username. (re: /\A[¥x21-¥x7e]{1,20}\z/)');
            }
        } catch (PDOException $e) {
            throw new Exception('Something wrong. Try another username.');
        }
    }

    /**
     * @param array $session
     * @return bool
     */
    static function isAuthed(array $session)
    {
        return isset($session['user']);
    }

    /**
     * @param array $session
     * @return bool
     */
    static function isAuthedAdmin(array $session)
    {
        return (isset($session['user']) and
            $session['user']->isAdmin());
    }

    /**
     * @param array $session
     * @param int $stageid
     * @param PDO $pdo
     * @return bool
     */
    static function isValidAccessToStage(array $session, int $stageid, PDO $pdo)
    {
        return (isset($session['user']) and
            $stageid <= $session['user']->calcStage($pdo) or
            Auth::isAuthedAdmin($session));
    }

    /**
     * @param $thisarg
     * @return array
     */
    static function generateTokens($thisarg)
    {
        $csrfNameKey = $thisarg->csrf->getTokenNameKey();
        $csrfValueKey = $thisarg->csrf->getTokenValueKey();
        $csrfName = $thisarg->csrf->getTokenName();
        $csrfValue = $thisarg->csrf->getTokenValue();

        return [
            'keys' => [
                'name' => $csrfNameKey,
                'value' => $csrfValueKey
            ],
            'name' => $csrfName,
            'value' => $csrfValue,
        ];
    }
}
