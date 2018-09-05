<?php
declare(strict_types=1);

namespace icePHP;

/**
 * Memcache缓存类
 * 增强域功能
 * 增强多缓存服务器自动散列功能
 * 只为Cache使用
 */
final class Memcache extends MemcacheBase
{

    /**
     * 构造一个新的缓存对象实例
     * @return \Memcache
     */
    protected function _instance(): \Memcache
    {
        return new \Memcache();
    }
}
