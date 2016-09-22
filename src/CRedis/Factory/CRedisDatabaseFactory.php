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

use Vain\Database\Factory\AbstractDatabaseFactory;
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
    public function createDatabase(array $configData, $connection)
    {
        return new CRedis($connection);
    }
}