<?php
/**
 * Analisa se houve mudança de status em um objeto postado no correios
 **/

require_once __DIR__ . "/../bootstrap.php";
$Correios = new \App\Correios();
$Notify = new \App\Notify();
$track_codes = $Correios->needTrack();
foreach ($track_codes as $code) {
    echo "<h2>Analisando $code</h2>";
    $data = $Correios->getRealTimeStatus($code);
    $status = $data->status;
    if(preg_match("/Objeto\s+entregue\s+ao\s+destinatário/i", $status)){
        // Remove de track já que é o último status
        if($Correios->removeTrack($code)){
            echo "Objeto $code removido com sucesso!<br>";
        }
    }
    $date = $data->date;
    $data_br = $data->data_br;
    if ($Correios->hasCode($code)) {
        $last_registered_status_date = $Correios->getLastRegisteredStatus($code);
        if ($last_registered_status_date != $date) {
            echo "Diferente {$last_registered_status_date} = $date";
            if ($Correios->updateStatus($code, $status, $date)) {
                echo "Novo status adicionado com sucesso: <br>";
                echo "Status: {$status}<br>";
                echo "Data: {$data_br}<br>";
                $msg = "Novo status correios para $code:\n{$status}\nData: {$data_br}";
                $subject = "Novo status para objeto: $code";
                $Notify->senMail($subject, $msg);
                $Notify->sendZap($msg);
            }
        } else {
            echo "Nada novo";
        }
    } else {
        echo "Primeiro registro na base de dados<br>";
        if ($Correios->addStatus($code, $status, $date)) {
            echo "Status adicionado com sucesso: <br>";
            echo "Status: {$status}<br>";
            echo "Data: {$data_br}<br>";
            $msg = "Novo status correios para $code:\n{$status}\nData: {$data_br}";
            $subject = "Novo status para objeto: $code";
            $Notify->senMail($subject, $msg);
            $Notify->sendZap($msg);
        }

    }
}