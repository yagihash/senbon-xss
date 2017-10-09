<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/23
 * Time: 16:47
 */

class Stages
{
    /**
     * @param PDO $pdo
     * @return Stage[]
     */
    function fetchAllStages(PDO $pdo): array
    {
        $ret = [];

        $q = 'SELECT stages.id AS id, key, stages.name AS stagename, qtext, genres.name AS genrename, flag, (SELECT COUNT(DISTINCT userid) FROM flagsubmissions, users WHERE pf = 1 AND stages.id = stageid AND userid = users.id AND users.admin = 0) AS clearedusers FROM stages, genres WHERE genres.id = genreid';
        $r = $pdo->query($q);
        $rows = $r->fetchAll();
        foreach ($rows as $row) {
            $ret[] = new Stage($pdo, $row['key'], $row['id'], $row['stagename'], $row['qtext'], $row['genrename'], $row['flag'], $row['clearedusers']);
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
            $qtext = '[http://' . $key . '.knocsk.xss.moe/](http://' . $key . '.knock.xss.moe/?q=XSS)';
        }

        if ($flag === '') {
            $flag = 'FLAG{' . bin2hex(openssl_random_pseudo_bytes(20)) . '}';
        }

        if (!preg_match('/\AFLAG{.+?}\z/', $flag)) {
            throw new Exception('The flag isn\'t match the format(re: \AFLAG{.+?}\z).');
        }

        $q = 'INSERT INTO stages(key, name, qtext, genreid, flag) VALUES(?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$key, $name, $qtext, $genreid, $flag]);
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