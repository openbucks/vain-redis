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

namespace Vain\Redis\Exception;

use Vain\Redis\CRedis\Multi\MultiRedisInterface;

/**
 * Class MixedModeRedisException
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class MixedModeRedisException extends MultiRedisException
{
    /**
     * MixedModeRedisException constructor.
     *
     * @param MultiRedisInterface $multiRedis
     */
    public function __construct(MultiRedisInterface $multiRedis)
    {
        parent::__construct(
            $multiRedis,
            sprintf('Misc mode of multi and pipeline is now allowed on single connection')
        );
    }
}