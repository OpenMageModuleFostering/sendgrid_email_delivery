<?php
/**
 * Magento SendGrid SMTP send
 *
 * @category   SendGrid
 * @package    Sendgrid_Mailtransport
 * @copyright  Copyright (c) 2013 SendGrid.com
 * @author     Reseller Team ( www.sendgrid.com )
 */
class Sendgrid_Mailtransport_Model_Email_Template extends Mage_Core_Model_Email_Template
{
  /**
   * Send mail to recipient
   *
   * @param   array|string       $email        E-mail(s)
   * @param   array|string|null  $name         receiver name(s)
   * @param   array              $variables    template variables
   * @return  boolean
   */
  public function send($email, $name = null, array $variables = array())
  {
    Mage::log("Sendgrid send: " . Mage::getStoreConfig('sendgridsettings/general/sendgrid_enabled'));
    if (!Mage::getStoreConfig('sendgridsettings/general/sendgrid_enabled'))
    {
      return parent::send($email, $name, $variables);
    }

    if (!$this->isValidForSend())
    {
      Mage::logException(new Exception('This letter cannot be sent.')); // translation is intentionally omitted

      return false;
    }

    $emails = array_values((array)$email);
    $names = is_array($name) ? $name : (array)$name;
    $names = array_values($names);
    
    foreach ($emails as $key => $email) 
    {
      if (!isset($names[$key])) 
      {
        $names[$key] = substr($email, 0, strpos($email, '@'));
      }
    }

    $variables['email'] = reset($emails);
    $variables['name'] = reset($names);

    ini_set('SMTP', Mage::getStoreConfig('system/smtp/host'));
    ini_set('smtp_port', Mage::getStoreConfig('system/smtp/port'));

    $mail = $this->getMail();

    $setReturnPath = Mage::getStoreConfig(self::XML_PATH_SENDING_SET_RETURN_PATH);
    switch ($setReturnPath) 
    {
      case 1:
      {
        $returnPathEmail = $this->getSenderEmail();
        
        break;
      }
      case 2:
      {
        $returnPathEmail = Mage::getStoreConfig(self::XML_PATH_SENDING_RETURN_PATH_EMAIL);
        
        break;
      }
      default:
      {
        $returnPathEmail = null;
        
        break;
      }
    }

    if ($returnPathEmail !== null) 
    {
      $mailTransport = new Zend_Mail_Transport_Sendmail("-f" . $returnPathEmail);
      Zend_Mail::setDefaultTransport($mailTransport);
    }

    foreach ($emails as $key => $email) 
    {
      $mail->addTo($email, '=?utf-8?B?' . base64_encode($names[$key]) . '?=');
    }

    $this->setUseAbsoluteLinks(true);
    $text = $this->getProcessedTemplate($variables, true);

    if($this->isPlain()) 
    {
      $mail->setBodyText($text);
    } 
    else 
    {
      $mail->setBodyHTML($text);
    }

    $mail->setSubject('=?utf-8?B?' . base64_encode($this->getProcessedTemplateSubject($variables)) . '?=')
      ->setFrom($this->getSenderEmail(), $this->getSenderName())
      ->addHeader('X-SMTPAPI', '{"category": "magento_sendgrid_plugin"}', true);

    try 
    {
      $transport = Mage::helper('mailtransport')->getTransport();
      $mail->send($transport);
      $this->_mail = null;
    }
    catch (Exception $e) 
    {
      $this->_mail = null;
      Mage::logException($e);

      return false;
    }

    return true;
  }
}
