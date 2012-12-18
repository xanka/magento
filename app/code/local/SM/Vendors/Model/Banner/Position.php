<?php

class SM_Vendors_Model_Banner_Position  {

    public function toOptionArray(){
    	$options = array(
    		array(
    			'label' => 'Top',
    			'value' => 'top'
    		),
//    		array(
//    			'label' => 'Left',
//    			'value' => 'left'
//    		),
    		array(
    			'label' => 'Bottom',
    			'value' => 'bottom'
    		),
//    		array(
//    			'label' => 'Right',
//    			'value' => 'right'
//    		),
    	);
    	return $options;
    }
}