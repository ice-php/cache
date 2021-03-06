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
    //缓存类型,子类具象
    protected static $type = 'redis';

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
    const PREFIX = 'ice:';

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
        return parent::delete($key);
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

        return parent::debugGet($key, is_null($value) ? CacheFactory::NOT_FOUND : $value);
    }

    /**
     * 具体执行存储一个数据
     * @param string $key 键
     * @param mixed $data 值(自动JSON)
     * @param int $expire 生存期
     * @return bool
     */
    public function doSet(string $key, $data, int $expire = self::EXPIRE): bool
    {
        //我们不关注返回值
        Redis::string(self::PREFIX . $key, json($data),  $expire);
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

        //记录调试信息
        self::debugSet($field . ':' . $key, $data);

        //生成FIELD对象
        $field = Redis::list(self::PREFIX . self::PREFIX_FIELD . $field);

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
        Debug::setCache(static::$type, 'clear', 'FIELD', $field);

        // 如果未提供field参数,则清除全部缓存
        if (!$field) {
            //查看有哪些FIELD
            $keys = Redis::listKeys(self::PREFIX . 'TABLE:*');

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
        $list = Redis::get(self::PREFIX . self::PREFIX_FIELD . $field);

        //获取一个域中的键
        $keys = [];
        if ($list) while ($key = $list->popLeft()) {
            $keys[] = self::PREFIX . $key;
        }


        //删除所有键,以及域
        $keys[] = self::PREFIX . self::PREFIX_FIELD . $field;
        Redis::delete($keys);
        return true;
    }
}