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

namespace Vain\Redis\Multi;

/**
 * Interface MultiRedisInterface
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
interface MultiRedisInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return MultiRedisInterface
     */
    public function set(string $key, $value, int $ttl): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return MultiRedisInterface
     */
    public function add(string $key, $value, int $ttl): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function get(string $key): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function del(string $key): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function has(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $ttl
     *
     * @return MultiRedisInterface
     */
    public function expire(string $key, int $ttl): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function pSet(string $key, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $mode
     * @param int    $score
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function zAddMod(string $key, string $mode, int $score, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $score
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function zAdd(string $key, int $score, $value): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function zCard(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $member
     *
     * @return MultiRedisInterface
     */
    public function zRank(string $key, string $member): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $member
     *
     * @return MultiRedisInterface
     */
    public function zRevRank(string $key, string $member): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     *
     * @return MultiRedisInterface
     */
    public function zCount(string $key, int $fromScore, int $toScore): MultiRedisInterface;

    /**
     * @param string $key
     * @param float  $score
     * @param string $member
     *
     * @return MultiRedisInterface
     */
    public function zIncrBy(string $key, float $score, string $member): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $member
     *
     * @return MultiRedisInterface
     */
    public function zDelete(string $key, string $member): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     *
     * @return MultiRedisInterface
     */
    public function zDeleteRangeByScore(string $key, int $fromScore, int $toScore): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return MultiRedisInterface
     */
    public function zRemRangeByRank(string $key, int $start, int $stop): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     *
     * @return MultiRedisInterface
     */
    public function zRemRangeByScore(string $key, int $fromScore, int $toScore): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $member
     *
     * @return MultiRedisInterface
     */
    public function zScore(string $key, string $member): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return MultiRedisInterface
     */
    public function zRange(string $key, int $from, int $to): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return MultiRedisInterface
     */
    public function zRevRange(string $key, int $from, int $to): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return MultiRedisInterface
     */
    public function zRevRangeWithScores(string $key, int $from, int $to): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     * @param array  $options
     *
     * @return MultiRedisInterface
     */
    public function zRevRangeByScore(
        string $key,
        int $fromScore,
        int $toScore,
        array $options = []
    ): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     * @param int    $offset
     * @param int    $count
     *
     * @return MultiRedisInterface
     */
    public function zRevRangeByScoreLimit(
        string $key,
        int $fromScore,
        int $toScore,
        int $offset,
        int $count
    ): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $fromScore
     * @param int    $toScore
     * @param array  $options
     *
     * @return MultiRedisInterface
     */
    public function zRangeByScore(string $key, int $fromScore, int $toScore, array $options = []): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $member
     *
     * @return MultiRedisInterface
     */
    public function sAdd(string $key, string $member): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function sCard(string $key): MultiRedisInterface;

    /**
     * @param array $keys
     *
     * @return MultiRedisInterface
     */
    public function sDiff(array $keys): MultiRedisInterface;

    /**
     * @param array $keys
     *
     * @return MultiRedisInterface
     */
    public function sInter(array $keys): MultiRedisInterface;

    /**
     * @param array $keys
     *
     * @return MultiRedisInterface
     */
    public function sUnion(array $keys): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $member
     *
     * @return MultiRedisInterface
     */
    public function sIsMember(string $key, string $member): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function sMembers(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $member
     *
     * @return MultiRedisInterface
     */
    public function sRem(string $key, string $member): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $value
     *
     * @return MultiRedisInterface
     */
    public function append(string $key, string $value): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function decr(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $value
     *
     * @return MultiRedisInterface
     */
    public function decrBy(string $key, int $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $from
     * @param int    $to
     *
     * @return MultiRedisInterface
     */
    public function getRange(string $key, int $from, int $to): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function incr(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $value
     *
     * @return MultiRedisInterface
     */
    public function incrBy(string $key, int $value): MultiRedisInterface;

    /**
     * @param array $keys
     *
     * @return MultiRedisInterface
     */
    public function mGet(array $keys): MultiRedisInterface;

    /**
     * @param array $keysAndValues
     *
     * @return MultiRedisInterface
     */
    public function mSet(array $keysAndValues): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return MultiRedisInterface
     */
    public function setEx(string $key, $value, int $ttl): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function setNx(string $key, $value): MultiRedisInterface;

    /**
     * @return MultiRedisInterface
     */
    public function pipeline(): MultiRedisInterface;

    /**
     * @return MultiRedisInterface
     */
    public function multi(): MultiRedisInterface;

    /**
     * @return array
     */
    public function exec(): array;

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @return MultiRedisInterface
     */
    public function rename(string $oldName, string $newName): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $field
     *
     * @return MultiRedisInterface
     */
    public function hDel(string $key, string $field): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $field
     *
     * @return MultiRedisInterface
     */
    public function hGet(string $key, string $field): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function hGetAll(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param array  $keysAndValues
     *
     * @return MultiRedisInterface
     */
    public function hSetAll(string $key, array $keysAndValues): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $field
     * @param string $value
     *
     * @return MultiRedisInterface
     */
    public function hSet(string $key, string $field, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $field
     * @param string $value
     *
     * @return MultiRedisInterface
     */
    public function hSetNx(string $key, string $field, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $field
     *
     * @return MultiRedisInterface
     */
    public function hExists(string $key, string $field): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $field
     * @param int    $value
     *
     * @return MultiRedisInterface
     */
    public function hIncrBy(string $key, string $field, int $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param string $field
     * @param float  $floatValue
     *
     * @return MultiRedisInterface
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function hVals(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $index
     *
     * @return MultiRedisInterface
     */
    public function lIndex(string $key, int $index): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $index
     * @param string $pivot
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function lInsert(string $key, int $index, string $pivot, $value): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function lLen(string $key): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function lPop(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function lPush(string $key, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function lPushNx(string $key, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return MultiRedisInterface
     */
    public function lRange(string $key, int $start, int $stop): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $reference
     * @param int    $count
     *
     * @return MultiRedisInterface
     */
    public function lRem(string $key, $reference, int $count): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $index
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function lSet(string $key, int $index, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $start
     * @param int    $stop
     *
     * @return MultiRedisInterface
     */
    public function lTrim(string $key, int $start, int $stop): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function rPop(string $key): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function rPush(string $key, $value): MultiRedisInterface;

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return MultiRedisInterface
     */
    public function rPushNx(string $key, $value): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function ttl(string $key): MultiRedisInterface;

    /**
     * @param string $key
     *
     * @return MultiRedisInterface
     */
    public function watch(string $key): MultiRedisInterface;

    /**
     * @return MultiRedisInterface
     */
    public function unwatch(): MultiRedisInterface;

    /**
     * @param string $key
     * @param int    $ttl
     *
     * @return MultiRedisInterface
     */
    public function expireAt(string $key, int $ttl): MultiRedisInterface;

    /**
     * @return MultiRedisInterface
     */
    public function flush(): MultiRedisInterface;

    /**
     * @param string $query
     * @param array  $bindParams
     * @param array  $bindTypes
     *
     * @return MultiRedisInterface
     */
    public function runQuery($query, array $bindParams, array $bindTypes = []): MultiRedisInterface;

    /**
     * @return MultiRedisInterface
     */
    public function info(): MultiRedisInterface;
}
