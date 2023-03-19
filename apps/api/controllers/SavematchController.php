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
    function listAction()
    {
        $list_match = $this->requestParams['list_match'];
        $time_plus = $this->requestParams['time_plus'];
        $cacheTeam = new CacheTeam();
        $arrTeamOb = $cacheTeam->getCache();

        $matchRepo = new MatchRepo();
        $total = 0;
        $is_new = false;
        $arrMatchCrawl = [];
        foreach ($list_match as $match) {
            $home = Team::saveTeam($match->getHome(), $match->getHomeImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl);
            $away = Team::saveTeam($match->getAway(), $match->getAwayImg(), $match->getCountryCode(), $arrTeamOb, $this->type_crawl);
            $tournament = Tournament::saveTournament($match->getTournament(), $this->type_crawl);

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
        return [
            'code' => 200,
            'status' => 'success',
            'total' => $total,
            'is_new' => $is_new,
            'arrMatchCrawl' => $arrMatchCrawl
        ];
    }
}
