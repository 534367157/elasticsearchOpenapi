<?php

namespace elasticsearchOpenapi;


class ElasticSearchAnalysis
{
    private static $_analysisArr = [];
    private static $_params = [];
    private static $_operatorAnalysis = null;

    public static function _setAnalysisArr($analysisArr)
    {
        self::$_analysisArr = $analysisArr;
        self::$_operatorAnalysis = new ElasticSearchOperator();
    }

    public static function _getEsParams()
    {
        return self::$_params;
    }

    public static function _elasticSerachQueryAnalysis()
    {
        self::_esIndexAnalysis();
        //self::_esTypeAnalysis();
        self::_esSourceAnalysis();
        self::_esBodyAnalysis();
        if (empty(self::$_params['_source']) && empty(self::$_params['body']['aggs'])) {
            throw new \Exception('source or aggs must define one');
        }
        if (empty(self::$_params['_source'])) {
            self::$_params['body']['from'] = 0;
            self::$_params['body']['size'] = 0;
        }
    }

    private static function _esIndexAnalysis()
    {
        if (!isset(self::$_analysisArr['table']) || empty(self::$_analysisArr['table'])) {
            throw new \Exception('table not define');
        }
        self::$_params['index'] = self::$_analysisArr['table'];
    }

    private static function _esTypeAnalysis()
    {
        if (!isset(self::$_analysisArr['table']) || empty(self::$_analysisArr['table'])) {
            throw new \Exception('table not define');
        }
        self::$_params['type'] = self::$_analysisArr['table'];
    }

    private static function _esSourceAnalysis()
    {
        if (isset(self::$_analysisArr['source'])) {
            if (empty(self::$_analysisArr['source'])) {
                throw new \Exception('source not define');
            } else {
                self::$_params['_source'] = array_map('trim', explode(',', self::$_analysisArr['source']));
            }
        }
    }

    private static function _esBodyAnalysis()
    {
        $body = [];
        $body['query'] = self::_esQueryAnalysis();
        $body['from'] = self::_esFromAnalysis();
        $body['size'] = self::_esSizeAnalysis();
        $body['sort'] = self::_esSortAnalysis();
        if (!empty(self::$_analysisArr['aggs'])) {
            $body['aggs'] = self::_esAggsAnalysis();
        }
        self::$_params['body'] = $body;
    }

    private static function _esQueryAnalysis()
    {
        if (empty(self::$_analysisArr['where'])) {
            throw new \Exception('where not define');
        }
        if (!is_array(self::$_analysisArr['where'])) {
            throw new \Exception('where must be array');
        }
        $where = self::$_analysisArr['where'];
        $where = self::_esWhereAnalysis($where, 1);

        // var_dump($where);die();
        return $where;
    }

    private static function _esWhereAnalysis($where, $init = 0)
    {
        if (!empty($where[0]) && $where[0] === 'or') {
            $fiOp = 'should';
            unset($where[0]);
        } elseif ((!empty($where[0]) && $where[0] === 'and') || $init) {
            if (!empty($where[0]) && $where[0] === 'and') unset($where[0]);
            $fiOp = 'filter';
        }
        $ki = [];

        foreach ($where as $key => $value) {
            if (!is_array($value)) {
                $kii['term'] = [$key => $value];
            } else {
                if ((!empty($value[0])) && in_array($value[0], ElasticSearchOperator::$_all_operator)) {
                    $kii = ElasticSearchOperator::operatorAnalysis($key, $value);
                } elseif ((array_values($value) === $value) && (count($value) == count($value, 1))) {
                    $kii['terms'] = [$key => $value];
                } else {
                    $kii = self::_esWhereAnalysis($value);

                }
            }
            $ki[] = $kii;
        }
        if (!empty($fiOp)) {
            $fii['bool'][$fiOp] = $ki;
        } else {
            $fii = $ki;
        }
        return $fii;
    }

    private static function _esFromAnalysis()
    {
        return (!empty(self::$_analysisArr['from'])) ? self::$_analysisArr['from'] : 0;
    }

    private static function _esSizeAnalysis()
    {
        return (isset(self::$_analysisArr['size'])) ? self::$_analysisArr['size'] : 10;
    }

    private static function _esSortAnalysis()
    {
        $sort = [];
        if (!empty(self::$_analysisArr['order'])) {
            $orderArr = array_map('trim', explode(',', self::$_analysisArr['order']));
            foreach ($orderArr as $k => $v) {
                $chars = preg_split('/\s+/', $v, -1);
                $sort[$chars[0]] = $chars[1];
            }
        }
        return (!empty($sort)) ? $sort : ['id' => 'desc'];
    }

    private static function _esAggsAnalysis()
    {
        $aggs = [];
        if (!empty(self::$_analysisArr['aggs'])) {
            $aggsArr = array_map('trim', explode(',', self::$_analysisArr['aggs']));
            foreach ($aggsArr as $k => $v) {
                if (!empty($v)) {
                    $vArr = array_map('trim', explode('as', $v));
                    $key = !empty($vArr[1]) ? $vArr[1] : $vArr[0];
                    $aggStr = substr($vArr[0], 0, strpos($vArr[0], '('));
                    preg_match('[\(\w+\)]', $vArr[0], $fieldArr);
                    $field = substr($fieldArr[0], 1, -1);
                    $aggStr = (strtolower($aggStr) == 'distinct') ? 'cardinality' : $aggStr;
                    $aggs[$key] = [
                        $aggStr => [
                            "field" => $field,
//                            "format"=>"0.00"
                        ]
                    ];
                }
            }
        }
        return (!empty($aggs)) ? $aggs : [];
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