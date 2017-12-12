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

use Vain\Redis\Exception\LevelIntegrityRedisException;
use Vain\Redis\RedisInterface;

/**
 * Class AbstractMultiRedis
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 */
abstract class AbstractMultiRedis implements MultiRedisInterface
{
    private $redis;

    private $level = 1;

    /**
     * MultiRedis constructor.
     *
     * @param RedisInterface $redisInterface
     */
    public function __construct(RedisInterface $redisInterface)
    {
        $this->redis = $redisInterface;
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
     * @return RedisInterface
     */
    public function getRedis(): RedisInterface
    {
        return $this->redis;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $ttl = 0): MultiRedisInterface
    {
        $this->redis->set($key, $value, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key) : MultiRedisInterface
    {
        $this->redis->get($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function del(string $key) : MultiRedisInterface
    {
        $this->redis->del($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key) : MultiRedisInterface
    {
        $this->redis->has($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key) : MultiRedisInterface
    {
        $this->redis->ttl($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl) : MultiRedisInterface
    {
        $this->redis->expire($key, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pSet(string $key, $value) : MultiRedisInterface
    {
        $this->redis->pSet($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add(string $key, $value, int $ttl) : MultiRedisInterface
    {
        $this
            ->setNx($key, $value)
            ->expire($key, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zAddMod(string $key, string $mode, int $score, $value) : MultiRedisInterface
    {
        $this->redis->zAddMod($key, $mode, $score, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zAddCond(string $key, string $mode, int $score, $value): MultiRedisInterface
    {
        $this->redis->zAddCond($key, $mode, $score, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zAdd(string $key, int $score, $value) : MultiRedisInterface
    {
        $this->redis->zAdd($key, $score, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zDelete(string $key, string $member) : MultiRedisInterface
    {
        $this->redis->zDelete($key, $member);

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
        $this->redis->zRemRangeByScore($key, $fromScore, $toScore);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByRank(string $key, int $start, int $stop) : MultiRedisInterface
    {
        $this->redis->zRemRangeByRank($key, $start, $stop);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScore(
        string $key,
        int $fromScore,
        int $toScore,
        array $options = []
    ) : MultiRedisInterface
    {
        $this->redis->zRevRangeByScore($key, $fromScore, $toScore, $options);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScoreLimit(
        string $key,
        int $fromScore,
        int $toScore,
        int $offset,
        int $count
    ) : MultiRedisInterface
    {
        return $this->zRevRangeByScore(
            $key,
            $fromScore,
            $toScore,
            [
                RedisInterface::ZRANGE_LIMIT => $count,
                RedisInterface::ZRANGE_OFFSET => $offset,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function zRangeByScore(string $key, int $fromScore, int $toScore, array $options = []) : MultiRedisInterface
    {
        $this->redis->zRangeByScore($key, $fromScore, $toScore, $options);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zCard(string $key) : MultiRedisInterface
    {
        $this->redis->zCard($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRank(string $key, string $member) : MultiRedisInterface
    {
        $this->redis->zRank($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRank(string $key, string $member) : MultiRedisInterface
    {
        $this->redis->zRevRank($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, int $fromScore, int $toScore) : MultiRedisInterface
    {
        $this->redis->zCount($key, $fromScore, $toScore);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, float $score, string $member) : MultiRedisInterface
    {
        $this->redis->zIncrBy($key, $score, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member) : MultiRedisInterface
    {
        $this->redis->zScore($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRange(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->redis->zRange($key, $from, $to);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->redis->zRevRange($key, $from, $to);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeWithScores(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->redis->zRevRangeWithScores($key, $from, $to);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sAdd(string $key, string $member) : MultiRedisInterface
    {
        $this->redis->sAdd($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sCard(string $key) : MultiRedisInterface
    {
        $this->redis->sCard($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sDiff(array $keys) : MultiRedisInterface
    {
        $this->redis->sDiff($keys);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sInter(array $keys) : MultiRedisInterface
    {
        $this->redis->sInter($keys);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sInterStore(string $destination, array $keys) : MultiRedisInterface
    {
        $this->redis->sInterStore($destination, $keys);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sUnion(array $keys) : MultiRedisInterface
    {
        $this->redis->sUnion($keys);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function sUnionStore(string $destination, array $keys) : MultiRedisInterface
    {
        $this->redis->sUnionStore($destination, $keys);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sIsMember(string $key, string $member) : MultiRedisInterface
    {
        $this->redis->sIsMember($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sMembers(string $key) : MultiRedisInterface
    {
        $this->redis->sMembers($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sRem(string $key, string $member) : MultiRedisInterface
    {
        $this->redis->sRem($key, $member);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pfAdd(string $key, $element): MultiRedisInterface
    {
        $this->redis->pfAdd($key, $element);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pfCount(string $key): MultiRedisInterface
    {
        $this->redis->pfCount($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function pfMerge(string $destination, array $keys): MultiRedisInterface
    {
        $this->redis->pfMerge($destination, $keys);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function append(string $key, string $value) : MultiRedisInterface
    {
        $this->redis->append($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function decr(string $key) : MultiRedisInterface
    {
        $this->redis->decr($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function decrBy(string $key, int $value) : MultiRedisInterface
    {
        $this->redis->decrBy($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRange(string $key, int $from, int $to) : MultiRedisInterface
    {
        $this->redis->getRange($key, $from, $to);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function incr(string $key) : MultiRedisInterface
    {
        $this->redis->incr($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $value) : MultiRedisInterface
    {
        $this->redis->incrBy($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mGet(array $keys) : MultiRedisInterface
    {
        $this->redis->mGet($keys);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function mSet(array $keysAndValues) : MultiRedisInterface
    {
        $this->redis->mSet($keysAndValues);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setEx(string $key, $value, int $ttl) : MultiRedisInterface
    {
        $this->redis->setEx($key, $value, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setNx(string $key, $value) : MultiRedisInterface
    {
        $this->redis->setNx($key, $value);

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

        return $this->redis->exec($this);
    }

    /**
     * @inheritDoc
     */
    public function rename(string $oldName, string $newName) : MultiRedisInterface
    {
        $this->redis->rename($oldName, $newName);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hDel(string $key, string $field) : MultiRedisInterface
    {
        $this->redis->hDel($key, $field);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hGet(string $key, string $field) : MultiRedisInterface
    {
        $this->redis->hGet($key, $field);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hGetAll(string $key) : MultiRedisInterface
    {
        $this->redis->hGetAll($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hSetAll(string $key, array $keysAndValues) : MultiRedisInterface
    {
        $this->redis->hSetAll($key, $keysAndValues);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hSet(string $key, string $field, $value) : MultiRedisInterface
    {
        $this->redis->hSet($key, $field, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hSetNx(string $key, string $field, $value) : MultiRedisInterface
    {
        $this->redis->hSetNx($key, $field, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hExists(string $key, string $field) : MultiRedisInterface
    {
        $this->redis->hExists($key, $field);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hIncrBy(string $key, string $field, int $value) : MultiRedisInterface
    {
        $this->redis->hIncrBy($key, $field, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue) : MultiRedisInterface
    {
        $this->redis->hIncrByFloat($key, $field, $floatValue);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hVals(string $key) : MultiRedisInterface
    {
        $this->redis->hVals($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lIndex(string $key, int $index) : MultiRedisInterface
    {
        $this->redis->lIndex($key, $index);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lInsert(string $key, int $index, string $pivot, $value) : MultiRedisInterface
    {
        $this->redis->lInsert($key, $index, $pivot, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lLen(string $key) : MultiRedisInterface
    {
        $this->redis->lLen($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lPop(string $key) : MultiRedisInterface
    {
        $this->redis->lPop($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lPush(string $key, $value) : MultiRedisInterface
    {
        $this->redis->lPush($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lPushNx(string $key, $value) : MultiRedisInterface
    {
        $this->redis->lPushNx($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lRange(string $key, int $start, int $stop) : MultiRedisInterface
    {
        $this->redis->lRange($key, $start, $stop);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lRem(string $key, $reference, int $count) : MultiRedisInterface
    {
        $this->redis->lRem($key, $reference, $count);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lSet(string $key, int $index, $value) : MultiRedisInterface
    {
        $this->redis->lSet($key, $index, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lTrim(string $key, int $start, int $stop) : MultiRedisInterface
    {
        $this->redis->lTrim($key, $start, $stop);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rPop(string $key) : MultiRedisInterface
    {
        $this->redis->rPop($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rPush(string $key, $value) : MultiRedisInterface
    {
        $this->redis->rPush($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rPushNx(string $key, $value) : MultiRedisInterface
    {
        $this->redis->rPushNx($key, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function watch(string $key) : MultiRedisInterface
    {
        $this->redis->watch($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unwatch() : MultiRedisInterface
    {
        $this->redis->unwatch();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expireAt(string $key, int $ttl) : MultiRedisInterface
    {
        $this->redis->expireAt($key, $ttl);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush() : MultiRedisInterface
    {
        $this->redis->flush();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function info() : MultiRedisInterface
    {
        $this->redis->info();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function runQuery($query, array $bindParams, array $bindTypes = []) : MultiRedisInterface
    {
        return $this;
    }
}
