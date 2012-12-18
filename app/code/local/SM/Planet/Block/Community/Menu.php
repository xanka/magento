<?php
/**
 * Date: 10/25/12
 * Time: 12:16 AM
 */

class SM_Planet_Block_Community_Menu extends Mage_Core_Block_Template {
    var $page = array();
    var $pageImage = array();

    public function __construct() {
        // hardcode
        $listPage = array(
            'about-us'
        );

        $listImage = array(
            'http://www.planet-v.co.uk/wordpress/wp-content/uploads/2012/09/180x180_fitbox-bird_about.png',
            'http://www.planet-v.co.uk/wordpress/wp-content/uploads/2012/09/birds_team.png',
            'http://www.planet-v.co.uk/wordpress/wp-content/uploads/2012/09/bird_recipes.png',
            'http://www.planet-v.co.uk/wordpress/wp-content/uploads/2012/09/bird_news.png',
            'http://www.planet-v.co.uk/wordpress/wp-content/uploads/2012/09/bird_story.png',
            'http://www.planet-v.co.uk/wordpress/wp-content/uploads/2012/09/bird_help.png'
        );

        $i = 0;
        foreach($listPage as $value) {
            $this->addPage($value);
            $this->addImage($value,$listImage[$i++]);
        }

    }

    public function getPage() {
        return $this->page;
    }

    public function getPageImage() {
        return $this->pageImage;
    }

    protected function addPage($identify) {
        $this->page[] = $identify;
        return $this;
    }
    protected function addImage($identify,$imageUrl) {
        $this->pageImage[$identify] = $imageUrl;
        return $this;
    }




}