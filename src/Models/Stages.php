<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/23
 * Time: 16:47
 */

namespace SenbonXSS\Models;

use PDO;
use Exception;

class Stages
{
    /**
     * @param PDO $pdo
     * @return Stage[]
     */
    function fetchAllStages(PDO $pdo): array
    {
        $ret = [];

        $q = 'SELECT stages.id AS id, key, stages.name AS stagename, qtext, genres.name AS genrename, flag, (SELECT COUNT(DISTINCT userid) FROM flagsubmissions, users WHERE pf = 1 AND stages.id = stageid AND userid = users.id AND users.admin = 0) AS clearedusers, modeid FROM stages, genres WHERE genres.id = genreid';
        $r = $pdo->query($q);
        $rows = $r->fetchAll();
        foreach ($rows as $row) {
            $ret[] = new Stage($pdo, $row['key'], $row['id'], $row['stagename'], $row['qtext'], $row['genrename'], $row['flag'], $row['clearedusers'], $row['modeid']);
        }
        return $ret;
    }

    /**
     * @param PDO $pdo
     * @param string $name
     * @param string $qtext
     * @param int $genreid
     * @param string $flag
     * @throws Exception
     */
    function createStage(PDO $pdo, string $name, string $qtext, int $genreid, string $flag)
    {
        $key = bin2hex(openssl_random_pseudo_bytes(20));

        if ($qtext === '') {
            $domain = getenv('DOMAIN');
            $qtext = sprintf('[http://%s.%s/](http://%s.%s/?q=XSS)', $key, $domain, $key, $domain);
        }

        if ($flag === '') {
            $flag = 'FLAG{' . bin2hex(openssl_random_pseudo_bytes(20)) . '}';
        }

        if (!preg_match('/\AFLAG{.+?}\z/', $flag)) {
            throw new Exception('The flag isn\'t match the format(re: \AFLAG{.+?}\z).');
        }

        $q = 'INSERT INTO stages(key, name, qtext, genreid, flag) VALUES(?, \'Stage\'||(SELECT count(id)+1 FROM stages), ?, ?, ?)';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$key, $qtext, $genreid, $flag]);
    }

    /**
     * @param PDO $pdo
     * @return array
     */
    function fetchAllStagesForJson(PDO $pdo)
    {
        $ret = [];

        $q = 'SELECT key, name, qtext, genreid, flag, modeid FROM stages';
        $r = $pdo->query($q);
        $rows = $r->fetchAll();
        foreach ($rows as $row) {
            $ret[] = [
                'key' => $row['key'],
                'name' => $row['name'],
                'qtext' => $row['qtext'],
                'genreid' => (int)$row['genreid'],
                'flag' => $row['flag'],
                'modeid' => (int)$row['modeid'],
            ];
        }
        return $ret;
    }
}
