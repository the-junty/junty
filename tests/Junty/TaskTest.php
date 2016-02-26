<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Test\Junty;

use Junty\Task;
use Junty\Stream\StreamHandler;

class TaskTest extends \PHPUnit_Framework_TestCase
{
    public function testGetterForName()
    {
        $taskName = 'task1';
        $task = new Task($taskName, function () {});

        $this->assertEquals($task->getName(), $taskName);
    }

    public function testGetterForCallback()
    {
        $return = ':)';
        $taskCallback = function () use ($return) {
            return $return;
        };
        $task = new Task('task1', $taskCallback);
        $cb = $task->getCallback();

        $this->assertEquals($cb(), $return);
    }

    public function testInvokeMagicMethod()
    {
        $return = ':)';
        $taskCallback = function () use ($return) {
            return $return;
        };
        $task = new Task('task1', $taskCallback);

        $this->assertEquals($task(), $return);
    }

    /*public function testCallbackBindsInstanceOfStreamHandler()
    {
        $_this = null;

        $taskCallback = function () use (&$_this) {
            $_this = $this;
        }

        $task = new Task('task1', $taskCallback);

        $task();

        $this->assertTrue($_this instanceof StreamHndler);
    }*/

    public function testAddingNextTasks()
    {
        $task1 = new Task('task1', function () {});
        $task2 = new Task('task2', function () {});

        $task2->runAfter('task1');

        $this->assertTrue($task2->hasNext());
        $this->assertFalse($task1->hasNext());
    }
}