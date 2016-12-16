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

use Vain\Core\Connection\ConnectionInterface;
use Vain\Core\Connection\Factory\AbstractConnectionFactory;
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
    public function getName() : string
    {
        return 'credis';
    }

    /**
     * @inheritDoc
     */
    public function createConnection(string $connectionName) : ConnectionInterface
    {
        return new CRedisConnection($this->getConfigData($connectionName));
    }
}
