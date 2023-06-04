<?php

namespace Score\Models;

class ScTeamLang extends \Phalcon\Mvc\Model
{
    protected $team_id;
    protected $team_lang_code;
    protected $team_name;
    protected $team_slug;

    public function getTeamId()
    {
        return $this->team_id;
    }

    public function setTeamId($team_id)
    {
        $this->team_id = $team_id;
    }

    public function getTeamLangCode()
    {
        return $this->team_lang_code;
    }

    public function setTeamLangCode($team_lang_code)
    {
        $this->team_lang_code = $team_lang_code;
    }

    public function getTeamName()
    {
        return $this->team_name;
    }

    public function setTeamName($team_name)
    {
        $this->team_name = $team_name;
    }

    public function getTeamSlug()
    {
        return $this->team_slug;
    }

    public function setTeamSlug($team_slug)
    {
        $this->team_slug = $team_slug;
    }
    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_team_lang';
    }
}