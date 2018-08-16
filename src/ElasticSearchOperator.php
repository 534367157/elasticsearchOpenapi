<?php

namespace elasticsearchOpenapi;


class ElasticSearchOperator
{
    public static $_operator = [
        'range' => [
            'between','gt','gte','lt','lte',
        ],
        'wildcard' => ['like'],
        'must_not' => ['neq']
    ];
    public static $_all_operator = [];

    public function __construct()
    {
        self::$_all_operator = array_merge(self::$_operator['range'],self::$_operator['wildcard'],self::$_operator['must_not']);
    }

    public static function operatorAnalysis($key, $value){
        $operatorA = [];
        if(in_array($value[0], self::$_operator['range'])){
            if($value[0] === 'between'){
                $operatorA = [
                    'range'=>[
                        $key => [
                            'gte'=>$value[1][0],
                            'lte'=>$value[1][1]
                        ]
                    ]
                ];
            }else{
                if($value[0] === 'egt') $value[0] = 'gte';
                if($value[0] === 'elt') $value[0] = 'lte';
                $operatorA = [
                    'range'=>[
                        $key => [
                            $value[0]=>$value[1]
                        ]
                    ]
                ];
            }
        }elseif (in_array($value[0], self::$_operator['wildcard'])){
            $operatorA = [
                'wildcard'=>[
                    $key=>str_replace('%','*',$value[1])
                ]
            ];
        }
        return $operatorA;
    }
}