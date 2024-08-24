<?php
require_once __DIR__ . "/../bootstrap.php";
$EvolutionAPI = new App\EvolutionAPI();
$instance_name = $_ENV['EVO_INSTANCE_NAME'] ?? '';
$zap_number = $_ENV['ZAP_NUMBER'] ?? '';
try {
    $arr = $EvolutionAPI->createInstance($instance_name, $zap_number, $instance_name);
    $b64 = $arr->qrcode->base64 ?? '';
    echo "Escanei o código com o WhatsApp para logar<br>";
    echo "<img src='$b64' alt='qrcode'>";
    echo "<hr>";
    echo "<pre>";
    print_r($arr);

} catch (Exception $e) {
    echo $e->getMessage();
}
