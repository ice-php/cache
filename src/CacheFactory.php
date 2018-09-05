<?php
declare(strict_types=1);
namespace icePHP;
/**
 * 缓存 工厂
 * @author 蓝冰大侠
 */
final class CacheFactory
{
    // 用来表示真的没有数据
    const NOT_FOUND = '缓存里面没有找到这个数据啊~~~';

    /**
     * 获取一个缓存实例
     * @param string $type 类型:Page(页面缓存)/Data(数据缓存)/Must(必须)
     * @return CacheBase
     */
    static public function instance($type): CacheBase
    {
        //防止单词大小写错误
        $type = strtolower(trim($type));

        //多例句柄
        static $instances = [];

        //如果尚未实例化,则创建
        if (!isset($instances[$type])) {
            $instances[$type] = self::createInstance($type);
        }

        //返回实例
        return $instances[$type];
    }

    /**
     * 创建一个指定类型的缓存实例
     * @param $type string
     * @return CacheBase
     */
    private static function createInstance(string $type): CacheBase
    {
        // 取相应类型的缓存配置要求
        if ($type == 'page') {
            $config = Config::get('system', 'cachePage');
        } elseif ($type == 'data') {
            $config = Config::get('system', 'cacheData');
        } elseif ($type == 'must') {
            $config = Config::get('system', 'cacheMust');
        } else {
            $config = 'none';
        }

        // 返回文件缓存对象实例
        if ($config == 'file') {
            return CacheFile::instance();
        }

        //返回 Redis缓存的对象实例
        if ($config == 'redis') {
            return RedisCache::instance();
        }

        //返回APC共享内存的缓存对象
        if ($config == 'apc') {
            return CacheApc::instance();
        }

        // 返回内存缓存对象实例
        if ($config == 'mem') {
            //优先检测是否有Memcached扩展
            if (class_exists('memcached', false)) {
                return Memcached::instance();
            }

            //再次检测是否有Memcache扩展
            if (class_exists('memcache', false)) {
                return Memcache::instance();
            }
        }

        // 返回无缓存对象实例
        return new CacheNone();
    }

    /**
     * 清除所有缓存
     */
    public static function clearAll(): void
    {
        // 构造 文件缓存 对象
        $cache = self::instance('file');

        // 清除文件缓存内容
        $cache->clearAll();

        // 构造Memcache缓存对象
        $cache = self::instance('data');

        // 清除Memcache缓存内容
        $cache->clearAll();

        //清除MUST类型的缓存
        $cache = self::instance('must');
        $cache->clearAll();
    }
}