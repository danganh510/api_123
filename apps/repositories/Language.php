<?php

namespace Score\Repositories;

use Score\Models\ScLanguage;
use Phalcon\Mvc\User\Component;

class Language extends Component
{

    public static function checkCode($language_code, $language_id)
    {
        return ScLanguage::findFirst(
            array(
                'language_code = :CODE: AND language_id != :languageid:',
                'bind' => array('CODE' => $language_code, 'languageid' => $language_id),
            ));
    }

    /**
     * @return ScLanguage|ScLanguage[]
     */
    public static function getLanguages()
    {
        return ScLanguage::find(array("language_active = 'Y'",
            "order" => "language_code"));
    }
    public static function getLanguagesNotActive()
    {
        return ScLanguage::find(array("order" => "language_code"));
    }
    public static function arrLanguages($hasEn = true)
    {
        $arr_language = array();
        if ($hasEn) {
            $arr_language['vi'] = "Việt Nam";
        }
        $languages = self::getLanguages();
        foreach ($languages as $lang) {
            if ($lang->getLanguageCode() != 'vi') {
                $arr_language[$lang->getLanguageCode()] = $lang->getLanguageName();
            }
        }
        return $arr_language;
    }


    public static function geScameByCode($language_code)
    {
        $sc_language = ScLanguage::findFirst(array('language_code = :CODE: AND language_active="Y"', 'bind' => array('CODE' => $language_code),));
        return $sc_language ? $sc_language->getLanguageName() : '';
    }
    public static function getComboLanguage($lang_code)
    {
        $list_language = self::getLanguagesNotActive();

        $string = "";
        foreach ($list_language as $language) {
            $selected = '';
            if (strtoupper($language->getLanguageCode()) == $lang_code) {
                $selected = 'selected';
            }
            $string .= "<option " . $selected . " value='" . $language->getLanguageCode() . "'>" . $language->getLanguageName() . "</option>";
        }
        return $string;
    }
    public static function getCombo($lang_code)
    {
        $list_language = self::getLanguages();
        $string = "";
        foreach ($list_language as $language) {
            $selected = '';
            if ($language->getLanguageCode() == $lang_code) {
                $selected = 'selected';
            }
            $string .= "<option " . $selected . " value='" . $language->getLanguageCode() . "'>" . strtoupper($language->getLanguageCode()) . ' - ' . $language->getLanguageName() . "</option>";
        }
        return $string;
    }
    public static function findFirstById($languageId)
    {
        return ScLanguage::findFirst(array(
            "language_id =:ID:",
            'bind' => array('ID' => $languageId)
        ));
    }
    public static function getIsTranslateKeyword($language_code) {
        $sc_language = ScLanguage::findFirst(array('language_code = :CODE: AND language_active="Y"', 'bind' => array('CODE' => $language_code),));
        return $sc_language ? $sc_language->getLanguageIsTranslateKeyword() : '';
    }
}
