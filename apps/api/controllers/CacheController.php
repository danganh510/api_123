<?php

namespace Score\Api\Controllers;

use ConstEnv;
use Exception;
use Score\Models\ScMatch;
use Score\Repositories\Team;


use Score\Models\ScTeam;
use Score\Models\ScTournament;
use Score\Repositories\CacheGetData;
use Score\Repositories\CacheMatch;
use Score\Repositories\CacheMatchIdLive;
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
    public function getcacheteamAction()
    {
        $type = $this->request->get("type");
        $teamCache = new CacheTeam($this->requestParams['language']);
        $arrTeam = $teamCache->get($type);

        die(json_encode($arrTeam));
    }
    public function getcachetournamentAction()
    {
        $type = $this->request->get("type");
        $cacheTour = new CacheTour($this->requestParams['language']);
        $arrTour = $cacheTour->get($type);
        die(json_encode($arrTour));
    }
    public function cachematchliveAction()
    {
        $cache = new CacheMatchIdLive();
        $arrMatchIdLive = $cache->getCache();

        $arrMatch = ScMatch::find([
            'FIND_IN_SET(match_id,:arrId:)',
            'bind' => [
                'arrId' => implode(",", $arrMatchIdLive)
            ]
        ]);
        $arrMatch = $arrMatch->toArray();
        $matchCache = new CacheMatchLive();
        $result = $matchCache->setCache(json_encode($arrMatch));

        die($result);
    }
    public function cachematch7dayAction()
    {
        $timestamp_before_7 = time() - 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
        $timestamp_affter_7 = time() + 7 * 24 * 60 * 60 + 60 * 60; //backup 1h
        $arrMatch = ScMatch::find(
            "match_start_time > $timestamp_before_7 AND match_start_time < $timestamp_affter_7"
        );
        $arrMatch = $arrMatch->toArray();
        $matchCache = new CacheMatch();
        $result = $matchCache->setCache(json_encode($arrMatch));
        die($result);
    }


    function countryAction()
    {
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
    function deletecacheAction()
    {
        $cache = new CacheGetData();
        $result = $cache->deleteFolder();        
        return [
            'code' => 200,
            "message" => "delete cache success",
            'status' => $result,
        ];
    }
}
