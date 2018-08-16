<?php

namespace elasticsearchOpenapi;


class ElasticResultHandle
{
    public static function getListData($results)
    {
        $list = $results['hits']['hits'];
        $arr = [];
        foreach ($list as $k => $v) {
            $arr[] = $v['_source'];
        }
        return $arr;
    }

    public static function getCount($results)
    {
        return $results['hits']['total'];
    }

    public static function getAggData($results)
    {
        if (empty($results['aggregations'])) return [];
        $aggregations = $results['aggregations'];
        $arr = [];
        foreach ($aggregations as $k => $v) {
            $arr[$k] = $v['value'];
        }
        return $arr;
    }
}