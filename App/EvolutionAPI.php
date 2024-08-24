<?php

namespace App;

use Exception;

class EvolutionAPI
{
    private string $api_key;
    private $curl;

    private string $instanceName;

    public function __construct(string|null $instanceName = null, string|null $api_key = null)
    {
        $this->api_key =  $api_key ?? $_ENV['EVOLUTION_API_KEY'];
        $this->instanceName = $instanceName ?? $_ENV['EVO_INSTANCE_NAME'];
        $this->curl = curl_init();
    }

    /**
     * @throws Exception
     */
    public function createInstance(string $name, $number, $token)
    {
        $array = [
            "instanceName" => $name,
            "token" => $token,
            "qrcode" => true,
            "number" => $number,
            "integration" => "WHATSAPP-BAILEYS",
            "webhook" => EVO_WEBHOOK,
            "webhook_by_events" => true,
            "events" => [
                "MESSAGES_UPSERT"
//                ,"APPLICATION_STARTUP", "QRCODE_UPDATED", "MESSAGES_SET", "MESSAGES_UPSERT",
//                "MESSAGES_UPDATE", "MESSAGES_DELETE", "SEND_MESSAGE", "CONTACTS_SET", "CONTACTS_UPSERT", "CONTACTS_UPDATE",
//                "PRESENCE_UPDATE", "CHATS_SET", "CHATS_UPSERT", "CHATS_UPDATE", "CHATS_DELETE", "GROUPS_UPSERT",
//                "GROUP_UPDATE", "GROUP_PARTICIPANTS_UPDATE", "CONNECTION_UPDATE", "CALL", "NEW_JWT_TOKEN", "TYPEBOT_START",
//                "TYPEBOT_CHANGE_STATUS"
            ],
            "reject_call" => false,
            "groups_ignore" => true,
            "always_online" => false,
            "read_messages" => true,
            "read_status" => true,
            "websocket_enabled" => true,
            "websocket_events" => ["APPLICATION_STARTUP"],
            "rabbitmq_enabled" => true,
            "rabbitmq_events" => ["APPLICATION_STARTUP"],
            "sqs_enabled" => true,
            "sqs_events" => ["APPLICATION_STARTUP"],
        ];

        return $this->makeRequest("/instance/create", "POST", $array);
    }

    /**
     * @throws Exception
     */
    public function sendMessage($toNumber, $message)
    {
        $array = [
            "number" => $toNumber,
            "textMessage" => [
                "text" => $message
            ]
        ];

        return $this->makeRequest("/message/sendText/$this->instanceName", "POST", $array);
    }

    /**
     * @throws Exception
     */
    public function statusMessage()
    {
        $array = [
            "limit" => 123
        ];

        return $this->makeRequest("/chat/findStatusMessage/$this->instanceName", "POST", $array);
    }

    /**
     * @throws Exception
     */
    public function logout($instanceName)
    {
        return $this->makeRequest("/instance/logout/$instanceName", "DELETE");
    }


    /**
     * @throws Exception
     */
    public function deleteInstance(string $instanceName)
    {
        $this->logout($instanceName); // primeiro faz logout depois deleta
        return $this->makeRequest("/instance/delete/$instanceName", "DELETE");
    }


    /**
     * @throws Exception
     */
    private function makeRequest($endpoint, $method, $data = null)
    {
        $url = EVO_SERVER_URL . $endpoint;
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "apikey: $this->api_key"
            ],
        ];
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        curl_setopt_array($this->curl, $options);
        $response = curl_exec($this->curl);
        $err = curl_error($this->curl);
        if ($err) {
            throw new Exception("cURL Error #: $err");
        }
        return json_decode($response);
    }

    /**
     * @throws Exception
     */
    public function changeProfilePicture($pic_url)
    {
        if (!filter_var($pic_url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $array = [
            "picture" => $pic_url
        ];

        return $this->makeRequest("/chat/updateProfilePicture/$this->instanceName", "PUT", $array);

    }

    /**
     * @throws Exception
     */
    public function changeRecado(string $text)
    {
        $array = [
            "status" => $text
        ];
        return $this->makeRequest("/chat/updateProfileStatus/$this->instanceName", "POST", $array);
    }

    /**
     * @throws Exception
     */
    public function sendTextStatus($instance, $content, $backgroundColor = '#a7e6d7', $font = 1, bool $allContacts = true, array $statusJidList = [])
    {
        $statusMessage = [
            "type" => "text",
            'backgroundColor' => $backgroundColor,
            "content" => $content,
            "font" => $font,
            "allContacts" => $allContacts,
        ];
        if ($statusJidList) {
            $statusMessage["statusJidList"] = $statusJidList;
        }


        $data = [
            "statusMessage" => $statusMessage
        ];

        return $this->makeRequest("/message/sendStatus/$this->instanceName", "POST", $data);
    }


    /**
     * @throws Exception
     */
    public function setWebhook(string $url, string $instanceName)
    {
        $array = [
            "url" => $url,
            "webhook_by_events" => true,
            "webhook_base64" => true,
            "events" => [
                "MESSAGES_UPSERT"
//                ,"APPLICATION_STARTUP", "QRCODE_UPDATED", "MESSAGES_SET", "MESSAGES_UPSERT",
//                "MESSAGES_UPDATE", "MESSAGES_DELETE", "SEND_MESSAGE", "CONTACTS_SET", "CONTACTS_UPSERT", "CONTACTS_UPDATE",
//                "PRESENCE_UPDATE", "CHATS_SET", "CHATS_UPSERT", "CHATS_UPDATE", "CHATS_DELETE", "GROUPS_UPSERT",
//                "GROUP_UPDATE", "GROUP_PARTICIPANTS_UPDATE", "CONNECTION_UPDATE", "CALL", "NEW_JWT_TOKEN", "TYPEBOT_START",
//                "TYPEBOT_CHANGE_STATUS"

            ]
        ];

        return $this->makeRequest("/webhook/set/$instanceName", "POST", $array);

    }


    /**
     * Envia uma mensagem de lista para um número específico
     * @param string $instanceName Nome da instância
     * @param string $toNumber Número de telefone do destinatário
     * @param array $listMessage Dados da mensagem de lista
     * @param array $options Opções adicionais (delay, presence)
     * @return mixed Resposta da API
     * @throws Exception
     */
    public function sendList(string $toNumber, array $listMessage, array $options = [])
    {
        $data = [
            'number' => $toNumber,
            'options' => $options,
            'listMessage' => $listMessage
        ];

        return $this->makeRequest("/message/sendList/$this->instanceName", "POST", $data);
    }


    /**
     * @throws Exception
     */
    public function findWebhook(string $instanceName)
    {
        return $this->makeRequest("/webhook/find/$instanceName", 'GET');
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }


    /**
     * Envia uma mensagem de template para um número específico
     *
     * @param string $toNumber Número de telefone do destinatário
     * @param array $templateMessage Dados da mensagem de template
     * @return mixed Resposta da API
     * @throws Exception
     */
    public function sendTemplate(string $toNumber, array $templateMessage)
    {
        $data = [
            'number' => $toNumber,
            'templateMessage' => $templateMessage
        ];
        return $this->makeRequest("/message/sendTemplate/$this->instanceName", "POST", $data);
    }


    /**
     * Não está funcionando pode haver algum erro nesse código ou bug na EvolutionAPI
     **/
    public function sendLink($toNumber, $link)
    {
        $templateMessage = [
            'name' => 'nome_do_template',
            'language' => 'pt_BR',
            'components' => [
                [
                    'type' => 'header',
                    'sub_type' => 'quick_reply',
                    'index' => '1',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => 'Baixar video'
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendTemplate($toNumber, $templateMessage);
    }


    /**
     * Envia l=ocalização no mapa
     *
     * @throws Exception
     */
    public function sendLocation(string $to_number, $latitude, $longitude, string $name, string $address)
    {
        $geo_info = [
            "number" => $to_number,
            "options" => [
                "delay" => 2,
                "presence" => "composing"
            ],
            "locationMessage" => [
              //  "name" => $name,
               // "address" => $address,
                "latitude" => $latitude,
                "longitude" => $longitude
            ]
        ];
        return $this->makeRequest("/message/sendLocation/$this->instanceName", 'POST', $geo_info);
    }


    public function checkIsWhatsApp(array $numbers)
    {
        $data = [
            'numbers' => $numbers,
        ];
        return $this->makeRequest("/chat/whatsappNumbers/$this->instanceName", "POST", $data);

    }

    public function fetchProfilePictureUrl($number)
    {
        $data = [
            'number' => $number,
        ];
        return $this->makeRequest("/chat/fetchProfilePictureUrl/$this->instanceName", "POST", $data);

    }
}