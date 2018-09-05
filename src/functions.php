<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 获取一个缓存实例
 * 这是一个SCache的快捷入口
 * @param string $type Page/Data: 页面缓存/数据缓存
 * @return CacheBase
 */
function cache(string $type): CacheBase
{
    return CacheFactory::instance($type);
}

/**
 * 清除全部缓存
 */
function clearCache(): void
{
    CacheFactory::clearAll();
}