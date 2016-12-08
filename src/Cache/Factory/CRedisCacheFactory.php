<?php
/**
 * Vain Framework
 *
 * PHP Version 7
 *
 * @package   vain-redis
 * @license   https://opensource.org/licenses/MIT MIT License
 * @link      https://github.com/allflame/vain-redis
 */

namespace Vain\Redis\Cache\Factory;

use Vain\Cache\CacheInterface;
use Vain\Cache\Factory\AbstractCacheFactory;
use Vain\Connection\ConnectionInterface;
use Vain\Redis\Connection\CRedisConnection;
use Vain\Redis\CRedis\CRedis;

/**
 * Class CRedisCacheFactory
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedisCacheFactory extends AbstractCacheFactory
{
    /**
     * @inheritDoc
     */
    public function createCache(array $configData, ConnectionInterface $connection) : CacheInterface
    {
        /**
         * @var CRedisConnection $connection
         */
        return new CRedis($connection);
    }
}
