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

    private $multi = false;

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
        $result = $this->cRedisConnection->establish()->set($key, $value, $ttl);

        return $this->multi ? true : $result;
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
        $result = $this->cRedisConnection->establish()->del($key);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key) : bool
    {
        $result = $this->cRedisConnection->establish()->exists($key);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key) : int
    {
        $result = $this->cRedisConnection->establish()->ttl($key);
        if (false === $result) {
            return 0; 
        }
        
        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl) : bool
    {
        $result =  $this->cRedisConnection->establish()->expire($key, $ttl);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function pSet(string $key, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->set($key, $value);

        return $this->multi ? true : $result;
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

        return $this->multi ? true : (isset($result[0]) && $result[0]);
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
        $result = $this->cRedisConnection->establish()->zAdd($key, $score, $value);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function zDelete(string $key, string $member) : bool
    {
        $result = $this->cRedisConnection->establish()->zDelete($key, $member);

        return $this->multi ? true : (1 === $result);
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
        $result = $this->cRedisConnection->establish()->zRemRangeByScore($key, $fromScore, $toScore);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByRank(string $key, int $start, int $stop) : int
    {
        $result = $this->cRedisConnection->establish()->zRemRangeByRank($key, $start, $stop);

        return $this->multi ? 0 : $result;
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

        $result = $this->cRedisConnection->establish()->zRevRangeByScore($key, $fromScore, $toScore, $cRedisOptions);

        return $this->multi ? [] : $result;
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

        $result = $this->cRedisConnection->establish()->zRangeByScore($key, $fromScore, $toScore, $cRedisOptions);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function zCard(string $key) : int
    {
        $result = $this->cRedisConnection->establish()->zCard($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRank(string $key, string $member) : int
    {
        $result = $this->cRedisConnection->establish()->zRank($key, $member);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRank(string $key, string $member) : int
    {
        $result = $this->cRedisConnection->establish()->zRevRank($key, $member);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, int $fromScore, int $toScore) : int
    {
        $result = $this->cRedisConnection->establish()->zCount($key, $fromScore, $toScore);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, float $score, string $member) : float
    {
        $result = $this->cRedisConnection->establish()->zIncrBy($key, $score, $member);

        return $this->multi ? 0.0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member) : float
    {
        $result = $this->cRedisConnection->establish()->zScore($key, $member);

        return $this->multi ? 0.0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRange(string $key, int $from, int $to) : array
    {
        $result = $this->cRedisConnection->establish()->zRange($key, $from, $to);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int $from, int $to) : array
    {
        $result = $this->cRedisConnection->establish()->zRevRange($key, $from, $to);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeWithScores(string $key, int $from, int $to) : array
    {
        $result = $this->cRedisConnection->establish()->zRevRange($key, $from, $to, true);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sAdd(string $key, string $member) : bool
    {
        $result = $this->cRedisConnection->establish()->sAdd($key, $member);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function sCard(string $key) : int
    {
        $result = $this->cRedisConnection->establish()->sCard($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function sDiff(string $key1, string $key2) : array
    {
        $result = $this->cRedisConnection->establish()->sDiff($key1, $key2);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sInter(string $key1, string $key2) : array
    {
        $result = $this->cRedisConnection->establish()->sInter($key1, $key2);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sIsMember(string $key, string $member) : bool
    {
        $result = $this->cRedisConnection->establish()->sIsMember($key, $member);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function sMembers(string $key) : array
    {
        $result = $this->cRedisConnection->establish()->sMembers($key);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sRem(string $key, string $member) : bool
    {
        $result = $this->cRedisConnection->establish()->sRem($key, $member);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function append(string $key, string $value) : bool
    {
        $result = $this->cRedisConnection->establish()->append($key, $value);

        return $this->multi ? true : (0 < $result);
    }

    /**
     * @inheritDoc
     */
    public function decr(string $key) : int
    {
        $result = $this->cRedisConnection->establish()->decr($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function decrBy(string $key, int $value) : int
    {
        $result = $this->cRedisConnection->establish()->decrBy($key, $value);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function getRange(string $key, int $from, int $to) : array
    {
        $result = $this->cRedisConnection->establish()->getRange($key, $from, $to);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function incr(string $key) : int
    {
        $result = $this->cRedisConnection->establish()->incr($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $value) : int
    {
        $result = $this->cRedisConnection->establish()->incrBy($key, $value);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function mGet(array $keys) : array
    {
        $result = $this->cRedisConnection->establish()->mget($keys);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function mSet(array $keysAndValues) : bool
    {
        $result = $this->cRedisConnection->establish()->mset($keysAndValues);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function setEx(string $key, $value, int $ttl) : bool
    {
        $result = $this->cRedisConnection->establish()->setex($key, $value, $ttl);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function setNx(string $key, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->setnx($key, $value);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function pipeline() : MultiRedisInterface
    {
        $this->cRedisConnection->establish()->multi(\Redis::PIPELINE);
        $this->multi = true;

        return new PipelineRedis($this);
    }

    /**
     * @inheritDoc
     */
    public function multi() : MultiRedisInterface
    {
        $this->cRedisConnection->establish()->multi(\Redis::MULTI);
        $this->multi = true;

        return new TransactionRedis($this);
    }

    /**
     * @inheritDoc
     */
    public function exec(MultiRedisInterface $multiRedis) : array
    {
        $this->multi = false;

        return $this->cRedisConnection->establish()->exec();
    }

    /**
     * @inheritDoc
     */
    public function rename(string $oldName, string $newName) : bool
    {
        $result = $this->cRedisConnection->establish()->rename($oldName, $newName);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hDel(string $key, string $field) : bool
    {
        $result = $this->cRedisConnection->establish()->hDel($key, $field);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function hGet(string $key, string $field)
    {
        $result = $this->cRedisConnection->establish()->hGet($key, $field);

        return $this->multi ? '' : $result;
    }

    /**
     * @inheritDoc
     */
    public function hGetAll(string $key) : array
    {
        $result = $this->cRedisConnection->establish()->hGetAll($key);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function hSetAll(string $key, array $keysAndValues) : bool
    {
        $result = $this->cRedisConnection->establish()->hMset($key, $keysAndValues);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hSet(string $key, string $field, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->hSet($key, $field, $value);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function hSetNx(string $key, string $field, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->hSetNx($key, $field, $value);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hExists(string $key, string $field) : bool
    {
        $result = $this->cRedisConnection->establish()->hExists($key, $field);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hIncrBy(string $key, string $field, int $value) : int
    {
        $result = $this->cRedisConnection->establish()->hIncrBy($key, $field, $value);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue) : float
    {
        $result = $this->cRedisConnection->establish()->hIncrByFloat($key, $field, $floatValue);

        return $this->multi ? 0.0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function hVals(string $key) : array
    {
        $result = $this->cRedisConnection->establish()->hVals($key);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function lIndex(string $key, int $index) : string
    {
        $result = $this->cRedisConnection->establish()->lIndex($key, $index);

        return $this->multi ? '' : $result;
    }

    /**
     * @inheritDoc
     */
    public function lInsert(string $key, int $index, string $pivot, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->lInsert($key, $index, $pivot, $value);

        return $this->multi ? true : (-1 !== $result);
    }

    /**
     * @inheritDoc
     */
    public function lLen(string $key) : int
    {
        $result = $this->cRedisConnection->establish()->lLen($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function lPop(string $key)
    {
        $result = $this->cRedisConnection->establish()->lPop($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function lPush(string $key, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->lPush($key, $value);

        return $this->multi ? true : (false !== $result);
    }

    /**
     * @inheritDoc
     */
    public function lPushNx(string $key, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->lPushx($key, $value);

        return $this->multi ? true : (false !== $result);
    }

    /**
     * @inheritDoc
     */
    public function lRange(string $key, int $start, int $stop) : array
    {
        $result = $this->cRedisConnection->establish()->lRange($key, $start, $stop);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function lRem(string $key, $reference, int $count) : int
    {
        $result = $this->cRedisConnection->establish()->lRem($key, $reference, $count);
        if (false === $result) {
            return 0;
        }

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function lSet(string $key, int $index, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->lSet($key, $index, $value);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function lTrim(string $key, int $start, int $stop) : array
    {
        $result = $this->cRedisConnection->establish()->lTrim($key, $start, $stop);
        if (false === $result) {
            return [];
        }

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function rPop(string $key)
    {
        $result = $this->cRedisConnection->establish()->rPop($key);

        return $this->multi ? '' : $result;
    }

    /**
     * @inheritDoc
     */
    public function rPush(string $key, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->rPush($key, $value);

        return $this->multi ? true : (false !== $result);
    }

    /**
     * @inheritDoc
     */
    public function rPushNx(string $key, $value) : bool
    {
        $result = $this->cRedisConnection->establish()->rPushx($key, $value);

        return $this->multi ? true : (false !== $result);
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
        $result =$this->cRedisConnection->establish()->expireAt($key, $ttl);

        return $this->multi ? true : $result;
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