<?php

namespace Score\Repositories;

use Score\Models\ForexcecConfig;
use Phalcon\Mvc\User\Component;
use Score\Models\ScTournament;
use Symfony\Component\DomCrawler\Crawler;

class Tournament extends Component
{
    public static function getNameById($id)
    {
        $model = self::findFirstById($id);
        return $model ? $model->getTournamentName() : "";
    }
    public static function findFirstById($id)
    {
        return ScTournament::findFirst([
            'tournament_id = :id:',
            'bind' => [
                'id' => $id
            ]
        ]);
    }
    public static function getCombobox($id)
    {
        $tours = ScTournament::find([
            'columns' => 'tournament_id,tournament_name,tournament_country'
        ]);
        $arrTour = [];
        foreach ($tours as $tour) {
            $arrTour[$tour->tournament_id] = $tour->tournament_name . "(" . $tour->tournament_country . ")";
        }
        return MyRepo::getComboBox($arrTour, $id);
    }
    public static function findByName($name, $country_code = "", $slug = "")
    {
        return ScTournament::findFirst([
            '(tournament_name = :name: OR tournament_slug = :slug: OR tournament_name_flash_score = :name:) AND tournament_country_code = :countryCode:',
            'bind' => [
                'name' => $name,
                'slug' => $slug,
                'countryCode' => $country_code
            ]
        ]);
    }
    public static function saveTournament($tournamentInfo, $type_crawl, $arrTournament = [], &$is_cache_tour = false)
    {
        $slug = MyRepo::create_slug($tournamentInfo->getTournamentName());
        if (isset($arrTournament[$tournamentInfo->getTournamentName()])) {
            $tournament = new ScTournament();
            $tournament->setData($arrTournament[$tournamentInfo->getTournamentName() . "_" . $tournamentInfo->getCountryCode()]);
        } else {
            $tournament = self::findByName($tournamentInfo->getTournamentName(), $tournamentInfo->getCountryCode(), $slug);
        }

        if (!$tournament) {
            $tournament = new ScTournament();
            $tournament->setTournamentName($tournamentInfo->getTournamentName());
            $tournament->setTournamentSlug($slug);
            $tournament->setTournamentImage("");

            switch ($type_crawl) {
                case MatchCrawl::TYPE_FLASH_SCORE:
                    $tournament->setTournamentNameFlashScore($tournamentInfo->getTournamentName());
                    $tournament->setTournamentHrefFlashscore($tournamentInfo->getTournamentHref());
                    break;
                case MatchCrawl::TYPE_SOFA:
                    $tournament->setTournamentNameFlashScore($tournamentInfo->getTournamentName());
                    $tournament->setTournamentHrefFlashscore($tournamentInfo->getTournamentHref());
                    break;
                case MatchCrawl::TYPE_LIVE_SCORES:
                    $tournament->setTournamentNameLivescore($tournamentInfo->getTournamentName());
                    $tournament->setTournamentHrefLivescore($tournamentInfo->getTournamentHref());
                    break;
            }
            $tournament->setTournamentActive("Y");
            $tournament->setTournamentOrder($tournamentInfo->getId());
            $tournament->save();
            $is_cache_tour = true;
        }
        if (!$tournament->getTournamentCountryCode()) {
            $tournament->setTournamentCountry($tournamentInfo->getCountryName());
            $tournament->setTournamentCountryCode($tournamentInfo->getCountryCode());
            $tournament->save();
            $is_cache_tour = true;
        }


        return $tournament;
    }
}
