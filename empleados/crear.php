<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado</title>
    <link rel="stylesheet" href="../assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/maincss.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<?php include '../assets/header.php'?>
<main>
    <div class="container mt-5">
        <h1 class="text-center">Registro de Empleados</h1>
        <?php
        include '../db/conexion.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre = $_POST['nombre'];
            $apellido_paterno = $_POST['apellido_paterno'];
            $apellido_materno = $_POST['apellido_materno'];
            $departamento = $_POST['departamento'];
            $email = $_POST['email'];

            $nombre_completo = htmlspecialchars($nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno);

            $datos = [
                'opcion' => 'crear',
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'correo' => $email,
                'departamento' => $departamento
            ];
            $json_datos = json_encode($datos);

            try {
                $sql = "CALL sp_gestion_empleados(:json_datos)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                $stmt->execute();

                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Empleado <?php echo addslashes($nombre_completo); ?> se registró en el área <?php echo addslashes($departamento); ?> con éxito',
                        confirmButtonText: 'Aceptar',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = 'listar.php';
                    });
                </script>
                <?php
                exit();
            } catch (PDOException $e) {
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al registrar empleado: <?php echo addslashes($e->getMessage()); ?>',
                        confirmButtonText: 'Aceptar',
                        timer: 5000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = 'crear.php';
                    });
                </script>
                <?php
                exit();
            }
        }
        ?>
        <form id="formularioRegistro" method="POST" action="">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="apellido_paterno" class="form-label">Apellido Paterno:</label>
                <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
            </div>
            <div class="mb-3">
                <label for="apellido_materno" class="form-label">Apellido Materno:</label>
                <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" required>
            </div>
            <div class="mb-3">
                <label for="departamento" class="form-label">Departamento:</label>
                <input type="text" class="form-control" id="departamento" name="departamento" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo:</label>
                <input type="text" class="form-control" id="email" name="email" required>
            </div>
            <div class="d-flex justify-content-between flex-wrap">
                <button type="submit" class="btn btn-success mb-2">Registrar</button>
                <a href="listar.php" class="btn btn-danger mb-2">Cancelar</a>
            </div>
        </form>
        <div id="mensaje" class="mt-3"></div>
    </div>
</main>

<script>
    document.getElementById('formularioRegistro').addEventListener('submit', function(event) {
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor, ingrese un correo válido (ej: usuario@dominio.com).',
                confirmButtonText: 'Aceptar'
            });
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
 <?php include '../assets/footer.php'?> 