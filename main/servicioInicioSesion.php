<?php

ini_set("display_errors", 1);
error_reporting(E_ALL & ~E_DEPRECATED);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
  http_response_code(200);
  exit;
}

require "conexion.php";
require "firebase-php-jwt/vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

$usuario = [];
$login = false;

if (!isset($_GET["iniciarSesion"])) {
  $headers = getallheaders();
  $token = "";

  if (isset($headers["Authorization"])) {
    $token = str_replace("Bearer ", "", $headers["Authorization"]);
  }

  if ($token != "") {
    try {
      $decoded = JWT::decode($token, new Key("Test12345-----------------------------------------------", "HS256"));
      $usuario = explode("/", $decoded->sub);
      $login = true;
    } catch (Exception $e) {
      $login = false;
    }
  }
}

if (isset($_GET["sesion"])) {
  header("Content-Type: application/json");
  echo json_encode($usuario);
  exit;
}

if (isset($_GET["iniciarSesion"])) {

  if (!isset($_POST["txtUsuario"]) || !isset($_POST["txtContrasena"])) {
    echo "error";
    exit;
  }

  $user = $_POST["txtUsuario"];
  $pass = $_POST["txtContrasena"];

  if (!$con) {
    echo "error_conexion";
    exit;
  }

  try {
    $select = $con->select("usuarios");
    $select->where("nombre", "=", $user);
    $select->where_and("password", "=", $pass);

    $usuarios = $select->execute();

    if (count($usuarios)) {

      $usuarioDB = $usuarios[0];

      $payload = [
        "iat" => time(),
        "exp" => time() + (60 * 60 * 24 * 7),
        "sub" => $usuarioDB["id_usuario"] . "/" . $usuarioDB["nombre"] . "/" . $usuarioDB["tipo"]
      ];

      $jwt = JWT::encode($payload, "Test12345-----------------------------------------------", "HS256");

      echo $jwt;
    } else {
      echo "error";
    }

  } catch (Exception $e) {
    echo "error_bd";
  }

  exit;
}
