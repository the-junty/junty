<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty;

use Junty\{TaskInterface, AbstractTask};
use Junty\Stream\StreamHandler;

class Task extends AbstractTask
{
    private $name;

    private $callback;

    public function __construct(string $name, callable $callback)
    {
        $this->name = $name;
        $this->callback = $callback;
    }

    /**
     * Returns the task name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Returns the task callback
     *
     * @return callable
     */
    public function getCallback() : callable
    {
        return $this->callback;
    }
}