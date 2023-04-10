<?php

namespace Score\Repositories;

use Exception;
use Score\Models\ScCountry;

class CrawlerFlashScoreTourV2 extends CrawlerFlashScoreBase
{
    public function __construct($seleniumDriver, $url_crawl, $day_time, $isLive)
    {
        $this->seleniumDriver = $seleniumDriver;
        $this->url_crawl = $url_crawl;
        $this->day_time = $day_time;
        $this->isLive = $isLive;
    }
    public function getHtmlParent()
    {
        $time = microtime(true);
        $this->setupSiteTour();
        //     var_dump(microtime(true) - $time);
        $htmlDiv = "";
        $htmlDivHomeStanding = "";
        $htmlDivAwayStanding = "";
        sleep(1);
        try {
            $this->seleniumDriver->clickButton("#onetrust-reject-all-handler");
        } catch (Exception $e) {
            echo "not found cookie";
        }
        try {
            $parentsDiv = $this->seleniumDriver->findElements('.sportName');
            foreach ($parentsDiv as $parentDiv) {
                $htmlDiv .= $parentDiv->getAttribute("outerHTML");
            }
            $divsStanding = $this->seleniumDriver->findElements('#tournament-table-tabs-and-content > .tabs > .tabs__group > a');
            foreach ($divsStanding as $divStanding) {
                if (strtolower(trim($divStanding->getText())) == "standings") {
                    $divStanding->click();
                }
            }
            $divsHome = $this->seleniumDriver->findElements('#tournament-table-tabs-and-content > .subTabs > .subTabs__group > a');
            foreach ($divsHome as $divHome) {
                if (strtolower(trim($divHome->getText())) == "home") {
                    $divHome->click();
                    sleep(2);
                    $htmlDivHomeStanding = $this->seleniumDriver->findElement('#tournament-table-tabs-and-content > div > div > .tableWrapper');
                    $htmlDivHomeStanding = $htmlDivHomeStanding->getAttribute("outerHTML");
                }
                if (strtolower(trim($divHome->getText())) == "away") {
                    $divHome->click();
                    sleep(2);
                    $htmlDivAwayStanding = $this->seleniumDriver->findElement('#tournament-table-tabs-and-content > div > div > .tableWrapper');
                    $htmlDivAwayStanding = $htmlDivAwayStanding->getAttribute("outerHTML");
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $this->seleniumDriver->quit();
        //    var_dump(microtime(true) - $time);

        return [
            'htmlDiv' => $htmlDiv,
            'htmlDivHomeStanding' => $htmlDivHomeStanding,
            'htmlDivAwayStanding' => $htmlDivAwayStanding,
        ];
    }
    public function crawlList()
    {
        require_once(__DIR__ . "/../../library/simple_html_dom.php");
        $parentDiv = $this->getHtmlParent();
        $list_live_match = [];
        $list_standing_home = [];
        $list_standing_away = [];

        $divHomeStanding =  str_get_html($parentDiv['htmlDivHomeStanding']);
        $divAwayStanding =  str_get_html($parentDiv['htmlDivAwayStanding']);
        //         file_put_contents("htmlDivHomeStanding.html",$parentDiv['htmlDivHomeStanding']);
        //         file_put_contents("htmlDivAwayStanding.html",$parentDiv['htmlDivAwayStanding']);
        // exit;
        $parentDiv =  str_get_html($parentDiv['htmlDiv']);

        if (!$parentDiv) {
            return [];
        }

        $parentDivs = $parentDiv->find("div");
        $round = "";

        foreach ($parentDivs as $key => $div) {
            //   goto test;
            try {
                //check tournament
                $divTuornaments = $div->find('.event__title--type');
                if (!empty($divTuornaments)) {
                    $this->list_live_tournaments[] = $this->getTournament($div);
                    continue;
                }
                $classRound = $div->getAttribute('class');
                if (strpos($classRound, "event__round") !== false) {
                    $round = $div->text();
                    continue;
                }
                //match
                $divMatch = $div->find(".event__participant");
                if (!empty($divMatch)) {
                    $list_live_match[] = $this->getMatch($div, $round);

                    // echo "time get match: " . (microtime(true) - $time_1) . "</br>";
                }
            } catch (Exception $e) {
                echo "1-";

                continue;
            }
            test:
            // $text = $div->getAttribute("outerHTML");
            // $this->saveText($text, $key);
        }

        //crawl bảng xếp hạng
        $divsHomeTeam = $divHomeStanding->find(".ui-table__row");
        foreach ($divsHomeTeam as $divHomeTeam) {
            $this->crawlStanding($divHomeTeam);
        }

        return $list_live_match;
    }
    public function crawlStanding($divHomeTeam)
    {
        $divRank = $divHomeTeam->find(".tableCellRank", 0);
        $rank = $divRank->text();

        $divName = $divHomeTeam->find(".tableCellParticipant__name", 0);
        $name = $divName->text();
        $infos = $divHomeTeam->find(".table__cell--value");
        foreach ($infos as $key =>  $info) {
            switch ($key) {
                case 0:
                    $totalMatch = $info->text();
                    break;
                case 1:
                    $totalWin = $info->text();
                    break;
                case 2:
                    $totalLose = $info->text();
                    break;
                case 3:
                    $totalDraw = $info->text();
                    break;
                case 4:
                    $totalGoal = $info->text();
                    break;
                case 5:
                    $totalPoint = $info->text();
                    break;
            }
        }
        $divsHistory = $divHomeTeam->find(".table__cell--form > div");
        $matchInfo = [];
        foreach ($divsHistory as $key => $divHistory) {
            $matchInfo[] = [
                'matchText' => $divHistory->getAttribute("title"),
              //  'results' => $divHistory->find("div",0)->text(),
            ];
        }

        var_dump($rank, $name, $totalMatch,$totalWin,$totalLose, $totalDraw, $totalGoal, $totalPoint, $matchInfo);
        exit;
    }
}
