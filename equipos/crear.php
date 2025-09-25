<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Equipo</title>
    <link rel="stylesheet" href="../assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/maincss.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<?php include '../assets/header.php'?>
<main>
    <div class="container mt-5">
        <h1 class="text-center">Registrar Equipo</h1>
        <?php
        include '../db/conexion.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre_equipo = $_POST['nombre_equipo'];
            $tipo = $_POST['tipo'];
            $datos = [
                'opcion' => 'crear',
                'nombre_equipo' => $nombre_equipo,
                'tipo' => $tipo
            ];
            $json_datos = json_encode($datos);

            try {
                $sql = "CALL sp_gestion_equipos(:json_datos)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['mensaje'] === 'A') {
                    ?>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Se registró el equipo <?php echo addslashes($nombre_equipo); ?> con el tipo <?php echo addslashes($tipo); ?>',
                            confirmButtonText: 'Aceptar',
                            timer: 3000,
                            timerProgressBar: true
                        }).then(() => {
                            window.location.href = 'listar.php';
                        });
                    </script>
                    <?php
                    exit();
                } else {
                    throw new PDOException('Error al registrar el equipo');
                }
            } catch (PDOException $e) {
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo registrar el equipo: <?php echo addslashes($e->getMessage()); ?>',
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
                <label for="nombre_equipo" class="form-label">Nombre del Equipo:</label>
                <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo:</label>
                <input type="text" class="form-control" id="tipo" name="tipo" required>
            </div>
            <div class="d-flex justify-content-between flex-wrap">
                <button type="submit" class="btn btn-success mb-2">Registrar</button>
                <a href="listar.php" class="btn btn-danger mb-2">Cancelar</a>
            </div>
        </form>
        <div id="mensaje" class="mt-3"></div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
 <?php include '../assets/footer.php'?> 