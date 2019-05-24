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
declare(strict_types=1);

namespace Vain\Redis\Database;

use Vain\Core\Database\AbstractDatabase;
use Vain\Core\Database\Generator\DatabaseGeneratorInterface;
use Vain\Redis\Connection\CRedisConnection;
use Vain\Redis\Exception\BadMethodRedisException;
use Vain\Redis\Multi\MultiRedisInterface;
use Vain\Redis\Multi\Pipeline\PipelineRedis;
use Vain\Redis\Multi\Transaction\TransactionRedis;
use Vain\Redis\RedisInterface;

/***
 * Class RedisDatabase
 *
 * @author Taras P. Girnyk <taras.p.gyrnik@gmail.com>
 *
 * @method \Redis getConnection
 */
class RedisDatabase extends AbstractDatabase implements RedisInterface
{
    private $multi = false;

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $ttl = 0): bool
    {
        if (0 === $ttl) {
            $result = $this->getConnection()->set($key, $value);
        } else {
            $result = $this->getConnection()->setex($key, $ttl, $value);
        }


        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key)
    {
        if (false === ($result = $this->getConnection()->get($key))) {
            return null;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function del(string $key): bool
    {
        $result = $this->getConnection()->del($key);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        $result = $this->getConnection()->exists($key);

        return $this->multi ? true : !!$result;
    }

    /**
     * @inheritDoc
     */
    public function ttl(string $key): int
    {
        $result = $this->getConnection()->ttl($key);
        if (false === $result) {
            return 0;
        }

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function expire(string $key, int $ttl): bool
    {
        $result = $this->getConnection()->expire($key, $ttl);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function pSet(string $key, $value): bool
    {
        $result = $this->getConnection()->set($key, $value);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function add(string $key, $value, int $ttl): bool
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
    public function zAddMod(string $key, string $mode, int $score, $value): bool
    {
        if (false !== $this->getConnection()
                           ->evalSha(
                               sha1(CRedisConnection::scripts['zAddXXNX']),
                               [
                                   $this->getConnection()->_prefix(
                                       $key
                                   ),
                                   $mode,
                                   $score,
                                   $value,
                               ],
                               1
                           )
        ) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function zAddCond(string $key, string $mode, int $score, $value): bool
    {
        if (false !== $this->getConnection()
                           ->evalSha(
                               sha1(CRedisConnection::scripts['zAddCond']),
                    [
                        $this->getConnection()->_prefix(
                            $key
                        ),
                        $mode,
                        $score,
                        $value,
                    ],
                    1
                )
        ) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function zAdd(string $key, int $score, $value): bool
    {
        $result = $this->getConnection()->zAdd($key, $score, $value);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function zRem(string $key, string $member): bool
    {
        $result = $this->getConnection()->zRem($key, $member);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function zDelete(string $key, string $member): bool
    {
        $result = $this->getConnection()->zDelete($key, $member);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function zDeleteRangeByScore(string $key, string $fromScore, string $toScore): int
    {
        return $this->zRemRangeByScore($key, $fromScore, $toScore);
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByScore(string $key, string $fromScore, string $toScore): int
    {
        $result = $this->getConnection()->zRemRangeByScore($key, $fromScore, $toScore);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRemRangeByRank(string $key, int $start, int $stop): int
    {
        $result = $this->getConnection()->zRemRangeByRank($key, $start, $stop);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScore(string $key, string $fromScore, string $toScore, array $options = []): array
    {
        $cRedisOptions[self::WITH_SCORES] = array_key_exists(self::WITH_SCORES, $options) ? true : false;

        if (array_key_exists(self::ZRANGE_OFFSET, $options)) {
            $cRedisOptions[self::ZRANGE_LIMIT][] = $options[self::ZRANGE_OFFSET];
        }

        if (array_key_exists(self::ZRANGE_LIMIT, $options)) {
            $cRedisOptions[self::ZRANGE_LIMIT][] = $options[self::ZRANGE_LIMIT];
        }

        $result = $this->getConnection()->zRevRangeByScore($key, $fromScore, $toScore, $cRedisOptions);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeByScoreLimit(
        string $key,
        string $fromScore,
        string $toScore,
        int $offset,
        int $count
    ): array {
        return $this->zRevRangeByScore(
            $key,
            $fromScore,
            $toScore,
            [
                self::ZRANGE_LIMIT => $count,
                self::ZRANGE_OFFSET => $offset,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function zRangeByScore(string $key, string $fromScore, string $toScore, array $options = []): array
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

        $result = $this->getConnection()->zRangeByScore($key, $fromScore, $toScore, $cRedisOptions);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function zCard(string $key): int
    {
        $result = $this->getConnection()->zCard($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRank(string $key, string $member): int
    {
        $result = $this->getConnection()->zRank($key, $member);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRank(string $key, string $member): int
    {
        $result = $this->getConnection()->zRevRank($key, $member);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zCount(string $key, string $fromScore, string $toScore): int
    {
        if (-1 === $fromScore) {
            $fromScore = '-inf';
        }

        if (-1 === $toScore) {
            $toScore = '+inf';
        }

        $result = $this->getConnection()->zCount($key, (string)$fromScore, (string)$toScore);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zIncrBy(string $key, float $score, string $member): float
    {
        $result = $this->getConnection()->zIncrBy($key, $score, $member);

        return $this->multi ? 0.0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zScore(string $key, string $member): float
    {
        $result = $this->getConnection()->zScore($key, $member);

        return $this->multi ? 0.0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRange(string $key, int $from, int $to): array
    {
        $result = $this->getConnection()->zRange($key, $from, $to);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRange(string $key, int $from, int $to): array
    {
        $result = $this->getConnection()->zRevRange($key, $from, $to);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function zRevRangeWithScores(string $key, int $from, int $to): array
    {
        $result = $this->getConnection()->zRevRange($key, $from, $to, true);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sAdd(string $key, string $member): bool
    {
        $result = $this->getConnection()->sAdd($key, $member);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function sCard(string $key): int
    {
        $result = $this->getConnection()->sCard($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function sDiff(array $keys): array
    {
        $result = $this->getConnection()->sDiff(...$keys);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sDiffStore(string $destination, array $keys) : int
    {
        $result = $this->getConnection()->sDiffStore($destination, ...$keys);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function sInter(array $keys): array
    {
        $result = $this->getConnection()->sInter(...$keys);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sInterStore(string $destination, array $keys) : int
    {
        $result = $this->getConnection()->sInterStore($destination, ...$keys);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function sUnion(array $keys): array
    {
        $result = $this->getConnection()->sUnion(...$keys);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sUnionStore(string $destination, array $keys) : int
    {
        $result = $this->getConnection()->sUnionStore($destination, ...$keys);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function sIsMember(string $key, string $member): bool
    {
        $result = $this->getConnection()->sIsMember($key, $member);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function sMembers(string $key): array
    {
        $result = $this->getConnection()->sMembers($key);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function sRem(string $key, string $member): bool
    {
        $result = $this->getConnection()->sRem($key, $member);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function pfAdd(string $key, $element): bool
    {
        $result = $this->getConnection()->pfAdd($key, [$element]);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function pfCount(string $key): int
    {
        $result = $this->getConnection()->pfCount($key);

        return $this->multi ? 1 : $result;
    }

    /**
     * @inheritDoc
     */
    public function pfMerge(string $destination, array $keys): bool
    {
        $this->getConnection()->pfMerge($destination, $keys);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function append(string $key, string $value): bool
    {
        $result = $this->getConnection()->append($key, $value);

        return $this->multi ? true : (0 < $result);
    }

    /**
     * @inheritDoc
     */
    public function decr(string $key): int
    {
        $result = $this->getConnection()->decr($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function decrBy(string $key, int $value): int
    {
        $result = $this->getConnection()->decrBy($key, $value);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function getRange(string $key, int $from, int $to): string
    {
        $result = $this->getConnection()->getRange($key, $from, $to);

        return $this->multi ? '' : $result;
    }

    /**
     * @inheritDoc
     */
    public function incr(string $key): int
    {
        $result = $this->getConnection()->incr($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function incrBy(string $key, int $value): int
    {
        $result = $this->getConnection()->incrBy($key, $value);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function mGet(array $keys): array
    {
        $result = $this->getConnection()->mget($keys);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function mSet(array $keysAndValues): bool
    {
        $result = $this->getConnection()->mset($keysAndValues);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function setEx(string $key, $value, int $ttl): bool
    {
        $result = $this->getConnection()->setex($key, $value, $ttl);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function setNx(string $key, $value): bool
    {
        $result = $this->getConnection()->setnx($key, $value);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function pipeline(): MultiRedisInterface
    {
        $this->getConnection()->multi(\Redis::PIPELINE);
        $this->multi = true;

        return new PipelineRedis($this);
    }

    /**
     * @inheritDoc
     */
    public function multi(): MultiRedisInterface
    {
        $this->getConnection()->multi(\Redis::MULTI);
        $this->multi = true;

        return new TransactionRedis($this);
    }

    /**
     * @inheritDoc
     */
    public function exec(MultiRedisInterface $multiRedis): array
    {
        $this->multi = false;

        return $this->getConnection()->exec();
    }

    /**
     * @inheritDoc
     */
    public function rename(string $oldName, string $newName): bool
    {
        $result = $this->getConnection()->rename($oldName, $newName);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hDel(string $key, string $field): bool
    {
        $result = $this->getConnection()->hDel($key, $field);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function hGet(string $key, string $field)
    {
        $result = $this->getConnection()->hGet($key, $field);

        return $this->multi ? '' : $result;
    }

    /**
     * @inheritDoc
     */
    public function hGetAll(string $key): array
    {
        $result = $this->getConnection()->hGetAll($key);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function hSetAll(string $key, array $keysAndValues): bool
    {
        $result = $this->getConnection()->hMset($key, $keysAndValues);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hSet(string $key, string $field, $value): bool
    {
        $result = $this->getConnection()->hSet($key, $field, $value);

        return $this->multi ? true : (1 === $result);
    }

    /**
     * @inheritDoc
     */
    public function hSetNx(string $key, string $field, $value): bool
    {
        $result = $this->getConnection()->hSetNx($key, $field, $value);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hExists(string $key, string $field): bool
    {
        $result = $this->getConnection()->hExists($key, $field);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function hIncrBy(string $key, string $field, int $value): int
    {
        $result = $this->getConnection()->hIncrBy($key, $field, $value);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function hIncrByFloat(string $key, string $field, float $floatValue): float
    {
        $result = $this->getConnection()->hIncrByFloat($key, $field, $floatValue);

        return $this->multi ? 0.0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function hVals(string $key): array
    {
        $result = $this->getConnection()->hVals($key);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function lIndex(string $key, int $index): string
    {
        $result = $this->getConnection()->lIndex($key, $index);

        return $this->multi ? '' : $result;
    }

    /**
     * @inheritDoc
     */
    public function lInsert(string $key, int $index, string $pivot, $value): bool
    {
        $result = $this->getConnection()->lInsert($key, $index, $pivot, $value);

        return $this->multi ? true : (-1 !== $result);
    }

    /**
     * @inheritDoc
     */
    public function lLen(string $key): int
    {
        $result = $this->getConnection()->lLen($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function lPop(string $key)
    {
        $result = $this->getConnection()->lPop($key);

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function lPush(string $key, $value): bool
    {
        $result = $this->getConnection()->lPush($key, $value);

        return $this->multi ? true : (false !== $result);
    }

    /**
     * @inheritDoc
     */
    public function lPushNx(string $key, $value): bool
    {
        $result = $this->getConnection()->lPushx($key, $value);

        return $this->multi ? true : (false !== $result);
    }

    /**
     * @inheritDoc
     */
    public function lRange(string $key, int $start, int $stop): array
    {
        $result = $this->getConnection()->lRange($key, $start, $stop);

        return $this->multi ? [] : $result;
    }

    /**
     * @inheritDoc
     */
    public function lRem(string $key, $reference, int $count): int
    {
        $result = $this->getConnection()->lRem($key, $reference, $count);
        if (false === $result) {
            return 0;
        }

        return $this->multi ? 0 : $result;
    }

    /**
     * @inheritDoc
     */
    public function lSet(string $key, int $index, $value): bool
    {
        $result = $this->getConnection()->lSet($key, $index, $value);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function lTrim(string $key, int $start, int $stop): array
    {
        $result = $this->getConnection()->lTrim($key, $start, $stop);
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
        $result = $this->getConnection()->rPop($key);

        return $this->multi ? '' : $result;
    }

    /**
     * @inheritDoc
     */
    public function rPush(string $key, $value): bool
    {
        $result = $this->getConnection()->rPush($key, $value);

        return $this->multi ? true : (false !== $result);
    }

    /**
     * @inheritDoc
     */
    public function rPushNx(string $key, $value): bool
    {
        $result = $this->getConnection()->rPushx($key, $value);

        return $this->multi ? true : (false !== $result);
    }

    /**
     * @inheritDoc
     */
    public function watch(string $key): RedisInterface
    {
        $this->getConnection()->watch($key);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function unwatch(): RedisInterface
    {
        $this->getConnection()->unwatch();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function expireAt(string $key, int $ttl): bool
    {
        $result = $this->getConnection()->expireAt($key, $ttl);

        return $this->multi ? true : $result;
    }

    /**
     * @inheritDoc
     */
    public function flush(): RedisInterface
    {
        $this->getConnection()->flushDB();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flushAll() : RedisInterface
    {
        $this->getConnection()->flushAll();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function runQuery($query, array $bindParams, array $bindTypes = []): DatabaseGeneratorInterface
    {
        throw new BadMethodRedisException($this, __METHOD__);
    }

    /**
     * @inheritDoc
     */
    public function info(): array
    {
        return $this->getConnection()->info();
    }

    /**
     * @inheritDoc
     */
    public function exists(string $key): bool
    {
        return $this->getConnection()->exists($key) > 0;
    }
}