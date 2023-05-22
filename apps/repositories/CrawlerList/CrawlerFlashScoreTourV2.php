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
        $htmlDivOveralStanding = "";
        try {
            $this->seleniumDriver->clickButton("#onetrust-accept-btn-handler");
            echo "clicked";
        } catch (Exception $e) {
            echo "not found cookie";
        }

        try {
            $parentsDiv = $this->seleniumDriver->findElements('.sportName');
            foreach ($parentsDiv as $parentDiv) {
                $htmlDiv .= $parentDiv->getAttribute("outerHTML");
            }
            $divsStanding = $this->seleniumDriver->findElements('#tournament-table-tabs-and-content > .tabs > .tabs__group > a');
            foreach ($divsStanding as $key => $divStanding) {
                if (strtolower(trim($divStanding->getText())) == "standings") {
                    if ($key == 0) {
                        break;
                    }
                    $divStanding->click();
                }
            }

            $divsHome = $this->seleniumDriver->findElements('#tournament-table-tabs-and-content > .subTabs > .subTabs__group > a');
            foreach ($divsHome as $key =>  $divHome) {
                if (strtolower(trim($divHome->getText())) == "overall") {
                    if ($key != 0) {
                        $divHome->click();
                        sleep(2);
                    }
                    $htmlDivOveralStanding = $this->seleniumDriver->findElement('#tournament-table-tabs-and-content > div > div > .tableWrapper');
                    $htmlDivOveralStanding = $htmlDivOveralStanding->getAttribute("outerHTML");
                }

                if (strtolower(trim($divHome->getText())) == "home") {
                    if ($key != 0) {
                        $divHome->click();
                        sleep(2);
                    }
                    $htmlDivHomeStanding = $this->seleniumDriver->findElement('#tournament-table-tabs-and-content > div > div > .tableWrapper');
                    $htmlDivHomeStanding = $htmlDivHomeStanding->getAttribute("outerHTML");
                }

                if (strtolower(trim($divHome->getText())) == "away") {
                    if ($key != 0) {
                        $divHome->click();
                        sleep(2);
                    }
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
            'htmlDivOveralStanding' => $htmlDivOveralStanding
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
        $divOveralStanding =  str_get_html($parentDiv['htmlDivOveralStanding']);
        var_dump($divAwayStanding,$divHomeStanding,$divOveralStanding);exit;
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
        $tourInfoHome = [];
        $tourInfoAway = [];
        $tourInfoOveral = [];
        if ($divHomeStanding) {
            $divsHomeTeam = $divHomeStanding->find(".ui-table__row");
            foreach ($divsHomeTeam as $divHomeTeam) {
                $tourInfoHome[] = $this->crawlStanding($divHomeTeam);
            }
        }

        if ($divAwayStanding) {
            $divsAwayTeam = $divAwayStanding->find(".ui-table__row");
            foreach ($divsAwayTeam as $divAwayTeam) {
                $tourInfoAway[] =  $this->crawlStanding($divAwayTeam);
            }
        }
        if ($divOveralStanding) {
            $divsOveralTeam = $divOveralStanding->find(".ui-table__row");
            foreach ($divsOveralTeam as $divOvelralTeam) {
                $tourInfoOveral[] =  $this->crawlStanding($divOvelralTeam);
            }
        }
        return [
            'list_live_match' => $list_live_match,
            'tourInfoHome' => $tourInfoHome,
            'tourInfoAway' => $tourInfoAway,
            'tourInfoOveral' => $tourInfoOveral
        ];
    }
    public function crawlStanding($divHomeTeam)
    {
        $divRank = $divHomeTeam->find(".tableCellRank", 0);
        $rank = $this->replaceTextRank(trim($divRank->text()));

        $divName = $divHomeTeam->find(".tableCellParticipant__name", 0);
        $name = $divName->text();
        $infos = $divHomeTeam->find(".table__cell--value");
        $dataTournament = [
            'rank' => $rank,
            'name' => $name,
        ];
        foreach ($infos as $key =>  $info) {
            switch ($key) {
                case 0:
                    $dataTournament['totalMatch'] = trim($info->text());
                    break;
                case 1:
                    $dataTournament['totalWin'] = trim($info->text());
                    break;
                case 2:
                    $dataTournament['totalLose'] = trim($info->text());
                    break;
                case 3:
                    $dataTournament['totalDraw'] = trim($info->text());
                    break;
                case 4:
                    $dataTournament['totalGoal'] = trim($info->text());
                    break;
                case 5:
                    $dataTournament['totalPoint'] = trim($info->text());
                    break;
            }
        }
        $divsHistory = $divHomeTeam->find(".table__cell--form > div");
        $matchInfo = [];
        foreach ($divsHistory as $key => $divHistory) {
            $title  = $divHistory->getAttribute("title");
            $result = $divHistory->find("div", 0) ? $divHistory->find("div", 0)->text() : "";

            if ($title && $result) {
                $match = $this->replaceTextMatch($title);
                if (!is_array($match)) {
                    $matchInfo[] = [
                        'title' => $match,
                        'results' => $result,
                    ];
                } else {
                    $matchInfo[] = [
                        'date' => $match['date'],
                        'home' => $match['home'],
                        'away' => $match['away'],
                        'results' => $result,
                    ];
                }
            }
        }
        $dataTournament['matchInfo'] = $matchInfo;

        return $dataTournament;
    }
    public function replaceTextMatch($title)
    {
        $text = "";
        //[b]Next home match:[/b] Arsenal - Southampton 21.04.2023
        //[b]4:1&amp;nbsp;[/b](Arsenal - Leeds) 01.04.2023
        $title = str_replace(["(", ")"], ["", ""], $title);
        $texts = explode("[/b]", $title);
        if (!isset($texts[1])) {
            return $title;
        }
        $texttemp = $texts[1];
        $arrTemp = explode(" ", $texttemp);

        $date = $arrTemp[count($arrTemp) - 1];

        unset($arrTemp[count($arrTemp) - 1]);

        $text_match = implode(" ", $arrTemp);

        $arrTemp = explode(" - ", $text_match);
        $home = $arrTemp[0];
        $away = $arrTemp[1];

        return [
            'date' => trim($date),
            'home' => trim($home),
            'away' => trim($away)
        ];
    }
    public function replaceTextRank($rank)
    {
        //1.
        return str_replace(["."], [''], $rank);
    }
}
