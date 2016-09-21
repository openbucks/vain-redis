<?php
/**
 * Vain Framework
 *
 * PHP Version 7
 *
 * @package   vain-cache
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/allflame/vain-cache
 */
declare(strict_types = 1);

namespace Vain\Redis\Exception;

use Vain\Redis\RedisInterface;
use Vain\Core\Exception\AbstractCoreException;

/**
 * Class RedisException
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class RedisException extends AbstractCoreException
{
    /**
     * RedisException constructor.
     *
     * @param RedisInterface $cache
     * @param string         $message
     * @param int            $code
     * @param \Exception     $previous
     */
    public function __construct(RedisInterface $cache, string $message, int $code, \Exception $previous = null)
    {
        parent::__construct($cache, $message, $code, $previous);
    }
}