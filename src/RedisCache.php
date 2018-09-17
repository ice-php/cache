<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 使用Redis作为缓存服务器
 * Date: 2017/8/2
 * Time: 8:39
 */
final class RedisCache extends CacheBase
{
    /**
     * 单例句柄
     * @var RedisCache
     */
    private static $instance;

    /**
     * 获取本类单例的方法,公开
     * @return RedisCache
     */
    public static function instance(): RedisCache
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 禁止直接实例化
     */
    private function __construct()
    {
    }

    //本框架使用的缓存键的前缀(Redis Cache)
    const PREFIX = 'RC:';

    /**
     * 清除所有缓存相关的键
     * @return bool
     */
    public function clearAll(): bool
    {
        Redis::delete(Redis::listKeys(self::PREFIX . '*'));
        return true;
    }

    /**
     * 删除一个缓存项目
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        Redis::delete(self::PREFIX . $key);
        return true;
    }

    /**
     * 查看缓存服务器是否可用
     */
    public function enabled(): bool
    {
        Redis::connection()->ping();
        return true;
    }

    /**
     * 取出一个缓存的值
     * @param string $key 键
     * @return mixed 复杂对象
     */
    public function get(string $key)
    {
        //从缓存中取值,并解码
        $value = json_decode(Redis::getString(self::PREFIX . $key), true);

        //没有找到
        if (is_null($value)) {
            return CacheFactory::NOT_FOUND;
        }

        //返回解码后的值
        return $value;
    }

    /**
     * 具体执行存储一个数据
     * @param string $key 键
     * @param mixed $data 值(自动JSON)
     * @param int $expire 生存期
     * @return bool
     */
    public function doSet(string $key, $data, int $expire = 0): bool
    {
        //我们不关注返回值
        Redis::createString(self::PREFIX . $key, json($data), true, $expire);
        return true;
    }

    /**
     * 缓存一条数据
     *
     * @param string $field 域,
     * @param string $key 键
     * @param mixed $data 数据
     * @param int $expire 有效期
     * @return boolean
     * @override 重写
     */
    public function set(string $field, string $key, $data, int $expire = 0): bool
    {
        // 缓存数据
        $this->doSet($key, $data, $expire);

        //生成FIELD对象
        $field = Redis::createList(self::PREFIX . 'FIELD:' . $field);

        //向FIELD中增加一个键
        $field->append($key);
        return true;
    }

    /**
     * 清除指定域的缓存
     * @param string $field 域
     * @return bool
     */
    public function clear(string $field = null): bool
    {
        // 如果未提供field参数,则清除全部缓存
        if (!$field) {
            //查看有哪些FIELD
            $keys = Redis::listKeys(self::PREFIX . 'FIELD:*');

            //逐个FIELD删除
            foreach ($keys as $key) {
                $this->clear($key);
            }
            return true;
        }

        /**
         * 删除一个域
         * @var $list RedisList
         */
        $list = Redis::get(self::PREFIX . 'FIELD:' . $field);

        //获取一个域中的键
        $keys = [];
        if ($list) while ($key = $list->popLeft()) {
            $keys[] = $key;
        }

        //删除所有键,以及域
        Redis::delete(array_merge($keys, [self::PREFIX . 'FIELD:' . $field]));
        return true;
    }
}