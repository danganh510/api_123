<?php

use Phalcon\Mvc\User\Component;

class ConstEnv extends Component
{
    const CACHE_TYPE_ID = "ID";
    const CACHE_TYPE_NAME = "NAME";
    const CACHE_TYPE_NAME_FLASH = "FLASHSCORE";

    const TYPE_STANDING_OVERAL = "overal";
    const TYPE_STANDING_HOME = "home";
    const TYPE_STANDING_AWAY = "away";

    const HREF_DETAIL_START_EN = "/match-summary/match-statistics";
    const HREF_DETAIL_START_VI = "/tom-tat-tran-dau/thong-ke-tran-dau";

    const HREF_DETAIL_TRACKER_EN = "/match-summary/live-commentary";
    const HREF_DETAIL_TRACKER_VI = "/tom-tat-tran-dau/binh-luan-truc-tiep";
}
