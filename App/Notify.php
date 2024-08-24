<?php

namespace App;

use Exception;

class Notify
{
    private EvolutionAPI $EvolutionAPI;
    private string $instance_name;
    private string $notify_number;
    private MailService $MailService;

    public function __construct($instance_name = null, $api_key = null)
    {
        $this->instance_name = $instance_name ?? $_ENV['EVO_INSTANCE_NAME'];
        $this->notify_number = $_ENV['ZAP_NOTIFY_NUMBER'];
        $this->EvolutionAPI = new EvolutionAPI($this->instance_name, $api_key);
        $this->MailService = new \App\MailService();

    }


    public function senMail($subject, $body, $to_mail = null):bool
    {
        $from_mail = $_ENV['SENDGRID_MAIL'];
        $from_name = $_ENV['PROJECT_NAME'];
        $to_mail = $to_mail ?? $_ENV['SENDGRID_MAIL'];
        $to_name = $_ENV['ADM_NAME'] ?? '';
        $mail_api_key = $_ENV['SENDGRID_API_KEY'];
        return $this->MailService->sendMail($mail_api_key, $from_name, $from_mail, $to_name, $to_mail, $subject, $body);
    }


    /**
     * Envia uma notificação
     * @param string $message Mensagem a ser enviada
     * @param string|null $number (Opcional) Número que receberá a mensagem (default número em .env)
     **/
    public function sendZap(string $message, string|null $number = null): void
    {
        $number = $number ?? $this->notify_number;
        try {
            $result = $this->EvolutionAPI->sendMessage($number, $message);
            print_r($result);
        } catch (Exception $e) {
            echo "Erro: " . $e->getMessage();
        }
    }

}