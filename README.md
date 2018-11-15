缓存
=
* 创建缓存对象

    $cache = cache('page')
        
        创建一个页面缓存对象
    
    $cache = cache('data')
    
        创建一个数据缓存对象
    
    $cache = cache('must')
    
        创建一个特指缓存对象
        
    $cache = cacheByType('mem')
    
        创建一个Memcache/Memcached缓存对象

    $cache = cacheByType('redis')
    
        创建一个redis缓存对象

    $cache = cacheByType('apc')
    
        创建一个apc缓存对象

    $cache = cacheByType('file')
    
        创建一个File缓存对象
        
* 配置信息
    
    system|cachePage 
    
        指定页面缓存使用的类型:none|file|redis|mem|apc
        
    system|cacheData 
    
        指定数据缓存使用的类型:none|file|redis|mem|apc
        
    system|cacheMust 
    
        指定特指缓存使用的类型:none|file|redis|mem|apc
    
* 清除缓存  
    
    clearAll()
    
* 创建指定类型缓存对象

    $cache = cacheFactory::createInstance(string $type)
    
        $type指定缓存使用的类型:none|file|redis|mem|apc

* 删除指定域的全部缓存数据

    $cache->clear(string $field = null):bool 
    
* 存储缓存数据
    
    $cache->set(string $field, string $key, $data, int $expire = 0):bool
    
* 读取缓存数据

    $cache->get(string $key)
    
* 删除缓存数据

    $cache->delete(string $key):bool
    
* 判断缓存是否可用

    $cache->enabled():bool
    
    只有无缓存(NoneCache)是不可用的
    
   