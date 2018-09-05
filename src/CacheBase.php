<?php
declare(strict_types=1);
namespace icePHP;

/**
 * 所有基础缓存类的父类
 */
abstract class CacheBase
{
    /**
     * 删除缓存数据
     * @param string $field 缓存数据的域,如果不提供,则删除全部
     * @return boolean
     */
    public function clear(string $field = null):bool
    {
        // 如果未提供field参数,则清除全部缓存
        if (!$field) {
            return $this->clearAll();
        }

        // 取域缓存
        $keys = $this->get('Field_' . $field);
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
    abstract public function clearAll():bool ;

    /**
     * 计算有效期
     *
     * @param mixed $expire 有效期,可以是以下格式
     *            秒数: 指定秒数内有效
     *            时间戳: 指定时间戳前有效
     *            'Today': 当天有效
     * @throws \Exception
     * @return int
     */
    protected function expire($expire):int
    {
        // 如果未提供有效期,按一年算
        if (!$expire) {
            return time() + 365 * 24 * 60 * 60;
        }

        // 如果是Today字符串,认为是当天有效
        if ($expire == 'Today') {
            return strtotime(date('Y-m-d')) + 24 * 60 * 60 - 1;
        }

        // 如果是大于一年的整数,认为是截止时间戳
        if (is_int($expire)) {
            return $expire;
        }

        // 其它格式,抛出异常
        throw new \Exception('cache invalid expire:' . $expire);
    }

    /**
     * 子类必须实现的抽象方法
     * 具体进行缓存
     *
     * @param string $key
     * @param mixed $data
     * @param int $expire
     * @return bool
     */
    abstract protected function doSet(string $key, $data, int $expire = 0):bool ;

    /**
     * 缓存一条数据
     *
     * @param string $field 域,
     * @param string $key 键
     * @param mixed $data 数据
     * @param int $expire 有效期
     * @return boolean
     */
    public function set(string $field, string $key, $data, int $expire = 0):bool
    {
        // 缓存数据
        $this->doSet($key, $data, $expire);

        // 取出此表已经缓存的所有键
        $keys = $this->get('Field_' . $field);
        if (!$keys or $keys == CacheFactory::NOT_FOUND) {
            $keys = [];
        }

        // 增加一个键,并保存回去,以便以后统一清除
        $keys[] = $key;
        return $this->doSet('Field_' . $field, $keys, 0);
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
    abstract public function delete(string $key):bool;

    /**
     * 缓存是否可用,只有无缓存(NoneCache)是不可用的
     */
    abstract public function enabled():bool ;
}
