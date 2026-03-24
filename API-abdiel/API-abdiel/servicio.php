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


// Configuración de JWT
require $_SERVER['DOCUMENT_ROOT'] . "/AWOSVS/main/firebase-php-jwt/vendor/autoload.php";
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
} catch (Exception $e) {
  $login = false;
  $id_usuario = null;
}
$esAdmin = $login && $tipo == "1";

require "conexion.php";

// Configuración de la conexión
$conn = new Conexion(array(
    "tipo" => "mysql",
    "servidor" => "46.28.42.226",
    "bd" => "u760464709_24005224_bd",
    "usuario" => "u760464709_24005224_usr",
    "contrasena" => "8PEd!gd5x+Sb"
));

//Listar todas las ciudades
if (isset($_GET["ciudad"]) && !isset($_GET["id"])) {
   $select = $conn->select("view_ciudades", "*");
   $select->orderBy("nombre", "ASC");
    try {
        $datos = $select->execute();
    } catch (Exception $e) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "Error querying cities: " . $e->getMessage()]);
        exit;
    }
    header("Content-Type: application/json");
    echo json_encode($datos);
    exit;
}


if (isset($_GET["ciudad"]) && isset($_GET["id"])) {
    $id = intval($_GET["id"]);
try{
    $datos = $conn->query("CALL obtener_ID_ciudad($id)")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
header("Content-Type: application/json");
    echo json_encode($datos[0] ?? ["error" => "No encontrado"]);
    exit;
}
    
   

if (isset($_GET["editarCiudad"])&& $esAdmin) {
    
    $id       = intval($_POST["id_ciudad"]); 
    $nombre   = $_POST["nombre"];
    $pais     = $_POST["pais"];
    $latitud  = floatval($_POST["latitud"]);
    $longitud = floatval($_POST["longitud"]);

    try {
        $stmt = $conn->prepare("CALL editar_ciudad(?, ?, ?, ?, ?)");
        $stmt->execute([$id, $nombre, $pais, $latitud, $longitud]);
        $resultado = true;
    } catch (Exception $e) {
     $resultado = false;
    }

    header("Content-Type: application/json");
    echo json_encode(["success" => $resultado]);
    exit;
}

if(isset($_GET["nuevaCiudad"])&& $esAdmin) {
    $nombre   = $_POST["nombre"];
    $pais     = $_POST["pais"];
    $latitud  = floatval($_POST["latitud"]);
    $longitud = floatval($_POST["longitud"]);

    try {
        $stmt = $conn->prepare("CALL nueva_ciudad(?, ?, ?, ?)");
        $stmt->execute([$nombre, $pais, $latitud, $longitud]);
        $resultado = true;
    } catch (Exception $e){
        $resultado = false;
    }

    header("Content-Type: application/json");
   
    echo json_encode([
        "success" => $resultado
    ]);
    exit;
   }

   if (isset($_GET["ciudadesUbicacion"])) {
    $select = $conn->select("vista_ciudades_con_ubicacion", "*");

    try {
        $datos = $select->execute();
    } catch (Exception $e) {
        header("Content-Type: application/json");
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    }

    header("Content-Type: application/json");
    echo json_encode($datos);
    exit;
}