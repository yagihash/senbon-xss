<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/23
 * Time: 16:44
 */

namespace SenbonXSS\Models;

use PDO;

class User
{
    /**
     * @var int
     */
    protected $id = 0;
    /**
     * @var string
     */
    protected $username = '';
    /**
     * @var bool
     */
    protected $admin = false;

    /**
     * User constructor.
     * @param PDO $pdo
     * @param string $username
     * @param bool $admin
     */
    function __construct(PDO $pdo, string $username, bool $admin)
    {
        $this->username = $username;
        $this->admin = $admin;
        $this->id = $this->fetchId($pdo);
    }

    /**
     * @return bool
     */
    function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * @return int
     */
    function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param PDO $pdo
     * @return int
     */
    function calcStage(PDO $pdo): int
    {
        $q = 'SELECT stageid+1 AS stage FROM flagsubmissions, users WHERE users.id = userid AND pf = 1 AND userid= ? ORDER BY stageid DESC';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$this->id]);
        $row = $stmt->fetch();
        return isset($row['stage']) ? (int)$row['stage'] : 1;
    }

    /**
     * @param PDO $pdo
     * @return int
     */
    protected function fetchId(PDO $pdo): int
    {
        $q = 'SELECT id FROM users WHERE username = ?';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$this->username]);
        $row = $stmt->fetch();
        return (int)$row['id'];
    }

    // TODO: Implement method to ban the user
    function ban(PDO $pdo)
    {
        // 論理削除にしたいのでDBスキーマ側もいじらないといけない。当面必要ないから放置。
    }
}
