<?php

namespace Score\Repositories;

use Exception;
use Score\Models\ForexcecConfig;
use Phalcon\Mvc\User\Component;
use Score\Models\ScMatch;
use Score\Models\ScTournament;
use Symfony\Component\DomCrawler\Crawler;

class MatchDetailRepo extends Component
{
    public function getMatchCrawlNomal()
    {
        $arrTourKey = ScTournament::getTourIdCrawl();
        $matchCrawl = MatchDetailRepo::getMatchStartHT($arrTourKey);
        if (!$matchCrawl) {
            $matchCrawl = MatchDetailRepo::getMatchStartTour();
            if (!$matchCrawl) {
                $this->resetFlagTourStart();
                $matchCrawl = MatchDetailRepo::getMatchStartTour();
            }
        }
        return $matchCrawl;
    }
    public function getMatchCrawl($is_live, $id)
    {
        if ($is_live) {
            $arrTourKey = ScTournament::getTourIdCrawl();
            $matchCrawl = MatchDetailRepo::getMatchStartTourKey($arrTourKey);
            if (!$matchCrawl) {
                $this->resetFlagTourKey($arrTourKey);
                echo "--All restart: \r\n";
                $matchCrawl = MatchDetailRepo::getMatchStartTourKey($arrTourKey);
            }
            //for not found primary
            if (!$matchCrawl) {
                echo "--Start crawl not primary: \r\n";
                $matchCrawl = MatchDetailRepo::getMatchStartTour();
                if (!$matchCrawl) {
                    $this->resetFlagTourStart();
                    $matchCrawl = MatchDetailRepo::getMatchStartTour();
                }
            }
        } else {
            //7h tới 11h tối thứ 7 cn tắt detail
            $dayOfWeek = date('N', time()); // Lấy số thứ tự của ngày trong tuần
            $currentHour = date('G');
            echo "Today is: " . $dayOfWeek . " and " . $currentHour . " Hour \r\n";
            if ($dayOfWeek == '6' ||  $dayOfWeek == '7') {

                if ($currentHour >= 11 && $currentHour <= 16) {
                    return false;
                }
            }

            $matchCrawl = MatchDetailRepo::getMatchWait();
            if (!$matchCrawl) {
                //crawl detail cho trận FT
                $matchCrawl = MatchDetailRepo::getMatchFinish();
            }
        }
        if ($id) {
            $matchCrawl = ScMatch::findFirst([
                'match_id = :id:',
                'bind' => ['id' => $id]
            ]);
        }
        return $matchCrawl;
    }
    public static function getMatchStartTourKey($arrTourKey)
    {
        return ScMatch::findFirst([
            ' match_status = "S" AND match_crawl_detail_live = "0" AND FIND_IN_SET(match_tournament_id,:arrTour:)',
            'bind' => [
                'arrTour' => implode(",", $arrTourKey)
            ]
        ]);
    }
    public static function getMatchStartTour()
    {
        return ScMatch::findFirst([
            ' match_status = "S" AND match_crawl_detail_live = "0"'
        ]);
    }

    //ưu tiên HT trước
    public static function getMatchStartHT($arrTourKey)
    {
        return ScMatch::findFirst([
            'match_status = "S" AND match_crawl_detail_live = "0" AND match_time = "HT" AND (NOT FIND_IN_SET(match_tournament_id,:arrTour:))',
            'bind' => [
                'arrTour' => implode(",", $arrTourKey)
            ]
        ]);
    }
    public static function getMatchWait()
    {
        return ScMatch::findFirst([
            'match_crawl_detail = 0 AND match_status = "W"'
        ]);
    }
    public static function getMatchFinish()
    {
        return ScMatch::findFirst([
            '(match_crawl_detail = 1 OR match_crawl_detail = 0) AND match_status = "F"'
        ]);
    }
    public function resetFlagTourKey($arrTourKey)
    {
        $sql = 'UPDATE Score\Models\ScMatch SET match_crawl_detail_live = "0" WHERE (match_status = "S" AND FIND_IN_SET(match_tournament_id,:arrTour:)) ';
        $param = [
            'arrTour' => implode(",", $arrTourKey)
        ];
        return $this->modelsManager->executeQuery($sql, $param);
    }
    public function resetFlagTourStart()
    {
        $sql = 'UPDATE Score\Models\ScMatch SET match_crawl_detail_live = "0" WHERE (match_status = "S" ) ';
        return $this->modelsManager->executeQuery($sql);
    }
}