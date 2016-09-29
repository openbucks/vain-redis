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

namespace Vain\Redis\Connection\Factory;

use Vain\Connection\ConnectionInterface;
use Vain\Connection\Factory\AbstractConnectionFactory;
use Vain\Redis\Connection\CRedisConnection;

/**
 * Class CRedisConnectionFactory
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedisConnectionFactory extends AbstractConnectionFactory
{
    /**
     * @inheritDoc
     */
    public function createConnection(array $config) : ConnectionInterface
    {
        return new CRedisConnection($config);
    }
}