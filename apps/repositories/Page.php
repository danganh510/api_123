<?php

namespace Score\Repositories;

use Score\Models\ScPage;
use Phalcon\Mvc\User\Component;

class Page extends Component
{
    public static function checkKeyword($keyword, $id)
    {
        return ScPage::findFirst(array(
                'page_keyword = :keyword: AND page_id != :id: ',
                'bind' => array('keyword' => $keyword,
                    'id' => $id),
            )
        );
    }
    public static function findFirstById($id)
    {
        return ScPage::findFirst(array(
            'page_id = :id: ',
            'bind' => array('id' => $id)
        ));
    }
    public static function findById($id)
    {
        return ScPage::find(array(
            'page_id = :id:',
            'bind' => array('id' => $id),
        ));
    }
    public function findFirstPageByPageId($page_id, $lang = 'en', $location_code = 'gx')
    {
        $page = false;
        if ($lang && $lang != $this->globalVariable->defaultLanguage) {
            $para = ['LANG' => $lang, 'LOCATION_CODE' => $location_code, 'PAGEID' => $page_id];
            $sql = "SELECT p.*, pl.* FROM \Score\Models\ScPageLang pl 
                    INNER JOIN \Score\Models\ScPage p 
                        ON pl.page_id = p.page_id AND pl.page_lang_code = :LANG: 
                        AND pl.page_location_country_code = p.page_location_country_code 
                    WHERE p.page_id = :PAGEID: AND pl.page_location_country_code = :LOCATION_CODE: 
                ";
            $lists = $this->modelsManager->executeQuery($sql, $para)->getFirst();
            if($lists){
                $page = \Phalcon\Mvc\Model::cloneResult(
                    new ScPage(),
                    array_merge($lists->p->toArray(), $lists->pl->toArray())
                );
            }
        } else {
            $page = ScPage::findFirst(array(
                'page_id = :page_id: AND page_location_country_code = :LOCATION_CODE:',
                'bind' => array('page_id' => $page_id,'LOCATION_CODE' => $location_code)
            ));
        }
        return $page;
    }
    public function findFirstPageByPageKeyword($page_keyword, $lang = 'en', $location_code = 'gx')
    {
        $page = false;
        if ($lang && $lang != $this->globalVariable->defaultLanguage) {
            $para = ['LANG' => $lang, 'LOCATION_CODE' => $location_code, 'PAGEKEYWORD' => $page_keyword];
            $sql = "SELECT p.*, pl.* FROM \Score\Models\ScPageLang pl 
                    INNER JOIN \Score\Models\ScPage p 
                        ON pl.page_id = p.page_id AND pl.page_lang_code = :LANG:
                        AND pl.page_location_country_code = p.page_location_country_code
                    WHERE p.page_keyword = :PAGEKEYWORD: AND pl.page_location_country_code = :LOCATION_CODE: 
                ";
            $lists = $this->modelsManager->executeQuery($sql, $para)->getFirst();
            if($lists){
                $page = \Phalcon\Mvc\Model::cloneResult(
                    new ScPage(),
                    array_merge($lists->p->toArray(), $lists->pl->toArray())
                );
            }
        } else {
            $page = ScPage::findFirst(array(
                'page_keyword = :page_keyword: AND page_location_country_code = :LOCATION_CODE:',
                'bind' => array('page_keyword' => $page_keyword,'LOCATION_CODE' => $location_code)
            ));
        }
        return $page;
    }

    public function AutoGenMetaPage($page_keyword, $value_default, $lang = 'en', $location_code = 'gx', $more_value = null, $lang_url = null)
    {

        $page_object = $this->findFirstPageByPageKeyword($page_keyword,$lang,$location_code);
        if($page_object){
            $this->tag->setTitle($page_object->getPageTitle().$more_value);
            $this->view->meta_key = $page_object->getPageMetaKeyword().$more_value;
            $this->view->meta_descript = $page_object->getPageMetaDescription().$more_value;
            $this->view->menu_bread  =  $page_object->getPageName().$more_value;
            $this->view->page_content = $page_object->getPageContent();
            $this->view->meta_social_image = $page_object->getPageMetaImage();
        }
        else {
            $this->tag->setTitle($value_default.$more_value);
            $this->view->meta_key = $value_default.$more_value;
            $this->view->meta_descript = $value_default.$more_value;
            $this->view->menu_bread = $value_default.$more_value;
            $this->view->meta_social_image = '';
            $this->view->page_content = '';
        }
    }
    public function AutoGenMetaPageLang($page_keyword, $value_default, $lang = 'en', $location_code = 'gx', $more_value = null, $lang_url = null)
    {

        $page_object = $this->findFirstPageByPageLangKeyword($page_keyword,$lang,$location_code);
        if($page_object){
            $this->tag->setTitle($page_object->getPageTitle().$more_value);
            $this->view->meta_key = $page_object->getPageMetaKeyword().$more_value;
            $this->view->meta_descript = $page_object->getPageMetaDescription().$more_value;
            $this->view->menu_bread  =  $page_object->getPageName().$more_value;
            $this->view->page_content = $page_object->getPageContent($lang_url);
            $this->view->meta_social_image = $page_object->getPageMetaImage();
        }
        else {
            $this->tag->setTitle($value_default.$more_value);
            $this->view->meta_key = $value_default.$more_value;
            $this->view->meta_descript = $value_default.$more_value;
            $this->view->menu_bread = $value_default.$more_value;
            $this->view->meta_social_image = '';
            $this->view->page_content = '';
        }
        return $page_object;
    }
    public function generateStylePage($page_keyword,$location_code='gx',$lang='en'){

        $page_object = $this->findFirstPageByPageKeyword($page_keyword);
        if(!$page_object) {
            $this->view->page_style = '';
        }
        else{
            $this->view->page_style = $page_object->getPageStyle();
        }
    }
    public function generateParentBread($page_keyword,$default,$lang,$location_code='gx'){

        $page_object = $this->findFirstPageByPageKeyword($page_keyword,$lang,$location_code);
        if(!$page_object){
            $this->view->parent_bread = $default;
        }else{
            $this->view->parent_bread = $page_object->getPageName();
        }
    }

}
