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

namespace Vain\Redis\Connection;

use Vain\Connection\ConnectionInterface;
use Vain\Connection\Exception\NoRequiredFieldException;

/**
 * Class CRedisConnection
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedisConnection implements ConnectionInterface
{
    const REDIS_ZADD_XX_NX = "return redis.call('zAdd', KEYS[1], ARGV[1], ARGV[2], ARGV[3])";

    private $configData;

    /**
     * CRedisConnection constructor.
     *
     * @param array $configData
     */
    public function __construct(array $configData)
    {
        $this->configData = $configData;
    }

    /**
     * @param array $config
     *
     * @return string
     */
    protected function getPassword(array $config) : string
    {
        if (false === array_key_exists('password', $config)) {
            return '';
        }

        $password = $config['password'];

        if (false === array_key_exists('algo', $config)) {
            return $password;
        }

        return hash($config['algo'], $password);
    }

    /**
     * @param array $config
     *
     * @return array
     *
     * @throws NoRequiredFieldException
     */
    protected function getCredentials(array $config) : array
    {
        return [
            $config['host'],
            (int)$config['port'],
            (int)$config['db'],
            $this->getPassword($config),
            (bool)$config['serializer'],
        ];
    }

    /**
     * @return mixed
     */
    protected function getSerializerValue()
    {
        if (defined('Redis::SERIALIZER_IGBINARY') && extension_loaded('igbinary')) {
            return \Redis::SERIALIZER_IGBINARY;
        }

        return \Redis::SERIALIZER_PHP;
    }

    /**
     * @inheritDoc
     */
    public function getName() : string
    {
        return $this->configData['driver'];
    }

    /**
     * @inheritDoc
     */
    public function establish()
    {
        list ($host, $port, $db, $password, $serializer) = $this->getCredentials($this->configData);

        $redis = new \Redis();
        $redis->connect($host, $port);
        if ('' !== $password) {
            $redis->auth($password);
        }
        if ($serializer) {
            $redis->setOption(\Redis::OPT_SERIALIZER, $this->getSerializerValue());
        }
        $redis->select($db);

        if (false === $redis->script('exists', sha1(self::REDIS_ZADD_XX_NX))) {
            $redis->script('load', self::REDIS_ZADD_XX_NX);
        }

        return $redis;
    }
}
