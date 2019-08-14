<?php
/**
 * Magento SendGrid SMTP send
 *
 * @category   SendGrid
 * @package    Sendgrid_Mailtransport
 * @copyright  Copyright (c) 2013 SendGrid.com
 * @author     Reseller Team ( www.sendgrid.com )
 */
class Sendgrid_Mailtransport_Helper_Data extends Mage_Core_Helper_Abstract
{
  public function getTransport()
  {
    $config = array(
      'ssl'      => 'tls', 
      'port'     => 587, 
      'auth'     => 'login', 
      'username' => Mage::getStoreConfig('sendgridsettings/general/sendgrid_username'), 
      'password' => Mage::getStoreConfig('sendgridsettings/general/sendgrid_password'));
    
    $transport = new Zend_Mail_Transport_Smtp('smtp.sendgrid.net', $config);

    return $transport;
  }
}

