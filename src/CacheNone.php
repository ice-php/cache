<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 无缓存时的缓存对象
 * @author Ice
 */
final class CacheNone extends CacheBase
{
    //缓存类型
    protected static $type = 'none';

    /**
     * 删除缓存数据
     * @param string $field 缓存数据的域,如果不提供,则删除全部
     * @return boolean
     * @see CacheBase::clear()
     */
    public function clear(string $field = null): bool
    {
        Debug::setCache(static::$type,'clear','FIELD',$field);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see CacheInterface::clearAll()
     */
    public function clearAll(): bool
    {
        return true;
    }

    /**
     * 读取一个缓存的数据
     *
     * @param string $key 缓存数据的名称(键)
     * @see CacheInterface::get()
     * @return mixed
     */
    public function get(string $key)
    {
        parent::debugGet($key, CacheFactory::NOT_FOUND);
        return false;
    }

    /**
     * @param string $key 键
     * @param mixed $data 数据
     * @param int $expire 有效期
     * @return boolean
     * @see CacheBase::doSet
     */
    protected function doSet(string $key, $data, int $expire = 0): bool
    {
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see CacheInterface::enabled()
     */
    public function enabled(): bool
    {
        return false;
    }
}
