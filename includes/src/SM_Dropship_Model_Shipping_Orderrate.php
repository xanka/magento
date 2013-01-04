<?php
class SM_Dropship_Model_Shipping_Orderrate extends Mage_Core_Model_Abstract {
	public function _construct() {
        parent::_construct();
        $this->_init('smdropship/shipping_orderrate');
    }
    
	public function load($id, $field=null){
		return $post = parent::load($id, $field);
    }
    
    public function getRates(){
    	$rates = $this->getData('rates');
    	if(!empty($rates)){
    		$rates = unserialize($rates);
    		$rates = array_filter($rates);
    		return $rates;
    	}
    	return '';
    }
    
    public function getRateRanger(){
    	$rates = $this->getRates();
    	$ratesConvert =array();
    	$ranger = array();
    	$ratesConvert[] = array(
    		'order_amount'=>0,
    		'shipping_price' =>0
    	);
    	foreach($rates as $rate){
    		$ratesConvert[] = $rate;
    	}
    	

    	usort($ratesConvert, array($this, "cmp_array"));
    	
    	for($i = 0 ; $i < count($ratesConvert) - 1; $i++){
    		$ranger[] = array(
    			'min_amount' => floatval($ratesConvert[$i]['order_amount']),
    			'max_amount' => floatval($ratesConvert[$i+1]['order_amount']),
    			'shipping_price' => floatval($ratesConvert[$i+1]['shipping_price'])
    		);
    	}
    	return $ranger;
    }
    
    static function cmp_array($a,$b){
    	return floatval($a['order_amount']) > floatval($b['order_amount']);
    }
}