<?php
function conectar() {
    $con = @pg_connect("host=localhost port=5432 dbname=usuarios user=postgres password=12345");
    if (!$con) {
        $error = pg_last_error() ?: "No se pudo conectar";
        die("<div class='alert alert-danger'>
                <h4>Error de conexión PostgreSQL</h4>
                <strong>Error:</strong> $error
             </div>");
    }
    return $con;
}
?>