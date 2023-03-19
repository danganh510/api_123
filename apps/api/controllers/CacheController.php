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
use Score\Repositories\Tournament;

class CacheController extends ControllerBase
{

    public $type_crawl = MatchCrawl::TYPE_FLASH_SCORE;

    public function matchAction()
    {
        $timestamp_before_7 = time() - 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
        $timestamp_affter_7 = time() + 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
        $arrMatch = ScMatch::find(
            "match_start_time > $timestamp_before_7 AND match_start_time < $timestamp_affter_7"
        );
        $arrMatch = $arrMatch->toArray();
        $matchCache = new CacheMatch();
        $result = $matchCache->setCache(json_encode($arrMatch));
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



    function teamAction()
    {
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
    function liveAction()
    {
        $arrMatchCrawl = $this->requestParams['arrMatchCrawl'];
        $matchCache = new CacheMatchLive();
        $result = $matchCache->setCache(json_encode($arrMatchCrawl));
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
}
