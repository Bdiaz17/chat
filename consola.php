<?php
/* conexión a la base de datos */
function conectar(){
    $servidor = "127.0.0.1";
    $usuario = "root";
    $password = "123456";
    $database = "chat";
    
    try {
        $conexion = new PDO("mysql:host=$servidor;dbname=$database", $usuario, $password);
        // Configuración de PDO para lanzar excepciones en caso de errores.
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch(PDOException $e) {
        echo "Error de conexión: " . $e->getMessage();
        return null;
    }
}

/* función para ejecutar */
function ejecutar($sql, $params = array()){
    $conexion = conectar();
    if ($conexion) {
        try {
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);
        } catch(PDOException $e) {
            echo "Error al ejecutar la consulta: " . $e->getMessage();
        }
    }
}

/* función para consultar */
function consultar($sql, $params = array(), $cols_num){
    $conexion = conectar();
    $matriz = array();

    if ($conexion) {
        try {
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);

            $f = 0;
            while($celda = $stmt->fetch(PDO::FETCH_ASSOC)){
                $keys = array_keys($celda);
                for($c = 0; $c < $cols_num; $c++) {
                    $matriz[$f][$c] = $celda[$keys[$c]];
                }
                $f++;
            }
        } catch(PDOException $e) {
            echo "Error al ejecutar la consulta: " . $e->getMessage();
        }
    }

    return $matriz;
}

/* función de retorno de datos AJAX */
function AJAX($nombre, $mensaje){
    if(($nombre!="") && ($mensaje!="")){
        ejecutar("INSERT INTO mensajeria(usuarios, mensajes) VALUES(?, ?)", array($nombre, $mensaje));
    }
    
    $chat = consultar("SELECT CONCAT(idmensajeria, ';', usuarios, ';', mensajes) FROM mensajeria ORDER BY idmensajeria DESC LIMIT 5", array(), 1);

    $i = 0;
    $caracteres = "";
    foreach ($chat as $dato){
        if($i == 0){
            $caracteres = preg_replace("/\r|\n/", "-n", $dato[0]);
        } else {
            $caracteres = preg_replace("/\r|\n/", "-n", $dato[0])."\n". $caracteres;
        }
        $i = $i+1;
    }

    header("Content-Type: text/plain");
    echo($caracteres);
}

/* solo si recibe variable nombre y mensaje sabemos que es el AJAX */
if(isset($_REQUEST["nombre"]) && isset($_REQUEST["mensaje"])){
    $nombre = $_REQUEST["nombre"];
    $mensaje = $_REQUEST["mensaje"];
    AJAX($nombre, $mensaje);
}
/* si intenta ingresar sin autorización */
else{
    echo("Solo Personal Autorizado");
}
?>
