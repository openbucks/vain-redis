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

use Vain\Database\Generator\GeneratorInterface;
use Vain\Redis\Connection\CRedisConnection;
use Vain\Redis\Exception\BadMethodRedisException;
use Vain\Redis\Multi\MultiRedisInterface;
use Vain\Redis\Multi\Pipeline\PipelineRedis;
use Vain\Redis\Multi\Transaction\TransactionRedis;
use Vain\Redis\RedisInterface;

/***
 * Class CRedis
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
class CRedis implements RedisInterface
{
    private $cRedisConnection;

    /**
     * CRedis constructor.
     *
     * @param CRedisConnection $cRedisConnection
     */
    public function __construct(CRedisConnection $cRedisConnection)
    {
        $this->cRedisConnection = $cRedisConnection;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $ttl) : bool
    {
        return $this->cRedisConnection->establish()->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        if (false === ($result = $this->cRedisConnection->establish()->get($key))) {
            return null;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function del(string $key) : bool
    {
        return (1 === $this->cRedisConnection->establish()->del($key));
    }

    /**
     * @inheritDoc
     */
    public function has(string $key) : bool
    {
        return $this->cRedisConnection->establish()->exists($key);
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key) : int
    {
        if (false === ($result = $this->cRedisConnection->establish()->ttl($key))) {
            return 0;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl) : bool
    {
        return $this->cRedisConnection->establish()->expire($key, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function pSet(string $key, $value) : bool
    {
        return $this->cRedisConnection->establish()->set($key, $value);
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
        if (false !== $this->cRedisConnection->establish()->evalSha(
                sha1(CRedisConnection::REDIS_ZADD_XX_NX),
                [
                    $this->cRedisConnection->establish()->_prefix(
                        $key
                    ),
                    $mode,
                    $score,
                    $value,
                ],
                1
            )) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function zAdd(string $key, int $score, $value) : bool
    {
        return (1 === $this->cRedisConnection->establish()->zAdd($key, $score, $value));
    }

    /**
     * @inheritDoc
     */
    public function zDelete(string $key, string $member) : bool
    {
        return (1 === $this->cRedisConnection->establish()->zDelete($key, $member));
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
        return $this->cRedisConnection->establish()->zRemRangeByScore($key, $fromScore, $toScore);
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByRank(string $key, int $start, int $stop) : int
    {
        return $this->cRedisConnection->establish()->zRemRangeByRank($key, $start, $stop);
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

        return $this->cRedisConnection->establish()->zRevRangeByScore($key, $fromScore, $toScore, $cRedisOptions);
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

        return $this->cRedisConnection->establish()->zRangeByScore($key, $fromScore, $toScore, $cRedisOptions);
    }

    /**
     * @inheritDoc
     */
    public function zCard(string $key) : int
    {
        return $this->cRedisConnection->establish()->zCard($key);
    }

    /**
     * @inheritDoc
     */
    public function zRank(string $key, string $member) : int
    {
        return $this->cRedisConnection->establish()->zRank($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRevRank(string $key, string $member) : int
    {
        return $this->cRedisConnection->establish()->zRevRank($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, int $fromScore, int $toScore) : int
    {
        return $this->cRedisConnection->establish()->zCount($key, $fromScore, $toScore);
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, float $score, string $member) : float
    {
        return $this->cRedisConnection->establish()->zIncrBy($key, $score, $member);
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member) : float
    {
        return $this->cRedisConnection->establish()->zScore($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function zRange(string $key, int $from, int $to) : array
    {
        return $this->cRedisConnection->establish()->zRange($key, $from, $to);
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int $from, int $to) : array
    {
        return $this->cRedisConnection->establish()->zRevRange($key, $from, $to);
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeWithScores(string $key, int $from, int $to) : array
    {
        return $this->cRedisConnection->establish()->zRevRange($key, $from, $to, true);
    }

    /**
     * @inheritDoc
     */
    public function sAdd(string $key, string $member) : bool
    {
        return (1 === $this->cRedisConnection->establish()->sAdd($key, $member));
    }

    /**
     * @inheritDoc
     */
    public function sCard(string $key) : int
    {
        return $this->cRedisConnection->establish()->sCard($key);
    }

    /**
     * @inheritDoc
     */
    public function sDiff(string $key1, string $key2) : array
    {
        return $this->cRedisConnection->establish()->sDiff($key1, $key2);
    }

    /**
     * @inheritDoc
     */
    public function sInter(string $key1, string $key2) : array
    {
        return $this->cRedisConnection->establish()->sInter($key1, $key2);
    }

    /**
     * @inheritDoc
     */
    public function sIsMember(string $key, string $member) : bool
    {
        return $this->cRedisConnection->establish()->sIsMember($key, $member);
    }

    /**
     * @inheritDoc
     */
    public function sMembers(string $key) : array
    {
        return $this->cRedisConnection->establish()->sMembers($key);
    }

    /**
     * @inheritDoc
     */
    public function sRem(string $key, string $member) : bool
    {
        return (1 === $this->cRedisConnection->establish()->sRem($key, $member));
    }

    /**
     * @inheritDoc
     */
    public function append(string $key, string $value) : bool
    {
        return (0 < $this->cRedisConnection->establish()->append($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function decr(string $key) : int
    {
        return $this->cRedisConnection->establish()->decr($key);
    }

    /**
     * @inheritDoc
     */
    public function decrBy(string $key, int $value) : int
    {
        return $this->cRedisConnection->establish()->decrBy($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function getRange(string $key, int $from, int $to) : array
    {
        return $this->cRedisConnection->establish()->getRange($key, $from, $to);
    }

    /**
     * @inheritDoc
     */
    public function incr(string $key) : int
    {
        return $this->cRedisConnection->establish()->incr($key);
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $value) : int
    {
        return $this->cRedisConnection->establish()->incrBy($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function mGet(array $keys) : array
    {
        return $this->cRedisConnection->establish()->mget($keys);
    }

    /**
     * @inheritDoc
     */
    public function mSet(array $keysAndValues) : bool
    {
        return $this->cRedisConnection->establish()->mset($keysAndValues);
    }

    /**
     * @inheritDoc
     */
    public function setEx(string $key, $value, int $ttl) : bool
    {
        return $this->cRedisConnection->establish()->setex($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function setNx(string $key, $value) : bool
    {
        return $this->cRedisConnection->establish()->setnx($key, $value);
    }

    /**
     * @inheritDoc
     */
    public function pipeline() : MultiRedisInterface
    {
        $this->cRedisConnection->establish()->multi(\Redis::PIPELINE);

        return new PipelineRedis($this);
    }

    /**
     * @inheritDoc
     */
    public function multi() : MultiRedisInterface
    {
        $this->cRedisConnection->establish()->multi(\Redis::MULTI);

        return new TransactionRedis($this);
    }

    /**
     * @inheritDoc
     */
    public function exec(MultiRedisInterface $multiRedis) : array
    {
        return $this->cRedisConnection->establish()->exec();
    }

    /**
     * @inheritDoc
     */
    public function rename(string $oldName, string $newName) : bool
    {
        return $this->cRedisConnection->establish()->rename($oldName, $newName);
    }

    /**
     * @inheritDoc
     */
    public function hDel(string $key, string $field) : bool
    {
        return (1 === $this->cRedisConnection->establish()->hDel($key, $field));
    }

    /**
     * @inheritDoc
     */
    public function hGet(string $key, string $field)
    {
        return $this->cRedisConnection->establish()->hGet($key, $field);
    }

    /**
     * @inheritDoc
     */
    public function hGetAll(string $key) : array
    {
        return $this->cRedisConnection->establish()->hGetAll($key);
    }

    /**
     * @inheritDoc
     */
    public function hSetAll(string $key, array $keysAndValues) : bool
    {
        return $this->cRedisConnection->establish()->hMset($key, $keysAndValues);
    }

    /**
     * @inheritDoc
     */
    public function hSet(string $key, string $field, $value) : bool
    {
        return (1 === $this->cRedisConnection->establish()->hSet($key, $field, $value));
    }

    /**
     * @inheritDoc
     */
    public function hSetNx(string $key, string $field, $value) : bool
    {
        return $this->cRedisConnection->establish()->hSetNx($key, $field, $value);
    }

    /**
     * @inheritDoc
     */
    public function hExists(string $key, string $field) : bool
    {
        return $this->cRedisConnection->establish()->hExists($key, $field);
    }

    /**
     * @inheritDoc
     */
    public function hIncrBy(string $key, string $field, int $value) : int
    {
        return $this->cRedisConnection->establish()->hIncrBy($key, $field, $value);
    }

    /**
     * @inheritDoc
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue) : float
    {
        return $this->cRedisConnection->establish()->hIncrByFloat($key, $field, $floatValue);
    }

    /**
     * @inheritDoc
     */
    public function hVals(string $key) : array
    {
        return $this->cRedisConnection->establish()->hVals($key);
    }

    /**
     * @inheritDoc
     */
    public function lIndex(string $key, int $index) : string
    {
        return $this->cRedisConnection->establish()->lIndex($key, $index);
    }

    /**
     * @inheritDoc
     */
    public function lInsert(string $key, int $index, string $pivot, $value) : bool
    {
        return (-1 !== $this->cRedisConnection->establish()->lInsert($key, $index, $pivot, $value));
    }

    /**
     * @inheritDoc
     */
    public function lLen(string $key) : int
    {
        return $this->cRedisConnection->establish()->lLen($key);
    }

    /**
     * @inheritDoc
     */
    public function lPop(string $key)
    {
        return $this->cRedisConnection->establish()->lPop($key);
    }

    /**
     * @inheritDoc
     */
    public function lPush(string $key, $value) : bool
    {
        return (false !== $this->cRedisConnection->establish()->lPush($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function lPushNx(string $key, $value) : bool
    {
        return (false !== $this->cRedisConnection->establish()->lPushx($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function lRange(string $key, int $start, int $stop) : array
    {
        return $this->cRedisConnection->establish()->lRange($key, $start, $stop);
    }

    /**
     * @inheritDoc
     */
    public function lRem(string $key, $reference, int $count) : int
    {
        if (false === ($result = $this->cRedisConnection->establish()->lRem($key, $reference, $count))) {
            return 0;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function lSet(string $key, int $index, $value) : bool
    {
        return $this->cRedisConnection->establish()->lSet($key, $index, $value);
    }

    /**
     * @inheritDoc
     */
    public function lTrim(string $key, int $start, int $stop) : array
    {
        if (false === ($result = $this->cRedisConnection->establish()->lTrim($key, $start, $stop))) {
            return [];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function rPop(string $key)
    {
        return $this->cRedisConnection->establish()->rPop($key);
    }

    /**
     * @inheritDoc
     */
    public function rPush(string $key, $value) : bool
    {
        return (false !== $this->cRedisConnection->establish()->rPush($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function rPushNx(string $key, $value) : bool
    {
        return (false !== $this->cRedisConnection->establish()->rPushx($key, $value));
    }

    /**
     * @inheritDoc
     */
    public function watch(string $key) : RedisInterface
    {
        $this->cRedisConnection->establish()->watch($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unwatch() : RedisInterface
    {
        $this->cRedisConnection->establish()->unwatch();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expireAt(string $key, int $ttl) : bool
    {
        return $this->cRedisConnection->establish()->expireAt($key, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function flush() : RedisInterface
    {
        $this->cRedisConnection->establish()->flushDB();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function runQuery($query, array $bindParams, array $bindTypes = []) : GeneratorInterface
    {
        throw new BadMethodRedisException($this, __METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function info() : array
    {
        return $this->cRedisConnection->establish()->info();
    }
}