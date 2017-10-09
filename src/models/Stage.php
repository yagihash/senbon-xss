<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/23
 * Time: 16:45
 */

class Stage
{
    /**
     * @var string
     */
    protected $key = '';

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $qtext = '';

    /**
     * @var string
     */
    protected $genre = '';

    /**
     * @var string
     */
    protected $flag = '';

    /**
     * @var int
     */
    protected $clearedUsers = 0;

    /**
     * @var int
     */
    protected $modeid = 0;

    /**
     * @var string
     */
    protected $handler = '';

    /**
     * Stage constructor.
     * @param PDO $pdo
     * @param string $key
     * @param int $id
     * @param string $name
     * @param string $qtext
     * @param string $genre
     * @param string $flag
     * @param int $clearedUsers
     * @throws Exception
     */
    function __construct(PDO $pdo, string $key, int $id = 0, string $name = '', string $qtext = '', string $genre = '', string $flag = '', int $clearedUsers = 0)
    {
        $this->key = $key;
        if ($id === 0 and
            $name === '' and
            $qtext === '' and
            $genre === '' and
            $flag === '' and
            $clearedUsers === 0) {
            $q = 'SELECT stages.id AS id, key, stages.name AS stagename, qtext, genres.name AS genrename, flag, modeid, handler  FROM stages, genres, stagemode WHERE genres.id = genreid AND modeid = stagemode.id AND key = ?';
            $stmt = $pdo->prepare($q);
            $stmt->execute([$key]);
            $row = $stmt->fetch();

            if ($row) {
                $id = (int)$row['id'];
                $name = $row['stagename'];
                $qtext = $row['qtext'];
                $genre = $row['genrename'];
                $flag = $row['flag'];
                $modeid = (int)$row['modeid'];
                $handler = $row['handler'];
            } else {
                throw new Exception('Stage not found.');
            }
        }
        $this->id = $id;
        $this->name = $name;
        $this->qtext = $qtext;
        $this->genre = $genre;
        $this->flag = $flag;
        $this->clearedUsers = $clearedUsers;
        $this->modeid = isset($modeid) ? $modeid : 0;
        $this->handler = isset($handler) ? $handler : '';
    }

    /**
     * @param PDO $pdo
     * @param string $name
     * @param string $flag
     * @param string $qtext
     * @param int $genreid
     * @param int $modeid
     * @param string $key
     */
    function edit(PDO $pdo, string $name, string $flag, string $qtext, int $genreid, int $modeid)
    {
        $q = 'UPDATE stages SET name = ?, flag = ?, qtext = ?, genreid = ?, modeid = ? WHERE key = ?';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$name, $flag, $qtext, $genreid, $modeid, $this->key]);
    }

    /**
     * @param PDO $pdo
     */
    function delete(PDO $pdo)
    {
        $q = 'DELETE FROM stages WHERE id = ?';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$this->id]);
    }

    /**
     * @param PDO $pdo
     * @param User $user
     * @param string $url
     */
    function logUrlSubmission(PDO $pdo, User $user, string $url)
    {
        $q = 'INSERT INTO urlsubmissions(userid, stageid, url) VALUES(?, ?, ?)';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$user->getId(), $this->id, $url]);
    }

    /**
     * @param PDO $pdo
     * @param User $user
     * @param string $flag
     * @param bool $pf
     */
    function logFlagSubmission(PDO $pdo, User $user, string $flag, bool $pf)
    {
        $q = 'INSERT INTO flagsubmissions(userid, stageid, flag, pf) VALUES(?, ?, ?, ?)';
        $stmt = $pdo->prepare($q);
        $stmt->execute([$user->getId(), $this->id, $flag, $pf]);
    }

    /**
     * @param int $clearedUsers
     */
    function setClearedUsers(int $clearedUsers)
    {
        $this->clearedUsers = $clearedUsers;
    }

    /**
     * @return string
     */
    function getKey(): string
    {
        return $this->key;
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
    function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    function getQtext(): string
    {
        return $this->qtext;
    }

    /**
     * @return string
     */
    function getGenre(): string
    {
        return $this->genre;
    }

    /**
     * @return string
     */
    function getFlag(): string
    {
        return $this->flag;
    }

    /**
     * @return int
     */
    function getClearedUsers(): int
    {
        return $this->clearedUsers;
    }

    /**
     * @return int
     */
    function getModeid(): int
    {
        return $this->modeid;
    }

    /**
     * @return string
     */
    function getHandler(): string
    {
        return $this->handler;
    }

}