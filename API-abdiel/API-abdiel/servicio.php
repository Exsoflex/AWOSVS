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
    $select = $conn->select("ciudades", "id_ciudad, nombre, pais, latitud, longitud");

    $select ->orderBy("nombre", "ASC");
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
    $id = $_GET["id"];
    
    $query = $conn->select("ciudades", "id_ciudad, nombre, pais, latitud, longitud");
    $query->where("id_ciudad", "=", $id); 
    
    try {
        $datos = $query->execute(); 
    } catch (Exception $e) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "Error querying city: " . $e->getMessage()]);
        exit;
    }

    header("Content-Type: application/json");
    if ($datos && isset($datos[0])) {
        echo json_encode($datos[0]);
    } else {
        echo json_encode(["error" => "No encontrado"]);
    }
    exit;
}

if(isset($_GET["editarCiudad"])){
    $id       = $_POST["id_ciudad"];
    $nombre   = $_POST["nombre"];
    $pais     = $_POST["pais"];
    $latitud  = $_POST["latitud"];
    $longitud = $_POST["longitud"];

    
    $update = $conn->update("ciudades");

    $update->set("nombre", $nombre);
    $update->set("pais", $pais);
    $update->set("latitud", $latitud);
    $update->set("longitud", $longitud);

    $update->where("id_ciudad", "=", $id);

    $resultado = $update->execute();

    header("Content-Type: application/json");
   
    echo json_encode([
        "success" => ($resultado !== false)
    ]);
    exit;
}

if(isset($_GET["nuevaCiudad"])){
    $nombre   = $_POST["nombre"];
    $pais     = $_POST["pais"];
    $latitud  = $_POST["latitud"];
    $longitud = $_POST["longitud"];

   
    $insert = $conn->insert("ciudades", "nombre, pais, latitud, longitud");
    
    $insert->value($nombre);
    $insert->value($pais);
    $insert->value($latitud);
    $insert->value($longitud);

    $resultado = $insert->execute();

    header("Content-Type: application/json");
    
    echo json_encode([
        "success" => ($resultado !== false)
    ]);
    exit;
}