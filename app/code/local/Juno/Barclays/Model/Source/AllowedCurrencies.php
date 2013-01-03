<?php
/**
 *
 *
 *
 */
class Juno_Barclays_Model_Source_AllowedCurrencies
{
    public function toOptionArray()
    {
        return array(
            array('value' =>'AUD', 'label' => Mage::helper('barclays')->__('Australian Dollar')),
            array('value' =>'CAD', 'label' => Mage::helper('barclays')->__('Canadian Dollar')),
			array('value' =>'CNY', 'label' => Mage::helper('barclays')->__('China Yuan Renimbi')),
			array('value' =>'CYR', 'label' => Mage::helper('barclays')->__('Cyprus Pound')),
			array('value' =>'CZK', 'label' => Mage::helper('barclays')->__('Czech Koruna')),
			array('value' =>'DKK', 'label' => Mage::helper('barclays')->__('Danish Krone')),
			array('value' =>'EEK', 'label' => Mage::helper('barclays')->__('Estonian Kroon')),
			array('value' =>'EUR', 'label' => Mage::helper('barclays')->__('Euro')),
			array('value' =>'HKD', 'label' => Mage::helper('barclays')->__('Hong Kong Dollar')),
			array('value' =>'HUF', 'label' => Mage::helper('barclays')->__('Hungarian Forint')),
			array('value' =>'ISK', 'label' => Mage::helper('barclays')->__('Iceland Krona')),
			array('value' =>'INR', 'label' => Mage::helper('barclays')->__('Indian Rupee')),
			array('value' =>'ILS', 'label' => Mage::helper('barclays')->__('Israel New Shequel')),
			array('value' =>'JPY', 'label' => Mage::helper('barclays')->__('Japanese Yen')),
            array('value' =>'LVL', 'label' => Mage::helper('barclays')->__('Latvian Lat')),
			array('value' =>'LTL', 'label' => Mage::helper('barclays')->__('Lithuanian Litas')),
			array('value' =>'MTL', 'label' => Mage::helper('barclays')->__('Maltese Lira')),
			array('value' =>'MAL', 'label' => Mage::helper('barclays')->__('Moroccan Dinah')),
			array('value' =>'NZD', 'label' => Mage::helper('barclays')->__('New Zealand Dollar')),
			array('value' =>'NOK', 'label' => Mage::helper('barclays')->__('Norwegian Krone')),
			array('value' =>'PLN', 'label' => Mage::helper('barclays')->__('Polish Zlotych')),
			array('value' =>'RUB', 'label' => Mage::helper('barclays')->__('Russian Ruble')),
			array('value' =>'SGD', 'label' => Mage::helper('barclays')->__('Singapore Dollar')),
			array('value' =>'SKK', 'label' => Mage::helper('barclays')->__('Slovak Koruna')),
			array('value' =>'KRW', 'label' => Mage::helper('barclays')->__('South Korean')),
			array('value' =>'SEK', 'label' => Mage::helper('barclays')->__('Swedish Krona')),
			array('value' =>'CHF', 'label' => Mage::helper('barclays')->__('Swiss Francs')),
			array('value' =>'GBP', 'label' => Mage::helper('barclays')->__('Sterling')),
			array('value' =>'USD', 'label' => Mage::helper('barclays')->__('US Dollars')),
			array('value' =>'SAR', 'label' => Mage::helper('barclays')->__('Saudi Riyal')),
			array('value' =>'ZAR', 'label' => Mage::helper('barclays')->__('South African Rand')),
			array('value' =>'THB', 'label' => Mage::helper('barclays')->__('Thai Baht')),
			array('value' =>'AED', 'label' => Mage::helper('barclays')->__('United Arab Emirates dirham'))
        );
    }
}