<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'bd.php';

// Por si no exite la tabla
function crearTablaSiNoExiste($con) {
 $query = "CREATE TABLE IF NOT EXISTS usuarios (
 id SERIAL PRIMARY KEY,
 nombre VARCHAR(100) NOT NULL,
 email VARCHAR(100) UNIQUE NOT NULL,
 telefono VARCHAR(20),
 fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 )";
 return pg_query($con, $query);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar'])) {
    $con = conectar();
    if ($con) {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $telefono = $_POST['telefono'];

        $query = "UPDATE usuarios SET nombre=$1, email=$2, telefono=$3 WHERE id=$4";
        $result = pg_query_params($con, $query, array($nombre, $email, $telefono, $id));

        if ($result) {
            pg_close($con);
            header("Location: index.php?mensaje=Usuario editado exitosamente");
            exit;
        } else {
            $error_msg = "Error al editar: " . pg_last_error($con);
            pg_close($con);
            header("Location: index.php?error=" . urlencode($error_msg));
            exit;
        }
    }
}
// CREAR USUARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
 $con = conectar();
 if ($con) {

 // Asegura que la tabla existe
 crearTablaSiNoExiste($con);
 
 $nombre = $_POST['nombre'];
 $email = $_POST['email'];
 $telefono = $_POST['telefono'];
 
 $query = "INSERT INTO usuarios (nombre, email, telefono) VALUES ($1, $2, $3)";
 $result = pg_query_params($con, $query, array($nombre, $email, $telefono));
 
 if ($result) {
 pg_close($con);
 header("Location: index.php?mensaje=Usuario creado exitosamente");
 exit;
 } else {
 $error_msg = "Error al crear: " . pg_last_error($con);
 pg_close($con);
 header("Location: index.php?error=" . urlencode($error_msg));
 exit;
 }
 }
}

// ELIMINAR USUARIO
if (isset($_GET['eliminar'])) {
 $con = conectar();
 if ($con) {
 $id = $_GET['eliminar'];
 $query = "DELETE FROM usuarios WHERE id = $1";
 $result = pg_query_params($con, $query, array($id));
 
 if ($result && pg_affected_rows($result) > 0) {
 pg_close($con);
 header("Location: index.php?mensaje=Usuario eliminado exitosamente");
 exit;
 } else {
 $error_msg = "Error al eliminar: " . pg_last_error($con);
 pg_close($con);
 header("Location: index.php?error=" . urlencode($error_msg));
 exit;
 }
 }
}

// OBTENER USUARIOS
$con = conectar();
$usuarios = array();
if ($con) {
 // Asegura d enuevo antes de leer
 crearTablaSiNoExiste($con);
 
 $result = pg_query($con, "SELECT * FROM usuarios ORDER BY id DESC");
 if ($result) {
 $usuarios = pg_fetch_all($result) ?: array();
 }
 pg_close($con);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <title>CRUD Usuarios</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
 <div class="container mt-4">
 <h2 class="text-center mb-4">CRUD Usuarios</h2>

 <?php if (isset($_GET['mensaje'])): ?>
 <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
 <?php endif; ?>

 <?php if (isset($_GET['error'])): ?>
 <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
 <?php endif; ?>

 <div class="card mb-4">
 <div class="card-header bg-primary text-white">
 <h5 class="mb-0">Crear Nuevo Usuario</h5>
 </div>
 <div class="card-body">
 <form method="POST">
 <div class="row">
 <div class="col-md-4">
 <input type="text" name="nombre" class="form-control" placeholder="Nombre *" required>
 </div>
 <div class="col-md-4">
 <input type="email" name="email" class="form-control" placeholder="Email *" required>
 </div>
 <div class="col-md-3">
 <input type="tel" name="telefono" class="form-control" placeholder="Teléfono">
 </div>
 <div class="col-md-1">
 <button type="submit" class="btn btn-success w-100">Crear</button>
 </div>
 </div>
 </form>
 </div>
 </div>
<div class="card mb-4 mt-3" id="formEditar" style="display:none;">
    <div class="card-header bg-warning">
        <h5 class="mb-0">Editar Usuario</h5>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="editar" value="1">
            <input type="hidden" name="id" id="edit_id">

            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <input type="tel" name="telefono" id="edit_telefono" class="form-control">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-warning w-100">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
 <div class="card">
 <div class="card-header bg-success text-white">
 <h5 class="mb-0">Lista de Usuarios</h5>
 </div>
 <div class="card-body">
 <?php if (!empty($usuarios)): ?>
 <div class="table-responsive">
 <table class="table table-striped">
 <thead>
 <tr>
 <th>ID</th>
 <th>Nombre</th>
 <th>Email</th>
 <th>Teléfono</th>
 <th>Acciones</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($usuarios as $usuario): ?>
 <tr>
 <td><?php echo $usuario['id']; ?></td>
 <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
 <td><?php echo htmlspecialchars($usuario['email']); ?></td>
 <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
 <td>
  <td>
      <button 
          class="btn btn-warning btn-sm"
          onclick="llenarFormularioEditar(
              '<?php echo $usuario['id']; ?>',
              '<?php echo htmlspecialchars($usuario['nombre']); ?>',
              '<?php echo htmlspecialchars($usuario['email']); ?>',
              '<?php echo htmlspecialchars($usuario['telefono']); ?>'
          )">
          Editar
      </button>  
 <a href="index.php?eliminar=<?php echo $usuario['id']; ?>" 
 class="btn btn-danger btn-sm"
 onclick="return confirm('¿Estás seguro de eliminar a <?php echo htmlspecialchars($usuario['nombre']); ?>?')">
 Eliminar
 </a>
 </td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 </div>
 <?php else: ?>
 <p class="text-center text-muted">No hay usuarios registrados.</p>
 <?php endif; ?>
 </div>
 </div>
 </div>
<script>
function llenarFormularioEditar(id, nombre, email, telefono) {
    document.getElementById("formEditar").style.display = "block";

    document.getElementById("edit_id").value = id;
    document.getElementById("edit_nombre").value = nombre;
    document.getElementById("edit_email").value = email;
    document.getElementById("edit_telefono").value = telefono;

    window.scrollTo(0, 0);
}
</script> 
</body>
</html>