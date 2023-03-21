<?php

namespace Score\Api\Controllers;

use Exception;
use Score\Models\ScMatch;
use Score\Repositories\Team;


use Score\Models\ScTeam;
use Score\Models\ScTournament;
use Score\Repositories\CacheMatch;
use Score\Repositories\CacheMatchLive;
use Score\Repositories\CacheTeam;
use Score\Repositories\CacheTour;
use Score\Repositories\CrawlerList;
use Score\Repositories\MatchCrawl;
use Score\Repositories\MatchRepo;
use Score\Repositories\MatchTournament;
use Score\Repositories\Tournament;

class GetlinkdetailController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
    public function indexAction()
    {
        ini_set('max_execution_time', 20);
        $start_time_cron = time();
        $is_live =  $this->request->get("isLive");
        $this->type_crawl = $this->request->get("type");
        if ($is_live) {
            $arrTourKey = ScTournament::getTourIdCrawl();
            $matchCrawl = ScMatch::findFirst([
                ' match_status = "S" AND match_crawl_detail_live = "0" AND FIND_IN_SET(match_tournament_id,:arrTour:)',
                'bind' => [
                    'arrTour' => implode(",", $arrTourKey)
                ]
            ]);
            if (!$matchCrawl) {
                $sql = 'UPDATE Score\Models\ScMatch SET match_crawl_detail_live = "0" WHERE match_status = "S"';
                $this->modelsManager->executeQuery($sql);
                echo "--All restart: \r\n";
                $matchCrawl = ScMatch::findFirst([
                    ' match_status = "S" AND match_crawl_detail_live = "0" AND FIND_IN_SET(match_tournament_id,:arrTour:)',
                    'bind' => [
                        'arrTour' => implode(",", $arrTourKey)
                    ]
                ]);
            }
        } else {
            $matchCrawl = ScMatch::findFirst([
                'match_crawl_detail = 0 '
            ]);
            if (!$matchCrawl) {
                //crawl detail cho trận FT
                $matchCrawl = ScMatch::findFirst([
                    'match_crawl_detail = 1 '
                ]);
            }
        }
        if (!$matchCrawl) {
            echo "Not found Match";
            die();
        }
        if ($is_live) {
            $matchCrawl->setMatchCrawlDetailLive(1);
        } else {
            $flag_crawl = $matchCrawl->getMatchCrawlDetail() + 1;
            $flag_crawl = (int) $flag_crawl;
            $matchCrawl->setMatchCrawlDetail($flag_crawl);
        }
        $matchCrawl->save();

        echo $matchCrawl->getMatchId() . "---";
        if ($matchCrawl->getMatchLinkDetailFlashscore() == "" || $matchCrawl->getMatchLinkDetailFlashscore() == null) {
            goto end;
        }
    }
    public function detailAction()
    {
    }
}
