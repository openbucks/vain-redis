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

namespace Vain\Redis\Multi\Pipeline;

use Vain\Redis\Exception\MixedModeRedisException;
use Vain\Redis\Multi\AbstractMultiRedis;
use Vain\Redis\Multi\MultiRedisInterface;

/**
 * Class PipelineRedis
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class PipelineRedis extends AbstractMultiRedis
{
    /**
     * @inheritDoc
     */
    public function pipeline() : MultiRedisInterface
    {
        $this->increaseLevel();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function multi() : MultiRedisInterface
    {
        throw new MixedModeRedisException($this);
    }
}