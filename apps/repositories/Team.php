<?php

namespace Score\Repositories;

use Score\Models\ForexcecConfig;
use Phalcon\Mvc\User\Component;
use Score\Models\ScTeam;
use Symfony\Component\DomCrawler\Crawler;
use Score\Repositories\CacheTeam;

class Team extends Component
{
    const FOLDER_IMAGE_SMALL = "/images/team/small";
    const FOLDER_IMAGE_MEDIUM = "/images/team/medium";
    public static function findByName($name, $name_slug, $country_code = "")
    {
        if ($country_code) {
            return ScTeam::findFirst([
                '(team_name_flashscore = :name: OR team_name = :name: OR team_slug= :slug: OR team_name_livescore = :name:) AND team_country_code = :countryCode:',
                'bind' => [
                    'countryCode' => $country_code,
                    'name' => $name,
                    'slug' => $name_slug
                ]
            ]);
        } else {
            return ScTeam::findFirst([
                'team_name_flashscore = :name: OR team_name = :name: OR team_slug= :slug: OR team_name_livescore = :name: AND team_is_public = "Y"',
                'bind' => [
                    'name' => $name,
                    'slug' => $name_slug
                ]
            ]);
        }
    }
    public static function findByNameArray($name, $name_slug, $arrTeam, $country_code = "")
    {
        $team = array_search($name_slug, array_column($arrTeam, 'team_slug'));
        if (!$team) {
            $team = array_search($name, array_column($arrTeam, 'team_name'));
        }
        if (empty($arrTeam[$team])) {
            return false;
        }
        if ($country_code && $arrTeam[$team]['team_country_code'] &&  $country_code != $arrTeam[$team]['team_country_code']) {
            return false;
        }
        $teamModel = new ScTeam();
        $teamModel->setData($arrTeam[$team]);
        return $teamModel;
    }
    public static function saveTeam($team_name, $image, $country_code, $arrTeam, $type , &$is_cache_team = false)
    {
        if (isset($arrTeam[$team_name])) {
            $team = new ScTeam();
            $team->setData($arrTeam[$team_name]);
        } else {
            $team = Team::findByName($team_name, MyRepo::create_slug($team_name), $country_code);
        }
        if (!$team) {
            $team = new ScTeam();
            $team->setTeamName($team_name);
            $team->setTeamLogo($image);
            $team->setTeamSlug(MyRepo::create_slug($team_name));
            switch ($type) {
                case MatchCrawl::TYPE_FLASH_SCORE:
                    $team->setTeamNameFlashscore($team_name);
                    break;
                case MatchCrawl::TYPE_SOFA:
                case MatchCrawl::TYPE_API_SOFA:
                    $team->setTeamNameSofa($team_name);
                    break;
                case MatchCrawl::TYPE_LIVE_SCORES:
                    $team->setTeamNameLivescore($team_name);
                    break;
            }
            $team->setTeamActive("Y");
            $team->setTeamIsPublic("N");
            $team->save();
            $is_cache_team = true;
        }
        if (!$team->getTeamCountryCode()) {
            $team->setTeamCountryCode($country_code);
            $team->save();
            $is_cache_team = true;
        }

        return $team;
    }
    public static function getCombobox($id)
    {
        $tours = ScTeam::find([
            'columns' => 'team_id,team_name,team_country_code'
        ]);
        $arrTour = [];
        foreach ($tours as $tour) {
            $arrTour[$tour->team_id] = $tour->team_name .  ($tour->team_country_code ? "(" . $tour->team_country_code . ")" : "");
        }
        return MyRepo::getComboBox($arrTour, $id);
    }
    public static function getTeamNameById($team_id)
    {
        $team = self::getTeamById($team_id);
        return $team ? $team->getTeamName() : "";
    }
    public static function getTeamById($team_id)
    {
        $team = ScTeam::findFirst([
            'team_id = :id:',
            'bind' => [
                'id' => $team_id
            ]
        ]);
        return $team;
    }
    public function getTeamByLang($language) {
        if ($language == "vi") {
            return ScTeam::find("team_active = 'Y'")->toArray();
        } else {
            return $this->->modelsManager->createBuilder()
            ->columns("t.team_id, t.team_country_code, t.team_name_flashscore, t.team_logo, t.team_logo_crawl, t.team_logo_small, t.team_logo_medium, t.team_active, t.team_is_public,
            tl.team_name,tl.team_slug")
            ->from("Score\Models\ScTeam", "t")
            ->innerJoin("Score\Models\ScTeamLang", "t.team_id = tl.team_id", "tl")
            ->where("t.team_active = 'Y'")
            ->excute()
            ->toArray();
        }
    }
}
