<?php

namespace Score\Repositories;

use Exception;
use Score\Models\ScArea;
use Score\Models\ScCountry;

class CrawlerFlashScoreBase extends CrawlerList
{
    public function __construct($seleniumDriver, $url_crawl, $day_time, $isLive)
    {
        $this->seleniumDriver = $seleniumDriver;
        $this->url_crawl = $url_crawl;
        $this->day_time = $day_time;
        $this->isLive = $isLive;
    }
    public function setupSiteTour()
    {
        $this->runSelenium();
    }
    public function setupSite()
    {
        $time = microtime(true);

        $this->runSelenium();

        //k lưu cokkie
        try {
            $this->seleniumDriver->clickButton("#onetrust-reject-all-handler");
        } catch (Exception $e) {
            echo "not found cookie";
        }

        if (!$this->isLive) {
            //  $parentDiv = $seleniumDriver->findElements('div[id="live-table"] > section > div > div > div');
            //click button time cho lần đầu
            $this->seleniumDriver->clickButton("#calendarMenu");
            sleep(1);
            $divTimes = $this->seleniumDriver->findElements(".calendar__day");
            foreach ($divTimes as $div) {
                $text = $div->getText();
                $data_click = strftime('%d/%m', strtotime($this->day_time));
                if (strpos($text, $data_click) !== false) {
                    sleep(0.5);
                    try {
                        $div->click();
                    } catch (Exception $e) {
                        echo "33";
                    }
                    break;
                }
            }
            sleep(4);
        } else {
            //  click button LIVE cho lần đầu
            $divFilters = $this->seleniumDriver->findElements(".filters__text--short");
            foreach ($divFilters as $div) {
                // echo "time find div: " . (microtime(true) - $time_1) . "</br>";

                if ($div->getText() === 'LIVE') {
                    $div->click();
                    break;
                }
            }
            sleep(1);
        }
        $isWeekend = $this->my->isweekend($this->day_time);

        if (true) {
            $divClose = $this->seleniumDriver->findElements(".event__expander--close");
            $divClose = array_reverse($divClose);
            $click = 0;
            foreach ($divClose as $key =>  $div) {
                try {
                    $div->click();
                    //    sleep(0.1);
                    $click++;
                } catch (Exception $e) {
                    echo "error85:";
                }
            }
        }
    }
    public function getTournament($div)
    {
        $country_name = $div->find('.event__title--type', 0)->innertext();

        $name = $div->find(".event__title--name", 0)->innertext();

        $country_name =  strtolower($country_name);
        $country_name = str_replace(["&amp;"], ["&"], $country_name);
        $group = "";
        if ((strpos($name, "Group") || strpos($name, "Offs") || strpos($name, "Apertura") || strpos($name, "Clausura")
                || strpos($name, "Clausura") ||  strpos($name, "places") ||  strpos($name, "stage"))

            && strpos($name, "-")
        ) {
            $nameDetail = explode("-", $name);
            $name = trim($nameDetail[0]);
            $group = isset($nameDetail[1]) ? $nameDetail[1] . (isset($nameDetail[2]) ? $nameDetail[1] : "") : "";
            $group = isset($nameDetail[3]) ? $group . $nameDetail[3] : "";
            $group = trim($group);
        }
        $this->round = $group;

        $hrefTour = "/football/" . MyRepo::create_slug($country_name) . "/" . $this->create_slug(strtolower($name));

        $country_code = ScCountry::findFirstCodeByName($country_name);
        if (!$country_code) {
            $model = ScArea::findFirst([
                'columns' => 'area_id',
                "area_name = :country_name: OR LOWER(area_name) = :country_name:",
                'bind' => [
                    'country_name' => $country_name
                ]
            ]);
            $country_code = $model ? $model->area_id : "";
        }
        $tournamentModel = new MatchTournament();
        $tournamentModel->setCountryName(strtolower($country_name));
        $tournamentModel->setCountryCode(strtolower($country_code));
        $tournamentModel->setTournamentName(strtolower($name));
        $tournamentModel->setTournamentGroup(strtolower($group));
        $tournamentModel->setId(count($this->list_live_tournaments) + 1);
        $tournamentModel->setCountryImage("");
        $tournamentModel->setTournamentHref($hrefTour);

        return $tournamentModel;
    }
    public function getMatch($divMatch, $round = "")
    {

        $dataMatch = [];
        $id_insite = $divMatch->getAttribute("id");
        $id_insite = explode("_", $id_insite);
        $id_insite = $id_insite[count($id_insite) - 1];
        $dataMatch['href_detail'] = "/match/" . $id_insite;
        try {
            if (count($divMatch->find(".event__stage--block"))) {
                if ($divMatch->find(".event__time", 0)) {
                    $time = $divMatch->find(".event__time", 0)->text();
                } else {
                    $time = $divMatch->find(".event__stage--block")[0]->text();
                }
            } else {
                $time = $divMatch->find(".event__time")[0]->text();
            }
        } catch (Exception $e) {
        }
        $time = str_replace('&nbsp;', "", $time);
        $time = str_replace(' ', "", $time);
        $dataMatch['time'] = trim($time);

        $dataMatch['home'] = $divMatch->find(".event__participant--home")[0]->text();


        $home_image = $divMatch->find(".event__logo--home");
        $dataMatch['home_image'] = isset($home_image[0]) ? $home_image[0]->getAttribute("src") : '';

        $home_score = $divMatch->find(".event__score--home");
        $dataMatch['home_score'] = isset($home_score[0]) ? $home_score[0]->innertext() : 0;

        //find card red
        $home_card = $divMatch->find(".event__participant--home > .icon--redCard");
        $dataMatch['home_card_red'] = count($home_card);

        $away_card = $divMatch->find(".event__participant--away > .icon--redCard");
        $dataMatch['away_card_red'] = count($away_card);

        $dataMatch['away'] = $divMatch->find(".event__participant--away")[0]->text();
        $away_image = $divMatch->find(".event__logo--away");
        $dataMatch['away_image'] = isset($away_image[0]) ? $away_image[0]->getAttribute("src") : '';

        $away_score = $divMatch->find(".event__score--away");
        $dataMatch['away_score'] = isset($away_score[0]) ? $away_score[0]->innertext() : 0;

        if ($round) {
            $dataMatch['round'] = $round;
        } else {
            $dataMatch['round'] = $this->round;
        }

        $liveMatch = $this->saveMatch($dataMatch);
        return $liveMatch;
    }
}
