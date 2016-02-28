<?php 
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */

namespace Junty\Exception;

final class InvalidTaskType extends \Exception
{
    public function __construct()
    {
        parent::__construct('Passed tasks must be string or Junty\TaskInterface instance.');
    }
}