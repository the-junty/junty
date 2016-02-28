<?php 
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */

namespace Junty\Exception;

final class InvalidJuntyFileReturn extends \Exception
{
    public function __construct()
    {
        parent::__construct('\'juntyfile.php\' must return an instance of Junty\RunnerInterface.');
    }
}