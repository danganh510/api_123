<?php

namespace Score\Repositories;

use Exception;
use Score\Models\ForexcecConfig;
use Phalcon\Mvc\User\Component;
use Score\Models\ScMatch;
use Score\Models\ScTeam;
use Score\Models\ScTournament;
use Symfony\Component\DomCrawler\Crawler;

class MatchRepo extends Component
{
    const MATH_STATUS_WAIT = "W";
    const MATH_STATUS_START = "S";
    const MATH_STATUS_FINSH = "F";
    const MATH_WAIT_UPDATE = "U";
    const MATH_CANCEL = "C";
    const MATH_AFTER_FT = "A";
    const MATH_POSTPONED = "P";


    public function saveMatch($match, $home, $away, $tournament, $time_plus, $type_crawl, &$arrIdMatch = [], $is_list = true)
    {

        $is_new = false;
        $timeInfo = $this->getTime($match->getTime(), $time_plus);
        if (is_numeric($timeInfo['start_time']) && $timeInfo['start_time'] !== 0) {
            $month = date("m", $timeInfo['start_time']);
            $year = date("Y", $timeInfo['start_time']);
            $day = date("d", $timeInfo['start_time']);
        } else {
            $month = date("m", time());
            $year = date("Y", time());
            $day = date("d", time());
        }

        // $matchSave = ScMatch::findFirst([
        //     "match_home_id = :home_id: AND match_away_id = :away_id: 
        //     AND (match_start_day = :day: OR match_start_day = :day1: OR match_start_day = :day2: )
        //     AND match_start_month = :month: AND match_start_year = :year:",
        //     'bind' => [
        //         'home_id' => $home->getTeamId(),
        //         'away_id' => $away->getTeamId(),
        //         'day' => $day,
        //         'month' => $month,
        //         'year' => $year,
        //         'day1' => $day - 1,
        //         'day2' => $day - 2
        //     ]
        // ]);
        //chuyển crawl flashscore, sử dụng link để tìm, tránh trường hợp hoãn trận đấu dẫn đến 2 trận đấu giống nhau

        $matchSave = ScMatch::findFirst([
            " match_link_detail_flashscore = :id_match_flashscore:",
            'bind' => [
                'id_match_flashscore' => $match->getHrefDetail(),
            ]
        ]);

        if (!$matchSave) {
            $is_new = true;
            $matchSave = new ScMatch();
            $matchSave->setMatchName($home->getTeamSlug() . "-vs-" . $away->getTeamSlug());
            $matchSave->setMatchHomeId($home->getTeamId());
            $matchSave->setMatchAwayId($away->getTeamId());

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
        }
        if ($match->getRound()) {
            $matchSave->setMatchRound($match->getRound());
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

        if (!is_numeric($timeInfo['time_live']) || $timeInfo['time_live'] >= $matchSave->getMatchTime() || !is_numeric($matchSave->getMatchTime())) {
            $matchSave->setMatchTime($timeInfo['time_live']);
        }

        if ($match->getHomeScore() > $matchSave->getMatchHomeScore() || !is_numeric($matchSave->getMatchHomeScore())) {
            $matchSave->setMatchHomeScore(is_numeric($match->getHomeScore()) ? $match->getHomeScore() : 0);
        }
        if ($match->getAwayScore() > $matchSave->getMatchAwayScore() || !is_numeric($matchSave->getMatchAwayScore())) {
            $matchSave->setMatchAwayScore(is_numeric($match->getAwayScore()) ? $match->getAwayScore() : 0);
        }

        $matchSave->setMatchStatus($timeInfo['status']);
        $matchSave->setMatchHomeCardRed(is_numeric($match->getHomeCardRed()) ? $match->getHomeCardRed() : 0);
        $matchSave->setMatchAwayCardRed(is_numeric($match->getAwayCardRed()) ? $match->getAwayCardRed() : 0);
        $matchSave->setMatchInsertTime(time());

        // if ($is_list == false) {
        //     //nếu crawl bằng tournament thì k crawl bằng detail nữa
        //     $matchSave->setMatchCrawlDetailLive(1);
        // }

        $result = $matchSave->save();

        if ($matchSave->getMessages()) {
            echo json_encode($matchSave->getMessages());
        }
        if ($result) {
            $arrIdMatch[] = $matchSave->getMatchId();
            return [
                'matchSave' => $matchSave,
                'is_new' => $is_new
            ];
        } else {

            return [
                'messages' => $matchSave->getMessages()
            ];
        }

        return false;
    }
    public function getTime($match_time, $time_plus, $type = "list")
    {
        $match_time = trim($match_time);
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
            case "Awaiting updates":
                $time = 90;
                $start_time = 0;
                $time_live = "Awaiting updates";
                $status = self::MATH_WAIT_UPDATE;
                break;
            case "AfterET":
                $time = 120;
                $start_time = time() - $time * 60;
                $time_live = "AfterET";
                $status = self::MATH_STATUS_FINSH;
                break;
            case "After Extra Time":
                $time = 120;
                $start_time = time() - $time * 60;
                $time_live = "After Extra Time";
                $status = self::MATH_STATUS_FINSH;
                break;
            case "After Penalties":
                $time = 90;
                $start_time = time() - $time * 60;
                $time_live = "After Penalties";
                $status = self::MATH_STATUS_FINSH;
                break;
            case "AfterPen.":
                $time = 90;
                $start_time = time() - $time * 60;
                $time_live = "After Penalties";
                $status = self::MATH_STATUS_FINSH;
                break;
            case "Penalties":
                $time = 135;
                $start_time = time() - $time * 60;
                $time_live = "Penalties";
                $status = self::MATH_AFTER_FT;
                break;
            case "Awarded":
                $time = 135;
                $start_time = time() - $time * 60;
                $time_live = "Awarded";
                $status = self::MATH_AFTER_FT;
                break;
            case "Postponed":
                $time = 135;
                $start_time = time() - $time * 60;
                $time_live = "Postponed";
                $status = self::MATH_POSTPONED;
                break;
            case "Cancelled":
                $time = 135;
                $start_time = time() - $time * 60;
                $time_live = "Cancelled";
                $status = self::MATH_CANCEL;
                break;
            default:
                if (strpos($match_time, "ExtraTime") !== false) {
                    $arrTime = explode(" ", $match_time);
                    $time = isset($arrTime[1]) && is_numeric($arrTime[1]) ? (int) $arrTime[1] : 90;
                    $start_time = time() - $time * 60;
                    $time_live = $match_time;
                    $status = self::MATH_AFTER_FT;
                    break;
                } else if (strpos($match_time, ":")) {
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
                        if ($type == "detail") {
                            $arrTime = explode(":", $match_time);
                            $time_live = $arrTime[0] + 1;
                            $start_time = 0;
                            $status = self::MATH_STATUS_START;
                            break;
                        } else {
                            $time = 0;
                            $start_time = $this->my->formatDateYMD(time()) . " " . $match_time;
                            $start_time = strtotime($start_time);
                            $time_live = 0;
                            $status = self::MATH_STATUS_WAIT;
                            break;
                        }
                    }
                } elseif (strpos($match_time, "+")) {
                    $match_time = str_replace("'", "", $match_time);
                    $time_live = $match_time;
                    $arrTime = explode("+", $match_time);
                    $time = 0;
                    foreach ($arrTime as $time_1) {
                        if (is_numeric($time_1)) {
                            $time += (int) $time_1;
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
    public static function getMatchToday($time_request)
    {
        $today = strtotime($time_request);
        $start_day = $today - 7 * 60 * 60;
        //thời gian bonus là +- 160 phút
        $bonus_start_day = $start_day - 160 * 60;
        $end_day = $start_day + 24 * 60 * 60;
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
    public static function getMatchTodayByTourId($tourId, $limit)
    {
        $today = strtotime(strftime('%Y-%m-%d', time()));
        $start_day = $today - 7 * 60 * 60;
        //thời gian bonus là +- 160 phút
        $bonus_start_day = $start_day - 160 * 60;
        $end_day = $today + 24 * 60 * 60;
        //thời gian bonus là +- 160 phút
        $bonus_end_day = $end_day + 160 * 60;
        $arrMatch = ScMatch::find([
            '((match_start_time > :start: AND match_start_time < :end_day: AND match_status != "S")
            OR (match_start_time > :start_bonus: AND match_start_time < :end_day_bonus: AND match_status = "S") ) AND match_tournament_id = :TOUR_ID:',
            'bind' => [
                'start' => $start_day,
                'start_bonus' => $bonus_start_day,
                'end_day' => $end_day,
                'end_day_bonus' => $bonus_end_day,
                'TOUR_ID' => $tourId
            ],
            'limit' => (int) $limit,
            'order' => "match_status DESC"
        ]);
        return $arrMatch;
    }
    public static function getMatchScheduleByTourId($tourId, $limit)
    {
        $start_day = time();

        $arrMatch = ScMatch::find([
            '(match_start_time > :start_day: AND match_status = "W") AND match_tournament_id = :TOUR_ID:',
            'bind' => [
                'start_day' => $start_day,
                'TOUR_ID' => $tourId
            ],
            'limit' => (int) $limit,
            'order' => "match_start_time ASC"
        ]);
        return $arrMatch;
    }
    public static function getMatchOldByTourId($tourId, $limit)
    {
        $start_day = time();

        $arrMatch = ScMatch::find([
            '(match_start_time < :start_day: AND match_status = "F") AND match_tournament_id = :TOUR_ID:',
            'bind' => [
                'start_day' => $start_day,
                'TOUR_ID' => $tourId
            ],
            'limit' => (int) $limit,
            'order' => "match_start_time DESC"
        ]);
        return $arrMatch;
    }
    public static function getMatchScheduleByTourId2($tourId, $limit)
    {
        $today = strtotime(strftime('%Y-%m-%d', time()));
        $start_day = $today - 7 * 60 * 60;
        //thời gian bonus là +- 160 phút
        $bonus_start_day = $start_day - 160 * 60;
        $end_day = $today + 24 * 60 * 60;
        //thời gian bonus là +- 160 phút
        $bonus_end_day = $end_day + 160 * 60;
        $arrMatch = ScMatch::find([
            '((match_start_time > :end_day: AND match_status != "S") OR (match_start_time > :end_day_bonus: AND match_status = "S")) AND match_tournament_id = :TOUR_ID:',
            'bind' => [
                'end_day' => $end_day,
                'end_day_bonus' => $bonus_end_day,
                'TOUR_ID' => $tourId
            ],
            'limit' => (int) $limit,
            'order' => "match_start_time ASC"
        ]);
        return $arrMatch;
    }
    public static function getMatchOldByTourId2($tourId, $limit)
    {
        $today = strtotime(strftime('%Y-%m-%d', time()));
        $start_day = $today - 7 * 60 * 60;
        $arrMatch = ScMatch::find([
            '(match_start_time < :start_day: AND match_status = "F") AND match_tournament_id = :TOUR_ID:',
            'bind' => [
                'start_day' => $start_day,
                'TOUR_ID' => $tourId
            ],
            'limit' => (int) $limit,
            'order' => "match_status DESC"
        ]);
        return $arrMatch;
    }
    public static function getMatchTourIsShow($limit, $day, $strTour)
    {

        $time = time() + $day * 24 * 60 * 60; //lấy bao nhiêu ngày tiếp theo
        if ($day > 0) {
            $arrMatch = ScMatch::find([
                'match_start_time > :now_time: AND match_start_time < :to_time: AND FIND_IN_SET(match_tournament_id,:str_tour:) AND match_status = "W"',
                'bind' => [
                    'now_time' => time(),
                    'to_time' => $time,
                    'str_tour' => $str_tour,
                ],
                'limit' => (int) $limit,
                'order' => "match_start_time ASC"
            ]);
        } else {
            $arrMatch = ScMatch::find([
                'match_start_time < :now_time: AND match_start_time > :to_time: AND FIND_IN_SET(match_tournament_id,:str_tour:) AND match_status = "F"',
                'bind' => [
                    'now_time' => time(),
                    'to_time' => $time,
                    'str_tour' => $str_tour,
                ],
                'limit' => (int) $limit,
                'order' => "match_start_time ASC"
            ]);
        }

        return $arrMatch;
    }
    public static function implementsMatch($arrMatch, $arrTeam)
    {
        $result = [];
        $arrMatch = $arrMatch->toArray();

        foreach ($arrMatch as $match) {

            $homeModel = new ScTeam();
            $home = $homeModel->setData($arrTeam[$match['match_home_id']]);

            $awayModel = new ScTeam();
            $away = $awayModel->setData($arrTeam[$match['match_away_id']]);

            $matchInfo = [
                'status' => [
                    'description' => $match['match_status'],
                    'type' => $match['match_status']
                ],
                'matchInfo' => [
                    'id' => $match['match_id'],
                    'time_start' => $match['match_start_time'],
                    'time' => $match['match_time'],
                    'htScore' => $match['match_score_ht'],
                    'slugName' => $match['match_name'],
                ],
                'homeTeam' => [
                    'id' => $home->getTeamId(),
                    'name' => $home->getTeamName(),
                    'slug' => MyRepo::create_slug($home->getTeamName()),
                    'logo' => $home->getTeamLogoSmall(),
                    'score' => [
                        'score' => $match['match_home_score'],
                        'redCard' => $match['match_home_card_red'],
                        'time' => [$match['match_home_score']]
                    ]
                ],
                'awayTeam' => [
                    'id' => $away->getTeamId(),
                    'name' => $away->getTeamName(),
                    'slug' => MyRepo::create_slug($away->getTeamName()),
                    'logo' => $away->getTeamLogoSmall(),
                    'score' => [
                        'score' => $match['match_away_score'],
                        'redCard' => $match['match_away_card_red'],
                        'time' => [$match['match_home_score']]
                    ]
                ],
                'roundInfo' => $match['match_round'],
            ];
            $result[$match['match_id']] = $matchInfo;
        }
        return $result;
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