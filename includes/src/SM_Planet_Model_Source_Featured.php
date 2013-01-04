<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 10/14/12
 * Time: 1:25 AM
 * To change this template use File | Settings | File Templates.
 */

class SM_Planet_Model_Source_Featured extends Mage_Eav_Model_Entity_Attribute_Source_Table {
    public function getAllOptions()
    {


            $options[] = array(
                'value' => '1',
                'label' => 'Yes'
            );
            $options[] = array(
                'value' => '0',
                'label' => 'No'
            );

        return $options;
    }
}