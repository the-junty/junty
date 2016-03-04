<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Test\Junty\Runner;

use Junty\Runner\JuntyRunner;
use Junty\Stream\StreamHandler;

class JuntyRunnerTest extends \PHPUnit_Framework_TestCase
{
    public function testTaskBindsStreamHandler()
    {
        $_this = null;

        $runner = new JuntyRunner();

        $runner->task('task_1', function () use (&$_this) {
            $_this = $this;
        });

        $runner->order('task_1');

        $runner->run();

        $this->assertTrue($_this instanceof StreamHandler);
    }
}