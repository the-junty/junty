<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty;

use Junty\TaskInterface;

abstract class AbstractTask implements TaskInterface
{
    private $next = [];

    public function __invoke(array $params = [])
    {
        $cb = $this->getCallback();
        return $cb(...$params);
    }

    /**
     * Indicates what task must be runned after this one
     *
     * @param string|array $task
     *
     * @return self
     */
    public function runAfter($task) : self
    {
        if (!is_string($task) && !is_array($task)) {
            throw new \InvalidArgumentException('Pass an array with the tasks or a single one.');
        }

        is_string($task) ? $this->next[] = $task : $this->next = array_merge($this->next, $task);

        return $this;
    }

    /**
     * Checks if after this task, another one will be executed
     *
     * @return boolean
     */
    public function hasNext() : bool
    {
        return count($this->next);
    }
}