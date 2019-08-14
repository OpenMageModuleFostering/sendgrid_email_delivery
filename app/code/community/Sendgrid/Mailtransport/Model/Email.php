<?php
/**
 * Magento SendGrid SMTP send
 *
 * @category   SendGrid
 * @package    Sendgrid_Mailtransport
 * @copyright  Copyright (c) 2013 SendGrid.com
 * @author     Reseller Team ( www.sendgrid.com )
 */
class Sendgrid_Mailtransport_Model_Email extends Mage_Core_Model_Email
{
  public function send()
  {
    if (!Mage::getStoreConfig('sendgridsettings/general/sendgrid_enabled'))
    {
      return parent::send();
    }

    if (Mage::getStoreConfigFlag('system/smtp/disable')) 
    {
      return $this;
    }

    $mail = new Zend_Mail();

    if (strtolower($this->getType()) == 'html') 
    {
      $mail->setBodyHtml($this->getBody());
    }
    else 
    {
      $mail->setBodyText($this->getBody());
    }

    $mail->setFrom($this->getFromEmail(), $this->getFromName())
      ->addTo($this->getToEmail(), $this->getToName())
      ->setSubject($this->getSubject())
      ->addHeader('X-SMTPAPI', '{"category": "magento_sendgrid_plugin"}', true);

    $transport = Mage::helper('mailtransport')->getTransport();

    $mail->send($transport);

    return $this;
  }
}
