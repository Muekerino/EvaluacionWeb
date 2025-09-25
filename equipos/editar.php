<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluacion Web</title>
    <link rel="stylesheet" href="../assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/maincss.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<?php include '../assets/header.php'?> 
<main>
    <div class="container mt-5">
        <h1 class="text-center">Actualizar Equipo</h1>
        <?php
        include '../db/conexion.php';

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $nombre_equipo = '';
        $tipo = '';

        if ($id) {
            try {
                $datos = ['opcion' => 'obtener', 'id' => $id];
                $json_datos = json_encode($datos);
                $sql = "CALL sp_gestion_equipos(:json_datos)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                if ($result) {
                    $nombre_equipo = htmlspecialchars($result['nombre_equipo']);
                    $tipo = htmlspecialchars($result['tipo']);
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
                        text: 'Error al cargar datos del equipo: <?php echo addslashes($e->getMessage()); ?>',
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = 'listar.php';
                    });
                </script>
                <?php
                exit();
            }
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'editar') {
            $nuevo_nombre_equipo = $_POST['nombre_equipo'];
            $nuevo_tipo = $_POST['tipo'];

            $datos_iguales = (
                $nombre_equipo === $nuevo_nombre_equipo &&
                $tipo === $nuevo_tipo
            );

            if ($datos_iguales) {
                ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                <script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin cambios',
                        text: 'No se detectaron cambios en la información del equipo.',
                        confirmButtonText: 'Aceptar'
                    });
                </script>
                <?php
            } else {
                $datos = [
                    'opcion' => 'editar',
                    'id' => $id,
                    'nombre_equipo' => $nuevo_nombre_equipo,
                    'tipo' => $nuevo_tipo
                ];
                $json_datos = json_encode($datos);

                try {
                    $sql = "CALL sp_gestion_equipos(:json_datos)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();

                    if ($result['mensaje'] === 'A') {
                        $nombre_original = addslashes($nombre_equipo);
                        $tipo_original = addslashes($tipo);
                        $nuevo_nombre = addslashes($nuevo_nombre_equipo);
                        $nuevo_tipo = addslashes($nuevo_tipo);
                        ?>
                        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                        <script>
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'El equipo <?php echo $nombre_original; ?> del tipo <?php echo $tipo_original; ?> ahora es <?php echo $nuevo_nombre; ?> del tipo <?php echo $nuevo_tipo; ?>',
                                confirmButtonText: 'Aceptar',
                                timer: 5000,
                                timerProgressBar: true
                            });
                        </script>
                        <?php
                    } else {
                        throw new PDOException('Error al actualizar el equipo');
                    }
                } catch (PDOException $e) {
                    ?>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al actualizar equipo: <?php echo addslashes($e->getMessage()); ?>',
                            confirmButtonText: 'Aceptar',
                            timer: 5000,
                            timerProgressBar: true
                        });
                    </script>
                    <?php
                }
            }
        }
        ?>
        <form id="formularioEdicion" method="POST" action="">
            <input type="hidden" name="action" value="editar">
            <div class="mb-3">
                <label for="nombre_equipo" class="form-label">Nombre del Equipo:</label>
                <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" value="<?php echo $nombre_equipo; ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo" class="form-label">Tipo:</label>
                <input type="text" class="form-control" id="tipo" name="tipo" value="<?php echo $tipo; ?>" required>
            </div>
            <div class="d-flex justify-content-between flex-wrap">
                <button type="submit" class="btn btn-primary mb-2">Actualizar</button>
                <a href="integrantes.php?id=<?php echo $id; ?>" class="btn btn-success mb-2 me-2">Modificar Integrantes</a>
                <a href="listar.php" class="btn btn-secondary mb-2">Regresar</a>
            </div>
        </form>
        <div id="mensaje" class="mt-3"></div>

        <h2 class="mt-5">Lista del Equipo</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre del Empleado</th>
                        <th>Departamento</th>
                    </tr>
                </thead>
                <tbody id="tablaIntegrantes">
                    <?php
                    try {
                        $datos = ['opcion' => 'listar_integrantes', 'id_equipo' => $id];
                        $json_datos = json_encode($datos);
                        $sql = "CALL sp_gestion_equipos(:json_datos)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                        $stmt->execute();
                        $integrantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $stmt->closeCursor();

                        if (count($integrantes) > 0) {
                            foreach ($integrantes as $integrante) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($integrante['nombre_completo']) . "</td>";
                                echo "<td>" . htmlspecialchars($integrante['departamento']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' class='text-center'>No hay integrantes asignados</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='2' class='text-center text-danger'>Error al cargar integrantes: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
    
<?php include '../assets/footer.php'?> 