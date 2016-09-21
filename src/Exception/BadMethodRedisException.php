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
declare(strict_types=1);

namespace Vain\Redis\Exception;

use Vain\Redis\RedisInterface;

/**
 * Class BadMethodRedisException
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class BadMethodRedisException extends RedisException
{
    /**
     * BadMethodMemcachedException constructor.
     *
     * @param RedisInterface $cache
     * @param string            $method
     */
    public function __construct(RedisInterface $cache, string $method)
    {
        parent::__construct($cache, sprintf('Method %s is not supported', $method), 0, null);
    }
}