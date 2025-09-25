<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Datos de Empleados</title>
    <link rel="stylesheet" href="../assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/maincss.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<?php include '../assets/header.php'?>
<body>
<main>
    <div class="container mt-5">
        <h1 class="text-center">Actualización de Datos de Empleados</h1>
        <?php
        include '../db/conexion.php';

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $nombre = '';
        $apellido_paterno = '';
        $apellido_materno = '';
        $correo = '';
        $departamento = '';

        if ($id) {
            try {
                $datos = ['opcion' => 'obtener', 'id' => $id];
                $json_datos = json_encode($datos);
                $sql = "CALL sp_gestion_empleados(:json_datos)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                if ($result) {
                    $nombre = htmlspecialchars($result['nombre']);
                    $apellido_paterno = htmlspecialchars($result['apellido_paterno']);
                    $apellido_materno = htmlspecialchars($result['apellido_materno']);
                    $correo = htmlspecialchars($result['correo']);
                    $departamento = htmlspecialchars($result['departamento']);
                } else {
                    header("Location: listar.php");
                    exit();
                }
            } catch (PDOException $e) {
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar datos del empleado: <?php echo addslashes($e->getMessage()); ?>',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = 'listar.php';
                    });
                </script>
                <?php
                exit();
            }
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nuevo_nombre = $_POST['nombre'];
            $nuevo_apellido_paterno = $_POST['apellido_paterno'];
            $nuevo_apellido_materno = $_POST['apellido_materno'];
            $nuevo_correo = $_POST['email'];
            $nuevo_departamento = $_POST['departamento'];

            $datos_iguales = (
                $nombre === $nuevo_nombre &&
                $apellido_paterno === $nuevo_apellido_paterno &&
                $apellido_materno === $nuevo_apellido_materno &&
                $correo === $nuevo_correo &&
                $departamento === $nuevo_departamento
            );

            if ($datos_iguales) {
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                <script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin cambios',
                        text: 'No se detectaron cambios en la información del empleado.',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = 'listar.php';
                    });
                </script>
                <?php
                exit();
            } else {
                $datos = [
                    'opcion' => 'editar',
                    'id' => $id,
                    'nombre' => $nuevo_nombre,
                    'apellido_paterno' => $nuevo_apellido_paterno,
                    'apellido_materno' => $nuevo_apellido_materno,
                    'correo' => $nuevo_correo,
                    'departamento' => $nuevo_departamento
                ];
                $json_datos = json_encode($datos);

                try {
                    $sql = "CALL sp_gestion_empleados(:json_datos)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();

                    if ($result['mensaje'] === 'A') {
                        $nombre_completo_original = htmlspecialchars($nombre . ' ' . $apellido_paterno . ' ' . $apellido_materno);
                        $nombre_completo_nuevo = htmlspecialchars($nuevo_nombre . ' ' . $nuevo_apellido_paterno . ' ' . $nuevo_apellido_materno);
                        ?>
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'El empleado <?php echo addslashes($nombre_completo_original); ?> del departamento <?php echo addslashes($departamento); ?> ahora es <?php echo addslashes($nombre_completo_nuevo); ?> en departamento <?php echo addslashes($nuevo_departamento); ?> con correo <?php echo addslashes($nuevo_correo); ?>',
                                confirmButtonText: 'Aceptar',
                                timer: 5000,
                                timerProgressBar: true
                            }).then(() => {
                                window.location.href = 'listar.php';
                            });
                        </script>
                        <?php
                        exit();
                    } else {
                        throw new PDOException('Error al actualizar los datos del empleado');
                    }
                } catch (PDOException $e) {
                    ?>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al actualizar empleado: <?php echo addslashes($e->getMessage()); ?>',
                            confirmButtonText: 'Aceptar',
                            timer: 5000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = 'listar.php';
                        });
                    </script>
                    <?php
                    exit();
                }
            }
        }
        ?>
        <form id="formularioEdicion" method="POST" action="">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre; ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido_paterno" class="form-label">Apellido Paterno:</label>
                <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?php echo $apellido_paterno; ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido_materno" class="form-label">Apellido Materno:</label>
                <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?php echo $apellido_materno; ?>" required>
            </div>
            <div class="mb-3">
                <label for="departamento" class="form-label">Departamento:</label>
                <input type="text" class="form-control" id="departamento" name="departamento" value="<?php echo $departamento; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Correo:</label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo $correo; ?>" required>
            </div>
            <div class="d-flex justify-content-between flex-wrap">
                <button type="submit" class="btn btn-primary mb-2">Actualizar</button>
                <a href="listar.php" class="btn btn-danger mb-2">Cancelar</a>
            </div>
        </form>
        <div id="mensaje" class="mt-3"></div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
</body>
</html>