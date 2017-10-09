<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/30
 * Time: 18:41
 */

class Modes
{
    /**
     * @param PDO $pdo
     * @return Mode[]
     */
    function fetchAllModes(PDO $pdo): array
    {
        $ret = [];
        $q = 'SELECT id, name, handler FROM stagemode';
        $r = $pdo->query($q);
        $rows = $r->fetchAll();
        foreach ($rows as $row) {
            $ret[] = new Mode($row['id'], $row['name'], $row['handler']);
        }
        return $ret;
    }

    // TODO: Implement a method to create new genre.
    function createGenre()
    {

    }
}