<?php
declare(strict_types=1);

namespace icePHP;
/**
 * Memcached 方式缓存
 * @author "蓝冰大侠"
 *
 */
final class Memcached extends MemcacheBase
{

    //缓存类型
    protected static $type='memcached';

    /**
     * 构造一个新的缓存对象实例
     * @return \Memcached
     */
    protected function _instance(): \Memcached
    {
        return new \Memcached();
    }
}