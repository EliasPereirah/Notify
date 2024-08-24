<?php

namespace App;

class ParallelRequest
{
    private array $requests = [];
    private int $maxConcurrent;
    private string $userAgent;

    public function __construct(int $maxConcurrent = 10)
    {
        $this->maxConcurrent = $maxConcurrent;
        $this->userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36";
    }


    /**
     * Seta um novo user agent - default é Chrome
     * @param string $userAgent Novo user agent
    **/
    public function setUserAgent(string $userAgent): void
    {
        $this->userAgent = $userAgent;
    }


    /**
     * Adiciona uma URL à lista de requisições com a possibilidade de ser POST.
     *
     * @param string $url Url da requisição
     * @param string $identifier Identificador da requisição
     * @param string $method Método HTTP, 'GET' por default
     * @param array|null $postData Dados a serem enviados em caso de POST
     **/
    public function addURL(string $url, string $identifier = 'none', string $method = 'GET', array $postData = null): void
    {
        $this->requests[] = [
            'url' => $url,
            'identifier' => $identifier,
            'method' => strtoupper($method),
            'postData' => $postData
        ];
    }

    public function request(): array
    {
        $multiHandle = curl_multi_init();
        $handles = [];
        $results = [];
        $running = 0;
        $requestIndex = 0;

        try {
            do {
                while (count($handles) < $this->maxConcurrent && $requestIndex < count($this->requests)) {
                    $request = $this->requests[$requestIndex];
                    $handle = curl_init($request['url']);
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($handle, CURLOPT_USERAGENT, $this->userAgent);
                    curl_setopt($handle, CURLOPT_TIMEOUT, 30);
                    if ($request['method'] === 'POST') {
                        curl_setopt($handle, CURLOPT_POST, true);
                        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($request['postData']));
                    }
                    curl_multi_add_handle($multiHandle, $handle);
                    $handles[$requestIndex] = $handle;
                    $requestIndex++;
                }

                do {
                    $status = curl_multi_exec($multiHandle, $running);
                } while ($status === CURLM_CALL_MULTI_PERFORM);

                if ($running) {
                    curl_multi_select($multiHandle);
                }

                while ($done = curl_multi_info_read($multiHandle)) {
                    $info = curl_getinfo($done['handle']);
                    $content = curl_multi_getcontent($done['handle']);
                    $index = array_search($done['handle'], $handles);

                    if (curl_error($done['handle'])) {
                        $results[] = [
                            'identifier' => $this->requests[$index]['identifier'],
                            'url' => $info['url'],
                            'time' => $info['total_time'],
                            'http_code' => $info['http_code'],
                            'error' => curl_error($done['handle']),
                        ];
                    } else {
                        $results[] = [
                            'identifier' => $this->requests[$index]['identifier'],
                            'url' => $info['url'],
                            'time' => $info['total_time'],
                            'http_code' => $info['http_code'],
                            'content' => $content
                        ];
                    }

                    curl_multi_remove_handle($multiHandle, $done['handle']);
                    curl_close($done['handle']);
                    unset($handles[$index]);
                }

            } while ($running > 0 || count($handles) > 0);

        } finally {
            curl_multi_close($multiHandle);
        }

        $this->requests = []; // reset
        return $results;
    }

}
