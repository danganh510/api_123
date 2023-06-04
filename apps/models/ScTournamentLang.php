<?php

namespace Score\Models;

class ScTournamentLang extends \Phalcon\Mvc\Model
{
    protected $tournament_id;
    protected $tournament_lang_code;
    protected $tournament_name;
    protected $tournament_slug;

    public function getTournamentId()
    {
        return $this->tournament_id;
    }

    public function setTournamentId($tournament_id)
    {
        $this->tournament_id = $tournament_id;
    }

    public function getTournamentLangCode()
    {
        return $this->tournament_lang_code;
    }

    public function setTournamentLangCode($tournament_lang_code)
    {
        $this->tournament_lang_code = $tournament_lang_code;
    }

    public function getTournamentName()
    {
        return $this->tournament_name;
    }

    public function setTournamentName($tournament_name)
    {
        $this->tournament_name = $tournament_name;
    }

    public function getTournamentSlug()
    {
        return $this->tournament_slug;
    }

    public function setTournamentSlug($tournament_slug)
    {
        $this->tournament_slug = $tournament_slug;
    }
     /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_tournament_lang';
    }
}
