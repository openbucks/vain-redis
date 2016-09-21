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

namespace Vain\Redis\CRedis\Multi;

use Vain\Redis\Exception\BadMethodRedisException;
use Vain\Redis\Exception\LevelIntegrityRedisException;
use Vain\Redis\RedisInterface;

/**
 * Class AbstractMultiRedis
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
abstract class AbstractMultiRedis implements MultiRedisInterface
{
    private $cRedisInstance;

    private $level = 1;

    /**
     * MultiRedis constructor.
     *
     * @param \Redis $cRedisInstance
     */
    public function __construct(\Redis $cRedisInstance)
    {
        $this->cRedisInstance = $cRedisInstance;
    }

    /**
     * @return int
     */
    protected function increaseLevel() : int
    {
        return ++$this->level;
    }

    /**
     * @return int
     */
    protected function decreaseLevel() : int
    {
        return --$this->level;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return \Redis
     */
    public function getCRedisInstance(): \Redis
    {
        return $this->cRedisInstance;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $ttl) : MultiRedisInterface
    {
        $this->cRedisInstance->set($key, $value, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->get($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function del(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->del($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->exists($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->ttl($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl) : MultiRedisInterface
    {
        $this->cRedisInstance->expire($key, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pSet(string $key, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->set($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add(string $key, $value, int $ttl) : MultiRedisInterface
    {
        $this
            ->multi()
            ->setNx($key, $value)
            ->expire($key, $ttl)
            ->exec();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zAddMod(string $key, string $mode, int $score, $value) : MultiRedisInterface
    {
        throw new BadMethodRedisException($this, __METHOD__);

//        $zAddCommand = sprintf(
//            'return redis.call(\'zAdd\', \'%s\', \'%s\', \'%d\', \'%s\')',
//            $this->cRedisInstance->_prefix($key),
//            $mode,
//            $score,
//            $value
//        );
//
//        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zAdd(string $key, int $score, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->zAdd($key, $score, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zDelete(string $key, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->zDelete($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zDeleteRangeByScore(string $key, int $fromScore, int $toScore) : MultiRedisInterface
    {
        $this->zRemRangeByScore($key, $fromScore, $toScore);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByScore(string $key, int $fromScore, int $toScore) : MultiRedisInterface
    {
        $this->cRedisInstance->zRemRangeByScore($key, $fromScore, $toScore);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByRank(string $key, int $start, int $stop) : MultiRedisInterface
    {
        $this->cRedisInstance->zRemRangeByRank($key, $start, $stop);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScore(string $key, int $fromScore, int $toScore, array $options = []) : MultiRedisInterface
    {
        $cRedisOptions[RedisInterface::WITH_SCORES] = array_key_exists(RedisInterface::WITH_SCORES, $options) ? true : false;

        if (array_key_exists(RedisInterface::ZRANGE_OFFSET, $options)) {
            $cRedisOptions[RedisInterface::ZRANGE_LIMIT][] = $options[RedisInterface::ZRANGE_OFFSET];
        }

        if (array_key_exists(RedisInterface::ZRANGE_LIMIT, $options)) {
            $cRedisOptions[RedisInterface::ZRANGE_LIMIT][] = $options[RedisInterface::ZRANGE_LIMIT];
        }

        $this->cRedisInstance->zRevRangeByScore($key, $fromScore, $toScore, $cRedisOptions);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScoreLimit(string $key, int $fromScore, int $toScore, int $offset, int $count) : MultiRedisInterface
    {
        return $this->zRevRangeByScore(
            $key,
            $fromScore,
            $toScore,
            [
                RedisInterface::ZRANGE_LIMIT  => $count,
                RedisInterface::ZRANGE_OFFSET => $offset,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function zRangeByScore(string $key, int $fromScore, int $toScore, array $options = []) : MultiRedisInterface
    {
        $cRedisOptions[RedisInterface::WITH_SCORES] = array_key_exists(RedisInterface::WITH_SCORES, $options)
            ? $options[RedisInterface::WITH_SCORES]
            : false;

        if (array_key_exists(RedisInterface::ZRANGE_OFFSET, $options)) {
            $cRedisOptions[RedisInterface::ZRANGE_LIMIT][] = $options[RedisInterface::ZRANGE_OFFSET];
        }

        if (array_key_exists(RedisInterface::ZRANGE_LIMIT, $options)) {
            $cRedisOptions[RedisInterface::ZRANGE_LIMIT][] = $options[RedisInterface::ZRANGE_LIMIT];
        }

        $this->cRedisInstance->zRangeByScore($key, $fromScore, $toScore, $cRedisOptions);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zCard(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->zCard($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRank(string $key, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->zRank($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRank(string $key, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->zRevRank($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, int $fromScore, int $toScore) : MultiRedisInterface
    {
        $this->cRedisInstance->zCount($key, $fromScore, $toScore);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, float $score, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->zIncrBy($key, $score, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->zScore($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRange(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->cRedisInstance->zRange($key, $from, $to);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->cRedisInstance->zRevRange($key, $from, $to);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeWithScores(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->cRedisInstance->zRevRange($key, $from, $to, true);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sAdd(string $key, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->sAdd($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sCard(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->sCard($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sDiff(string $key1, string $key2) : MultiRedisInterface
    {
        $this->cRedisInstance->sDiff($key1, $key2);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sInter(string $key1, string $key2) : MultiRedisInterface
    {
        $this->cRedisInstance->sInter($key1, $key2);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sIsMember(string $key, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->sIsMember($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sMembers(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->sMembers($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sRem(string $key, string $member) : MultiRedisInterface
    {
        $this->cRedisInstance->sRem($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function append(string $key, string $value) : MultiRedisInterface
    {
        $this->cRedisInstance->append($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function decr(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->decr($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function decrBy(string $key, int $value) : MultiRedisInterface
    {
        $this->cRedisInstance->decrBy($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRange(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->cRedisInstance->getRange($key, $from, $to);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function incr(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->incr($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $value) : MultiRedisInterface
    {
        $this->cRedisInstance->incrBy($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mGet(array $keys) : MultiRedisInterface
    {
        $this->cRedisInstance->mget($keys);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mSet(array $keysAndValues) : MultiRedisInterface
    {
        $this->cRedisInstance->mset($keysAndValues);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setEx(string $key, $value, int $ttl) : MultiRedisInterface
    {
        $this->cRedisInstance->setex($key, $value, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setNx(string $key, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->setnx($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exec() : array
    {
        $currentLevel = $this->decreaseLevel();

        if (0 < $currentLevel) {
            return [];
        }

        if (0 > $currentLevel) {
            throw new LevelIntegrityRedisException($this, $currentLevel);
        }

        return $this->cRedisInstance->exec();
    }

    /**
     * @inheritDoc
     */
    public function rename(string $oldName, string $newName) : MultiRedisInterface
    {
        $this->cRedisInstance->rename($oldName, $newName);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hDel(string $key, string $field) : MultiRedisInterface
    {
        $this->cRedisInstance->hDel($key, $field);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hGet(string $key, string $field) : MultiRedisInterface
    {
        $this->cRedisInstance->hGet($key, $field);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hGetAll(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->hGetAll($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hSetAll(string $key, array $keysAndValues) : MultiRedisInterface
    {
        $this->cRedisInstance->hMset($key, $keysAndValues);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hSet(string $key, string $field, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->hSet($key, $field, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hSetNx(string $key, string $field, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->hSetNx($key, $field, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hExists(string $key, string $field) : MultiRedisInterface
    {
        $this->cRedisInstance->hExists($key, $field);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hIncrBy(string $key, string $field, int $value) : MultiRedisInterface
    {
        $this->cRedisInstance->hIncrBy($key, $field, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue) : MultiRedisInterface
    {
        $this->cRedisInstance->hIncrByFloat($key, $field, $floatValue);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hVals(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->hVals($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lIndex(string $key, int $index) : MultiRedisInterface
    {
        $this->cRedisInstance->lIndex($key, $index);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lInsert(string $key, int $index, string $pivot, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->lInsert($key, $index, $pivot, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lLen(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->lLen($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lPop(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->lPop($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lPush(string $key, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->lPush($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lPushNx(string $key, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->lPushx($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lRange(string $key, int $start, int $stop) : MultiRedisInterface
    {
        $this->cRedisInstance->lRange($key, $start, $stop);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lRem(string $key, $reference, int $count) : MultiRedisInterface
    {
        $this->cRedisInstance->lRem($key, $reference, $count);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lSet(string $key, int $index, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->lSet($key, $index, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lTrim(string $key, int $start, int $stop) : MultiRedisInterface
    {
        $this->cRedisInstance->lTrim($key, $start, $stop);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rPop(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->rPop($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rPush(string $key, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->rPush($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rPushNx(string $key, $value) : MultiRedisInterface
    {
        $this->cRedisInstance->rPushx($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function watch(string $key) : MultiRedisInterface
    {
        $this->cRedisInstance->watch($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unwatch() : MultiRedisInterface
    {
        $this->cRedisInstance->unwatch();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expireAt(string $key, int $ttl) : MultiRedisInterface
    {
        $this->cRedisInstance->expireAt($key, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush() : MultiRedisInterface
    {
        $this->cRedisInstance->flushDB();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function info() : MultiRedisInterface
    {
        $this->cRedisInstance->info();

        return $this;
    }
}