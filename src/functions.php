<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 获取一个缓存实例
 * 这是一个SCache的快捷入口
 * @param string $config Page/Data: 页面缓存/数据缓存
 * @return CacheBase
 */
function cache(string $config): CacheBase
{
    return CacheFactory::instance($config);
}

/**
 * 根据缓存类型创建缓存对象:redis/file/apc/mem/none
 * @param string $type
 * @return CacheBase
 */
function cacheByType(string $type): CacheBase
{
    return CacheFactory::createByType($type);
}

/**
 * 清除全部缓存
 */
function clearCache(): void
{
    CacheFactory::clearAll();
}