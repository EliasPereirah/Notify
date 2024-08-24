<?php
namespace App;
class SendGrid
{
    private string $api_key;
    public function setApiKey($api_key): void
    {
        $this->api_key = $api_key;
    }
    public function sendMail($from_name, $from_email, $to_name, $to_email, $subject, $body, $is_html = false):bool
    {
        if(empty($this->api_key)){
            // echo "Antes de chamar sendMail(), set uma chave API chamando setApiKey()<br>\n";
            return false;
        }
        $cnt_type = 'text/plain';
        if($is_html){
            $cnt_type = 'text/html';
        }
        $apiKey = $this->api_key;
        $data = array(
            'personalizations' => array(
                array(
                    'to' => array(
                        array(
                            'email' =>  $to_email,
                            'name' => $to_name
                        )
                    )
                )
            ),
            'from' => array(
                'email' => $from_email,
                "name" => $from_name
            ),
            'subject' => $subject,
            'content' => array(
                array(
                    'type' => $cnt_type,
                    'value' => $body
                )
            )
        );
        $postData = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpCode == 200 OR $httpCode == 202){
            return true;
        }
        return false;
    }

}