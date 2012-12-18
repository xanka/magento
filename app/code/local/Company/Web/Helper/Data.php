<?php

class Company_Web_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function loadAnswer($id) {
        $answer = array();
        $question = Mage::getModel('web/question')->load($id);
        if (is_null($question)) {
            return;
        }

        $_answer = $question->getData('answer');
        $answer = explode(';',$_answer);

        return $answer;
    }
}