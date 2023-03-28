<?php

namespace Score\Repositories;

use Exception;
use Score\Models\ForexcecConfig;
use Phalcon\Mvc\User\Component;
use Score\Models\ScMatch;
use Symfony\Component\DomCrawler\Crawler;

class MatchRepo extends Component
{
    const MATH_STATUS_WAIT = "W";
    const MATH_STATUS_START = "S";
    const MATH_STATUS_FINSH = "F";


    public  function saveMatch($match, $home, $away, $tournament, $time_plus, $type_crawl)
    {

        $is_new = false;
        $timeInfo = $this->getTime($match->getTime(), $time_plus);
        $month = date("m", time() + $time_plus * 24 * 60 * 60);
        $year = date("Y",  time() + $time_plus * 24 * 60 * 60);
        $day = date("d",  time() + $time_plus * 24 * 60 * 60);
        $matchSave = ScMatch::findFirst([
            "match_home_id = :home_id: AND match_away_id = :away_id: 
            AND (match_start_day = :day: OR match_start_day = :day1: OR match_start_day = :day2: )
            AND match_start_month = :month: AND match_start_year = :year:",
            'bind' => [
                'home_id' => $home->getTeamId(),
                'away_id' => $away->getTeamId(),
                'day' => $day,
                'month' => $month,
                'year' => $year,
                'day1' => $day - 1,
                'day2' => $day - 2
            ]
        ]);

        if (!$matchSave) {
            $is_new = true;
            $matchSave = new ScMatch();
            $matchSave->setMatchName($home->getTeamSlug() . "-vs-" . $away->getTeamSlug());
            $matchSave->setMatchHomeId($home->getTeamId());
            $matchSave->setMatchAwayId($away->getTeamId());
            $matchSave->setMatchInsertTime(time());

            if (!$timeInfo['start_time']) {
                $timeInfo['start_time'] = $match->getTime();
                $day_start = date('d', time());
                $month_start = date('m', time());
                $year_start = date('Y', time());
            } else {
                $day_start = date('d', $timeInfo['start_time']);
                $month_start = date('m', $timeInfo['start_time']);
                $year_start = date('Y', $timeInfo['start_time']);
            }
            $matchSave->setMatchStartDay($day_start);
            $matchSave->setMatchStartMonth($month_start);
            $matchSave->setMatchStartYear($year_start);
            if ($timeInfo['start_time']) {
                //use crawl api
                $matchSave->setMatchStartTime($timeInfo['start_time']);
            }
        } else {
            if ($timeInfo['start_time'] < $matchSave->getMatchStartTime()) {
                return [
                    'matchSave' => false,
                    'is_new' => false
                ];
            }
            if ($match->getHomeScore()  < $matchSave->getMatchHomeScore()) {
                return [
                    'matchSave' => false,
                    'is_new' => false
                ];
            }
            if ($match->getAwayScore()  < $matchSave->getMatchAwayScore()) {
                return [
                    'matchSave' => false,
                    'is_new' => false
                ];
            }
        }

        if ($timeInfo['time_live'] == "HT") {
            if ($matchSave->getMatchTimeHt() == "" && $timeInfo['status'] == self::MATH_STATUS_START) {
                $matchSave->setMatchTimeHt(time());
            }
            if ($matchSave->getMatchScoreHt() == "" && $timeInfo['status'] == self::MATH_STATUS_START) {
                $score_ht = $match->getHomeScore() . "-" . $match->getAwayScore();
                $matchSave->setMatchScoreHt($score_ht);
            }
        }
        if ($timeInfo['time_live'] == "FT") {
            if ($matchSave->getMatchTimeFt() == "") {
                $matchSave->setMatchTimeFt(time());
            }
            if ($matchSave->getMatchScoreFt() == "" && $timeInfo['status'] == self::MATH_STATUS_FINSH) {
                $score_ft = $match->getHomeScore() . "-" . $match->getAwayScore();
                $matchSave->setMatchScoreFt($score_ft);
            }
            if ($matchSave->getMatchTimeFinish() == "" && $timeInfo['status'] == self::MATH_STATUS_FINSH) {
                $matchSave->setMatchTimeFinish($timeInfo['start_time'] + 90 * 60);
            }
        }

        $matchSave->setMatchTime($timeInfo['time_live']);
        $matchSave->setMatchStatus($timeInfo['status']);
        $matchSave->setMatchRound($match->getRound());

        $matchSave->setMatchHomeScore(is_numeric($match->getHomeScore()) ? $match->getHomeScore() : 0);
        $matchSave->setMatchAwayScore(is_numeric($match->getAwayScore()) ? $match->getAwayScore() : 0);

        $matchSave->setMatchHomeCardRed(is_numeric($match->getHomeCardRed()) ? $match->getHomeCardRed() : 0);
        $matchSave->setMatchAwayCardRed(is_numeric($match->getAwayCardRed()) ? $match->getAwayCardRed() : 0);

        $matchSave->setMatchTournamentId($tournament->getTournamentId());
        $matchSave->setMatchTournamentId($tournament->getTournamentId());
        if ($type_crawl == MatchCrawl::TYPE_FLASH_SCORE) {
            $matchSave->setMatchLinkDetailFlashscore($match->getHrefDetail());
        }
        if ($type_crawl == MatchCrawl::TYPE_SOFA || $type_crawl == MatchCrawl::TYPE_API_SOFA) {
            $matchSave->setMatchLinkDetailSofa($match->getHrefDetail());
        }

        if ($type_crawl == MatchCrawl::TYPE_LIVE_SCORES) {
            $matchSave->setMatchLinkDetailLivescore($match->getHrefDetail());
        }
        $matchSave->setMatchOrder(1);
        $matchSave->setMatchOrder(1);
        if ($matchSave->save()) {
            return [
                'matchSave' => $matchSave,
                'is_new' => $is_new
            ];
        }
        var_dump($matchSave->getMessages());
        var_dump($match);
        return false;
    }
    public function getTime($match_time, $time_plus)
    {
        switch ($match_time) {
            case "HT":
            case "Half Time":
            case "HalfTime":
                $time = 45;
                $start_time = time() - $time * 60;

                $time_live = "HT";
                $status = self::MATH_STATUS_START;
                break;
            case "FT":
            case "Finished":
                $time = 90;
                $start_time = time() - $time * 60;

                $time_live = "FT";
                $status = self::MATH_STATUS_FINSH;
                break;
            case "AET":
                $time = 90;
                $start_time = time() - $time * 60;

                $time_live = "AET";
                $status = self::MATH_STATUS_FINSH;
                break;
            case "Interrupted":
                $time = 90;
                $start_time = time() - $time * 60;
                $time_live = "Interrupted";
                $status = self::MATH_STATUS_FINSH;
                break;
            default:
                if (strpos($match_time, ":")) {
                    $temp = "";
                    $arrString = str_split($match_time);
                    //  var_dump($arrString);exit;
                    foreach ($arrString as $char) {
                        if (is_numeric($char) || in_array($char, ['.', ":"])) {
                            $temp .= $char;
                        }
                    }
                    
                    $match_time = $temp;
                    if (strpos($match_time, ".")) {
                        //15.03. 07:00
                        // $match_time = "07.05.00:00FRO";
                        //  $match_time = str_replace("FRO","",$match_time);

                        $time = 0;
                        $arrTime = explode(".", $match_time);

                        $start_time = $arrTime[1] . "/" . $arrTime[0] . "/2023" . " " . $arrTime[2];

                        $start_time = strtotime($start_time);
                        if ($start_time < time()) {
                            $time_live = "FT";
                            $status = self::MATH_STATUS_FINSH;
                        } else {
                            $time_live = 0;
                            $status = self::MATH_STATUS_WAIT;
                        }
                        break;
                    } else {
                        $time = 0;
                        $start_time = $this->my->formatDateYMD(time()) . " " . $match_time;
                        $start_time = strtotime($start_time);
                        $time_live = 0;
                        $status = self::MATH_STATUS_WAIT;
                        break;
                    }
                } elseif (strpos($match_time, "+")) {
                    $match_time = str_replace("'", "", $match_time);
                    $time_live = $match_time;
                    $arrTime = explode("+", $match_time);
                    $time = 0;
                    foreach ($arrTime as $time_1) {
                        if (is_numeric($time_1)) {
                            $time += (int) $time_1;
                        } else {
                            echo "ERROR: " . $time_1;
                        }
                    }
                    $start_time = time() - $time * 60;
                    $status = self::MATH_STATUS_START;
                    break;
                } elseif (strpos($match_time, "'")) {
                    $arrTime = explode("'", $match_time);
                    $time = 0;
                    foreach ($arrTime as $time_1) {
                        $time += (int) $time_1;
                    }
                    $start_time = time() - $time * 60;

                    $arrTime = explode("'", $match_time);
                    $time_live = $time;

                    $status = self::MATH_STATUS_START;
                    break;
                } elseif (is_numeric($match_time)) {
                    $time = $match_time;
                    $start_time = time() - $time * 60;

                    $time_live = $match_time;
                    $status = self::MATH_STATUS_START;
                    break;
                } else {
                    $start_time = $match_time;
                    $start_time = strtotime($start_time);

                    $time_live = $match_time;
                    $status = self::MATH_STATUS_WAIT;
                    break;
                }
        }
        return [
            "status" => $status,
            'start_time' => $start_time && is_numeric($start_time) ? $start_time + $time_plus * 24 * 60 * 60 : $start_time,
            'time_live' => $time_live
        ];
    }
    public function getMatch($time, $status = "", $tournament = "")
    {
        $day = date('d', $time);
        $month = date('m', $time);
        $year = date('Y', $time);
        $status = "S";

        $match = ScMatch::query()
            ->innerJoin('Score\Models\ScTournament', 'match_tournament_id = t.tournament_id', 't')
            ->columns("match_id,match_tournament_id,match_name,match_home_id,match_away_id,match_home_score,match_away_score,
            match_insert_time,match_time,match_start_time,match_order,match_status,
            t.tournament_id,t.tournament_name,t.tournament_country,t.tournament_image,t.tournament_order")
            ->andWhere(
                "(match_start_day = :day: OR match_start_day = :day2: OR match_start_day = :day3:) AND match_start_month = :month: AND match_start_year = :year:",
                [
                    'day' => $day,
                    'day2' => $day - 1,
                    'day3' => $day + 1,
                    'month' => $month,
                    'year' => $year
                ]
            );
        if ($status) {
            $match = $match->andWhere("match_status = :status:", ['status' => $status]);
        }
        if ($tournament) {
            $match = $match->andWhere("t.tournament_id = :tournament:", ['tournament' => $tournament]);
        }

        $match = $match->orderBy("match_order")
            ->execute();
        return $match->toArray();
    }
    public function getOnlyMatch($time, $status = "", $tournament = "")
    {
        $day = date('d', $time);
        $month = date('m', $time);
        $year = date('Y', $time);
        $status = "S";

        $match = ScMatch::query()
            ->columns("match_id,match_tournament_id,match_name,match_home_id,match_away_id,match_home_score,match_away_score,
            match_insert_time,match_time,match_start_time,match_order,match_status")
            ->andWhere(
                "(match_start_day = :day: OR match_start_day = :day2: OR match_start_day = :day3:) AND match_start_month = :month: AND match_start_year = :year:",
                [
                    'day' => $day,
                    'day2' => $day - 1,
                    'day3' => $day + 1,
                    'month' => $month,
                    'year' => $year
                ]
            );
        if ($status) {
            $match = $match->andWhere("match_status = :status:", ['status' => $status]);
        }
        $match = $match->orderBy("match_order")
            ->execute();
        return $match->toArray();
    }
    public static function getMatchToday()
    {
        $today = strtotime(strftime('%Y-%m-%d', time()));
        $start_day = $today - 7 * 60 * 60;
        //thời gian bonus là +- 160 phút
        $bonus_start_day = $start_day - 160 * 60;
        $end_day = $today + 24 * 60 * 60;
        //thời gian bonus là +- 160 phút
        $bonus_end_day = $end_day + 160 * 60;
        $arrMatch = ScMatch::find([
            '(match_start_time > :start: AND match_start_time < :end_day: AND match_status != "S")
            OR (match_start_time > :start_bonus: AND match_start_time < :end_day_bonus: AND match_status = "S")',
            'bind' => [
                'start' => $start_day,
                'start_bonus' => $bonus_start_day,
                'end_day' => $end_day,
                'end_day_bonus' => $bonus_end_day,
            ],
            'order' => "match_status DESC"
        ]);
        return $arrMatch;
    }
    public static function getFirstById($id)
    {
        return ScMatch::findFirst([
            'match_id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);
    }
}
