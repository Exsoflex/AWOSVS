<?php
class Conexion {
   private $conexion;

    public function __construct()
    {
        $host = "46.28.42.226";
        $db   = "u760464709_24005224_bd";
        $usr  = "u760464709_24005224_usr";
        $pwd  = "8PEd!gd5x+Sb";

        try {
            $this->conexion = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8",
                $usr,
                $pwd,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die("Error de conexión");
        }
    }

    public function ejecutar($sql, $params = [])
    {
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
   