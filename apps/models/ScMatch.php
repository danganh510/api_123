<?php

namespace Score\Models;

class ScMatch extends \Phalcon\Mvc\Model
{
    protected $match_id;
    protected $match_tournament_id;
    protected $match_season;
    protected $match_name;
    protected $match_status;
    protected $match_crawl_detail;
    protected $match_crawl_detail_live;
    protected $match_home_id;
    protected $match_away_id;
    protected $match_home_score;
    protected $match_away_score;

    protected $match_score_ht;
    protected $match_score_ft;
    protected $match_time_ht;
    protected $match_time_ft;
    protected $match_time_finish;

    protected $match_home_card_red;
    protected $match_away_card_red;
    protected $match_round;
    protected $match_insert_time;
    protected $match_time;
    protected $match_start_time;
    protected $match_start_day;
    protected $match_start_month;
    protected $match_start_year;
    protected $match_order;
    protected $match_link_detail_flashscore;
    protected $match_link_detail_sofa;
    protected $match_link_detail_livescore;

    /**
     * @return mixed
     */
    public function getMatchId()
    {
        return $this->match_id;
    }

    /**
     * @param mixed $match_id
     */
    public function setMatchId($match_id)
    {
        $this->match_id = $match_id;
    }
    /**
     * @param mixed
     */
    public function getMatchSeaSon()
    {
        return $this->match_id;
    }
    /**
     * @param mixed $match_id
     */
    public function setMatchSeaSon($match_id)
    {
        $this->match_id = $match_id;
    }
    /**
     * @return mixed
     */
    public function getMatchTournamentId()
    {
        return $this->match_tournament_id;
    }

    /**
     * @param mixed $match_tournament_id
     */
    public function setMatchTournamentId($match_tournament_id)
    {
        $this->match_tournament_id = $match_tournament_id;
    }
    /**
     * @return mixed
     */
    public function getMatchName()
    {
        return $this->match_name;
    }

    /**
     * @param mixed $match_name
     */
    public function setMatchName($match_name)
    {
        $this->match_name = $match_name;
    }

    /**
     * @return mixed
     */
    public function getMatchStatus()
    {
        return $this->match_status;
    }

    /**
     * @param mixed $match_status
     */
    public function setMatchStatus($match_status)
    {
        $this->match_status = $match_status;
    }

    /**
     * @return mixed
     */
    public function getMatchCrawlDetail()
    {
        return $this->match_crawl_detail;
    }

    /**
     * @param mixed $match_crawl_detail
     */
    public function setMatchCrawlDetail($match_crawl_detail)
    {
        $this->match_crawl_detail = $match_crawl_detail;
    }
    /**
     * @return mixed
     */
    public function getMatchCrawlDetailLive()
    {
        return $this->match_crawl_detail_live;
    }

    /**
     * @param mixed $match_crawl_detail_live
     */
    public function setMatchCrawlDetailLive($match_crawl_detail_live)
    {
        $this->match_crawl_detail_live = $match_crawl_detail_live;
    }
    /**
     * @return mixed
     */
    public function getMatchHomeId()
    {
        return $this->match_home_id;
    }

    /**
     * @param mixed $match_home_id
     */
    public function setMatchHomeId($match_home_id)
    {
        $this->match_home_id = $match_home_id;
    }

    /**
     * @return mixed
     */
    public function getMatchAwayId()
    {
        return $this->match_away_id;
    }

    /**
     * @param mixed $match_away_id
     */
    public function setMatchAwayId($match_away_id)
    {
        $this->match_away_id = $match_away_id;
    }

    /**
     * @return mixed
     */
    public function getMatchHomeScore()
    {
        return $this->match_home_score;
    }
    /**
     * @return mixed
     */
    public function getMatchHomeHomeCardRed()
    {
        return $this->match_home_card_red;
    }


    /**
     * @param mixed $match_home_score
     */
    public function setMatchHomeScore($match_home_score)
    {
        $this->match_home_score = $match_home_score;
    }
    /**
     * @param mixed $match_home_card_red
     */
    public function setMatchHomeCardRed($match_home_card_red)
    {
        $this->match_home_card_red = $match_home_card_red;
    }
    /**
     * @return mixed
     */
    public function getMatchAwayScore()
    {
        return $this->match_away_score;
    }

    /**
     * @param mixed $match_away_score
     */
    public function setMatchAwayScore($match_away_score)
    {
        $this->match_away_score = $match_away_score;
    }
    /**
     * @return mixed
     */
    public function getMatchAwayCardRed()
    {
        return $this->match_away_card_red;
    }

    /**
     * @param mixed $match_away_card_red
     */
    public function setMatchAwayCardRed($match_away_card_red)
    {
        $this->match_away_card_red = $match_away_card_red;
    }
    public function getMatchScoreHt()
    {
        return $this->match_score_ht;
    }
    public function getMatchScoreFt()
    {
        return $this->match_score_ft;
    }
    public function getMatchTimeHt()
    {
        return $this->match_time_ht;
    }
    public function getMatchTimeFt()
    {
        return $this->match_time_ft;
    }
    public function getMatchTimeFinish()
    {
        return $this->match_time_finish;
    }
    /**
     * @return mixed
     */
    public function getMatchRound()
    {
        return $this->match_round;
    }

    /**
     * @param mixed $match_round
     */
    public function setMatchRound($match_round)
    {
        $this->match_round = $match_round;
    }

    /**
     * @return mixed
     */
    public function getMatchInsertTime()
    {
        return $this->match_insert_time;
    }

    /**
     * @param mixed $match_insert_time
     */
    public function setMatchInsertTime($match_insert_time)
    {
        $this->match_insert_time = $match_insert_time;
    }

    /**
     * @return mixed
     */
    public function getMatchTime()
    {
        return $this->match_time;
    }

    /**
     * @param mixed $match_time
     */
    public function setMatchTime($match_time)
    {
        $this->match_time = $match_time;
    }

    /**
     * @return mixed
     */
    public function getMatchStartTime()
    {
        return $this->match_start_time;
    }

    /**
     * @param mixed $match_start_time
     */
    public function setMatchStartTime($match_start_time)
    {
        //làm tròn giờ
        $this->match_start_time = is_numeric($match_start_time) ?  ceil($match_start_time / 100) * 100 : $match_start_time;
    }
    /**
     * @return mixed
     */
    public function getMatchStartDay()
    {
        return $this->match_start_day;
    }

    /**
     * @param mixed $match_start_day
     */
    public function setMatchStartDay($match_start_day)
    {
        $this->match_start_day = $match_start_day;
    }
    /**
     * @return mixed
     */
    public function getMatchStartMonth()
    {
        return $this->match_start_month;
    }

    /**
     * @param mixed $match_start_month
     */
    public function setMatchStartMonth($match_start_month)
    {
        $this->match_start_month = $match_start_month;
    }
    /**
     * @return mixed
     */
    public function getMatchStartYear()
    {
        return $this->match_start_year;
    }

    /**
     * @param mixed $match_start_year
     */
    public function setMatchStartYear($match_start_year)
    {
        $this->match_start_year = $match_start_year;
    }
    public function setMatchScoreHt($match_score_ht)
    {
        $this->match_score_ht = $match_score_ht;
        return $this->match_score_ht;
    }
    public function setMatchScoreFt($match_score_ft)
    {
        $this->match_score_ft = $match_score_ft;
        return $this->match_score_ft;
    }
    public function setMatchTimeHt($match_time_ht)
    {
        $this->match_time_ht = $match_time_ht;
        return $this->match_time_ht;
    }
    public function setMatchTimeFt($match_time_ft)
    {
        $this->match_time_ft = $match_time_ft;
        return $this->match_time_ft;
    }
    public function setMatchTimeFinish($match_time_finish)
    {
        $this->match_time_finish = $match_time_finish;
        return $this->match_time_finish;
    }
    /**
     * @return mixed
     */
    public function getMatchOrder()
    {
        return $this->match_order;
    }


    /**
     * @param mixed $match_order
     */
    public function setMatchOrder($match_order)
    {
        $this->match_order = $match_order;
    }
    /**
     * @return mixed
     */
    public function getMatchLinkDetailFlashscore()
    {
        return $this->match_link_detail_flashscore;
    }


    /**
     * @param mixed $match_link_detail_flashscore
     */
    public function setMatchLinkDetailFlashscore($match_link_detail_flashscore)
    {
        $this->match_link_detail_flashscore = $match_link_detail_flashscore;
    }
    /**
     * @return mixed
     */
    public function getMatchLinkDetailSofa()
    {
        return $this->match_link_detail_sofa;
    }


    /**
     * @param mixed $match_link_detail_sofa
     */
    public function setMatchLinkDetailSofa($match_link_detail_sofa)
    {
        $this->match_link_detail_sofa = $match_link_detail_sofa;
    }
    /**
     * @return mixed
     */
    public function getMatchLinkDetailLivescore()
    {
        return $this->match_link_detail_livescore;
    }


    /**
     * @param mixed $match_link_detail_livescore
     */
    public function setMatchLinkDetailLivescore($match_link_detail_livescore)
    {
        $this->match_link_detail_livescore = $match_link_detail_livescore;
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScMatch[]|ScMatch
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScMatch
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
