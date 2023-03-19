<?php

namespace Score\Backend\Controllers;


use Score\Models\ScBanner;
use Score\Models\ScBannerLang;
use Score\Models\ScLanguage;
use Score\Repositories\Banner;
use Score\Repositories\BannerLang;
use Score\Repositories\Country;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Score\Repositories\Language;
use Score\Repositories\Location;
use Score\Utils\Validator;

class BannerController extends ControllerBase
{
    public function indexAction()
    {
        $current_page = $this->request->getQuery('page', 'int');
        $keyword = $this->request->get('txtSearch', 'trim');
        $controller = $this->request->get('slcController');
        $countryCode = $this->request->get('slcCountry');

        $banner = new Banner();
        $select_controller = $banner->getControllerCombobox($controller);

        $sql = ScBanner::query();
        if (!empty($keyword)) {
            $sql->where("banner_id = :keyword: OR banner_title like CONCAT('%',:keyword:,'%')",["keyword" => $keyword]);
            $this->dispatcher->setParam("txtSearch", $keyword);
        }
        $banner_controller = Banner::getValue($controller, Banner::CONTROLLER);
        if (!empty($banner_controller)) {
            $sql->andWhere("banner_controller = :controller:",["controller" => $banner_controller]);
            $this->dispatcher->setParam("slcController", $controller);
        }
        if (!empty($countryCode)) {
            $sql->andWhere("FIND_IN_SET(:CountryCode:,banner_locations)",['CountryCode' => $countryCode]);
            $this->dispatcher->setParam("slcCountry", $countryCode);
        }

        $sql->orderBy("banner_id DESC");
        $list_banner = $sql->execute();

        $paginator = new PaginatorModel(
            [
                'data' => $list_banner,
                'limit' => 20,
                'page' => $current_page,
            ]
        );

        if ($this->session->has('msg_result')) {
            $msg_result = $this->session->get('msg_result');
            $this->session->remove('msg_result');
            $this->view->msg_result = $msg_result;
        }

        $select_country = Country::getComboboxByCode($countryCode);
        $this->view->setVars(array(
            'list_banner' => $paginator->getPaginate(),
            'select_controller' => $select_controller,
            'select_country' => $select_country
        ));
    }

    public function createAction()
    {
        $data = array('banner_id' => -1, 'banner_active' => 'Y', 'banner_order' => 1, 'banner_controller' => '','banner_locations' => '');
        if ($this->request->isPost()) {
            $data = array(
                'banner_controller' => trim($this->request->getPost('slcController')),
                'banner_title' => trim($this->request->getPost('txtTitle')),
                'banner_subtitle' => trim($this->request->getPost('txtSubtitle')),
                'banner_content' => trim($this->request->getPost('txtContent')),
                'banner_link' => $this->request->getPost('txtLink', array('string', 'trim')),
                'banner_image' => $this->request->getPost('txtImage', array('string', 'trim')),
                'banner_image_mobile' => $this->request->getPost('txtImageMobile', array('string', 'trim')),
                'banner_order' => $this->request->getPost('txtOrder', 'trim'),
                'banner_active' => $this->request->getPost('radActive'),
                'banner_start_time' => $this->request->getPost('txtStartTime'),
                'banner_end_time' => $this->request->getPost('txtEndTime'),
            );
            if (!empty($_POST['dataBannerLocations'])) {
                foreach ($_POST['dataBannerLocations'] as $country) {
                    $data['banner_locations'][] = $country;
                }
            }
            $messages = array();
            if (empty($data['banner_start_time']) || $data['banner_start_time'] === 0) {
                $data['banner_start_time'] = "";
            } else {
                $data['banner_start_time'] = $this->my->UTCTime(strtotime($data['banner_start_time']));
                if (!is_numeric($data['banner_start_time']) || $data['banner_start_time'] <= 0) $messages['banner_start_time'] = "Format day time false";

            }
            if (empty($data['banner_end_time']) || $data['banner_end_time'] === 0) {
                $data['banner_end_time'] = "";
            } else {
                $data['banner_end_time'] = $this->my->UTCTime(strtotime($data['banner_end_time']));
                if (!is_numeric($data['banner_end_time']) || $data['banner_end_time'] <= 0) $messages['banner_end_time'] = "Format day time false";
            }
            if ($data['banner_controller'] === "") {
                $messages['controller'] = 'Controller is required.';
            }
            if (empty($data['banner_image'])) {
                $messages['image'] = 'Image field is required.';
            }
            if (empty($data['banner_image_mobile'])) {
                $messages['image_mobile'] = 'Image mobile field is required.';
            }
            if (empty($data['banner_title'])) {
                $messages['title'] = 'Title field is required.';
            }
            if (empty($data['banner_order'])) {
                $messages["order"] = "Order field is required.";
            } else if (!is_numeric($data["banner_order"])) {
                $messages["order"] = "Order is not valid ";
            }
            if (count($messages) === 0) {
                $banner_controller = Banner::getValue($data['banner_controller'], Banner::CONTROLLER);
                $banner_article_keyword = Banner::getValue($data['banner_controller'], Banner::SERVICE);
                $msg_result = array();
                $new_banner = new ScBanner();
                $new_banner->setBannerController($banner_controller);
                $new_banner->setBannerArticleKeyword($banner_article_keyword);
                $new_banner->setBannerTitle($data['banner_title']);
                $new_banner->setBannerSubtitle($data['banner_subtitle']);
                $new_banner->setBannerContent($data['banner_content']);
                $new_banner->setBannerLink($data['banner_link']);
                $new_banner->setBannerImage($data['banner_image']);
                $new_banner->setBannerImageMobile($data['banner_image_mobile']);
                $new_banner->setBannerOrder($data['banner_order']);
                $new_banner->setBannerActive($data['banner_active']);
                $new_banner->setBannerLocations(strtolower(implode(',', $data['banner_locations'])));
                $new_banner->setBannerStartTime($data['banner_start_time']);
                $new_banner->setBannerEndTime($data['banner_end_time']);

                $result = $new_banner->save();
                if ($result === false) {
                    $message = "Create Banner Fail !";
                    $msg_result['status'] = 'error';
                    $msg_result['msg'] = $message;
                } else {
                    $msg_result = array('status' => 'success', 'msg' => 'Create Banner Success');
                }
                $this->session->set('msg_result', $msg_result);
                return $this->response->redirect("/banner");
            }
        }
        $banner = new Banner();
        $select_controller = $banner->getControllerCombobox($data['banner_controller']);
        $list_banner_locations = Country::getAllCountries();
        $messages['status'] = 'border-red';
        $this->view->setVars(array(
            'formData' => $data,
            'messages' => $messages,
            'select_controller' => $select_controller,
            'list_banner_locations' => $list_banner_locations,
            'list_banner_locations_selected' => $data['banner_locations'],
        ));
    }

    public function editAction()
    {
        $id_banner = $this->request->getQuery('id');
        $checkID = new Validator();
        if (!$checkID->validInt($id_banner)) {
            return $this->response->redirect('notfound');
        }

        $banner_model = ScBanner::findFirstById($id_banner);
        if (empty($banner_model)) {
            return $this->response->redirect('notfound');
        }
        $arr_translate = array();
        $messages = array();
        $data_post = array(
            'banner_controller' => '',
            'banner_title' => '',
            'banner_subtitle' => '',
            'banner_content' => '',
            'banner_link' => '',
            'banner_image' => '',
            'banner_image_mobile' => '',
            'banner_order' => '',
            'banner_is_home' => '',
            'banner_active' => '',
        );
        $save_mode = '';
        $lang_default = $this->globalVariable->defaultLanguage;
        $arr_language = Language::arrLanguages();
        $lang_current = $lang_default;
        if ($this->request->isPost()) {
            if (!isset($_POST['save'])) {
                $this->view->disable();
                $this->response->redirect("notfound");
                return;
            }
            $save_mode = $_POST['save'];
            if (isset($arr_language[$save_mode])) {
                $lang_current = $save_mode;
            }
            if ($save_mode != ScLanguage::GENERAL) {
                $data_post['banner_title'] = trim($this->request->getPost('txtTitle'));
                $data_post['banner_subtitle'] = trim($this->request->getPost('txtSubtitle'));
                $data_post['banner_content'] = trim($this->request->getPost('txtContent'));

                if (empty($data_post['banner_title'])) {
                    $messages[$save_mode]['title'] = 'Title field is required.';
                }
            } else {
                $data_post = array(
                    'banner_controller' => trim($this->request->getPost('slcController')),
                    'banner_type' => trim($this->request->getPost('slcType')),
                    'banner_link' => $this->request->getPost('txtLink', array('string', 'trim')),
                    'banner_order' => trim($this->request->getPost('txtOrder', 'trim')),
                    'banner_active' => trim($this->request->getPost('radActive')),
                    'banner_image' => $this->request->getPost('txtImage', array('string', 'trim')),
                    'banner_image_mobile' => $this->request->getPost('txtImageMobile', array('string', 'trim')),
                    'banner_start_time' => $this->request->getPost('txtStartTime', array('string', 'trim')),
                    'banner_end_time' => $this->request->getPost('txtEndTime', array('string', 'trim')),
                );

                if (empty($data_post['banner_start_time']) || $data_post['banner_start_time'] === 0) {
                    $data_post['banner_start_time'] = 0;
                } else {
                    $data_post['banner_start_time'] = $this->my->UTCTime(strtotime($data_post['banner_start_time']));
                    if (!is_numeric($data_post['banner_start_time']) || $data_post['banner_start_time'] <= 0) $messages['banner_start_time'] = "Format day time false";

                }
                if (empty($data_post['banner_end_time']) || $data_post['banner_end_time'] === 0) {
                    $data_post['banner_end_time'] = 0;
                } else {
                    $data_post['banner_end_time'] = $this->my->UTCTime(strtotime($data_post['banner_end_time']));
                    if (!is_numeric($data_post['banner_end_time']) || $data_post['banner_end_time'] <= 0) $messages['banner_end_time'] = "Format day time false";
                }
                if (!empty($_POST['dataBannerLocations'])) {
                    foreach ($_POST['dataBannerLocations'] as $country) {
                        $data_post['banner_locations'][] = $country;
                    }
                }
                if ($data_post['banner_controller'] === "") {
                    $messages['controller'] = 'Controller is required.';
                }
                if (empty($data_post['banner_order'])) {
                    $messages["order"] = "Order field is required.";
                } else if (!is_numeric($data_post["banner_order"])) {
                    $messages["order"] = "Order is not valid ";
                }
                if (empty($data_post['banner_image'])) {
                    $messages['image'] = 'Image field is required.';
                }
                if (empty($data_post['banner_image_mobile'])) {
                    $messages['image_mobile'] = 'Image mobile field is required.';
                }
            }
            if (empty($messages)) {
                switch ($save_mode) {
                    case ScLanguage::GENERAL:
                        $banner_controller = Banner::getValue($data_post['banner_controller'], Banner::CONTROLLER);
                        $banner_article_keyword = Banner::getValue($data_post['banner_controller'], Banner::SERVICE);
                        $banner_model->setBannerController($banner_controller);
                        $banner_model->setBannerArticleKeyword($banner_article_keyword);
                        $banner_model->setBannerLink($data_post['banner_link']);
                        $banner_model->setBannerOrder($data_post['banner_order']);
                        $banner_model->setBannerActive($data_post['banner_active']);
                        $banner_model->setBannerImage($data_post['banner_image']);
                        $banner_model->setBannerImageMobile($data_post['banner_image_mobile']);
                        $banner_model->setBannerLocations(strtolower(implode(',', $data_post['banner_locations'])));
                        $banner_model->setBannerStartTime($data_post['banner_start_time']);
                        $banner_model->setBannerEndTime($data_post['banner_end_time']);
                        $result = $banner_model->update();
                        $info = ScLanguage::GENERAL;
                        break;
                    case $this->globalVariable->defaultLanguage :
                        $banner_model->setBannerTitle($data_post['banner_title']);
                        $banner_model->setBannerSubtitle($data_post['banner_subtitle']);
                        $banner_model->setBannerContent($data_post['banner_content']);
                        $result = $banner_model->update();
                        $info = $arr_language[$save_mode];
                        break;
                    default:
                        $banner_lang_model = BannerLang::findFirstByIdAndLang($id_banner, $save_mode);
                        if (!$banner_lang_model) {
                            $banner_lang_model = new ScBannerLang();
                            $banner_lang_model->setBannerId($id_banner);
                            $banner_lang_model->setBannerLangCode($save_mode);
                        }
                        $banner_lang_model->setBannerTitle($data_post['banner_title']);
                        $banner_lang_model->setBannerSubtitle($data_post['banner_subtitle']);
                        $banner_lang_model->setBannerContent($data_post['banner_content']);
                        $result = $banner_lang_model->save();
                        $info = $arr_language[$save_mode];
                        break;
                }
                if ($result) {
                    $messages = array(
                        'message' => ucfirst($info . " Update Banner success"),
                        'typeMessage' => "success",
                    );
                } else {
                    $messages = array(
                        'message' => "Update Banner fail",
                        'typeMessage' => "error",
                    );
                }
            }
        }
        $item = array(
            'banner_id' => $id_banner,
            'banner_title' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['banner_title'] : $banner_model->getBannerTitle(),
            'banner_subtitle' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['banner_subtitle'] : $banner_model->getBannerSubtitle(),
            'banner_content' => ($save_mode === $this->globalVariable->defaultLanguage) ? $data_post['banner_content'] : $banner_model->getBannerContent(),

        );
        $arr_translate[$lang_default] = $item;
        $arr_banner_lang = ScBannerLang::findById($id_banner);
        foreach ($arr_banner_lang as $banner_language) {
            $item = array(
                'banner_id' => $banner_language->getBannerId(),
                'banner_title' => ($save_mode === $banner_language->getBannerLangCode()) ? $data_post['banner_title'] : $banner_language->getBannerTitle(),
                'banner_subtitle' => ($save_mode === $banner_language->getBannerLangCode()) ? $data_post['banner_subtitle'] : $banner_language->getBannerSubtitle(),
                'banner_content' => ($save_mode === $banner_language->getBannerLangCode()) ? $data_post['banner_content'] : $banner_language->getBannerContent(),

            );
            $arr_translate[$banner_language->getBannerLangCode()] = $item;
        }
        if (!isset($arr_translate[$save_mode]) && isset($arr_language[$save_mode])) {
            $item = array(
                'banner_id' => -1,
                'banner_title' => $data_post['banner_title'],
                'banner_subtitle' => $data_post['banner_subtitle'],
                'banner_content' => $data_post['banner_content'],
            );
            $arr_translate[$save_mode] = $item;
        }
        $formData = array(
            'banner_id' => $banner_model->getBannerId(),
            'banner_controller' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_controller'] : Banner::getItem($banner_model->getBannerController(), $banner_model->getBannerArticleKeyword()),
            'banner_link' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_link'] : $banner_model->getBannerLink(),
            'banner_order' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_order'] : $banner_model->getBannerOrder(),
            'banner_active' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_active'] : $banner_model->getBannerActive(),
            'banner_image' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_image'] : $banner_model->getBannerImage(),
            'banner_image_mobile' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_image_mobile'] : $banner_model->getBannerImageMobile(),
            'banner_locations' => ($save_mode === ScLanguage::GENERAL) ? implode(',', $data_post['banner_locations']) : $banner_model->getBannerLocations(),
            'banner_start_time' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_start_time'] : $banner_model->getBannerStartTime(),
            'banner_end_time' => ($save_mode === ScLanguage::GENERAL) ? $data_post['banner_end_time'] : $banner_model->getBannerEndTime(),
            'arr_translate' => $arr_translate,
            'arr_language' => $arr_language,
            'lang_default' => $lang_default,
            'lang_current' => $lang_current
        );

        $banner = new Banner();
        $select_controller = $banner->getControllerCombobox($formData['banner_controller']);
        $list_banner_locations = Country::getAllCountries();
        $list_banner_locations_selected = explode(',', strtoupper($formData['banner_locations']));

        $messages['status'] = 'border-red';
        $this->view->setVars(array(
            'formData' => $formData,
            'messages' => $messages,
            'select_controller' => $select_controller,
            'list_banner_locations_selected' => $list_banner_locations_selected,
            'list_banner_locations' => $list_banner_locations
        ));
    }

    public function deleteAction()
    {
        $banner_checked = $this->request->getPost("item");
        $msg_result = array();
        if (!empty($banner_checked)) {
            $total = 0;
            foreach ($banner_checked as $id) {
                $banner_item = ScBanner::findFirstById($id);
                if ($banner_item) {
                    if ($banner_item->delete() === false) {
                        $message_delete = 'Can\'t delete banner Title = ' . $banner_item->getBannerTitle();
                        $msg_result['status'] = 'error';
                        $msg_result['msg'] = $message_delete;
                    } else {
                        $total ++;
                        BannerLang::deleteById($id);
                    }
                }
            }
            if ($total > 0) {
                $message_delete = 'Delete ' . $total . ' banner successfully.';
                $msg_result['status'] = 'success';
                $msg_result['msg'] = $message_delete;
            }
            $this->session->set('msg_result', $msg_result);
            return $this->response->redirect("/banner");
        }
    }
}

