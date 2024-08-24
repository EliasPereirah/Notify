<?php

namespace App;
use stdClass;

class Correios
{
    private ParallelRequest $HttpRequest;
    private Database $Database;

    public function __construct()
    {
        $this->HttpRequest = new ParallelRequest();
        $this->Database = new Database();
    }


    /**
     * Obtém último status de um objeto postado no correios
     * @param string $code Código de rastreio
     **/
    public function getRealTimeStatus(string $code):stdClass
    {
        $url = "https://www.linkcorreios.com.br/?id=$code";
        $this->HttpRequest->addURL($url, $code);
        $data = $this->HttpRequest->request();
        $content = $data[0]['content'];
        preg_match("/<ul\s+class=\"linha_status\s+m-0\"(.*?)<\/ul>/is", $content, $matches);
        $result = $matches[1] ?? '';
        preg_match("/Status:(.*?)<\/li>/", $result, $matches);
        $status = $matches[1] ?? '';
        $status = strip_tags($status);
        preg_match("/Data(\s+)?:\s+(?<data>\d{2}\/\d{2}\/\d{4})\s+\|\s+Hora:\s+(?<hora>\d{2}:\d{2})/", $content, $matches);
        $data = $matches['data'];
        $hora = $matches['hora'];
        $data_hora = "$data $hora";
        $date = date_create_from_format('d/m/Y H:i', $data_hora);
        $formato_americano = $date->format('Y-m-d H:i:s');
        $obj = new stdClass();
        $obj->status = $status;
        $obj->date = $formato_americano;
        $obj->data_br = $data_hora;
        return $obj;
    }

    /**
     * Obtém a data do último registro armazenado na base de dados
     * @param string $code Código de rastreio dos correios
    **/
    public function getLastRegisteredStatus(string $code):string
    {
        $code = trim($code);
        $sql = "SELECT last_status FROM correios WHERE code = :code ORDER BY id DESC LIMIT 1";
        $binds = ['code' => $code];
        return $this->Database->select($sql, $binds)->fetch()->last_status ?? 'none';
    }

    /**
     * Atualiza status existente na base de dados
     * @param string $code Código de rastreio dos correios
     * @param string $new_status Mensagem do novo status
     * @param string $new_date Data da última mudança no status pelo correios
    **/
    public function updateStatus(string $code, string $new_status, string $new_date):bool
    {
        $code = trim($code);
        $sql = "UPDATE correios SET last_status = :new_date, content = :new_cnt WHERE code = :code";
        $binds = ['new_date' => $new_date, 'new_cnt' => $new_status, 'code' => $code];
        return $this->Database->update($sql, $binds);

    }
    /**
     * Verifica se já existe pelo menos um registro para o código informado na base de dados
     * @param string $code Código de rastreio correios
    **/
    public function hasCode(string $code):bool
    {
        $code = trim($code);
        $sql = "SELECT code FROM correios WHERE code = :code";
        $binds = ['code' => $code];
        return $this->Database->select($sql, $binds)->rowCount() > 0;
    }

    public function addStatus(string $code, string $status, string $date):bool
    {
        $code = trim($code);
        $sql = "INSERT INTO correios (code, last_status, content) VALUES (:code, :last_status, :cnt)";
        $binds = ['code' => $code, 'last_status' => $date, 'cnt' => $status];
        return $this->Database->insert($sql, $binds);
    }

    /**
     * Retorna lista de códigos dos Correios que precisam ser traqueados
    **/
    public function needTrack(int $limit = 5):array
    {
        $limit = (int) $limit;
        $sql = "SELECT code FROM track WHERE finished = :finished LIMIT $limit";
        $binds = ['finished' => 0];
        $data = $this->Database->select($sql, $binds)->fetchAll();
        $all_codes = [];
        foreach ($data as $row) {
            $all_codes[] = $row->code;
        }
        return $all_codes;
    }

    public function removeTrack(string $code):bool
    {
        $code = trim($code);
        $sql = "UPDATE track SET finished = :finished WHERE code = :code";
        $binds = ['finished' => 1,'code' => $code];
        return $this->Database->update($sql, $binds);
    }
}