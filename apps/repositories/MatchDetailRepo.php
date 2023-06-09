<?php

namespace Score\Repositories;


use Phalcon\Mvc\User\Component;
use Score\Models\ScMatch;
use Score\Models\ScTournament;

class MatchDetailRepo extends Component
{
    public function getMatchCrawlNomal()
    {
        //uu tien check cac match_status = S and time_start < time() - 120 * 60
        $matchCrawl = ScMatch::findFirst([
            'match_status = "S" OR match_status = "W" AND match_start_time < :TIME:',
            'bind' => [
                'TIME' => time() - 130 * 60
            ]
        ]);
        if ($matchCrawl) {
            return $matchCrawl;
        }

        $arrTourKey = ScTournament::getTourIdCrawl();
        $matchCrawl = MatchDetailRepo::getMatchStartHT($arrTourKey);
        if (!$matchCrawl) {
            $this->resetFlagTourNomal($arrTourKey);
            $matchCrawl = MatchDetailRepo::getMatchStartHT($arrTourKey);
            if (!$matchCrawl) {
                $matchCrawl = MatchDetailRepo::getMatchStartTour();
                if (!$matchCrawl) {
                    $this->resetFlagTourStart();
                    $matchCrawl = MatchDetailRepo::getMatchStartTour();
                }
            }
        }
        if (!$matchCrawl) {
            //crawl detail cho trận FT
            $matchCrawl = MatchDetailRepo::getMatchFinish();
        }
        return $matchCrawl;
    }
    public function getMatchCrawl(&$is_live, $id)
    {

        if ($is_live) {
            $arrTourKey = ScTournament::getTourIdCrawl();

            //25-5: chỉnh lại crawl detail tất cả các trận ở ngày thường, riêng t7 chủ nhật mới crawl riêng các trận chính
            if (MyRepo::checkTimeEndWeek()) {
                $matchCrawl = MatchDetailRepo::getMatchStartTourKey($arrTourKey);
            } else {
                $matchCrawl = false;
                goto not_primary;
            }

            if (!$matchCrawl) {
                $this->resetFlagTourKey($arrTourKey);
                echo "--All restart: \r\n";
                $matchCrawl = MatchDetailRepo::getMatchStartTourKey($arrTourKey);
            }
            //for not found primary
            not_primary: 
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
            if (!$id) {
                $dayOfWeek = date('N', time()); // Lấy số thứ tự của ngày trong tuần
                $currentHour = date('G');
                echo "Today is: " . $dayOfWeek . " and " . $currentHour . " Hour \r\n";
                if ($dayOfWeek == 6 || $dayOfWeek == 7) {

                    if ($currentHour >= 11 && $currentHour <= 16) {
                        echo "Wait for crawl list";
                        die();
                    }
                }
            }


            echo "match wait\r\n";
            //ưu tiên trận lỗi:
            $matchCrawl = MatchDetailRepo::getMatchWaitError();
            if (!$matchCrawl) {
                $matchCrawl = MatchDetailRepo::getMatchWait();
            }
        }

        if (!$matchCrawl) {
            //crawl detail cho trận FT
            echo "match finish\r\n";
            $matchCrawl = MatchDetailRepo::getMatchFinish();
            $is_live = false;
        }
        if (!$matchCrawl) {
            //crawl detail cho trận FT
            echo "match wait\r\n";
            $matchCrawl = MatchDetailRepo::getMatchWaitError();
        }
        if (!$matchCrawl) {
            $matchCrawl = MatchDetailRepo::getMatchWait();
        }
        if (!$matchCrawl) {
            $matchCrawl = MatchDetailRepo::getMatchCrawlMin();
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
    public static function getMatchWaitError()
    {
        return ScMatch::findFirst([
            ' (match_status = "W" OR match_status = "S") AND match_start_time < :time_now:',
            'bind' => [
                'time_now' => time() - 150 * 60
            ]
        ]);
    }
    public static function getMatchFinish()
    {
        return ScMatch::findFirst([
            '(match_crawl_detail = 1 OR match_crawl_detail = 0) AND match_status = "F"'
        ]);
    }
    public static function getMatchCrawlMin()
    {
        return ScMatch::findFirst([
            'orderBy' => 'match_crawl_detail ASC'
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
    public function resetFlagTourNomal($arrTourKey)
    {
        $sql = 'UPDATE Score\Models\ScMatch SET match_crawl_detail_live = "0" WHERE (match_status = "S" ) AND (NOT FIND_IN_SET(match_tournament_id,:arrTour:))';
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