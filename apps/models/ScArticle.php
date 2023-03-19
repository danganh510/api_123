<?php

namespace Score\Models;

class ScArticle extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $article_id;

    /**
     *
     * @var string
     * @Primary
     * @Column(type="string", length=5, nullable=false)
     */
    protected $article_location_country_code;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $article_type_id;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $article_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    protected $article_icon;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $article_keyword;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $article_title;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $article_meta_keyword;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $article_meta_description;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=true)
     */
    protected $article_meta_image;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $article_summary;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $article_content;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $article_content_mobile;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $article_content_app;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    protected $article_order;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $article_active;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $article_is_home;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $article_insert_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    protected $article_update_time;

    /**
     * Method to set the value of field article_id
     *
     * @param integer $article_id
     * @return $this
     */
    public function setArticleId($article_id)
    {
        $this->article_id = $article_id;

        return $this;
    }

    /**
     * Method to set the value of field article_location_country_code
     *
     * @param string $article_location_country_code
     * @return $this
     */
    public function setArticleLocationCountryCode($article_location_country_code)
    {
        $this->article_location_country_code = $article_location_country_code;

        return $this;
    }

    /**
     * Method to set the value of field article_type_id
     *
     * @param integer $article_type_id
     * @return $this
     */
    public function setArticleTypeId($article_type_id)
    {
        $this->article_type_id = $article_type_id;

        return $this;
    }

    /**
     * Method to set the value of field article_name
     *
     * @param string $article_name
     * @return $this
     */
    public function setArticleName($article_name)
    {
        $this->article_name = $article_name;

        return $this;
    }

    /**
     * Method to set the value of field article_icon
     *
     * @param string $article_icon
     * @return $this
     */
    public function setArticleIcon($article_icon)
    {
        $this->article_icon = $article_icon;

        return $this;
    }

    /**
     * Method to set the value of field article_keyword
     *
     * @param string $article_keyword
     * @return $this
     */
    public function setArticleKeyword($article_keyword)
    {
        $this->article_keyword = $article_keyword;

        return $this;
    }

    /**
     * Method to set the value of field article_title
     *
     * @param string $article_title
     * @return $this
     */
    public function setArticleTitle($article_title)
    {
        $this->article_title = $article_title;

        return $this;
    }

    /**
     * Method to set the value of field article_meta_keyword
     *
     * @param string $article_meta_keyword
     * @return $this
     */
    public function setArticleMetaKeyword($article_meta_keyword)
    {
        $this->article_meta_keyword = $article_meta_keyword;

        return $this;
    }

    /**
     * Method to set the value of field article_meta_description
     *
     * @param string $article_meta_description
     * @return $this
     */
    public function setArticleMetaDescription($article_meta_description)
    {
        $this->article_meta_description = $article_meta_description;

        return $this;
    }

    /**
     * Method to set the value of field article_meta_image
     *
     * @param string $article_meta_image
     * @return $this
     */
    public function setArticleMetaImage($article_meta_image)
    {
        $this->article_meta_image = $article_meta_image;

        return $this;
    }

    /**
     * Method to set the value of field article_summary
     *
     * @param string $article_summary
     * @return $this
     */
    public function setArticleSummary($article_summary)
    {
        $this->article_summary = $article_summary;

        return $this;
    }

    /**
     * Method to set the value of field article_content
     *
     * @param string $article_content
     * @return $this
     */
    public function setArticleContent($article_content)
    {
        $this->article_content = $article_content;

        return $this;
    }
    /**
     * Method to set the value of field article_content_mobile
     *
     * @param string $article_content_mobile
     * @return $this
     */
    public function setArticleContentMobile($article_content_mobile)
    {
        $this->article_content_mobile = $article_content_mobile;

        return $this;
    }
    /**
     * Method to set the value of field article_content_app
     *
     * @param string $article_content_app
     * @return $this
     */
    public function setArticleContentApp($article_content_app)
    {
        $this->article_content_app = $article_content_app;

        return $this;
    }

    /**
     * Method to set the value of field article_order
     *
     * @param integer $article_order
     * @return $this
     */
    public function setArticleOrder($article_order)
    {
        $this->article_order = $article_order;

        return $this;
    }

    /**
     * Method to set the value of field article_active
     *
     * @param string $article_active
     * @return $this
     */
    public function setArticleActive($article_active)
    {
        $this->article_active = $article_active;

        return $this;
    }

    /**
     * Method to set the value of field article_is_home
     *
     * @param string $article_is_home
     * @return $this
     */
    public function setArticleIsHome($article_is_home)
    {
        $this->article_is_home = $article_is_home;

        return $this;
    }

    /**
     * Method to set the value of field article_insert_time
     *
     * @param integer $article_insert_time
     * @return $this
     */
    public function setArticleInsertTime($article_insert_time)
    {
        $this->article_insert_time = $article_insert_time;

        return $this;
    }

    /**
     * Method to set the value of field article_update_time
     *
     * @param integer $article_update_time
     * @return $this
     */
    public function setArticleUpdateTime($article_update_time)
    {
        $this->article_update_time = $article_update_time;

        return $this;
    }

    /**
     * Returns the value of field article_id
     *
     * @return integer
     */
    public function getArticleId()
    {
        return $this->article_id;
    }

    /**
     * Returns the value of field article_location_country_code
     *
     * @return string
     */
    public function getArticleLocationCountryCode()
    {
        return $this->article_location_country_code;
    }

    /**
     * Returns the value of field article_type_id
     *
     * @return integer
     */
    public function getArticleTypeId()
    {
        return $this->article_type_id;
    }

    /**
     * Returns the value of field article_name
     *
     * @return string
     */
    public function getArticleName()
    {
        return $this->article_name;
    }

    /**
     * Returns the value of field article_icon
     *
     * @return string
     */
    public function getArticleIcon()
    {
        return $this->article_icon;
    }

    /**
     * Returns the value of field article_keyword
     *
     * @return string
     */
    public function getArticleKeyword()
    {
        return $this->article_keyword;
    }

    /**
     * Returns the value of field article_title
     *
     * @return string
     */
    public function getArticleTitle()
    {
        return $this->article_title;
    }

    /**
     * Returns the value of field article_meta_keyword
     *
     * @return string
     */
    public function getArticleMetaKeyword()
    {
        return $this->article_meta_keyword;
    }

    /**
     * Returns the value of field article_meta_description
     *
     * @return string
     */
    public function getArticleMetaDescription()
    {
        return $this->article_meta_description;
    }

    /**
     * Returns the value of field article_meta_image
     *
     * @return string
     */
    public function getArticleMetaImage()
    {
        return $this->article_meta_image;
    }

    /**
     * Returns the value of field article_summary
     *
     * @return string
     */
    public function getArticleSummary()
    {
        return $this->article_summary;
    }

    /**
     * Returns the value of field article_content
     *
     * @return string
     */
    public function getArticleContent()
    {
        return $this->article_content;
    }

    /**
     * Returns the value of field article_content_mobile
     *
     * @return string
     */
    public function getArticleContentMobile()
    {
        return $this->article_content_mobile;
    }
    /**
     * Returns the value of field article_content_app
     *
     * @return string
     */
    public function getArticleContentApp()
    {
        return $this->article_content_app;
    }

    /**
     * Returns the value of field article_order
     *
     * @return integer
     */
    public function getArticleOrder()
    {
        return $this->article_order;
    }

    /**
     * Returns the value of field article_active
     *
     * @return string
     */
    public function getArticleActive()
    {
        return $this->article_active;
    }

    /**
     * Returns the value of field article_is_home
     *
     * @return string
     */
    public function getArticleIsHome()
    {
        return $this->article_is_home;
    }

    /**
     * Returns the value of field article_insert_time
     *
     * @return integer
     */
    public function getArticleInsertTime()
    {
        return $this->article_insert_time;
    }

    /**
     * Returns the value of field article_update_time
     *
     * @return integer
     */
    public function getArticleUpdateTime()
    {
        return $this->article_update_time;
    }

    /**
     * Initialize method for model.
     */
//    public function initialize()
//    {
//        $this->setSchema("travelner");
//    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'sc_article';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScArticle[]|ScArticle
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return ScArticle
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
