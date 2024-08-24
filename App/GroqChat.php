<?php

namespace App;
class GroqChat
{

    public function completion(array $chat_history, string $model, $system): array
    {
        $curl = curl_init();
        $endpoint = GROQ_ENDPOINT;
        $api_key = $_ENV['GROQ_API_KEY'] ?? '';
        array_unshift($chat_history, ['role' => 'system', 'content' => $system]);
        $postFields = array(
            "messages" => $chat_history,
            "model" => $model
        );


        $postFieldsJson = json_encode($postFields);
        $http_header = array(
            "Authorization: Bearer " . $api_key,
            "Content-Type: application/json"
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true, // Return response as string
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFieldsJson,
            CURLOPT_HTTPHEADER => $http_header,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $data = [];
        $data['msg'] = "";
        if ($err) {
            $msg_de_erro = "cURL Error #: $err";
            $data['error_msg'] = "Ops! Request failed: $msg_de_erro";
        } else {
            $result = json_decode($response);
            $data['msg'] = $result->choices[0]->message->content ?? '';
        }
        if (empty($msg)) {
            $arr = json_decode($response);
            $err_msg = $arr->error->message ?? '';
            if ($err_msg) {
                $data['error_msg'] = $err_msg;
            }
        }
        return $data;
    }


}