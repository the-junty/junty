<?php 
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */

namespace Junty\Exception;

final class JuntyFileNotFound extends \Exception
{
    public function __construct()
    {
        parent::__construct('\'juntyfile.php\' not found in your application root.');
    }
}