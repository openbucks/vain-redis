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

namespace Vain\Redis\CRedis;

use Vain\Redis\Exception\BadMethodRedisException;
use Vain\Redis\Exception\DirectExecRedisException;
use Vain\Redis\CRedis\Multi\MultiRedisInterface;
use Vain\Redis\CRedis\Multi\Pipeline\PipelineRedis;
use Vain\Redis\CRedis\Multi\Transaction\TransactionRedis;
use Vain\Redis\RedisInterface;

/***
 * Class CRedis
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedis implements RedisInterface
{
    private $cRedisInstance;

    /**
     * CRedis constructor.
     *
     * @param \Redis $cRedisInstance
     */
    public function __construct(\Redis $cRedisInstance)
    {
        $this->cRedisInstance = $cRedisInstance;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $ttl) : bool
    {
        return $this->cRedisInstance->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        if (false === ($result = $this->cRedisInstance->get($key))) {
            return null;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function del(string $key) : bool
    {
        return (1 === $this->cRedisInstance->del($key));
    }

    /**
     * @inheritDoc
     */
    public function has(string $key) : bool
    {
        return $this->cRedisInstance->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key) : int
    {
        if (false === ($result = $this->cRedisInstance->ttl($key))) {
            return 0;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl) : bool
    {
        return $this->cRedisInstance->expire($key, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function pSet(string $key, $value) : bool
    {
        return $this->cRedisInstance->set($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function add(string $key, $value, int $ttl) : bool
    {
        $result = $this
            ->multi()
            ->setNx($key, $value)
            ->expire($key, $ttl)
            ->exec();

        return (isset($result[0]) && $result[0]);
    }

    /**
     * @inheritDoc
     */
    public function zAddMod(string $key, string $mode, int $score, $value) : bool
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
//        return $this->cRedisInstance->eval($zAddCommand);
    }

    /**
     * @inheritDoc
     */
    public function zAdd(string $key, int $score, $value) : bool
    {
        return (1 === $this->cRedisInstance->zAdd($key, $score, $value));
    }

    /**
     * @inheritDoc
     */
    public function zDelete(string $key, string $member) : bool
    {
        return (1 === $this->cRedisInstance->zDelete($key, $member));
    }

    /**
     * @inheritDoc
     */
    public function zDeleteRangeByScore(string $key, int $fromScore, int $toScore) : int
    {
        return $this->zRemRangeByScore($key, $fromScore, $toScore);
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByScore(string $key, int $fromScore, int $toScore) : int
    {
        return $this->cRedisInstance->zRemRangeByScore($key, $fromScore, $toScore);
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByRank(string $key, int $start, int $stop) : int
    {
        return $this->cRedisInstance->zRemRangeByRank($key, $start, $stop);
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScore(string $key, int $fromScore, int $toScore, array $options = []) : array
    {
        $cRedisOptions[self::WITH_SCORES] = array_key_exists(self::WITH_SCORES, $options) ? true : false;

        if (array_key_exists(self::ZRANGE_OFFSET, $options)) {
            $cRedisOptions[self::ZRANGE_LIMIT][] = $options[self::ZRANGE_OFFSET];
        }

        if (array_key_exists(self::ZRANGE_LIMIT, $options)) {
            $cRedisOptions[self::ZRANGE_LIMIT][] = $options[self::ZRANGE_LIMIT];
        }

        return $this->cRedisInstance->zRevRangeByScore($key, $fromScore, $toScore, $cRedisOptions);
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScoreLimit(string $key, int $fromScore, int $toScore, int $offset, int $count) : array
    {
        return $this->zRevRangeByScore(
            $key,
            $fromScore,
            $toScore,
            [
                self::ZRANGE_LIMIT  => $count,
                self::ZRANGE_OFFSET => $offset,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function zRangeByScore(string $key, int $fromScore, int $toScore, array $options = []) : array
    {
        $cRedisOptions[self::WITH_SCORES] = array_key_exists(self::WITH_SCORES, $options)
            ? $options[self::WITH_SCORES]
            : false;

        if (array_key_exists(self::ZRANGE_OFFSET, $options)) {
            $cRedisOptions[self::ZRANGE_LIMIT][] = $options[self::ZRANGE_OFFSET];
        }

        if (array_key_exists(self::ZRANGE_LIMIT, $options)) {
            $cRedisOptions[self::ZRANGE_LIMIT][] = $options[self::ZRANGE_LIMIT];
        }

        return $this->cRedisInstance->zRangeByScore($key, $fromScore, $toScore, $cRedisOptions);
    }

    /**
     * @inheritDoc
     */
    public function zCard(string $key) : int
    {
        return $this->cRedisInstance->zCard($key);
    }

    /**
     * @inheritDoc
     */
    public function zRank(string $key, string $member) : int
    {
        return $this->cRedisInstance->zRank($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRevRank(string $key, string $member) : int
    {
        return $this->cRedisInstance->zRevRank($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, int $fromScore, int $toScore) : int
    {
        return $this->cRedisInstance->zCount($key, $fromScore, $toScore);
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, float $score, string $member) : float
    {
        return $this->cRedisInstance->zIncrBy($key, $score, $member);
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member) : float
    {
        return $this->cRedisInstance->zScore($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRange(string $key, int $from, int $to) : array
    {
        return $this->cRedisInstance->zRange($key, $from, $to);
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int $from, int $to) : array
    {
        return $this->cRedisInstance->zRevRange($key, $from, $to);
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeWithScores(string $key, int $from, int $to) : array
    {
        return $this->cRedisInstance->zRevRange($key, $from, $to, true);
    }

    /**
     * @inheritDoc
     */
    public function sAdd(string $key, string $member) : bool
    {
        return (1 === $this->cRedisInstance->sAdd($key, $member));
    }

    /**
     * @inheritDoc
     */
    public function sCard(string $key) : int
    {
        return $this->cRedisInstance->sCard($key);
    }

    /**
     * @inheritDoc
     */
    public function sDiff(string $key1, string $key2) : array
    {
        return $this->cRedisInstance->sDiff($key1, $key2);
    }

    /**
     * @inheritDoc
     */
    public function sInter(string $key1, string $key2) : array
    {
        return $this->cRedisInstance->sInter($key1, $key2);
    }

    /**
     * @inheritDoc
     */
    public function sIsMember(string $key, string $member) : bool
    {
        return $this->cRedisInstance->sIsMember($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function sMembers(string $key) : array
    {
        return $this->cRedisInstance->sMembers($key);
    }

    /**
     * @inheritDoc
     */
    public function sRem(string $key, string $member) : bool
    {
        return (1 === $this->cRedisInstance->sRem($key, $member));
    }

    /**
     * @inheritDoc
     */
    public function append(string $key, string $value) : bool
    {
        return (0 < $this->cRedisInstance->append($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function decr(string $key) : int
    {
        return $this->cRedisInstance->decr($key);
    }

    /**
     * @inheritDoc
     */
    public function decrBy(string $key, int $value) : int
    {
        return $this->cRedisInstance->decrBy($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function getRange(string $key, int $from, int $to) : array
    {
        return $this->cRedisInstance->getRange($key, $from, $to);
    }

    /**
     * @inheritDoc
     */
    public function incr(string $key) : int
    {
        return $this->cRedisInstance->incr($key);
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $value) : int
    {
        return $this->cRedisInstance->incrBy($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function mGet(array $keys) : array
    {
        return $this->cRedisInstance->mget($keys);
    }

    /**
     * @inheritDoc
     */
    public function mSet(array $keysAndValues) : bool
    {
        return $this->cRedisInstance->mset($keysAndValues);
    }

    /**
     * @inheritDoc
     */
    public function setEx(string $key, $value, int $ttl) : bool
    {
        return $this->cRedisInstance->setex($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function setNx(string $key, $value) : bool
    {
        return $this->cRedisInstance->setnx($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function pipeline() : MultiRedisInterface
    {
        $this->cRedisInstance->multi(\Redis::PIPELINE);

        return new PipelineRedis($this->cRedisInstance);
    }

    /**
     * @inheritDoc
     */
    public function multi() : MultiRedisInterface
    {
        $this->cRedisInstance->multi(\Redis::MULTI);

        return new TransactionRedis($this->cRedisInstance);
    }

    /**
     * @inheritDoc
     */
    public function exec() : array
    {
        throw new DirectExecRedisException($this);
    }

    /**
     * @inheritDoc
     */
    public function rename(string $oldName, string $newName) : bool
    {
        return $this->cRedisInstance->rename($oldName, $newName);
    }

    /**
     * @inheritDoc
     */
    public function hDel(string $key, string $field) : bool
    {
        return (1 === $this->cRedisInstance->hDel($key, $field));
    }

    /**
     * @inheritDoc
     */
    public function hGet(string $key, string $field)
    {
        return $this->cRedisInstance->hGet($key, $field);
    }

    /**
     * @inheritDoc
     */
    public function hGetAll(string $key) : array
    {
        return $this->cRedisInstance->hGetAll($key);
    }

    /**
     * @inheritDoc
     */
    public function hSetAll(string $key, array $keysAndValues) : bool
    {
        return $this->cRedisInstance->hMset($key, $keysAndValues);
    }

    /**
     * @inheritDoc
     */
    public function hSet(string $key, string $field, $value) : bool
    {
        return (1 === $this->cRedisInstance->hSet($key, $field, $value));
    }

    /**
     * @inheritDoc
     */
    public function hSetNx(string $key, string $field, $value) : bool
    {
        return $this->cRedisInstance->hSetNx($key, $field, $value);
    }

    /**
     * @inheritDoc
     */
    public function hExists(string $key, string $field) : bool
    {
        return $this->cRedisInstance->hExists($key, $field);
    }

    /**
     * @inheritDoc
     */
    public function hIncrBy(string $key, string $field, int $value) : int
    {
        return $this->cRedisInstance->hIncrBy($key, $field, $value);
    }

    /**
     * @inheritDoc
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue) : float
    {
        return $this->cRedisInstance->hIncrByFloat($key, $field, $floatValue);
    }

    /**
     * @inheritDoc
     */
    public function hVals(string $key) : array
    {
        return $this->cRedisInstance->hVals($key);
    }

    /**
     * @inheritDoc
     */
    public function lIndex(string $key, int $index) : string
    {
        return $this->cRedisInstance->lIndex($key, $index);
    }

    /**
     * @inheritDoc
     */
    public function lInsert(string $key, int $index, string $pivot, $value) : bool
    {
        return (-1 !== $this->cRedisInstance->lInsert($key, $index, $pivot, $value));
    }

    /**
     * @inheritDoc
     */
    public function lLen(string $key) : int
    {
        return $this->cRedisInstance->lLen($key);
    }

    /**
     * @inheritDoc
     */
    public function lPop(string $key)
    {
        return $this->cRedisInstance->lPop($key);
    }

    /**
     * @inheritDoc
     */
    public function lPush(string $key, $value) : bool
    {
        return (false !== $this->cRedisInstance->lPush($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function lPushNx(string $key, $value) : bool
    {
        return (false !== $this->cRedisInstance->lPushx($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function lRange(string $key, int $start, int $stop) : array
    {
        return $this->cRedisInstance->lRange($key, $start, $stop);
    }

    /**
     * @inheritDoc
     */
    public function lRem(string $key, $reference, int $count) : int
    {
        if (false === ($result = $this->cRedisInstance->lRem($key, $reference, $count))) {
            return 0;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function lSet(string $key, int $index, $value) : bool
    {
        return $this->cRedisInstance->lSet($key, $index, $value);
    }

    /**
     * @inheritDoc
     */
    public function lTrim(string $key, int $start, int $stop) : array
    {
        if (false === ($result = $this->cRedisInstance->lTrim($key, $start, $stop))) {
            return [];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function rPop(string $key)
    {
        return $this->cRedisInstance->rPop($key);
    }

    /**
     * @inheritDoc
     */
    public function rPush(string $key, $value) : bool
    {
        return (false !== $this->cRedisInstance->rPush($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function rPushNx(string $key, $value) : bool
    {
        return (false !== $this->cRedisInstance->rPushx($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function watch(string $key) : RedisInterface
    {
        $this->cRedisInstance->watch($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unwatch() : RedisInterface
    {
        $this->cRedisInstance->unwatch();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expireAt(string $key, int $ttl) : bool
    {
        return $this->cRedisInstance->expireAt($key, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function flush() : RedisInterface
    {
        $this->cRedisInstance->flushDB();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function info() : array
    {
        return $this->cRedisInstance->info();
    }
}