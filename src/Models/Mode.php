<?php
/**
 * Created by PhpStorm.
 * User: yagihash
 * Date: 2017/09/30
 * Time: 18:41
 */

namespace SenbonXSS\Models;

class Mode
{
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
    protected $handler = '';

    /**
     * Mode constructor.
     * @param int $id
     * @param string $name
     * @param string $handler
     */
    function __construct(int $id, string $name, string $handler)
    {
        $this->id = $id;
        $this->name =$name;
        $this->handler = $handler;
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
    function getHandler(): string
    {
        return $this->handler;
    }
}
