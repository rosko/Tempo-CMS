<?php
class Messenger extends CApplicationComponent
{
    public $email=array();

    public function send($via,$to,$subject,$message,$params=array())
    {        
        if (!is_array($to)) $to = array($to);
        
        if ($via == 'email') {
            $mailer = Yii::createComponent('application.extensions.mailer.EMailer');
            foreach ($this->email as $k => $v) {
                $mailer->$k = mb_convert_encoding($v, 'CP-1251', 'UTF-8');
            }
            foreach ($params as $k => $v) {
                $mailer->$k = mb_convert_encoding($v, 'CP-1251', 'UTF-8');
            }
            foreach ($to as $v) {
                $mailer->AddAddress($v);
            }
            $mailer->AddReplyTo($mailer->From);
            $mailer->CharSet = 'WINDOWS-1251';
            $mailer->SetLanguage(Yii::app()->language);
            $mailer->Subject = mb_convert_encoding($subject, 'CP-1251', 'UTF-8');
            $mailer->MsgHTML(mb_convert_encoding($message, 'CP-1251', 'UTF-8'), Yii::getPathOfAlias('webroot'));
            //try {
                $mailer->Send();
            //} catch (phpmailerException $e) {
            //}
        }
    }
}

?>
