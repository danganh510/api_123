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
    $tourRepo = new Tournament();
    $arrTour = $tourRepo->getTourIsShowByLang($this->requestParams['language']);
    $list_data = [];
    foreach ($arrTour as $tour) {
      var_dump($tour);exit;
      
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
    $tourRepo = new Tournament();
    $arrTourIsShow = $tourRepo->getTourIsShowByLang($this->requestParams['language']);
    $str_tour = array_column($arrTourIsShow, "tournament_id");
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
    $limit = $this->request->get("limit");

    $tourInfo = $this->getTourInfor($tour_id);
    if (!$tourInfo) {
      $dataReturn = [
        'code' => 200,
        'status' => false,
        'messages' => "not found tournament"
      ];
      goto end;
    }

    $cacheTeam = new CacheTeam();
    $arrTeam = $cacheTeam->get(ConstEnv::CACHE_TYPE_ID);

    $matchOld = MatchRepo::getMatchOldByTourId($tour_id, $limit);
    $matchToday = MatchRepo::getMatchTodayByTourId($tour_id, $limit);
    $matchSchedule = MatchRepo::getMatchScheduleByTourId($tour_id, $limit);

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

    $tourInfo = $this->getTourInfor($tour_id);
    if (!$tourInfo) {
      $dataReturn = [
        'code' => 200,
        'status' => false,
        'messages' => "not found tournament"
      ];
      goto end;
    }

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
  public function getTourInfor($tour_id)
  {
    $tourRepo = new Tournament();
    $tourModel = $tourRepo->getTourByIdAndLang($tour_id, $this->requestParams['language']);
    if (!$tourModel) {
      return false;
    }

    $tourInfo = [
      'name' => $tourModel['tournament_name'],
      'slug' => $tourModel['tournament_slug'],
      'category' => [
        'name' => $tourModel['tournament_country_code'],
        'slug' => MyRepo::create_slug($tourModel['tournament_country']),
        'sport' => [
          "name" => "football",
          "slug" => "football"
        ],
        'flag' => $tourModel['tournament_country'],
        "countryCode" => $tourModel['tournament_country_code']
      ]
    ];
    return $tourInfo;
  }
}
