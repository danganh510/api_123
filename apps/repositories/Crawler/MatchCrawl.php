<?php

namespace Score\Repositories;

use Phalcon\Mvc\User\Component;

class MatchCrawl extends Component
{
    const TYPE_FLASH_SCORE = "flashScore";
    const TYPE_SOFA = "sofa";
    const TYPE_API_SOFA = "apisofa";
    const TYPE_LIVE_SCORES = "liveScores";

    const TYPE_default = "default";


    public $time;
    public $start_time;

    public $home;
    public $home_score;
    public $home_card_red;
    public $home_img;
    public $away;
    public $away_score;
    public $away_card_red;

    public $away_img;
    public $href_detail;
    public $country_code;
    public $tournament;
    public $round; //round or group
    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
    public function getTime()
    {
        return $this->time;
    }
    public function getStartTime()
    {
        return $this->start_time;
    }
    public function getHome()
    {
        return $this->home;
    }
    public function getHomeScore()
    {
        return $this->home_score;
    }
    public function getHomeCardRed()
    {
        return $this->home_card_red;
    }
    public function getHomeImg()
    {
        return $this->home_img;
    }
    public function getAway()
    {
        return $this->away;
    }
    public function getAwayScore()
    {
        return $this->away_score;
    }
    public function getAwayCardRed()
    {
        return $this->away_card_red;
    }
    public function getAwayImg()
    {
        return $this->away_img;
    }
    public function getHrefDetail()
    {
        return $this->href_detail;
    }
    public function getTournament()
    {
        return $this->tournament;
    }
    public function setTime($time)
    {
        $this->time = $time;
        return $this->time;
    }
    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        return $this->start_time;
    }
    public function setHome($home)
    {
        $this->home = $home;
        return $this->home;
    }
    public function setHomeScore($home_score)
    {
        $this->home_score = $home_score;
        return $this->home_score;
    }
    public function setHomeCardRed($home_card_red)
    {
        $this->home_card_red = $home_card_red;
        return $this->home_card_red;
    }
    public function setHomeImg($home_img)
    {
        $this->home_img = $home_img;
        return $this->home_img;
    }
    public function setAway($away)
    {
        $this->away = $away;
        return $this->away;
    }
    public function setAwayScore($away_score)
    {
        $this->away_score = $away_score;
        return $this->away_score;
    }
    public function setAwayCardRed($away_card_red)
    {
        $this->away_card_red = $away_card_red;
        return $this->away_card_red;
    }
    public function setAwayImg($away_img)
    {
        $this->away_img = $away_img;
        return $this->away_img;
    }
    public function setHrefDetail($href_detail)
    {
        $this->href_detail = $href_detail;
        return $this->href_detail;
    }
    public function setTournament($tournament)
    {
        $this->tournament = $tournament;
        return $this->tournament;
    }
    public function getRound()
    {
        return $this->round;
    }
    public function setRound($round)
    {
        $this->round = $round;
        return $this->round;
    }
    public function getCountryCode()
    {
        return $this->country_code;
    }
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this->country_code;
    }
}
