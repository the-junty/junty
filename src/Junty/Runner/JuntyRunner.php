<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty\Runner;

use Junty\TaskRunner\Runner\Runner;
use Junty\TaskRunner\Task\{Task, TaskInterface};
use Junty\Stream\StreamHandler;
use Junty\Exception\JuntyException;

class JuntyRunner extends Runner
{
    /**
     * Runs one single task
     *
     * @param string|TaskInterface $task
     */
    public function runTask($task)
    {
        if (!is_string($task) && !$task instanceof TaskInterface) {
            throw JuntyException::invalidTaskType();
        }

        $tasks = $this->getTasks();

        if (is_string($task)) {
            if (!isset($tasks[$task])) {
                throw JuntyException::taskNotRegistred($task);
            }
        }

        $task = $task instanceof TaskInterface ? $task : $tasks[$task];
        $cb = \Closure::bind($task->getCallback(), new StreamHandler());
        $cb();

        if ($task->hasNext()) {
            $this->runTask($tasks[$task->getNext()]);
        }
    }
}