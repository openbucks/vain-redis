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

namespace Vain\Redis\Exception;

use Vain\Redis\RedisInterface;

/**
 * Class DirectExecRedisException
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class DirectExecRedisException extends RedisException
{
    /**
     * DirectExecRedisException constructor.
     *
     * @param RedisInterface $cache
     */
    public function __construct(RedisInterface $cache)
    {
        parent::__construct($cache, sprintf('Direct exec() call on redis is not allowed'));
    }
}