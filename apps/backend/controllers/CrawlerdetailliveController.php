<?php

namespace Score\Backend\Controllers;

use Exception;
use GuzzleHttp\Promise\Promise;
use Score\Models\ScMatch;
use Score\Repositories\CrawlerDetail;
use Score\Repositories\CrawlerScore;
use Score\Repositories\MatchCrawl;


use Score\Models\ScMatchInfo;
use Score\Models\ScTeam;
use Score\Models\ScTournament;
use Score\Repositories\MatchDetailRepo;
use Score\Repositories\MatchRepo;
use Score\Repositories\MyRepo;
use Score\Repositories\Team;

class CrawlerdetailliveController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
    public function indexAction()
    {
        $id = $this->request->get("id");

        if (!$id) {
            $this->checkTimeList();
        }

        $start_time_cron = time();
        echo "============================\n\r";
        echo "Start crawl data in " . $this->my->formatDateTime($start_time_cron) . "\n\r";
        ini_set('max_execution_time', 20);
        $is_live =  $this->request->get("isLive");
        $this->type_crawl = $this->request->get("type");
        $is_nomal = $this->request->get("isNomal");


        $detailRepo = new MatchDetailRepo();
        
        if ($is_nomal) {
            $is_nomal = false;
            //is Live
            $matchCrawl = $detailRepo->getMatchCrawlNomal();
        } else {
            $matchCrawl = $detailRepo->getMatchCrawl($is_live, $id);
        }

        if (!$matchCrawl) {
            echo "Not found Match";
            die();
        }
        if ($is_live) {
            $matchCrawl->setMatchCrawlDetailLive(1);
        } else {
            if ($matchCrawl->getMatchStatus() == "F") {
                $flag_crawl = 2;
            } else {
                $flag_crawl = $matchCrawl->getMatchCrawlDetail() + 1;
            }
            $matchCrawl->setMatchCrawlDetail($flag_crawl);
        }
        $matchCrawl->setMatchInsertTime(time());
        $result = $matchCrawl->save();
        echo $matchCrawl->getMatchId() . "---";
        if ($matchCrawl->getMatchLinkDetailFlashscore() == "" || $matchCrawl->getMatchLinkDetailFlashscore() == null) {
            goto end;
        }

        $urlDetail = "https://www.flashscore.com/" . $matchCrawl->getMatchLinkDetailFlashscore() . "/#/match-summary/match-summary";
     //   $urlDetail = "https://www.flashscore.com/match/xU7bnMdH/#/match-summary/match-summary";
        //tab: info,tracker,statistics
        $crawler = new CrawlerDetail($this->type_crawl, $urlDetail, $is_live);
        $detail = $crawler->getInstance();
        $infoModel = ScMatchInfo::findFirst([
            'info_match_id = :id:',
            'bind' => [
                'id' => $matchCrawl->getMatchId()
            ]
        ]);
        if (!$infoModel) {
            $infoModel = new ScMatchInfo();
            $infoModel->setInfoMatchId($matchCrawl->getMatchId());
        }
        $infoModel->setInfoTime(json_encode($detail['info']));
        $infoModel->setInfoStats(json_encode($detail['start']));
        $infoModel->setInfoSummary(json_encode($detail['tracker']));

        $result = $infoModel->save();
        if ($result) {
            echo "crawl succes--";
        }

        //lưu thông tin mới của match
        if (
            (!empty($detail['match']) && isset($detail['match']['homeScore']) && isset($detail['match']['awayScore'])
            && is_numeric($detail['match']['homeScore']) && is_numeric($detail['match']['homeScore'])) || !is_numeric($detail['match']['timeNow'])
        ) {

            $detail['match']['timeNow'] = trim($detail['match']['timeNow']);

            if (is_numeric($detail['match']['homeScore'])) {
                $matchCrawl->setMatchHomeScore($detail['match']['homeScore']);
            }
            if (is_numeric($detail['match']['awayScore'])) {
                $matchCrawl->setMatchAwayScore($detail['match']['awayScore']);
            }
            $time = $detail['match']['timeNow'];
            $time = trim($time);
            if ($time) {
                $matchRepo = new MatchRepo();
                $timeInfo = $matchRepo->getTime($time, 0, "detail");
                
                $matchCrawl->setMatchTime($timeInfo['time_live']);
                if ($id == 4800) {
                    var_dump($time, $timeInfo);
                }
                //còn 1 lỗi
                $matchCrawl->setMatchStatus($timeInfo['status']);
            }
        }
        if ($detail['match']['startTime'] && isset($detail['match']['startTime'])) {
            $start_time = strtotime($detail['match']['startTime']);
            $start_time = is_numeric($start_time) && $start_time != 0 ? $start_time : false;
            if ($start_time) {
                $matchCrawl->setMatchStartTime($start_time);
            }
        }

        //save logo team:
        $homeTeam = ScTeam::findFirstById($matchCrawl->getMatchHomeId());
        if ($homeTeam && !$homeTeam->getTeamLogoCrawl() && !empty($detail['match']['homeLogo'])) {
            $homeTeam->setTeamLogoCrawl($detail['match']['homeLogo']);
            $homeTeam->save();
        }
        //save logo team:
        $awayTeam = ScTeam::findFirstById($matchCrawl->getMatchAwayId());
        if ($awayTeam && !$awayTeam->getTeamLogoCrawl() && !empty($detail['match']['awayLogo'])) {
            $awayTeam->setTeamLogoCrawl($detail['match']['awayLogo']);
            $awayTeam->save();
        }
        $matchCrawl->save();
                
        if ($is_live) {
            $cache =  file_get_contents("http://123tyso.live/cache-match-live");
            if ($cache) {
                echo "Cache suscces\r\n";
            } else {
                echo "Cache fail\r\n";
            }
        }
        end:
        echo "---finish in " . (time() - $start_time_cron) . " second ---- \n\r";
        die();
    }
}
