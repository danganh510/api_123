<?php
namespace Score\Models;

class ScCronTour extends \Phalcon\Mvc\Model
{
    private $cron_id;
    private $cron_tour_id;
    private $cron_update_time;
    private $match_status;

    public function getCronId()
    {
        return $this->cron_id;
    }

    public function setCronId($cron_id)
    {
        $this->cron_id = $cron_id;
    }

    public function getCronTourId()
    {
        return $this->cron_tour_id;
    }

    public function setCronTourId($cron_tour_id)
    {
        $this->cron_tour_id = $cron_tour_id;
    }

    public function getCronUpdateTime()
    {
        return $this->cron_update_time;
    }

    public function setCronUpdateTime($cron_update_time)
    {
        $this->cron_update_time = $cron_update_time;
    }

    public function getMatchStatus()
    {
        return $this->match_status;
    }

    public function setMatchStatus($match_status)
    {
        $this->match_status = $match_status;
    }
    public function getSource()
    {
        return 'sc_cron_tour';
    }
    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScCronTour[]|ScCronTour
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScCronTour
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
