<?php
/**
 * Junty
 *
 * @author Gabriel Jacinto aka. GabrielJMJ <gamjj74@hotmail.com>
 * @license MIT License
 */
 
namespace Junty;

use Junty\InjectableInterface;

interface TaskInterface extends InjectableInterface
{
    public function __invoke(array $params = []);
}