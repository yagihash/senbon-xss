<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/23
 * Time: 16:46
 */

class Genre
{
    protected $id = 0;
    protected $name = '';

    /**
     * Genre constructor.
     * @param int $id
     * @param string $name
     */
    function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
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
}