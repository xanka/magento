<?php
class SM_Vendors_Model_System_Config_Source_Role
{
    public function toOptionArray()
    {
    	$collection = Mage::getResourceModel('admin/role_collection');
        $collection->setRolesFilter();
		$options = array();
		foreach($collection as $role){
			
			$options[] = array(
					'value'=> $role->getId(), 
					'label'=> $role->getRoleName()
			);
		}
        return $options;
    }
}
