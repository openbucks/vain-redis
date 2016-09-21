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

namespace Vain\Redis\CRedis\Factory;

use Vain\Cache\Factory\AbstractCacheFactory;
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
    public function createCache(array $configData, $connection)
    {
        return new CRedis($connection);
    }
}