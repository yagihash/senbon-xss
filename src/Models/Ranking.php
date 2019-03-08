<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/23
 * Time: 16:46
 */

namespace SenbonXSS\Models;

use PDO;

class Ranking
{
    /**
     * @param PDO $pdo
     * @return array
     */
    function fetchRanking(PDO $pdo): array
    {
        $ret = [];

        $q = 'SELECT DISTINCT username, stageid FROM flagsubmissions, users WHERE users.id = userid AND users.admin = 0 AND pf = 1 GROUP BY userid ORDER BY stageid DESC, t ASC LIMIT 100';
        $r = $pdo->query($q);
        $rows = $r->fetchAll();
        foreach ($rows as $i => $row) {
            $ret[] = array(
                'rank' => $i + 1,
                'username' => $row['username'],
                'stageid' => $row['stageid'],
            );
        }
        return $ret;
    }
}
