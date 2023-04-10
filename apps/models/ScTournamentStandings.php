<?php

namespace Score\Models;

class ScTournamentStandings extends extends \Phalcon\Mvc\Model
{
    private $standing_tournament_id;
    private $standing_team_id;
    private $standing_enemy;
    private $standing_round;
    private $standing_goal;
    private $standing_point;
    private $standing_total_win;
    private $standing_total_lose;
    private $standing_total_draw;
    private $standing_is_home;
    private $standing_update_time;

    public function getStandingTournamentId()
    {
        return $this->standing_tournament_id;
    }

    public function setStandingTournamentId($standing_tournament_id)
    {
        $this->standing_tournament_id = $standing_tournament_id;
    }

    public function getStandingTeamId()
    {
        return $this->standing_team_id;
    }

    public function setStandingTeamId($standing_team_id)
    {
        $this->standing_team_id = $standing_team_id;
    }

    public function getStandingEnemy()
    {
        return $this->standing_enemy;
    }

    public function setStandingEnemy($standing_enemy)
    {
        $this->standing_enemy = $standing_enemy;
    }

    public function getStandingRound()
    {
        return $this->standing_round;
    }

    public function setStandingRound($standing_round)
    {
        $this->standing_round = $standing_round;
    }

    public function getStandingGoal()
    {
        return $this->standing_goal;
    }

    public function setStandingGoal($standing_goal)
    {
        $this->standing_goal = $standing_goal;
    }

    public function getStandingPoint()
    {
        return $this->standing_point;
    }

    public function setStandingPoint($standing_point)
    {
        $this->standing_point = $standing_point;
    }

    public function getStandingTotalWin()
    {
        return $this->standing_total_win;
    }

    public function setStandingTotalWin($standing_total_win)
    {
        $this->standing_total_win = $standing_total_win;
    }

    public function getStandingTotalLose()
    {
        return $this->standing_total_lose;
    }

    public function setStandingTotalLose($standing_total_lose)
    {
        $this->standing_total_lose = $standing_total_lose;
    }

    public function getStandingTotalDraw()
    {
        return $this->standing_total_draw;
    }

    public function setStandingTotalDraw($standing_total_draw)
    {
        $this->standing_total_draw = $standing_total_draw;
    }

    public function getStandingIsHome()
    {
        return $this->standing_is_home;
    }

    public function setStandingIsHome($standing_is_home)
    {
        $this->standing_is_home = $standing_is_home;
    }
    public function getStandingUpdateTime()
    {
        return $this->standing_update_time;
    }

    public function setStandingUpdateTime($standing_update_time)
    {
        $this->standing_update_time = $standing_update_time;
    }
    public function getSource()
    {
        return 'sc_tournament_standings';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScTournamentStandings[]|ScTournamentStandings
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScTournamentStandings
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}