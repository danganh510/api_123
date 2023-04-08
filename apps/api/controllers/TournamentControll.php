<?php

namespace Score\Api\Controllers;

use ConstEnv;
use Exception;
use Score\Models\ScMatch;
use Score\Repositories\Team;


use Score\Models\ScTeam;
use Score\Models\ScTournament;
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
    public function gettounamentshowAction()
    {
        $list_data = [
            [
                'id' => 1,
                "name" => "champion",
                "order" => 999,
                "season" => "2023/2024"
            ],
            [
                'id' => 1,
                "name" => "champion",
                "order" => 999,
                "season" => "2023/2024"
            ],
            [
                'id' => 1,
                "name" => "champion",
                "order" => 999,
                "season" => "2023/2024"
            ],
            [
                'id' => 1,
                "name" => "champion",
                "order" => 999,
                "season" => "2023/2024"
            ]
        ];
        $dataReturn = [
            'code' => 200,
            'status' => true,
            'data' => $list_data
        ];
        return $dataReturn;
    }
    public function getscheduletourAction()
    {
        $tour_id = $this->request->get("id");
        $matchInfo = [
            'status' => [
                'description' => "S",
                'type' => "S"
            ],
            'matchInfo' => [
                'id' => 1,
                'time_start' => 1680933100,
                'time' => "FT",
                'htScore' => "1-0",
                'slugName' => "vietnam-vs-lao",
            ],
            'homeTeam' => [
                'id' => 1,
                'name' => $home->getTeamName(),
                'slug' => $this->create_slug($home->getTeamName()),
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
                'slug' => $this->create_slug(
                    $away->getTeamName(),
                ),
                'logo' => $away->getTeamLogoSmall(),
                'score' => [
                    'score' => $match['match_away_score'],
                    'redCard' => $match['match_away_card_red'],
                    'time' => [$match['match_home_score']]
                ]
            ],
            'roundInfo' => $match['match_round'],
        ];
    }
    public function getstandingstourAction()
    {
        $tour_id = $this->request->get("id");
    }
}
