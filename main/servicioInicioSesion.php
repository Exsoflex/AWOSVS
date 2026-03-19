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

if (isset($_GET["PING"])) {
  exit;
}

date_default_timezone_set("America/Matamoros");

if (isset($_GET["DATETIME"])) {
  echo date("Y-m-d H:i:s");
  exit;
}


// ------------------------------------------------------
// ------------------------------------------------------
// Debajo de este comentario irá la configuración a la BD
// y las funciones del servicio para la aplicación móvil.

require "conexion.php";
require "enviarCorreo.php";

/*
$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_prueba_bd",
  "usuario"    => "u760464709_prueba_usr",
  "contrasena" => "|Au/mc*H2jH3"
));
*/

$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "localhost",
  "bd"         => "prueba",
  "usuario"    => "root",
  "contrasena" => ""
));


require "firebase-php-jwt/vendor/autoload.php";

$headers = getallheaders();

$token = "";
if (isset($headers["Authorization"])) {
  $token = str_replace("Bearer ", "", $headers["Authorization"]);
}

try {
  # el segundo parametro es la clave para codificar y decodificar el JWT
  # debe ser una string no corta, por eso rellené de guiones
  $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key("Test12345-----------------------------------------------", "HS256"));

  # $usuario puede ser usada para validaciones
  $usuario = explode("/", $decoded->sub);
  $id      = $usuario[0];
  $usuario = $usuario[1];
  $tipo    = $usuario[2];

  # $login puede ser usada para validaciones
  $login = true;
}
catch (Exception $error) {
  $usuario = array();
  $login   = false;
}


# endpoint para revisar estado de la sesión
if (isset($_GET["sesion"])) {

  header("Content-Type: application/json");
  echo json_encode($usuario);
}
# endpoint para iniciar sesión
elseif (isset($_GET["iniciarSesion"])) {
  $select = $con->select("usuarios");
  $select->where("nombre", "=", $_POST["txtUsuario"]);
  $select->where_and("password", "=", $_POST["txtContrasena"]);

  $usuarios = $select->execute();

  if (count($usuarios)) {
    $usuario = $usuarios[0];

    $payload = [
      "iat" => time(),
      "exp" => time() + (60 * 60 * 24 * 7),
      "sub" => $usuario["id_usuario"] . "/" . $usuario["nombre"] . "/" . $usuario["tipo"]
    ];
    # el segundo parametro es la clave para codificar y decodificar el JWT
    # debe ser una string no corta, por eso rellené de guiones
    $jwt = Firebase\JWT\JWT::encode($payload, "Test12345-----------------------------------------------", "HS256");

    echo $jwt;
  }
  else {
    echo "error";
  }
}