<?php

namespace Score\Api\Controllers;

use ConstEnv;
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

class SavematchController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
    function listAction()
    {
        $list_match = $this->requestParams['list_match'];
        $time_plus = $this->requestParams['time_plus'];
        $this->type_crawl = $this->requestParams['type_crawl'];
        $is_live = $this->requestParams['is_live'];
        $is_live = (boolean) $is_live;
        $tour = $this->requestParams['tour'];

        $cacheTeam = new CacheTeam();
        $arrTeamOb = $cacheTeam->get(ConstEnv::CACHE_TYPE_NAME_FLASH);

        $cacheTour = new CacheTour();
        $arrTour = $cacheTour->get(ConstEnv::CACHE_TYPE_NAME_FLASH);

        $matchRepo = new MatchRepo();
        $total = 0;
        $is_new = false;
        $arrMatchCrawl = [];
        $is_cache_team = false;
        $is_cache_tour = false;
        foreach ($list_match as $match_info) {
            $match = new MatchCrawl();
            $match->setData($match_info);

            $tournamentCrawl = new MatchTournament();
            $tournamentCrawl->setData($match->getTournament());

            $home = Team::saveTeam($match->getHome(), $match->getHomeImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl,$is_cache_team);
            $away = Team::saveTeam($match->getAway(), $match->getAwayImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl,$is_cache_team);
            $tournament = Tournament::saveTournament($tournamentCrawl, $this->type_crawl, $arrTour,$is_cache_tour);

            if (!$home) {
                echo "can't save home team";
                continue;
            }
            if (!$away) {
                echo "can't save away team";
                continue;
            }
            if (!$tournament) {
                echo "can't save tournament team";
                continue;
            }
            $result =  $matchRepo->saveMatch($match, $home, $away, $tournament, $time_plus, $this->type_crawl);
            if (isset($result['matchSave'])) {
                $arrMatchCrawl[] = $result['matchSave'];
                $total++;
                //  echo "Save match success --- ";
            } else {
                echo "Save match false ---";
            }
            if ($result['is_new']) {
                $is_new = true;
            }
        }
        if ($is_cache_team) {
            $cache = new CacheTeam();
            $cache->set("all");
        }
        // if ($is_cache_tour) {
        //     $cache = new CacheTour();
        //     $cache->set("all");
        // }
        delete_cache:

     
        if (($is_live !== true)) {
            $timestamp_before_7 = time() - 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
            $timestamp_affter_7 = time() + 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
            $arrMatch = ScMatch::find(
                "match_start_time > $timestamp_before_7 AND match_start_time < $timestamp_affter_7"
            );
            $arrMatch = $arrMatch->toArray();
            $matchCache = new CacheMatch();
            $matchCache->setCache(json_encode($arrMatch));
        } else {
            var_dump($is_live);exit;
            $time_end = time() + 3 * 60;
            $time_begin = time() - 3 * 60;
            $time_now = time();
            $arrMatch = ScMatch::find(
                "match_status = 'S' OR (match_status = 'F' AND match_time_finish < $time_end  AND match_time_finish > $time_now) OR (match_status = 'W' AND match_start_time > $time_begin AND match_start_time < $time_now) "
            );
            $arrMatch = $arrMatch->toArray();
            $matchCache = new CacheMatchLive();
            $matchCache->setCache(json_encode($arrMatch));
  
        }

        return [
            'code' => 200,
            'status' => 'success',
            'total' => $total,
            'is_new' => $is_new,
        ];
    }
    public function detailAction()
    {
    }
}
