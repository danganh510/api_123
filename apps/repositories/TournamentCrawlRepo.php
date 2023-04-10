<?php

namespace Score\Repositories;

use Score\Models\ForexcecConfig;
use Phalcon\Mvc\User\Component;
use Score\Models\ScTournament;
use Symfony\Component\DomCrawler\Crawler;

class TournamentCrawlRepo extends Component
{
    public function getTournamentNomal($strTour)
    {
        $tour = ScTournament::findFirst("tournament_is_crawling = 'Y' AND tournament_crawl = 'N' AND (FIND_IN_SET(tournament_id,'{$strTour}')) ");
        if (!$tour) {
            $sql = "UPDATE Score\Models\ScTournament SET tournament_is_crawling = 'Y' WHERE (FIND_IN_SET(tournament_id,'{$strTour}')) AND tournament_crawl = 'N'";
            $this->modelsManager->executeQuery($sql);
            $tour = ScTournament::findFirst("tournament_is_crawling = 'Y' AND tournament_crawl = 'N' AND FIND_IN_SET(tournament_id,'{$strTour}')");
        }
        return $tour;
    }
    public function getTournament($strTour)
    {
        $tour = ScTournament::findFirst("tournament_is_crawling = 'Y' AND tournament_crawl = 'Y' AND FIND_IN_SET(tournament_id,'{$strTour}')");

        if (!$tour) {
            $sql = "UPDATE Score\Models\ScTournament SET tournament_is_crawling = 'Y' WHERE FIND_IN_SET(tournament_id,'{$strTour}') AND tournament_crawl = 'Y'";
            $this->modelsManager->executeQuery($sql);
            echo "All reset \r\n";
            $tour = ScTournament::findFirst("tournament_is_crawling = 'Y' AND FIND_IN_SET(tournament_id,'{$strTour}') AND tournament_crawl = 'Y'");

            if (!$tour) {
                $tour = ScTournament::findFirst("tournament_is_crawling = 'Y' AND FIND_IN_SET(tournament_id,'{$strTour}') ");
                if ($tour) {
                    $sql = "UPDATE Score\Models\ScTournament SET tournament_is_crawling = 'Y' WHERE FIND_IN_SET(tournament_id,'{$strTour}')";
                    $this->modelsManager->executeQuery($sql);
                    echo "All reset \r\n";
                    $tour = ScTournament::findFirst("tournament_is_crawling = 'Y' AND FIND_IN_SET(tournament_id,'{$strTour}') ");
                }
            }
        }
        return $tour;
    }
    public function getTournamentToshow($strTour)
    {
        $tour = ScTournament::findFirst("tournament_is_show = 'Y'  AND NOT FIND_IN_SET(tournament_id,'{$strTour}')");
        return $tour;
    }
}
