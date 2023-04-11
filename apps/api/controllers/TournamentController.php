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
use Score\Repositories\MyRepo;
use Score\Repositories\Tournament;

class TournamentController extends ControllerBase
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
    $tourModel = Tournament::findFirstById($tour_id);
    if (!$tourModel) {
      $dataReturn = [
        'code' => 200,
        'status' => false,
        'messages' => "not found tournament"
      ];
      goto end;
    }


    $tourInfo = [
      'name' => $tourModel->getTournamentName(),
      'slug' => $tourModel->getTournamentSlug(),
      'category' => [
        'name' => $tourModel->getTournamentCountry(),
        'slug' => MyRepo::create_slug($tourModel->getTournamentCountry()),
        'sport' => [
          "name" =>  "football",
          "slug" => "football"
        ],
        'flag' => $tourModel->getTournamentCountry(),
        "countryCode" => $tourModel->getTournamentCountryCode()
      ]
    ];

    $cacheTeam = new CacheTeam();
    $arrTeam = $cacheTeam->get(ConstEnv::CACHE_TYPE_ID);

    $matchOld = MatchRepo::getMatchOldByTourId($tour_id);
    $matchToday = MatchRepo::getMatchTodayByTourId($tour_id);
    $matchSchedule = MatchRepo::getMatchScheduleByTourId($tour_id);

    $arrMatchOld = MatchRepo::implementsMatch($matchOld, $arrTeam);
    $arrMatchToday = MatchRepo::implementsMatch($matchToday, $arrTeam);
    $arrMatchSchedule = MatchRepo::implementsMatch($matchSchedule, $arrTeam);

    $data = [
      $tour_id => [
        'tournament' => $tourInfo,
        'match_old' => $arrMatchOld,
        'match_today' => $arrMatchToday,
        'match_schedule' => $arrMatchSchedule,
      ]

    ];

    $dataReturn = [
      'code' => 200,
      'status' => true,
      'data' => $data
    ];
    end:
    return $dataReturn;
  }
  public function getstandingstourAction()
  {
    $tour_id = $this->request->get("id");
  }
}
