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
// endpoints

if (isset($_GET["usuarios"]) && $esAdmin) {
  $select = $con->select("view_usr_busquedas");
  $select->orderby("id_usuario DESC");
  $select->limit(10);

  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
elseif (isset($_GET["editarUsuario"]) && $esAdmin) {
  $id = $_GET["id"];

  $select = $con->select("usuarios", "*");
  $select->where("id_usuario", "=", $id);

  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
elseif (isset($_GET["categoriasCombo"]) && $esAdmin) {
  $select = $con->select("categorias", "id AS value, nombre AS label");
  $select->orderby("nombre ASC");
  $select->limit(10);

  $array = array(array("index" => 0, "value" => "", "label" => "Selecciona una opción"));

  foreach ($select->execute() as $x => $categoria) {
      $array[] = array("index" => $x + 1, "value" => $categoria["value"], "label" => $categoria["label"]);
  }

  header("Content-Type: application/json");
  echo json_encode($array);
}
elseif (isset($_GET["eliminarUsuario"]) && $esAdmin) {
  $prepare = $con->prepare("CALL eliminarUsuario(:id_usuario)");
  $prepare->bindParam(":id_usuario", $_POST["txtId"]);

  if ($prepare->execute()) {
    echo "correcto";
  } else {
    echo "error";
  }
}
elseif (isset($_GET["agregarUsuario"]) && $esAdmin) {
  $prepare = $con->prepare("CALL insertarUsuario(:nombre, :email, :password)");
  $prepare->bindParam(":nombre", $_POST["txtNombre"]);
  $prepare->bindParam(":email", $_POST["txtEmail"]);
  $prepare->bindParam(":password", $_POST["txtContrasena"]);
  $prepare->execute();

  echo "correcto";
}
elseif (isset($_GET["modificarUsuario"]) && $esAdmin) {
  $prepare = $con->prepare("CALL modificarUsuario(:id_usuario,:nombre,:email,:password)");
  $prepare->bindParam(":id_usuario", $_POST["txtId"]);
  $prepare->bindParam(":nombre", $_POST["txtNombre"]);
  $prepare->bindParam(":email", $_POST["txtEmail"]);
  $prepare->bindParam(":password", $_POST["txtContrasena"]);

  if ($prepare->execute()) {
    echo "correcto";
  } else {
    echo "error";
  }
}
?>