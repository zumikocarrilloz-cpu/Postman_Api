<?php
function connection() {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db = "baseaproductos";

    $connect = new mysqli($host, $user, $pass, $db);

    if ($connect->connect_error) {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            "status" => "error",
            "message" => "Error de Conexión: " . $connect->connect_error
        ]);
        exit();
    }

    $connect->set_charset("utf8mb4");
    return $connect;
}
?>

