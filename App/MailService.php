<?php
namespace App;

## Ainda não implementado
/**
 * Essa classe sempre irá chamar retornar outra classe de envio de e-mails
 * Irá facilitar caso, queira no futuro usar outro serviço de e-mail
 * Inicialmente foi usado SendGrind
 *
 **/

class MailService
{
    /**
     * Esse método envia e-mail
     * @return bool
     */
    public function sendMail($api_key, $from_name, $from_email, $to_name, $to_email, $subject, $body, $is_html = false):bool
    {
        $Mail = new SendGrid(); // se for usar outro serviço crie a nova classe com o método sendMail
        $Mail->setApiKey($api_key);
        return $Mail->sendMail($from_name, $from_email, $to_name, $to_email, $subject, $body, $is_html);
    }

}