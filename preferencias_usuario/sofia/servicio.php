<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL & ~E_DEPRECATED);

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Allow: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
  http_response_code(200);
  exit;
}

if (isset($_GET["PING"])) exit;

date_default_timezone_set("America/Matamoros");

if (isset($_GET["DATETIME"])) {
  echo date("Y-m-d H:i:s");
  exit;
}

require "conexion.php";
require $_SERVER['DOCUMENT_ROOT'] . "/AWOSVS/main/firebase-php-jwt/vendor/autoload.php";
$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

$headers = getallheaders();
$token = "";
if (isset($headers["Authorization"])) {
  $token = str_replace("Bearer ", "", $headers["Authorization"]);
}

try {
  $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key("Test12345-----------------------------------------------", "HS256"));
  $usuario = explode("/", $decoded->sub);
  $id_usuario = $usuario[0];
  $usr        = $usuario[1];
  $tipo       = $usuario[2];
  $login = true;
}
catch (Exception $e) {
  $login = false;
  $id_usuario = null;
}
$esAdmin = $login && $tipo == "1";

if (isset($_GET["obtener_preferencias"]) && $login) {

    $prepare = $con->prepare("SELECT unidad_temperatura, tema FROM preferencias_usuario WHERE id_usuario = :id");
    $prepare->bindParam(":id", $id_usuario);
    $prepare->execute();
    $pref = $prepare->fetch(PDO::FETCH_ASSOC);

    header("Content-Type: application/json");
    echo json_encode([
        'unidad_temperatura' => $pref['unidad_temperatura'] ?? 'Celsius',
        'tema'               => $pref['tema'] ?? 'claro',
        'logueado'           => true
    ]);
    exit;
}

elseif (isset($_GET["obtener_preferencias"]) && !$login) {
    header("Content-Type: application/json");
    echo json_encode(['unidad_temperatura' => 'Celsius', 'logueado' => false]);
    exit;
}

elseif (isset($_GET["preferencias"]) && $esAdmin) {
    $select = $con->select("vista_preferencias_completo");
    header("Content-Type: application/json");
    echo json_encode($select->execute());
}

elseif (isset($_GET["eliminarpreferencia"]) && $esAdmin) {
    $idUsuario = $_POST["id_usuario"];
    $prepare = $con->prepare("CALL eliminar_preferencias(:p_id_usuario)");
    $prepare->bindParam(":p_id_usuario", $idUsuario);
    $prepare->execute();

    echo $prepare->rowCount() > 0 ? "correcto" : "error";
    exit;
}

elseif (isset($_GET["guardar_preferencias"]) && $login) {

    $unidad = ($_POST['unidad'] === 'Fahrenheit') ? 'Fahrenheit' : 'Celsius';
    $tema   = ($_POST['tema']   === 'oscuro')     ? 'oscuro'     : 'claro';

    $prepare = $con->prepare("SELECT id_usuario FROM preferencias_usuario WHERE id_usuario = :id");
    $prepare->bindParam(":id", $id_usuario);
    $prepare->execute();

    if ($prepare->rowCount() > 0) {
        $prepare = $con->prepare("UPDATE preferencias_usuario SET unidad_temperatura = :unidad, tema = :tema WHERE id_usuario = :id");
        $prepare->bindParam(":unidad", $unidad);
        $prepare->bindParam(":tema", $tema);
        $prepare->bindParam(":id", $id_usuario);
        $prepare->execute();
    } else {
        $prepare = $con->prepare("INSERT INTO preferencias_usuario (id_usuario, unidad_temperatura, tema) VALUES (:id, :unidad, :tema)");
        $prepare->bindParam(":id", $id_usuario);
        $prepare->bindParam(":unidad", $unidad);
        $prepare->bindParam(":tema", $tema);
        $prepare->execute();
    }

    echo "correcto";
    exit;
}

elseif (isset($_GET["agregar_preferencia_sp"]) && $login) {
    $prepare = $con->prepare("CALL insertar_preferencias(:p_id_usuario, :p_unidad_temperatura, :p_tema)");
    $prepare->bindParam(":p_id_usuario", $_POST["id_usuario"]);
    $prepare->bindParam(":p_unidad_temperatura", $_POST["unidad"]);
    $prepare->bindParam(":p_tema", $_POST["tema"]);
    $prepare->execute();

    echo "correcto";
    exit;
}

elseif (isset($_GET["modificar_preferencia"]) && $esAdmin) {
    $prepare = $con->prepare("CALL actualizar_preferencias(:p_id_usuario, :p_unidad_temperatura, :p_tema)");
    $prepare->bindParam(":p_id_usuario", $_POST["id_usuario"]);
    $prepare->bindParam(":p_unidad_temperatura", $_POST["unidad"]);
    $prepare->bindParam(":p_tema", $_POST["tema"]);
    $prepare->execute();

    echo "correcto";
    exit;
}
?>