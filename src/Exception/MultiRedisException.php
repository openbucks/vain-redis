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
declare(strict_types = 1);

namespace Vain\Redis\Exception;

use Vain\Redis\Multi\MultiRedisInterface;
use Vain\Core\Exception\AbstractCoreException;

/**
 * Class MultiRedisException
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class MultiRedisException extends AbstractCoreException
{
    private $multiRedis;

    /**
     * RedisException constructor.
     *
     * @param MultiRedisInterface $multiRedis
     * @param string              $message
     * @param int                 $code
     * @param \Exception          $previous
     */
    public function __construct(
        MultiRedisInterface $multiRedis,
        string $message,
        int $code = 500,
        \Exception $previous = null
    ) {
        $this->multiRedis = $multiRedis;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return MultiRedisInterface
     */
    public function getMultiRedis(): MultiRedisInterface
    {
        return $this->multiRedis;
    }
}