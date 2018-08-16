<?php

namespace elasticsearchOpenapi;

class EsService
{

    public static function query($inputs)
    {
        try {
            $inputs = json_decode($inputs, true);
            $esAnalysis = new ElasticSearchAnalysis();
            $esAnalysis::_setAnalysisArr($inputs);
            $esAnalysis::_elasticSerachQueryAnalysis();
            $params = $esAnalysis::_getEsParams();
            $results = ElasticSearchHandle::search($params);
            $listData = ElasticResultHandle::getListData($results);
            $count = ElasticResultHandle::getCount($results);
            $aggData = ElasticResultHandle::getAggData($results);
            $return = [
                'count' => $count,
                'list' => $listData
            ];
            if (!empty($aggData)) $return['agg_results'] = $aggData;
            return ['status' => true, 'data' => $return];
        } catch (\Exception $e) {
            $exceptionMsg = $e->getMessage();
            $err = json_decode($exceptionMsg, true);
            $errMsg = (is_array($err) && !empty($err['error']['failed_shards'][0]['reason']['caused_by']["reason"])) ? $err['error']['failed_shards'][0]['reason']['caused_by']["reason"] : $exceptionMsg;
            return ['status' => false, 'msg' => $errMsg];
        }
    }
}