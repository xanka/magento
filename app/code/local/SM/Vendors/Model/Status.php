<?php
class SM_Vendors_Model_Status extends Varien_Object {
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    static public function getOptionArray() {
        return array(
            self::STATUS_ENABLED => Mage::helper('smvendors')->__('Enabled'),
            self::STATUS_DISABLED => Mage::helper('smvendors')->__('Disabled')
        );
    }

    static public function getAnimationArray() {
        $animations = array();
        $animations = array(
            array(
                'value' => 'Fade/Appear',
                'label' => Mage::helper('smvendors')->__('Fade / Appear'),
            ),
            array(
                'value' => 'Shake',
                'label' => Mage::helper('smvendors')->__('Shake'),
            ),

            array(
                'value' => 'Pulsate',
                'label' => Mage::helper('smvendors')->__('Pulsate'),
            ),
            array(
                'value' => 'Puff',
                'label' => Mage::helper('smvendors')->__('Puff'),
            ),
            array(
                'value' => 'Grow',
                'label' => Mage::helper('smvendors')->__('Grow'),
            ),
            array(
                'value' => 'Shrink',
                'label' => Mage::helper('smvendors')->__('Shrink'),
            ),
            array(
                'value' => 'Fold',
                'label' => Mage::helper('smvendors')->__('Fold'),
            ),         
            array(
                'value' => 'Squish',
                'label' => Mage::helper('smvendors')->__('Squish'),
            ),
   
            array(
                'value' => 'BlindUp',
                'label' => Mage::helper('smvendors')->__('Blindup'),
            ),
             array(
                'value' => 'BlindDown',
                'label' => Mage::helper('smvendors')->__('BlindDown'),
            ),            
            array(
                'value' => 'DropOut',
                'label' => Mage::helper('smvendors')->__('DropOut'),
            ),
        );
        array_unshift($animations, array('label' => '--Select--', 'value' => ''));
        return $animations;
    }

    static public function getPreAnimationArray() {
        $animations = array();
        $animations = array(

            array(
                'value' => 'Image Slide Show',
                'label' => Mage::helper('smvendors')->__('Image Slide Show'),
            ),
            array(
                'value' => 'Text Fade Banner',
                'label' => Mage::helper('smvendors')->__('Text Fade Banner'),
            ),
            array(
                'value' => 'Square Transition Banner',
                'label' => Mage::helper('smvendors')->__('Square Transition Banner'),
            ),
            array(
                'value' => 'Play And Pause Slide Show',
                'label' => Mage::helper('smvendors')->__('Play And Pause Slide Show'),
            ),
            array(
                'value' => 'Numbered Banner',
                'label' => Mage::helper('smvendors')->__('Numbered Banner'),
            ),
            array(
                'value' => 'image glider',
                'label' => Mage::helper('smvendors')->__('Image Slider'),
            ),
            array(
                'value' => 'image vertical slider',
                'label' => Mage::helper('smvendors')->__('Image Vertical Slider'),
            ),

             /*array(
                'value' => 'image spring slider',
                'label' => Mage::helper('smvendors')->__('Image Spring Slider'),
            ),*/
        );
        array_unshift($animations, array('label' => '--Select--', 'value' => ''));
        return $animations;
    }

}