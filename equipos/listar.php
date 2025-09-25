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
        <h1 class="text-center">Lista de Equipos</h1>
        <h2 class="text-center">Equipos</h2>
        <div class="mb-3">
            <input type="text" id="busqueda" class="form-control" placeholder="Buscar por nombre, tipo o número de serie...">
        </div>
        <div class="mb-3 d-flex justify-content-between flex-wrap">
            <button onclick="window.location.href='crear.php'" class="btn btn-primary mb-2 me-2">Registrar Equipo</button>
            <button onclick="window.location.href='../index.php'" class="btn btn-secondary mb-2">Regresar al Menu</button>
        </div>
        <div class="table-responsive">
            <table id="tablaEquipos" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre del Equipo</th>
                        <th>Tipo</th>
                        <th>Número de Serie</th>
                        <th>Opcion</th>
                    </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
            <div id="paginacion" class="mt-3 d-flex justify-content-center gap-2"></div>
        </div>
    </div>

    <?php
    include '../db/conexion.php';

    try {
        $datos = ['opcion' => 'listar'];
        $json_datos = json_encode($datos);

        $sql = "CALL sp_gestion_equipos(:json_datos)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p class='text-center text-danger'>Error al cargar datos: " . $e->getMessage() . "</p>";
    }

    $conn = null;
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
    <script>
        const equipos = <?php echo json_encode($result); ?>;
        const filasPorPagina = 10;
        let paginaActual = 1;

        function mostrarPagina(pagina, datosFiltrados = equipos) {
            const tablaBody = document.getElementById('tablaBody');
            tablaBody.innerHTML = '';

            const inicio = (pagina - 1) * filasPorPagina;
            const fin = inicio + filasPorPagina;
            const datosPagina = datosFiltrados.slice(inicio, fin);

            datosPagina.forEach(equipo => {
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${equipo.nombre_equipo}</td>
                    <td>${equipo.tipo}</td>
                    <td>${equipo.id}</td>
                    <td>
                        <a href="editar.php?id=${equipo.id}" class="btn btn-warning btn-sm me-1">Editar</a>
                        <a href="#" class="btn btn-danger btn-sm eliminar-btn" data-numero-serie="${equipo.id}" data-nombre="${equipo.nombre_equipo}" data-tipo="${equipo.tipo}">Eliminar</a>
                    </td>
                `;
                tablaBody.appendChild(fila);
            });

            actualizarPaginacion(datosFiltrados);
        }

        function actualizarPaginacion(datosFiltrados = equipos) {
            const totalPaginas = Math.ceil(datosFiltrados.length / filasPorPagina);
            const paginacion = document.getElementById('paginacion');
            paginacion.innerHTML = '';

            const btnAnterior = document.createElement('button');
            btnAnterior.className = 'btn btn-secondary';
            btnAnterior.innerHTML = '&laquo; Anterior';
            btnAnterior.disabled = paginaActual === 1;
            btnAnterior.addEventListener('click', () => {
                if (paginaActual > 1) {
                    paginaActual--;
                    mostrarPagina(paginaActual);
                }
            });
            paginacion.appendChild(btnAnterior);

            for (let i = 1; i <= totalPaginas; i++) {
                const btnPagina = document.createElement('button');
                btnPagina.className = 'btn btn-outline-primary' + (i === paginaActual ? ' active' : '');
                btnPagina.innerText = i;
                btnPagina.addEventListener('click', () => {
                    paginaActual = i;
                    mostrarPagina(paginaActual);
                });
                paginacion.appendChild(btnPagina);
            }

            const btnSiguiente = document.createElement('button');
            btnSiguiente.className = 'btn btn-secondary';
            btnSiguiente.innerHTML = 'Siguiente &raquo;';
            btnSiguiente.disabled = paginaActual === totalPaginas;
            btnSiguiente.addEventListener('click', () => {
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    mostrarPagina(paginaActual);
                }
            });
            paginacion.appendChild(btnSiguiente);
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('eliminar-btn')) {
                e.preventDefault();
                const numero_serie = e.target.getAttribute('data-numero-serie');
                const nombre_equipo = e.target.getAttribute('data-nombre');
                const tipo = e.target.getAttribute('data-tipo');

                Swal.fire({
                    icon: 'warning',
                    title: 'Confirmar Eliminación',
                    text: `Se va a eliminar el equipo ${nombre_equipo} de tipo ${tipo}, ¿está seguro de realizar esta opción?`,
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Redirigiendo a eliminar.php con numero_serie:', numero_serie);
                        window.location.href = `eliminar.php?numero_serie=${encodeURIComponent(numero_serie)}`;
                    }
                });
            }
        });

        const inputBusqueda = document.getElementById('busqueda');
        inputBusqueda.addEventListener('keyup', function() {
            const filtro = inputBusqueda.value.toLowerCase();
            const datosFiltrados = equipos.filter(equipo => {
                return (
                    equipo.nombre_equipo.toLowerCase().includes(filtro) ||
                    equipo.tipo.toLowerCase().includes(filtro) ||
                    String(equipo.id).toLowerCase().includes(filtro)
                );
            });

            paginaActual = 1;
            mostrarPagina(paginaActual, datosFiltrados);
        });

        mostrarPagina(paginaActual);
    </script>
</main>
 <?php include '../assets/footer.php'?> 