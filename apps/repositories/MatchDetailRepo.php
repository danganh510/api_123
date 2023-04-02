<?php

namespace Score\Repositories;

use Exception;
use Score\Models\ForexcecConfig;
use Phalcon\Mvc\User\Component;
use Score\Models\ScMatch;
use Symfony\Component\DomCrawler\Crawler;

class MatchDetailRepo extends Component
{
    public static function getMatchStartTourKey($arrTourKey) {
        return ScMatch::findFirst([
            ' match_status = "S" AND match_crawl_detail_live = "0" AND FIND_IN_SET(match_tournament_id,:arrTour:)',
            'bind' => [
                'arrTour' => implode(",", $arrTourKey)
            ]
        ]);
    }
    public function resetFlagTourKey($arrTourKey) {
        $sql = 'UPDATE Score\Models\ScMatch SET match_crawl_detail_live = "0" WHERE (match_status = "S" AND FIND_IN_SET(match_tournament_id,:arrTour:)) ';
        $param = [
            'arrTour' => implode(",", $arrTourKey)
        ];
        return $this->modelsManager->executeQuery($sql, $param);
    }
}
