<?php 
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */

namespace Junty\Exception;

final class TaskNotRegistred extends \Exception
{
    public function __construct($task)
    {
        parent::__construct((string) $task . ' is not a registred task.');
    }
}