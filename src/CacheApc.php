<?php
declare(strict_types=1);
namespace icePHP;
/**
 * 使用共享内存缓存一些数据
 * 这个是为开发人员主动调用准备的
 *
 * @author Ice 只为Cache所使用
 */
final class CacheApc extends CacheBase
{
    /**
     * 禁止实例化
     */
    private function __construct()
    {
    }

    /**
     * @var string apcu/apc
     */
    private static $type;

    /**
     * 获取本类单例的方法,公开
     */
    public static function instance(): CacheBase
    {
        // 单例句柄
        static $instance;

        // 初次创建对象
        if (!$instance) {
            if (function_exists('apcu_store')) {
                self::$type = 'apcu';
                $instance = new self();
            } elseif (function_exists('apc_store')) {
                self::$type = 'apc';
                $instance = new self();
            } else {
                $instance = new CacheNone();
            }
        }

        // 返回对象句柄
        return $instance;
    }

    /**
     * 判断是否可用,只要启用,总是可用
     */
    public function enabled(): bool
    {
        return self::$type ? true : false;
    }

    /**
     * 保存要缓存的数据
     * 这将保存到设置好的缓存目录(默认是/cache)下的data目录下
     *
     * @param string $srcKey 要缓存的数据名(Key),取数据时要用到,可以是复杂名字
     * @param mixed $data 要缓存的数据,可以是复杂数据结构
     * @param mixed $expire 有效期,可以是以下格式
     * 秒数: 指定秒数内有效
     *            时间戳: 指定时间戳前有效
     *            'Today': 当天有效
     * @throws \Exception
     * @return bool
     */
    protected function doSet(string $srcKey, $data, int $expire = 0): bool
    {
        // 计算有效期
        $expire = $this->expire($expire);
        if (!$expire) {
            return false;
        }

        $func = self::$type . '_store';
        $func($srcKey, $data, $expire - time());
        return true;
    }

    /**
     * 取出缓存的数据
     *
     * @param string $key 数据的名(Key)
     * @return mixed|bool 如果未取到数据,返回false
     */
    public function get(string $key)
    {
        $func = self::$type . '_fetch';
        return $func($key);
    }

    /**
     * 删除一条已经缓存的数据
     *
     * @param string $key 数据的名(Key)
     * @return bool true/false 数据存在,并进行了真实删除/数据本来就不存在
     */
    public function delete(string $key): bool
    {
        $func = self::$type . '_delete';
        $func($key);
        return true;
    }

    /**
     * 清除全部数据缓存
     * 我会尽可能删除文件,但有可能有遗留文件
     */
    public function clearAll(): bool
    {
        $func = self::$type . '_clear_cache';
        $func('user');
        return true;
    }
}
