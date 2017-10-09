<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/26
 * Time: 15:11
 */

class Genres
{
    /**
     * @param PDO $pdo
     * @return Genre[]
     */
    function fetchAllGenres(PDO $pdo): array
    {
        $ret = [];
        $q = 'SELECT id, name FROM genres';
        $r = $pdo->query($q);
        $rows = $r->fetchAll();
        foreach ($rows as $row) {
            $ret[] = new Genre($row['id'], $row['name']);
        }
        return $ret;
    }

    // TODO: Implement a method to create new genre.
    function createGenre()
    {

    }
}