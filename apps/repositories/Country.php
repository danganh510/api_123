<?php

namespace Score\Repositories;

use Phalcon\Di;
use Score\Models\ScArea;
use Score\Models\ScCountry;
use Phalcon\Mvc\User\Component;

class Country extends Component
{
    public static function getCountryGlobalComboBox($country_code,$status = true)
    {
        $globalVariable = Di::getDefault()->get('globalVariable');
        $global = $globalVariable->global;
        $list_country = self::getAllCountries();
        $output = "";
        $selected = "";
        $list_location = Location::findAllLocationCountryCodes();
        if ($status) {
            $code = strtoupper($global['code']);

            if ($country_code == 'all') {
                $selected = "selected = 'selected'";
            }
            $output .= "<option " . $selected . " value='all'> All Location Country </option>";
            $selected = "";
            if ($country_code == $code) {
                $selected = "selected = 'selected'";
            }
            $output .= "<option " . $selected . " value='" . $code . "'>" . strtoupper($global['code']) . ' - ' . $global['name'] . "</option>";
        }
        foreach ($list_country as $country) {
            $active = "";
            $selected = "";
            if ($country['country_code'] == $country_code) {
                $selected = "selected = 'selected'";

            }
            if (in_array(strtolower($country['country_code']),$list_location)) {
                $active = " (Active Location)";
            }
            $lable_option =  strtoupper($country['country_code']) . ' - ' . $country['country_name'] . $active;
            $output .= "<option " . $selected . " value='" . $country['country_code'] . "'>" .$lable_option. "</option>";
        }
        return $output;
    }
    public static function getCountryGlobalComboBoxNoAllLowerKey($country_code,$status = true)
    {
        $arrCountryCode = explode(',',$country_code);
        $globalVariable = Di::getDefault()->get('globalVariable');
        $global = $globalVariable->global;
        $list_country = self::getAllCountries();
        $output = "";
        if ($status) {
            $code = strtolower($global['code']);
            $selected = "";
            if (in_array($code,$arrCountryCode)) {
                $selected = "selected = 'selected'";
            }
            $output .= "<option " . $selected . " value='" . $code . "'>" . strtoupper($global['code']) . ' - ' . $global['name'] . "</option>";
        }
        foreach ($list_country as $country) {
            $selected = "";
            if (in_array(strtolower($country['country_code']),$arrCountryCode)) {
                $selected = "selected = 'selected'";

            }
            $output .= "<option " . $selected . " value='" . strtolower($country['country_code']) . "'>" . strtoupper($country['country_code']) . ' - ' . $country['country_name'] . "</option>";
        }
        return $output;
    }
    public static function getCountryGlobalComboBoxNoAllUpperKey($country_code,$status = true)
    {
        $arrCountryCode = explode(',',$country_code);
        $globalVariable = Di::getDefault()->get('globalVariable');
        $global = $globalVariable->global;
        $list_country = self::getAllCountries();
        $output = "";
        if ($status) {
            $code = strtoupper($global['code']);
            $selected = "";
            if (in_array($code,$arrCountryCode)) {
                $selected = "selected = 'selected'";
            }
            $output .= "<option " . $selected . " value='" . $code . "'>" . strtoupper($global['code']) . ' - ' . $global['name'] . "</option>";
        }
        foreach ($list_country as $country) {
            $selected = "";
            if (in_array(strtoupper($country['country_code']),$arrCountryCode)) {
                $selected = "selected = 'selected'";

            }
            $output .= "<option " . $selected . " value='" . strtoupper($country['country_code']) . "'>" . strtoupper($country['country_code']) . ' - ' . $country['country_name'] . "</option>";
        }
        return $output;
    }
    public static function getCountryGlobalSelectAddOns($country_code,$status = true,$name_post = "slcLocationCountry") {
        $arrCountryCode = explode(',',$country_code);
        $globalVariable = Di::getDefault()->get('globalVariable');
        $global = $globalVariable->global;
        $list_area = ScArea::find([
            'area_active = "Y"',
            'area_order ASC'
        ]);
        $output_parent = "";
        if ($status) {
            $code = strtolower($global['code']);
            $checked = "";
            if (in_array($code,$arrCountryCode)) {
                $checked = "checked = 'checked'";
            }
            $output_parent .= "<div  class='col-lg-3'><div class='role_block main m-2'>
<input  id='". strtolower($global['code']) ."' name='{$name_post}[]'  type='checkbox' ".$checked." value='". strtolower($global['code']) ."'>
<label class='container_checkbox' for='". strtolower($global['code']) ."'>" . strtoupper($global['code']) . ' - ' . $global['name'] . "</label></div></div>";
        }
        foreach ($list_area as $area) {
            $list_country = self::findByArea($area->getAreaId());
            $output_parent .= "<div  class='col-lg-12'><hr><div class='role_block main m-2'>
<input class='checkbox-parent' id='".$area->getAreaId()."' name='{$name_post}[]' type='checkbox' "." value='".$area->getAreaId()."'>
<label  class='container_checkbox ' for='".$area->getAreaId()."'>".$area->getAreaName()."</label></div><div class='row ml-1'> ";
            $output = "";

            foreach ($list_country as $country) {
                $checked = "";
                if (in_array(strtolower($country->getCountryCode()),$arrCountryCode)) {
                    $checked = "checked = 'checked'";

                }
                $output .= "<div  class='col-lg-3'><div class='role_block main m-2'>
<input class='checkbox-child' id='".strtolower($country->getCountryCode())."' title='".$area->getAreaId()."' name='{$name_post}[]' type='checkbox' ".$checked." value='".strtolower($country->getCountryCode())."'>
<label for='".strtolower($country->getCountryCode())."'>". strtoupper($country->getCountryCode()) . ' - ' .$country->getCountryName()."</label></div></div>";
            }
            $output_parent .= $output . "</div></div>";

        }
        return $output_parent;
    }
    public static function getCountryGlobalSelectCurrency($country_codes,$area_ids,$status = true,$country_disabled = "",$name_post = "slcLocationCountry[]") {
        $globalVariable = Di::getDefault()->get('globalVariable');
        $arrArea = explode(',',$area_ids);
        $global = $globalVariable->global;
        $list_area = ScArea::find([
            'area_active = "Y"',
            'area_order ASC'
        ]);
        $output_parent = "";
        if ($status) {
            $code = strtoupper($global['code']);
            $checked = "";
            if (in_array($code,$country_codes)) {
                $checked = "checked = 'checked'";
            }
            $output_parent .= "<div  class='col-lg-3'><div class='role_block main m-2'>
<input  id='". strtoupper($global['code']) ."' name='".$name_post."'  type='checkbox' ".$checked." value='". strtoupper($global['code']) ."'>
<label class='container_checkbox' for='". strtoupper($global['code']) ."'>" . strtoupper($global['code']) . ' - ' . $global['name'] . "</label></div></div>";
        }
        foreach ($list_area as $area) {
            $list_country = self::findByArea($area->getAreaId());
            $checked_area = "";
            if (in_array($area->getAreaId(),$arrArea)) {
                $checked_area = "checked";
            }
            $output_parent .= "<div  class='col-lg-12'><hr><div class='role_block main m-2'>
<input class='checkbox-parent' id='".$area->getAreaId()."' name='".$name_post."' type='checkbox' "." ".$checked_area." value='".$area->getAreaId()."'>
<label  class='container_checkbox ' for='".$area->getAreaId()."'>".$area->getAreaName()."</label></div><div class='row ml-1'> ";
            $output = "";
            foreach ($list_country as $country) {
                $checked = "";
                $disabled = "";
                if ($country_disabled == strtoupper($country->getCountryCode())) {
                    $disabled = " disabled";
                }
                if (in_array(strtoupper($country->getCountryCode()),$country_codes)) {
                    $checked = " checked = 'checked'";
                }
                $output .= "<div  class='col-lg-3'><div class='role_block main m-2'>
<input class='checkbox-child' id='".strtoupper($country->getCountryCode())."' title='".$area->getAreaId()."' name='".$name_post."' type='checkbox' ".$disabled.$checked." value='".strtoupper($country->getCountryCode())."'>
<label for='".strtoupper($country->getCountryCode())."'>". strtoupper($country->getCountryCode()) . ' - ' .$country->getCountryName()."</label></div></div>";
            }
            $output_parent .= $output . "</div></div>";

        }
        return $output_parent;
    }

    public static function getCountryCommunicationGlobalComboBox($country_code)
    {
        $globalVariable = Di::getDefault()->get('globalVariable');
        $global = $globalVariable->global;
        $list_country = self::getAllCountries();
        $output = "";
        $code = strtoupper($global['code']);
        $selected = "";
        if ($country_code == $code) {
            $selected = "selected = 'selected'";
        }
        $output .= "<option " . $selected . " value='" . $code . "'>" . strtoupper($global['code']) . ' - ' . $global['name'] . "</option>";
        foreach ($list_country as $country) {
            $selected = "";
            if ($country['country_code'] == $country_code) {
                $selected = "selected = 'selected'";

            }
            $output .= "<option " . $selected . " value='" . $country['country_code'] . "'>" . strtoupper($country['country_code']) . ' - ' . $country['country_name'] . "</option>";
        }
        return $output;
    }

    /**
     * find all countries active
     * @return ScCountry[]
     */
    public static function getAllCountries()
    {
        return ScCountry::find(array(
            "columns" => "country_code,country_name",
            "country_active='Y' AND country_code !=''",
            "order" => "country_name ASC"
        ))->toArray();
    }
    public static function getComboboxAndAreaByCode($code)
    {
        $areas = ScArea::find(array(
            'area_active = "Y" ',
            "order" => "area_name ASC",
        ));
        $output = '';
        foreach ($areas as $area) {
            $selected = '';
            if ($area->getAreaId() == $code) {
                $selected = 'selected';
            }
            $output .= "<option " . $selected . " value='" . $area->getAreaId() . "'>" . $area->getAreaName() . ' - ' . $area->getAreaId() . "</option>";

        }
        $jurisdiction = ScCountry::find(array(
            'country_active = "Y" ',
            "order" => "country_name ASC",
        ));
        foreach ($jurisdiction as $value) {
            $selected = '';
            if ($value->getCountryCode() == $code) {
                $selected = 'selected';
            }
            $output .= "<option " . $selected . " value='" . $value->getCountryCode() . "'>" . $value->getCountryCode() . ' - ' . $value->getCountryName() . "</option>";

        }
        return $output;
    }
    public static function getComboboxByCode($code)
    {
        $jurisdiction = ScCountry::find(array(
            'country_active = "Y" ',
            "order" => "country_name ASC",
        ));
        $output = '';
        foreach ($jurisdiction as $value) {
            $selected = '';
            if ($value->getCountryCode() == $code) {
                $selected = 'selected';
            }
            $output .= "<option " . $selected . " value='" . $value->getCountryCode() . "'>" . $value->getCountryCode() . ' - ' . $value->getCountryName() . "</option>";

        }
        return $output;
    }
    public static function getComboboxByCodeAndAreaId($code,$area_id)
    {
        $jurisdiction = ScCountry::find(array(
            'country_active = "Y" AND country_area_id = :area_id:',
            'bind' => ['area_id' => $area_id],
            "order" => "country_name ASC",
        ));
        $output = '';
        foreach ($jurisdiction as $value) {
            $selected = '';
            if ($value->getCountryCode() == $code) {
                $selected = 'selected';
            }
            $output .= "<option " . $selected . " value='" . $value->getCountryCode() . "'>" . $value->getCountryCode() . ' - ' . $value->getCountryName() . "</option>";
        }
        return $output;
    }
    public static function getCountryNameOrGlobalByCode($coutry_code)
    {
        $globalVariable = Di::getDefault()->get('globalVariable');
        if (strtolower($coutry_code) == $globalVariable->global['code']) {
            return $globalVariable->global['name'];
        }
        return self::getCountryNameByCode($coutry_code);
    }
    public static function getCountryNameByCode($country_code)
    {
        if ($country_code == "gx") {
            return "Global";
        }
        $result = ScCountry::findFirst(array(
            'country_code = :country_code:',
            'bind' => array('country_code' => $country_code)
        ));
        return ($result) ? $result->getCountryName() : '';
    }
    public static function findByCode($countryCode)
    {
        return ScCountry::findFirst(array(
            "country_code=:countryCode: AND country_active='Y'",
            "bind" => array("countryCode" => $countryCode)
        ));
    }
    public static function findFirstById($id)
    {
        return ScCountry::findFirst(array(
            "country_id =:ID:",
            'bind' => array('ID' => $id)
        ));
    }

    public static function getCountryByAreaId($areaId,$countryCode)
    {
        if ($areaId) {
            $list_country = self::findCountryByAreaId($areaId);
        } else {
            $list_country = ScCountry::find(array("country_active = 'Y'",
                                            'order' => 'country_name ASC'));
        }
        $string = "";
        foreach ($list_country as $country) {
            $selected = '';
            if ($country->getCountryCode() == $countryCode) {
                $selected = 'selected';
            }
            $string .= "<option " . $selected . " value='" . $country->getCountryCode() . "'>" .$country->getCountryName() . "</option>";
        }
        return $string;
    }
    public static function findCountryByAreaId($Id)
    {
        return ScCountry::find(array(
            'column' => 'country_id , country_name',
            'conditions' => 'country_area_id = :ID:',
            'bind' => array('ID' => $Id),
            'order' => 'country_name ASC'
        ));
    }
    public static function getCheckboxCountry($input) {
        $data = ScCountry::find([
            'country_active = "Y"  '
        ]);
        $checked_gx = (in_array("GX",$input)) ? 'checked' : '';
        $output = "<div class='role_block country_block col-md-4'><label class='container_checkbox'> Global
                            <input type='checkbox' 
                                    class='form-control check' 
                                    name='slcCountry[]' 
                                     " . $checked_gx. " 
                                     value='GX' />
                            <span class='checkmark_checkbox'></span>
                            </label>
                            </div> ";
        if($data) {
            foreach ($data as $key => $value)
            {
                $checked = (in_array($value->getCountryCode(),$input)) ? 'checked' : '';
                $output.= "<div class='role_block country_block col-md-4'><label class='container_checkbox'> ".$value->getCountryName()."
                            <input type='checkbox' 
                                    class='form-control check' 
                                    name='slcCountry[]' 
                                     " . $checked. " 
                                     value='" . $value->getCountryCode() . "' />
                            <span class='checkmark_checkbox'></span>
                            </label>
                            </div> ";
            }
        }

        return $output;
    }
    public static function getAllCountryCode() {
        return ScCountry::find(array(
            'columns' => "country_code",
            "country_active = :active:",
            'bind' => [
                'active' => 'Y'
            ],
            "order" => "country_name ASC"
        ));
    }
    public static function getAllCountry() {
        return ScCountry::find(array(
            "country_active = :active:",
            'bind' => [
                'active' => 'Y',
            ],
            "order" => "country_name ASC"
        ));
    }
    public static function getCountryCombobox($code)
    {
        $country = self::getAllCountry();
        $output = '';
        foreach ($country as $value)
        {
            $selected = '';
            if($value->getCountryCode() == $code)
            {
                $selected = 'selected';
            }
            $output.= "<option ".$selected." value='".$value->getCountryCode()."'>".$value->getCountryCode().' - '.$value->getCountryName()."</option>";

        }
        return $output;
    }
    public static function findFirstByArea($area_id) {
        return ScCountry::findFirst(array(
            "country_area_id = :area_id:",
            'bind' => [
                'area_id' => $area_id,
            ],
        ));
    }
    public static function findByArea($area_id) {
        return ScCountry::find(array(
            "country_area_id = :area_id:",
            'bind' => [
                'area_id' => $area_id,
            ],
        ));
    }
    public static function getCountryComboboxGX($code)
    {
        $country = ScCountry::find();
        $output = '';
        $selected = '';
        if($code == "gx")
        {
            $selected = 'selected';
        }
        $output.= "<option ".$selected." value='gx'>GX - Global</option>";
        foreach ($country as $value)
        {
            $selected = '';
            if(strtolower($value->getCountryCode()) == $code)
            {
                $selected = 'selected';
            }
            $output.= "<option ".$selected." value='".strtolower($value->getCountryCode())."'>".$value->getCountryCode()." - ".$value->getCountryName()."</option>";

        }
        return $output;
    }
    public static function getNameByCode($code)
    {
        $result = self::findByCode($code);
        return $result ? $result->getCountryName() : '';
    }
    public static function getNameGlobalByCode($code) {
        return strtolower($code) == "gx" ? "Global" : self::getNameByCode(strtolower($code));
    }
    public static function getNameByArrayCode($arrCode) {
        $arrName = [];
        foreach ($arrCode as $code) {
            $name = self::getNameGlobalByCode($code) ? self::getNameGlobalByCode($code) : $code;
            array_push($arrName,$name);
        }
        return implode(', ',$arrName);
    }
    public static function getArrCodeAndName() {
        $arrCountry = ScCountry::find([
            "columns" => "country_code,country_name,country_active",
            "country_active = 'Y'"
        ]);
        return array_column($arrCountry->toArray(),"country_name",'country_code');
    }
    public static function getNameAndAreaByCode($code) {
        $name = self::getNameByCode($code);
        if (!$name) {
            $name = Area::getNameById($code);
        }
        return $name;
    }

    public function getNameByCodeAndLang($countryCode, $lang = 'en')
    {
        $result = false;
        $para = array('countryCode' => $countryCode);
        if ($lang && $lang != $this->globalVariable->defaultLanguage) {
            $sql = "SELECT c.*, cl.* FROM \Score\Models\ScCountry c
                INNER JOIN \Score\Models\ScCountryLang cl
                ON cl.country_id = c.country_id AND cl.country_lang_code = :LANG: 
                WHERE c.country_code = :countryCode: 
                AND c.country_active = 'Y' 
                ";
            $para['LANG'] = $lang;
            $lists = $this->modelsManager->executeQuery($sql, $para)->getFirst();
            if ($lists) {
                $result = \Phalcon\Mvc\Model::cloneResult(
                    new ScCountry(), array_merge($lists->c->toArray(), $lists->cl->toArray()));
            }
        } else {
            $sql = 'SELECT * FROM Score\Models\ScCountry  
                WHERE country_active = "Y" AND country_code = :countryCode: 
                ';
            $lists = $this->modelsManager->executeQuery($sql, $para)->getFirst();
            if ($lists) $result = $lists;
        }
        return ($result)?$result->getCountryName():'';
    }
}

