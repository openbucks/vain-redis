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

namespace Vain\Redis;

use Vain\Core\Cache\CacheInterface;
use Vain\Core\Database\DatabaseInterface;
use Vain\Redis\Multi\MultiRedisInterface;

/**
 * Interface RedisInterface
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
interface RedisInterface extends CacheInterface, DatabaseInterface
{
    const POSITIVE_INFINITY = '+inf';
    const NEGATIVE_INFINITY = '-inf';
    const ZADD_MODE_CH = 'CH';
    const ZADD_MODE_XX = 'XX';
    const ZADD_MODE_NX = 'NX';
    const WITH_SCORES = 'withscores';
    const ZRANGE_LIMIT = 'limit';
    const ZRANGE_OFFSET = 'offset';

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function pSet(string $key, $value) : bool;

    /**
     * @param string $key
     * @param string $mode
     * @param int    $score
     * @param mixed  $value
     *
     * @return mixed
     */
    public function zAddMod(string $key, string $mode, int $score, $value) : bool;

    /**
     * @param string $key
     * @param int    $score
     * @param mixed  $value
     *
     * @return bool
     */
    public function zAdd(string $key, int $score, $value) : bool;

    /**
     * @param string $key
     *
     * @return int
     */
    public function zCard(string $key) : int;

    /**
     * @param string $key
     * @param string $member
     *
     * @return int
     */
    public function zRank(string $key, string $member);


    /**
     * @param string $key
     * @param string $member
     *
     * @return int
     */
    public function zRevRank(string $key, string $member) : int;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     *
     * @return int
     */
    public function zCount(string $key, int $fromScore, int $toScore) : int;

    /**
     * @param string $key
     * @param float  $score
     * @param string $member
     *
     * @return float
     */
    public function zIncrBy(string $key, float $score, string $member) : float;

    /**
     * @param string $key
     * @param string $member
     *
     * @return bool
     */
    public function zDelete(string $key, string $member) : bool;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     *
     * @return int
     */
    public function zDeleteRangeByScore(string $key, int $fromScore, int $toScore) : int;

    /**
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return int
     */
    public function zRemRangeByRank(string $key, int $start, int $stop) : int;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     *
     * @return int
     */
    public function zRemRangeByScore(string $key, int $fromScore, int $toScore) : int;

    /**
     * @param string $key
     * @param string $member
     *
     * @return float
     */
    public function zScore(string $key, string $member) : float;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return array
     */
    public function zRange(string $key, int $from, int $to) : array;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return array
     */
    public function zRevRange(string $key, int $from, int $to) : array;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return array
     */
    public function zRevRangeWithScores(string $key, int $from, int $to) : array;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     * @param array  $options
     *
     * @return array
     */
    public function zRevRangeByScore(string $key, int $fromScore, int $toScore, array $options = []) : array;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     * @param int    $offset
     * @param int    $count
     *
     * @return array
     */
    public function zRevRangeByScoreLimit(string $key, int $fromScore, int $toScore, int $offset, int $count) : array;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     * @param array  $options
     *
     * @return array
     */
    public function zRangeByScore(string $key, int $fromScore, int $toScore, array $options = []) : array;

    /**
     * @param string $key
     * @param mixed  $member
     *
     * @return bool
     */
    public function sAdd(string $key, string $member) : bool;

    /**
     * @param string $key
     *
     * @return int
     */
    public function sCard(string $key) : int;

    /**
     * @param string $key1
     * @param string $key2
     *
     * @return array
     */
    public function sDiff(string $key1, string $key2) : array;

    /**
     * @param string $key1
     * @param string $key2
     *
     * @return array
     */
    public function sInter(string $key1, string $key2) : array;

    /**
     * @param string $key
     * @param string $member
     *
     * @return bool
     */
    public function sIsMember(string $key, string $member) : bool;

    /**
     * @param string $key
     *
     * @return array
     */
    public function sMembers(string $key) : array;

    /**
     * @param string $key
     * @param mixed $member
     *
     * @return bool
     */
    public function sRem(string $key, string $member) : bool;

    /**
     * @param string $key
     * @param string $value
     *
     * @return bool
     */
    public function append(string $key, string $value) : bool;

    /**
     * @param string $key
     *
     * @return int
     */
    public function decr(string $key) : int;

    /**
     * @param string $key
     * @param int    $value
     *
     * @return int
     */
    public function decrBy(string $key, int $value) : int;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return array
     */
    public function getRange(string $key, int $from, int $to) : array;

    /**
     * @param string $key
     *
     * @return int
     */
    public function incr(string $key) : int;

    /**
     * @param string $key
     * @param int    $value
     *
     * @return int
     */
    public function incrBy(string $key, int $value) : int;

    /**
     * @param array $keys
     *
     * @return array
     */
    public function mGet(array $keys) : array;

    /**
     * @param array $keysAndValues
     *
     * @return bool
     */
    public function mSet(array $keysAndValues) : bool;

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return bool
     */
    public function setEx(string $key, $value, int $ttl) : bool;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function setNx(string $key, $value) : bool;

    /**
     * @return MultiRedisInterface
     */
    public function pipeline() : MultiRedisInterface;

    /**
     * @return MultiRedisInterface
     */
    public function multi() : MultiRedisInterface;

    /**
     * @param MultiRedisInterface $multiRedis
     *
     * @return array
     */
    public function exec(MultiRedisInterface $multiRedis) : array;

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @return bool
     */
    public function rename(string $oldName, string $newName) : bool;

    /**
     * @param string $key
     * @param string $field
     *
     * @return bool
     */
    public function hDel(string $key, string $field) : bool;

    /**
     * @param string $key
     * @param string $field
     *
     * @return mixed
     */
    public function hGet(string $key, string $field);

    /**
     * @param string $key
     *
     * @return array
     */
    public function hGetAll(string $key) : array;

    /**
     * @param string $key
     * @param array  $keysAndValues
     *
     * @return bool
     */
    public function hSetAll(string $key, array $keysAndValues) : bool;

    /**
     * @param string $key
     * @param string $field
     * @param string $value
     *
     * @return bool
     */
    public function hSet(string $key, string $field, $value) : bool;

    /**
     * @param string $key
     * @param string $field
     * @param string $value
     *
     * @return bool
     */
    public function hSetNx(string $key, string $field, $value) : bool;

    /**
     * @param string $key
     * @param string $field
     *
     * @return bool
     */
    public function hExists(string $key, string $field) : bool;

    /**
     * @param string $key
     * @param string $field
     * @param int    $value
     *
     * @return int
     */
    public function hIncrBy(string $key, string $field, int $value) : int;

    /**
     * @param string $key
     * @param string $field
     * @param float  $floatValue
     *
     * @return float
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue) : float;

    /**
     * @param string $key
     *
     * @return array
     */
    public function hVals(string $key) : array;

    /**
     * @param string $key
     * @param int    $index
     *
     * @return mixed
     */
    public function lIndex(string $key, int $index);

    /**
     * @param string $key
     * @param int    $index
     * @param string $pivot
     * @param mixed  $value
     *
     * @return bool
     */
    public function lInsert(string $key, int $index, string $pivot, $value) : bool;

    /**
     * @param string $key
     *
     * @return int
     */
    public function lLen(string $key) : int;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function lPop(string $key);

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function lPush(string $key, $value) : bool;

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public function lPushNx(string $key, $value) : bool;

    /**
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return array
     */
    public function lRange(string $key, int $start, int $stop) : array;

    /**
     * @param string $key
     * @param mixed  $reference
     * @param int    $count
     *
     * @return int
     */
    public function lRem(string $key, $reference, int $count) : int;

    /**
     * @param string $key
     * @param int    $index
     * @param mixed  $value
     *
     * @return bool
     */
    public function lSet(string $key, int $index, $value) : bool;

    /**
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return array
     */
    public function lTrim(string $key, int $start, int $stop) : array;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function rPop(string $key);

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function rPush(string $key, $value) : bool;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return bool
     */
    public function rPushNx(string $key, $value) : bool;

    /**
     * @param string $key
     *
     * @return int
     */
    public function ttl(string $key) : int;

    /**
     * @param string $key
     *
     * @return RedisInterface
     */
    public function watch(string $key) : RedisInterface;

    /**
     * @return RedisInterface
     */
    public function unwatch() : RedisInterface;

    /**
     * @param string $key
     * @param int    $ttl
     *
     * @return bool
     */
    public function expireAt(string $key, int $ttl) : bool;

    /**
     * @return RedisInterface
     */
    public function flush() : RedisInterface;

    /**
     * @return array
     */
    public function info() : array;
}
