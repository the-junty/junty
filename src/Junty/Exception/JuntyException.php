<?php 
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */

namespace Junty\Exception;

use Junty\Exception\{
    JuntyFileNotFound,
    TaskNotRegistred,
    InvalidJuntyFileReturn,
    InvalidTaskType
};

class JuntyException extends \Exception
{
    public static function juntyFileNotFound()
    {
        return new JuntyFileNotFound();
    }

    public static function taskNotRegistred($taskName)
    {
        return new TaskNotRegistred($taskName);
    }

    public static function invalidJuntyFileReturn()
    {
        return new InvalidJuntyFileReturn();
    }

    public static function invalidTaskType()
    {
        return new InvalidTaskType();
    }
}