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

class SavematchController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;
    function listAction()
    {
        $list_match = $this->requestParams['list_match'];
        $time_plus = $this->requestParams['time_plus'];
        $this->type_crawl = $this->requestParams['type_crawl'];
        $is_live = $this->requestParams['is_live'];
        $tour = $this->requestParams['tour'];

        $cacheTeam = new CacheTeam();
        $arrTeamOb = $cacheTeam->getCache();

        $matchRepo = new MatchRepo();
        $total = 0;
        $is_new = false;
        $arrMatchCrawl = [];
        foreach ($list_match as $match_info) {
            $match = new MatchCrawl();
            $match->setData($match_info);

            $tournamentCrawl = new MatchTournament();
            $tournamentCrawl->setData($match->getTournament());

            $home = Team::saveTeam($match->getHome(), $match->getHomeImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl);
            $away = Team::saveTeam($match->getAway(), $match->getAwayImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl);
            $tournament = Tournament::saveTournament($tournamentCrawl, $this->type_crawl);

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
            if ($result['matchSave']) {
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
        delete_cache:
        if (($is_live !== true && $total > 1)) {
            $timestamp_before_7 = time() - 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
            $timestamp_affter_7 = time() + 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
            $arrMatch = ScMatch::find(
                "match_start_time > $timestamp_before_7 AND match_start_time < $timestamp_affter_7"
            );
            $arrMatch = $arrMatch->toArray();
            $matchCache = new CacheMatch();
            $matchCache->setCache(json_encode($arrMatch));
        } else if (!$tour) {
              // islive
            $matchCache = new CacheMatchLive();
            $result = $matchCache->setCache(json_encode($arrMatchCrawl));
        } else {
          
            //to be conitnei
            $matchCache = new CacheMatchLive();
            $matchs = $matchCache->getCache();
            foreach ($matchs as $match) {

            }
        }

        //nếu có trận được tạo mới thì cache lại:
        if ($is_new || ($is_live !== true && $total > 1)) {
            $arrTeam = ScTeam::find("team_active = 'Y'");
            $arrTeam = $arrTeam->toArray();
            $arrTeamCache = [];
            foreach ($arrTeam as $team) {
                $arrTeamCache[$team['team_id']] = $team;
            }
            $teamCache = new CacheTeam();
            $teamCache->setCache(json_encode($arrTeamCache));

            echo "cache total: " . count($arrTeamCache) . " team /r/n";
            //cache tour
            $arrTour = ScTournament::find("tournament_active = 'Y'");
            $arrTour = $arrTour->toArray();
            $arrTourCache = [];
            foreach ($arrTour as $tour) {
                $arrTourCache[$tour['tournament_id']] = $tour;
            }
            $tourCache = new CacheTour();
            $result = $tourCache->setCache(json_encode($arrTourCache));
            if ($result) {
                return [
                    'code' => 200,
                    'status' => 'success',
                ];
            } else {
                return [
                    'code' => 200,
                    'status' => 'false',
                ];
            }
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
