<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 所有基础缓存类的父类
 */
abstract class CacheBase
{
    //缓存类型,子类具象
    protected static $type = '';

    //域前缀
    const PREFIX_FIELD = 'Field:';

    //默认的缓存时间(三天)
    const EXPIRE=259200;

    /**
     * 删除缓存数据
     * @param string $field 缓存数据的域,如果不提供,则删除全部
     * @return boolean
     */
    public function clear(string $field = null): bool
    {
        Debug::setCache(static::$type, 'clear', 'FIELD', $field);

        // 如果未提供field参数,则清除全部缓存
        if (!$field) {
            return $this->clearAll();
        }

        // 取域缓存
        $keys = $this->get(self::PREFIX_FIELD . $field);
        if ($keys and $keys !== CacheFactory::NOT_FOUND) {

            // 逐个删除域中的所有项
            foreach ($keys as $key) {
                $this->delete($key);
            }

            // 删除域
            $this->delete('Field_' . $field);
        }

        return true;
    }

    /**
     * 清除全部缓存
     */
    abstract public function clearAll(): bool;

    /**
     * 子类必须实现的抽象方法
     * 具体进行缓存
     *
     * @param string $key
     * @param mixed $data
     * @param int $expire
     * @return bool
     */
    abstract protected function doSet(string $key, $data, int $expire = 0): bool;

    /**
     * 缓存一条数据
     *
     * @param string $field 域,
     * @param string $key 键
     * @param mixed $data 数据
     * @param int $expire 有效期
     * @return boolean
     */
    public function set(string $field, string $key, $data, int $expire = self::EXPIRE): bool
    {
        // 缓存数据
        $this->doSet($key, $data, $expire);

        //记录调试信息
        self::debugSet($field . ':' . $key, $data);

        // 取出此表已经缓存的所有键
        $keys = $this->get(self::PREFIX_FIELD . $field);
        if (!$keys or $keys == CacheFactory::NOT_FOUND) {
            $keys = [];
        }

        // 增加一个键,并保存回去,以便以后统一清除
        $keys[] = $key;
        return $this->doSet(self::PREFIX_FIELD . $field, $keys, $expire);
    }

    /**
     * 子类必须实现
     * @param string $key
     * @return mixed
     */
    abstract public function get(string $key);

    /**
     * 删除一个缓存数据
     * @param string $key 缓存数据的名称(键)
     * @return bool
     */
    public function delete(string $key): bool
    {
        Debug::setCache(static::$type, 'delete', $key);
        return true;
    }

    /**
     * 缓存是否可用,只有无缓存(NoneCache)是不可用的
     */
    abstract public function enabled(): bool;

    /**
     * 将读取缓存 记录到调试信息中
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected static function debugGet(string $key, $value)
    {
        Debug::setCache(static::$type, 'get', $key, is_string($value) ? $value : json($value));
        return $value;
    }

    /**
     * 将写入缓存 记录到调试信息中
     * @param string $key
     * @param mixed $value
     */
    protected static function debugSet(string $key, $value): void
    {
        Debug::setCache(static::$type, 'set', $key, is_string($value) ? $value : json($value));
    }
}
