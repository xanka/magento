<?php
/**
 * Date: 10/25/12
 * Time: 8:22 PM
 */

class SM_Planet_CommunityController extends Mage_Core_Controller_Front_Action {
    public function indexAction()    {
        $this->loadLayout();
        $this->renderLayout();
    }
}