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
require "enviarCorreo.php";
require $_SERVER['DOCUMENT_ROOT'] . "/AWOSVS/main/firebase-php-jwt/vendor/autoload.php";

$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

// VALIDAR JWT
$headers = getallheaders();
$token = "";
if (isset($headers["Authorization"])) {
  $token = str_replace("Bearer ", "", $headers["Authorization"]);
}

try {
  $decoded    = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key("Test12345-----------------------------------------------", "HS256"));
  $usuario    = explode("/", $decoded->sub);
  $id_usuario = $usuario[0];
  $usr        = $usuario[1];
  $tipo       = $usuario[2];
  $login      = true;
}
catch (Exception $e) {
  $login      = false;
  $id_usuario = null;
}

$esAdmin = $login && $tipo == "1";

// ENDPOINTS

if (isset($_GET["usuarios"])) {
  $select = $con->select("view_usuarios_favoritos", "*");
  $select->limit(20);
  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
elseif (isset($_GET["editarProducto"]) && $esAdmin) {
  $id = $_GET["id"];
  $select = $con->select("productos", "*");
  $select->where("id", "=", $id);
  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
elseif (isset($_GET["usuariosCombo"]) && $login) {
  $select = $con->select("usuarios", "id_usuario AS value, nombre AS label");
  $select->orderby("nombre ASC");
  $array = array(array("index" => 0, "value" => "", "label" => "Selecciona una opción"));
  foreach ($select->execute() as $x => $usuario) {
    $array[] = array("index" => $x + 1, "value" => $usuario["value"], "label" => $usuario["label"]);
  }
  header("Content-Type: application/json");
  echo json_encode($array);
}
elseif (isset($_GET["eliminarFavorito"]) && $esAdmin) {
  if (!isset($_POST["id_usuario"])) { echo "Falta id_usuario"; exit; }
  $prepare = $con->prepare("CALL eliminarFavoritos(:usuario)");
  $prepare->bindParam(":usuario", $_POST["id_usuario"]);
  echo $prepare->execute() ? "correcto" : "error";
}
elseif (isset($_GET["ciudadesCombo"]) && $login) {
  $select = $con->select("ciudades", "id_ciudad AS value, nombre AS label");
  $select->orderby("nombre ASC");
  $array = array(array("index" => 0, "value" => "", "label" => "Selecciona una opción"));
  foreach ($select->execute() as $x => $ciudad) {
    $array[] = array("index" => $x + 1, "value" => $ciudad["value"], "label" => $ciudad["label"]);
  }
  header("Content-Type: application/json");
  echo json_encode($array);
}
elseif (isset($_GET["agregarFavorito"]) && $login) {
  if (!isset($_POST["id_usuario"]) || !isset($_POST["id_ciudad"])) { echo "faltan_datos"; exit; }
  $prepare = $con->prepare("CALL insertarFavorito(:usuario, :ciudad)");
  $prepare->bindParam(":usuario", $_POST["id_usuario"]);
  $prepare->bindParam(":ciudad", $_POST["id_ciudad"]);
  echo $prepare->execute() ? "correcto" : "error";
}
elseif (isset($_GET["editarFavorito"]) && $login) {
  $id = $_GET["id"];
  $select = $con->select("favoritos", "*");
  $select->where("id_favorito", "=", $id);
  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
elseif (isset($_GET["ciudadesPopulares"])) {
  $select = $con->select("view_ciudades_populares", "*");
  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
?>