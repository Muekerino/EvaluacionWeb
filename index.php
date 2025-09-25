 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluacion Web</title>
    <link rel="stylesheet" href="assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/maincss.css">
</head>
 <?php include 'assets/header.php'?>   
    <main>
        <div class="container d-flex flex-column align-items-center">
            <h2>Evaluacion web para puesto de programador</h2>
            <h2>Crear un sistema web completo tipo CRUD utilizando únicamente PHP</h2>
        </div>
        <div class="container d-flex flex-column align-items-center">
            <div class="d-flex gap-3">
                <button type="button" class="btn btn-primary btn-lg" onclick="window.location.href='empleados/listar.php'">Empleados</button>
                <button type="button" class="btn btn-primary btn-lg" onclick="window.location.href='equipos/listar.php'">Equipos</button>
            </div>
        </div>
    </main>
 <?php include 'assets/footer.php'?> 
    