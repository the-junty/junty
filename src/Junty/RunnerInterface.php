<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty;

use Junty\TaskInterface;

interface RunnerInterface
{
    public function run();

    public function runTask($task);
}