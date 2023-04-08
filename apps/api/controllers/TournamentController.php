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
        $a = '{
        "658": {
          "tournament": {
            "name": "LaLiga",
            "slug": "laliga",
            "category": {
              "name": "Spain",
              "slug": "Spain",
              "sport": {
                "name": "football",
                "slug": "football"
              },
              "flag": "Spain",
              "countryCode": "es"
            }
          },
          "match_old": {
            "21055": {
              "status": {
                "description": "W",
                "type": "W"
              },
              "matchInfo": {
                "id": "21055",
                "time_start": "1680955200",
                "time": "0",
                "htScore": null,
                "slugName": "osasuna-vs-elche"
              },
              "homeTeam": {
                "id": "6401",
                "name": "Osasuna",
                "slug": "osasuna",
                "logo": "\/images\/team\/small\/6401",
                "score": {
                  "score": "0",
                  "redCard": "0",
                  "time": [
                    "0"
                  ]
                }
              },
              "awayTeam": {
                "id": "6404",
                "name": "Elche",
                "slug": "elche",
                "logo": "\/images\/team\/small\/6404",
                "score": {
                  "score": "0",
                  "redCard": "0",
                  "time": [
                    "0"
                  ]
                }
              },
              "roundInfo": ""
            },
            "21056": {
              "status": {
                "description": "W",
                "type": "W"
              },
              "matchInfo": {
                "id": "21056",
                "time_start": "1680963300",
                "time": "0",
                "htScore": null,
                "slugName": "espanyol-vs-ath-bilbao"
              },
              "homeTeam": {
                "id": "6409",
                "name": "Espanyol",
                "slug": "espanyol",
                "logo": "\/images\/team\/small\/6409",
                "score": {
                  "score": "0",
                  "redCard": "0",
                  "time": [
                    "0"
                  ]
                }
              },
              "awayTeam": {
                "id": "6416",
                "name": "Ath Bilbao",
                "slug": "ath-bilbao",
                "logo": "\/images\/team\/small\/6416",
                "score": {
                  "score": "0",
                  "redCard": "0",
                  "time": [
                    "0"
                  ]
                }
              },
              "roundInfo": ""
            },
            "21057": {
              "status": {
                "description": "W",
                "type": "W"
              },
              "matchInfo": {
                "id": "21057",
                "time_start": "1680971400",
                "time": "0",
                "htScore": null,
                "slugName": "real-sociedad-vs-getafe"
              },
              "homeTeam": {
                "id": "6403",
                "name": "Real Sociedad",
                "slug": "real-sociedad",
                "logo": "\/images\/team\/small\/6403",
                "score": {
                  "score": "0",
                  "redCard": "0",
                  "time": [
                    "0"
                  ]
                }
              },
              "awayTeam": {
                "id": "6399",
                "name": "Getafe",
                "slug": "getafe",
                "logo": "\/images\/team\/small\/6399",
                "score": {
                  "score": "0",
                  "redCard": "0",
                  "time": [
                    "0"
                  ]
                }
              },
              "roundInfo": ""
            },
            "19331": {
              "status": {
                "description": "F",
                "type": "F"
              },
              "matchInfo": {
                "id": "19331",
                "time_start": "1680894000",
                "time": "FT",
                "htScore": "1-0",
                "slugName": "sevilla-vs-celta-vigo"
              },
              "homeTeam": {
                "id": "6400",
                "name": "Sevilla",
                "slug": "sevilla",
                "logo": "\/images\/team\/small\/6400",
                "score": {
                  "score": "2",
                  "redCard": "2",
                  "time": [
                    "2"
                  ]
                }
              },
              "awayTeam": {
                "id": "6410",
                "name": "Celta Vigo",
                "slug": "celta-vigo",
                "logo": "\/images\/team\/small\/6410",
                "score": {
                  "score": "2",
                  "redCard": "0",
                  "time": [
                    "2"
                  ]
                }
              },
              "roundInfo": ""
            }
          },
          "order": "1001"
        }
      }
      ';
        $data = json_decode($a, true);
        $dataReturn = [
            'code' => 200,
            'status' => true,
            'data' => $data
        ];
        return $dataReturn;
    }
    public function getstandingstourAction()
    {
        $tour_id = $this->request->get("id");
    }
}
