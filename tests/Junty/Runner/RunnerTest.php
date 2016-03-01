<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Test\Junty;

use Junty\Runner\Runner;
use Junty\{TaskInterface, AbstractTask};
use Junty\Stream\StreamHandler;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatingAndExecutingATask()
    {
        $runner = new Runner();

        $runner->task('task_1', function () {
            $_SERVER['FOO'] = 'bar';
        });

        $runner->run();

        $this->assertEquals($_SERVER['FOO'], 'bar');
    }

    public function testOrderingTasks()
    {
        $runner = new Runner();

        $runner->task('task_1', function () {
            $_SERVER['BARZ'] = 'bar';
        });

        $runner->task('task_2', function () {
            $_SERVER['BARZ'] = 'show';
        });

        $runner->task('task_3', function () {
            $_SERVER['BARZ'] = 'kbz';
        });

        $runner->order('task_3', 'task_1', 'task_2');

        $runner->run();

        $this->assertEquals($_SERVER['BARZ'], 'show');
    }

    public function testReturnOfTaskMethodIsTheTask()
    {
        $runner = new Runner();
        $name = 'task_1';
        $callback = function () {
            $_SERVER['FOO'] = 'bar';
        };

        $return = $runner->task($name, $callback);

        $this->assertEquals($return->getName(), $name);
        $this->assertEquals($return->getCallback(), $callback);
    }

    public function testCreatingTaskByInstanceOfATask()
    {
        $runner = new Runner();

        $runner->task(new class() extends AbstractTask {
            public function getName() : string
            {
                return 'task_1';
            }

            public function getCallback() : callable
            {
                return function () {
                    $_SERVER['CHUBBY'] = 'bunny';
                };
            }
        });

        $runner->order('task_1');

        $runner->run();

        $this->assertEquals($_SERVER['CHUBBY'], 'bunny');
    }

    public function testTaskBindsStreamHandler()
    {
        $_this = null;

        $runner = new Runner();

        $runner->task('task_1', function () use (&$_this) {
            $_this = $this;
        });

        $runner->order('task_1');

        $runner->run();

        $this->assertTrue($_this instanceof StreamHandler);
    }

    public function testIfGetterForTasksReturnsAllTasks()
    {
        $runner = new Runner();

        $task2CallbackReturn = 'hiiii';

        $runner->task('task_1', function () {});
        $runner->task('task_2', function () use ($task2CallbackReturn) {
            return $task2CallbackReturn;
        });

        $tasks = $runner->getTasks();

        $this->assertCount(2, $tasks);

        $this->assertArrayHasKey('task_1', $tasks);
        $this->assertArrayHasKey('task_2', $tasks);
        
        foreach ($tasks as $task) {
            $this->assertTrue($task instanceof TaskInterface);
        }

        $cb2 = $tasks['task_2']->getCallback();

        $this->assertEquals($cb2(), $task2CallbackReturn);
    }
}