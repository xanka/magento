<?php
/**
 * Date: 11/17/12
 * Time: 1:51 PM
 */

class SM_Planet_AboutmeController extends Mage_Core_Controller_Front_Action {
    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction() {
        $params = $this->getRequest()->getParams();
        $customer = Mage::helper('customer')->getCustomer();
        $answers = Mage::getModel('web/answer')->getCollection();

        foreach($params as $value=>$_param) {
            $answered = false;
            // check if question has been answer before
            foreach($answers as $_answer) {
                $_answer = Mage::getModel('web/answer')->load($_answer->getId());
                if (($_answer->getData('question_id') == $value) && ($_answer->getData('customer_id') == $customer->getId())) {
                    $_answer->setData('answer',$_param);
                    $_answer->save();
                    $answered = true;
                    break; // move to next question
                }
            }

            if (!$answered) { // question hasnt answer before , then create new answer
                $model = Mage::getModel('web/answer');
                $question = Mage::getModel('web/question')->load($value);
                $model->setData('customer_id',$customer->getId());
                $model->setData('customer',$customer->getEmail());
                $model->setData('question_id',$value);
                $model->setData('question',$question->getData('question'));
                $model->setData('answer',$_param);
                $model->save();
            }
        }
        $this->loadLayout();
        $this->renderLayout();
    }
}