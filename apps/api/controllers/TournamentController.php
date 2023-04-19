<?php

namespace Score\Api\Controllers;

use ConstEnv;
use Exception;
use Score\Models\ScMatch;
use Score\Repositories\Team;


use Score\Models\ScTeam;
use Score\Models\ScTournament;
use Score\Models\ScTournamentStandings;
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
    $arrTour = ScTournament::find([
      'tournament_is_show = "Y"',
      "order" => "tournament_order DESC"
    ]);
    $list_data = [];
    foreach ($arrTour as $tour) {
      $list_data[] = [
        'id' => $tour->getTournamentId(),
        'name' => $tour->getTournamentName(),
        'order' => $tour->getTournamentOrder(),
        'season' => $tour->getTournamentSeason()
      ];
    }

    $dataReturn = [
      'code' => 200,
      'status' => true,
      'data' => $list_data
    ];
    return $dataReturn;
  }
  public function getscheduletourshowAction()
  {
    $limit = $this->request->get("limit");
    $day = $this->request->get("day");
    $arrTourIsShow = ScTournament::find([
      'tournament_is_show = "Y"',
      'columns' => "tournament_id"
    ]);
    $str_tour = array_column($arrTourIsShow->toArray(), "tournament_id");
    $str_tour = implode(",", $str_tour);

    $cacheTeam = new CacheTeam();
    $arrTeam = $cacheTeam->get(ConstEnv::CACHE_TYPE_ID);

    $matchSchedule = MatchRepo::getMatchTourIsShow($limit, $day, $str_tour);

    $arrMatchSchedule = MatchRepo::implementsMatch($matchSchedule, $arrTeam);

    $data = [
      'match_schedule' => $arrMatchSchedule,
    ];

    $dataReturn = [
      'code' => 200,
      'status' => true,
      'data' => $data
    ];
    end:
    return $dataReturn;
  }
  public function getscheduletourAction()
  {
    $tour_id = $this->request->get("id");
    $tourModel = Tournament::findFirstById($tour_id);
    $limit = $this->request->get("limit");
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
          "name" => "football",
          "slug" => "football"
        ],
        'flag' => $tourModel->getTournamentCountry(),
        "countryCode" => $tourModel->getTournamentCountryCode()
      ]
    ];

    $cacheTeam = new CacheTeam();
    $arrTeam = $cacheTeam->get(ConstEnv::CACHE_TYPE_ID);

    $matchOld = MatchRepo::getMatchOldByTourId($tour_id,$limit);
    $matchToday = MatchRepo::getMatchTodayByTourId($tour_id,$limit);
    $matchSchedule = MatchRepo::getMatchScheduleByTourId($tour_id,$limit);

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
    $limit = $this->request->get("limit");
    $type = $this->request->get("type");
    $type = $type ? $type : "all";
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
          "name" => "football",
          "slug" => "football"
        ],
        'flag' => $tourModel->getTournamentCountry(),
        "countryCode" => $tourModel->getTournamentCountryCode()
      ]
    ];
    $standingHome = [];
    $standingAway = [];
    $standingOveral = [];
    switch ($type) {
      case ConstEnv::TYPE_STANDING_HOME:
        $standingHome = ScTournamentStandings::findByIdAndType($tour_id, ConstEnv::TYPE_STANDING_HOME, $limit);
        break;
      case ConstEnv::TYPE_STANDING_AWAY:
        $standingAway = ScTournamentStandings::findByIdAndType($tour_id, ConstEnv::TYPE_STANDING_AWAY, $limit);
        break;
      case ConstEnv::TYPE_STANDING_OVERAL:
        $standingOveral = ScTournamentStandings::findByIdAndType($tour_id, ConstEnv::TYPE_STANDING_OVERAL, $limit);
        break;
      default:
        $standingHome = ScTournamentStandings::findByIdAndType($tour_id, ConstEnv::TYPE_STANDING_HOME, $limit);
        $standingAway = ScTournamentStandings::findByIdAndType($tour_id, ConstEnv::TYPE_STANDING_AWAY, $limit);
        $standingOveral = ScTournamentStandings::findByIdAndType($tour_id, ConstEnv::TYPE_STANDING_OVERAL, $limit);
        break;
    }

    $data = [
      'tournament' => $tourInfo,
      'standingHome' => $standingHome,
      'standingAway' => $standingAway,
      'standingOveral' => $standingOveral,
    ];
    $dataReturn = [
      'code' => 200,
      'status' => true,
      'data' => $data
    ];
    end:
    return $dataReturn;
  }
}