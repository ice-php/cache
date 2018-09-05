<?php
/**
 * Created by IcePHP Framework.
 * User: 蓝冰大侠
 * Date: 2018/2/27
 * Time: 10:03
 */
declare(strict_types=1);

namespace icePHP;

abstract class MemcacheBase extends CacheBase
{
    /**
     * 获取本类单例的方法,公开
     * @return CacheBase
     */
    public static function instance(): CacheBase
    {
        static $instance;
        if (!$instance) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * 判断 缓存是是否可用,只要启用,总是可用
     * @return bool true
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * 缓存服务器句柄
     * @var \Memcache|\Memcached
     */
    protected $_handle;


    // 保存当前的配置
    protected $config;

    /**
     * 创建一个真正的缓存对象
     * @return \Memcache|\Memcached
     */
    abstract protected function _instance();

    /**
     * 禁止实例化
     */
    private function __construct()
    {
        // 取配置信息
        $config = config('mem');
        $this->config = $config;

        // 添加所有缓存服务器
        $handle = $this->_instance();
        for ($i = 1; $i <= $config['servers']; $i++) {
            $handle->addServer($config[$i]['host'], $config[$i]['port']);
        }
        $this->_handle = $handle;
    }

    /**
     * 删除一个缓存数据
     * @param mixed $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        /**
         * @var $handle \Memcache|\Memcached
         */
        $handle = $this->_handle;
        return $handle->delete($key);
    }

    /**
     * 删除全部缓存数据
     */
    public function clearAll(): bool
    {
        /**
         * @var $handle \Memcache|\Memcached
         */
        $handle = $this->_handle;
        return $handle->flush();
    }

    /**
     * 缓存一条数据
     * @param string $key 键(实际是查询语句)
     * @param mixed $data 数据
     * @param $expire int 有效期
     * @return  bool
     * @throws \Exception
     */
    protected function doSet(string $key, $data, int $expire = 0): bool
    {
        /**
         * @var $handle \Memcache|\Memcached
         */
        $handle = $this->_handle;

        // 缓存数据
        return $handle->set($key, $data, MEMCACHE_COMPRESSED, $this->expire($expire));
    }

    /**
     * 从缓存中取一条数据
     * @param string $key 键名
     * @return mixed 数据
     */
    public function get(string $key)
    {
        /**
         * @var $handle \Memcache|\Memcached
         */
        $handle = $this->_handle;

        // 从缓存中读取内容
        $ret = $handle->get($key);

        // 没找到
        if ($ret === false) {
            return CacheFactory::NOT_FOUND;
        }

        // 返回内容
        return $ret;
    }
}