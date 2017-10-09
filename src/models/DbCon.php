<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/25
 * Time: 16:48
 */

class DbCon
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * DbCon constructor.
     * @param string $path4db
     */
    function __construct(string $path4db)
    {

    }

    /**
     *
     */
    function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * @return PDO
     */
    function getCon(): PDO
    {
        return $this->pdo;
    }
}