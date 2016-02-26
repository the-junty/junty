<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Test\Junty;

use Junty\{TaskInterface, Runner, AbstractTask};
use Junty\Stream\StreamHandler;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatingAndExecutingATask()
    {
        $tasker = new Runner();

        $tasker->task('task_1', function () {
            $_SERVER['FOO'] = 'bar';
        });

        $tasker->run();

        $this->assertEquals($_SERVER['FOO'], 'bar');
    }

    public function testOrderingTasks()
    {
        $tasker = new Runner();

        $tasker->task('task_1', function () {
            $_SERVER['BARZ'] = 'bar';
        });

        $tasker->task('task_2', function () {
            $_SERVER['BARZ'] = 'show';
        });

        $tasker->task('task_3', function () {
            $_SERVER['BARZ'] = 'kbz';
        });

        $tasker->order('task_3', 'task_1', 'task_2');

        $tasker->run();

        $this->assertEquals($_SERVER['BARZ'], 'show');
    }

    public function testReturnOfTaskMethodIsTheTask()
    {
        $tasker = new Runner();
        $name = 'task_1';
        $callback = function () {
            $_SERVER['FOO'] = 'bar';
        };

        $return = $tasker->task($name, $callback);

        $this->assertEquals($return->getName(), $name);
        $this->assertEquals($return->getCallback(), $callback);
    }

    public function testCreatingTaskByInstanceOfATask()
    {
        $tasker = new Runner();

        $tasker->task(new class() extends AbstractTask {
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

        $tasker->order('task_1');

        $tasker->run();

        $this->assertEquals($_SERVER['CHUBBY'], 'bunny');
    }

    public function testTaskBindsStreamHandler()
    {
        $_this = null;

        $tasker = new Runner();

        $tasker->task('task_1', function () use (&$_this) {
            $_this = $this;
        });

        $tasker->order('task_1');

        $tasker->run();

        $this->assertTrue($_this instanceof StreamHandler);
    }
}