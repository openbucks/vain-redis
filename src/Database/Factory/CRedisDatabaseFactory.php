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

namespace Vain\Redis\Database\Factory;

use Vain\Connection\ConnectionInterface;
use Vain\Database\DatabaseInterface;
use Vain\Database\Factory\AbstractDatabaseFactory;
use Vain\Redis\Connection\CRedisConnection;
use Vain\Redis\CRedis\CRedis;

/**
 * Class CRedisDatabaseFactory
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedisDatabaseFactory extends AbstractDatabaseFactory
{
    /**
     * @inheritDoc
     */
    public function createDatabase(array $configData, ConnectionInterface $connection) : DatabaseInterface
    {
        /**
         * @var CRedisConnection $connection
         */
        return new CRedis($connection);
    }
}