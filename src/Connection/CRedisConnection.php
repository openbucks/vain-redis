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

use Vain\Core\Connection\AbstractConnection;
use Vain\Core\Exception\NoRequiredFieldException;

/**
 * Class CRedisConnection
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedisConnection extends AbstractConnection
{

    const scripts = [
        'zAddXXNX' => 'return redis.call(\'zAdd\', KEYS[1], ARGV[1], ARGV[2], ARGV[3])', // 185a09d32f70bd6274c081aafe6a2141aee0687e
        'zAddCond' => '
                    local score = redis.call("zScore", KEYS[1], ARGV[3]);
                    if score == false then
                        return redis.call("zAdd", KEYS[1], "CH", ARGV[2], ARGV[3]);
                    end
                    if (ARGV[1] == "LT" and score > ARGV[2]) or (ARGV[1] == "GT" and score < ARGV[2]) then
                        return redis.call("zAdd", KEYS[1], "XX", "CH", ARGV[2], ARGV[3]);
                    end

                    return 0;
        ' // bb8049d9b393db5b35998e1ed05c0913bff0a683
    ];

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
    public function doEstablish()
    {
        list ($host, $port, $db, $password, $serializer) = $this->getCredentials($this->getConfigData());

        $redis = new \Redis();
        $redis->connect($host, $port);
        if ('' !== $password) {
            $redis->auth($password);
        }
        if ($serializer) {
            $redis->setOption(\Redis::OPT_SERIALIZER, $this->getSerializerValue());
        }
        $redis->select($db);

        foreach (self::scripts as $script) {
            if ([0] === $redis->script('exists', sha1($script))) {
                $redis->script('load', $script);
            }
        }

        return $redis;
    }
}
