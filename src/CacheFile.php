<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 使用文件系统缓存一些数据
 * 这个是为开发人员主动调用准备的
 *
 * @author Ice 只为SCache所使用
 */
final class CacheFile extends CacheBase
{
    //缓存类型
    protected static $type = 'file';

    /**
     * 禁止实例化
     */
    private function __construct()
    {
    }

    /**
     * 获取本类单例的方法,公开
     */
    public static function instance(): CacheFile
    {
        // 单例句柄
        static $instance;

        // 初次创建对象
        if (!$instance) {
            $instance = new self();
        }

        // 返回对象句柄
        return $instance;
    }

    /**
     * 判断是否可用,只要启用,总是可用
     */
    public function enabled(): bool
    {
        return true;
    }

    /**
     * 保存要缓存的数据
     * 这将保存到设置好的缓存目录(默认是/cache)下的data目录下
     *
     * @param string $srcKey 要缓存的数据名(Key),取数据时要用到,可以是复杂名字
     * @param mixed $data 要缓存的数据,可以是复杂数据结构
     * @param int $expire 秒数
     * @return bool
     */
    protected function doSet(string $srcKey, $data, int $expire = self::EXPIRE): bool
    {
        // 获取文件名与文件位置
        list ($key, $path, $sqlPath) = $this->getFile($srcKey);

        // 创建目录
        makeDir(dirname($path));

        // 保存缓存数据
        write($path, serialize($data), LOCK_EX);

        // 保存缓存的键
        write($sqlPath, $srcKey, LOCK_EX);

        // 取出目录,修改,保存回去
        $fileList = $this->getFileList();
        $fileList[$key] = $expire + time();
        $this->setFileList($fileList);

        return true;
    }

    /**
     * 根据缓存数据的名称,获取缓存文件名
     *
     * @param string $key 缓存数据的名称(Key)
     * @return array [加密后的名称,缓存文件全路径名]
     */
    private function getFile(string $key): array
    {
        if (!preg_match('/^\w+$/', $key)) {
            $key = md5($key);
        }

        // 返回唯一Key,缓存内容的文件名,缓存语句的文件名
        return [$key, $this->path() . $key . '.cache', $this->path() . $key . '.sql'];
    }

    /**
     * 记录到调试信息中, 未命中
     * @param string $key
     * @return mixed
     */
    private function getMiss(string $key)
    {
        return parent::debugGet($key, CacheFactory::NOT_FOUND);
    }

    /**
     * 取出缓存的数据
     *
     * @param string $key 数据的名(Key)
     * @return mixed|bool 如果未取到数据,返回false
     */
    public function get(string $key)
    {
        // 获取文件名称和位置
        list ($key, $path, $sqlPath) = $this->getFile($key);

        // 取目录
        $fileList = $this->getFileList();

        // 如果此数据不在缓存目录中
        if (!isset($fileList[$key])) {
            return self::getMiss($key);
        }

        // 取出当时缓存的有效期截止时间戳
        $expire = $fileList[$key];

        // 过期
        if ($expire < time()) {
            // 删除数据缓存文件
            unlink($path);
            unlink($sqlPath);

            // 从目录中去除
            unset($fileList[$key]);
            $this->setFileList($fileList);
            return self::getMiss($key);
        }

        if (!is_file($path)) {
            return self::getMiss($key);
        }

        // 取出缓存的数据
        $data = file_get_contents($path);
        return parent::debugGet($key, unserialize($data));
    }

    /**
     * 删除一条已经缓存的数据
     *
     * @param string $key 数据的名(Key)
     * @return bool true/false 数据存在,并进行了真实删除/数据本来就不存在
     */
    public function delete(string $key): bool
    {
        // 获取文件名称和文件位置
        list ($key, $path, $sqlPath) = $this->getFile($key);

        // 取目录
        $fileList = $this->getFileList();

        // 如果不在目录中,未缓存
        if (!isset($fileList[$key])) {
            return false;
        }

        // 删除缓存文件
        unlink($path);
        unlink($sqlPath);

        // 在目录中删除此数据文件,并保存目录
        unset($fileList[$key]);
        $this->setFileList($fileList);

        return parent::delete($key);
    }

    /**
     * 清除全部数据缓存
     * 我会尽可能删除文件,但有可能有遗留文件
     */
    public function clearAll(): bool
    {
        // 取缓存目录
        $fileList = $this->getFileList();

        // 删除目录 中的每一个数据缓存文件
        foreach ($fileList as $key => $expire) {
            $info = $this->getFile($key);
            unlink($info[1]);
            unlink($info[2]);
        }

        // 删除目录文件
        $this->delFileList();
        return true;
    }

    /**
     * 本机文件缓存的目录
     */
    private function path(): string
    {
        return configDefault('./cache/', 'filecache', 'dir');
    }

    /**
     * 获取缓存数据文件目录的文件名
     */
    private function fileList(): string
    {
        // 目录也保存在 缓存目录下的data目录下,文件名为category.cache
        $path = $this->path();
        $category = $path . 'category.cache';

        return $category;
    }

    /**
     * 获取缓存数据文件的目录
     */
    private function getFileList()
    {
        // 获取缓存数据文件目录的文件名
        $category = $this->fileList();

        // 如果目录文件不存在
        if (!file_exists($category)) {
            return [];
        }

        // 取目录
        return unserialize(file_get_contents($category));
    }

    /**
     * 删除缓存数据文件的目录
     */
    private function delFileList(): void
    {
        // 获取缓存数据文件目录的文件名
        $category = $this->fileList();

        // 如果文件存在,则删除
        if (file_exists($category)) {
            unlink($category);
        }
    }

    /**
     * 保存缓存数据文件的目录
     * @param array $list 目录,结构为:array('name'=>$expire,...)
     */
    private function setFileList(array $list): void
    {
        // 获取缓存数据文件目录的文件名
        $category = $this->fileList();

        // 写入
        write($category, serialize($list), LOCK_EX);
    }
}
