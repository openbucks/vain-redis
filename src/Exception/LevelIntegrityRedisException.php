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

/**
 * Class LevelIntegrityRedisException
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class LevelIntegrityRedisException extends MultiRedisException
{
    /**
     * LevelIntegrityRedisException constructor.
     *
     * @param MultiRedisInterface $multiRedis
     * @param int            $level
     */
    public function __construct(MultiRedisInterface $multiRedis, int $level)
    {
        parent::__construct($multiRedis, sprintf('Level integrity check exception for level %d', $level));
    }
}