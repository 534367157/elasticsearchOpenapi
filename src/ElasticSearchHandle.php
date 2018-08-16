<?php

namespace elasticsearchOpenapi;


use Elasticsearch\ClientBuilder;

class ElasticSearchHandle
{
    private static $handler = [];
    private static $host = [
        [
            //'host' => '127.0.0.1',
            'host' => 'es-cn-4590pujgp000c304k.public.elasticsearch.aliyuncs.com',
            'port' => '9200',
            'scheme' => 'http',
            'user' => 'elastic',
            //'pass' => 'changeme',
            'pass' => 'P@ssw0rd87'
        ],
    ];
    private static $param = 0;


    /**
     * 单例模型，构造函数
     */
    private function __construct()
    {
    }


    /**
     * redis池
     * @param int $data
     * @return mixed
     */
    public static function getInstance()
    {
        if (empty(self::$handler)) {
            self::$handler = ClientBuilder::create()->setHosts(self::$host)->build();
        }
        return self::$handler;
    }

    public static function search($params)
    {
        return self::getInstance()->search($params);
    }

    /**
     *  私有, 单例模型，禁止克隆
     */
    private function __clone()
    {
    }

    /**
     *  公有，调用对象函数
     */
    public function __call($method, $args)
    {
        if (!self::$handler || !$method) {
            return false;
        }
        if (!method_exists(self::$handler, $method)) {
            throw new Exception("Class ClientBuilder not have method ($method) ");
        }
        return call_user_func_array(array(self::$handler, $method), $args);
    }

    /**
     * 设置redis的连接参数
     */
    private static function getRedis()
    {
        switch (self::$param) {
            case 1:
                self::$options['host'] = C('REDIS_HOST');
                self::$options['port'] = C('REDIS_PORT');
                self::$options['password'] = C('REDIS_AUTH');
                break;
            case 2:
                self::$options['host'] = C('REDIS_HOST2');
                self::$options['port'] = C('REDIS_PORT2');
                self::$options['password'] = C('REDIS_AUTH2');
                break;
        }
    }

    public function getListData($results)
    {
        $list = $results['hits']['hits'];
        $arr = [];
        foreach ($list as $k => $v) {
            $arr[] = $v['_source'];
        }
        return $arr;
    }

    public function getAggData($results)
    {
        $aggregations = $results['aggregations'];
        $arr = [];
        foreach ($aggregations as $k => $v) {
            $arr[$k] = $v['value'];
        }
        return $arr;
    }
}