<?php

namespace Score\Api\Controllers;

use ConstEnv;
use Score\Models\ScArea;
use Score\Models\ScCountry;
use Score\Models\ScMatch;
use Score\Models\ScTeam;
use Score\Repositories\CacheMatch;
use Score\Repositories\CacheMatchLive;
use Score\Repositories\CacheTeam;
use Score\Repositories\CacheTour;
use Score\Repositories\MatchDetailLocale;
use Score\Repositories\MatchRepo;
use Score\Repositories\Page;
use Score\Repositories\Team;

class MatchController extends ControllerBase
{
    public function listAction()
    {

        $time_zone = 7;
        //get các trận cần lấy theo thời gian


        $time_request = isset($this->requestParams['time']) ? $this->requestParams['time'] : "";
        $status = isset($this->requestParams['status']) ? $this->requestParams['status'] : "";
        //live
        $isLive = false;
        if (!$time_request || $time_request == "live") {
            $isLive = true;
            $time = time();
            $cacheMatch = new CacheMatchLive();
        } else {
            $time = strtotime($time_request);
            $cacheMatch = new CacheMatch();
        }
        $events = [];

        $cacheTeam = new CacheTeam($this->requestParams['language']);
        $arrTeam = $cacheTeam->get(ConstEnv::CACHE_TYPE_ID);

        $cacheTour = new CacheTour($this->requestParams['language']);
        $arrTournament = $cacheTour->get(ConstEnv::CACHE_TYPE_ID);
        // $matchRepo = new MatchRepo();
        // $arrMatch = $matchRepo->getMatch($time, $status);
        if ($this->my->getDays($time, time() + $time_zone * 60 * 60) == 0 && !$isLive) {
            $arrMatch = MatchRepo::getMatchToday($time_request);
            $arrMatch = $arrMatch->toArray();
        } else {
            $arrMatch = $cacheMatch->getCache();
        }
        if (!$arrMatch) {
            goto end;
        }
      
        
        foreach ($arrMatch as $key => $match) {
            if (!is_array($match)) {
                $match = (array) $match;
            }
            if (empty($arrTeam[$match['match_home_id']]) || empty($arrTeam[$match['match_away_id']])) {
                continue;
            }
            if (empty($arrTournament[$match['match_tournament_id']])) {
                continue;
            }
            if (!$isLive) {
                if ($this->my->getDays($time, $match['match_start_time'] + $time_zone * 60 * 60) != 0) {
                    continue;
                }
            }
            if ($status) {
                if ($status != $match['match_status']) {
                    continue;
                }
            }

            //con check điều kiện

            $homeModel = new ScTeam();
            $home = $homeModel->setData($arrTeam[$match['match_home_id']]);

            $awayModel = new ScTeam();
            $away = $awayModel->setData($arrTeam[$match['match_away_id']]);

            $matchInfo = [
                'status' => [
                    'description' => $match['match_status'],
                    'type' => $match['match_status']
                ],
                'matchInfo' => [
                    'id' => $match['match_id'],
                    'time_start' => $match['match_start_time'],
                    'time' => $match['match_time'],
                    'htScore' => $match['match_score_ht'],
                    'slugName' => $match['match_name'],
                ],
                'homeTeam' => [
                    'id' => $home->getTeamId(),
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
            if (isset($events[$match['match_tournament_id']])) {
                $events[$match['match_tournament_id']]['match'][$match['match_id']] = $matchInfo;
            } else {
                $events[$match['match_tournament_id']] = [
                    'tournament' => [
                        'name' => $arrTournament[$match['match_tournament_id']]['tournament_name'],
                        'slug' => $this->create_slug($arrTournament[$match['match_tournament_id']]['tournament_name']),
                        'category' => [
                            'name' => $arrTournament[$match['match_tournament_id']]['tournament_country'],
                            'slug' => $arrTournament[$match['match_tournament_id']]['tournament_country'],
                            'sport' => [
                                'name' => "football",
                                'slug' => "football"
                            ],
                            'flag' => $arrTournament[$match['match_tournament_id']]['tournament_country'],
                            'countryCode' => $arrTournament[$match['match_tournament_id']]['tournament_country_code'],
                        ]
                    ],
                    'match' => [
                        $match['match_id'] => $matchInfo
                    ],
                    'order' => $arrTournament[$match['match_tournament_id']]['tournament_order'],
                ];
            }
        }
        end:
        uasort($events, function ($a, $b) {
            return $b['order'] - $a['order'];
        });

        return $events;
        //get match and tournament

    }
    public function detailAction()
    {

        $id = $this->request->get('id');
        $sql = ScMatch::query()
            ->innerJoin('Score\Models\ScTournament', 'match_tournament_id = t.tournament_id', 't')
            ->leftJoin('Score\Models\ScMatchInfo', 'match_id  = i.info_match_id', 'i');
        if ($this->requestParams['language'] == "vi") {
            $sql = $sql->columns("match_tournament_id,match_name,match_home_id,match_away_id,match_home_score,match_away_score,match_id,match_start_time,match_time,match_status,match_score_ht,
                i.info_summary,i.info_time,i.info_stats,t.tournament_name,t.tournament_country_code");
        } else {
            $sql = $sql->innerJoin('Score\Models\ScTournamentLang', 'tl.tournament_id = t.tournament_id', 'tl');
            $sql = $sql->columns("match_tournament_id,match_name,match_home_id,match_away_id,match_home_score,match_away_score,match_id,match_start_time,match_time,match_status,match_score_ht,
            i.info_summary,i.info_time,i.info_stats,tl.tournament_name,t.tournament_country_code");
        }

        $sql =  $sql->where("match_id = :id:", [
            'id' => $id
        ]);
        $matchInfo = $sql->execute();
        if (empty($matchInfo->toArray())) {
            return [
                'stautus' => false,
                'messages' > "Not found match ID: " . $id
            ];
        }
        $matchInfo = $matchInfo->toArray()[0];
        $teamRepo = new Team();
        $home = $teamRepo->getTeamByIdAndLang($matchInfo['match_home_id'],$this->requestParams['language']);
        $away = $teamRepo->getTeamByIdAndLang($matchInfo['match_away_id'],$this->requestParams['language']);


        
        $match_start = MatchDetailLocale::changeKeyToContentStart(json_decode($matchInfo['info_stats'],true),$this->requestParams['language']);
        $info = [
            'id' => $matchInfo['match_id'],
            'name' => $matchInfo['match_name'],
            'startTime' => $matchInfo['match_start_time'],
            'time' => $matchInfo['match_time'],
            'status' => $matchInfo['match_status'],
            'tournament' => $matchInfo['tournament_name'],
            'tournamentCountryCode' => $matchInfo['tournament_country_code'],
            'home' => $home['team_name'],
            'homeLogo' => $home['team_logo_medium'],
            'away' => $away['team_name'],
            'awayLogo' => $away['team_logo_medium'],
            'homeSlug' => $home['team_slug'],
            'awaySlug' => $away['team_slug'],
            'homeScore' => $matchInfo['match_home_score'],
            'awayScore' => $matchInfo['match_away_score'],
            'htScore' => $matchInfo['match_score_ht'],
            'summary' => $matchInfo['info_summary'],
            'timeLine' => $matchInfo['info_time'],
            'stats' => json_encode($match_start,true),
        ];
        return $info;
    }
}
