<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/23
 * Time: 16:47
 */

namespace SenbonXSS\Models;

use PDO;

class Users
{
    /**
     * @param PDO $pdo
     * @return User[]
     */
    function fetchAllUsers(PDO $pdo): array
    {
        $ret = [];

        $q = 'SELECT username, admin FROM users';
        $r = $pdo->query($q);
        $rows = $r->fetchAll();
        foreach ($rows as $row) {
            $ret[] = new User($pdo, $row['username'], ($row['admin'] === '1'));
        }
        return $ret;
    }
}
