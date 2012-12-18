<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Admin
 * Date: 10/11/12
 * Time: 11:45 PM
 * To change this template use File | Settings | File Templates.
 */


class SM_Planet_Helper_Data extends Mage_Core_Helper_Abstract {
    public function resizeImg($fileName, $width, $height = '')
    {
        $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $imageURL = $folderURL . $fileName;

        $basePath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $fileName;
        $newPath = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . "resized" . DS . $fileName;
        //if width empty then return original size image's URL
        if ($width != '') {
            //if image has already resized then just return URL
            if (file_exists($basePath) && is_file($basePath) && !file_exists($newPath)) {
                $imageObj = new Varien_Image($basePath);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->keepFrame(FALSE);
                $imageObj->resize($width, $height);
                $imageObj->save($newPath);
            }
            $resizedURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . "resized" . DS . $fileName;
        } else {
            $resizedURL = $imageURL;
        }
        return $resizedURL;
    }
    // hardcode
    public function getRejectReason() {
        $reason = array();

        $reason['Out of stock'] = 'Out of stock';
        $reason['Cannot fulfill'] = 'Cannot fulfill';
        $reason['Don\'t wish to accept order'] = 'Don\'t wish to accept order';

        return $reason;
    }

    public function getDecision() {
        $decision = array();

        $decision[] = 'Confirm receipt and acceptance';
        $decision[] = 'Rejection of the order';

        return $decision;
    }
    
	public function getHashKey($passwordHash){
		$privateKey = 'XBfgSHTDADDTRFCGH';
		return md5($privateKey.'::'.$passwordHash);
	}
	
	public function checkHashKey($userId,$hashKey){
		$user = Mage::getModel('admin/user')->load($userId);
		$passwordHash = $user->getPassword();
		if($this->getHashKey($passwordHash) == $hashKey){
			return true;
		}
		return false;
	}
}