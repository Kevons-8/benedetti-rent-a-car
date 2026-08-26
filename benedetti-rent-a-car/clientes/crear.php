<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<title>Registrar Cliente</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family: Arial, sans-serif;
    min-height:100vh;
    background:
        linear-gradient(135deg, rgba(3,13,31,0.85), rgba(6,32,71,0.80)),
        url("/benedetti-rent-a-car/assets/img/fondo_vehiculos.png") center center / cover no-repeat fixed;
    color:#fff;
    padding:45px 7%;
}

.container{
    max-width:1100px;
    margin:0 auto;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
    margin-bottom:30px;
}

.badge{
    display:inline-flex;
    padding:8px 14px;
    border-radius:999px;
    background:rgba(34,197,94,0.15);
    border:1px solid rgba(34,197,94,0.35);
    color:#86efac;
    font-size:0.85rem;
    font-weight:700;
    margin-bottom:10px;
}

h1{
    font-size:clamp(2rem,4vw,3rem);
    margin-bottom:10px;
}

.header p{
    color:#cfd8e6;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    padding:12px 22px;
    border-radius:999px;
    text-decoration:none;
    font-weight:800;
    font-size:0.9rem;
    transition:all 0.25s ease;
}

.btn-dark{
    background:rgba(3,10,24,0.65);
    border:1px solid rgba(255,255,255,0.15);
    color:#fff;
}

.btn-dark:hover{
    transform:translateY(-2px);
    background:rgba(3,10,24,0.85);
}

.card{
    background:rgba(15,28,51,0.88);
    border:1px solid rgba(255,255,255,0.12);
    border-radius:26px;
    padding:30px;
    box-shadow:0 24px 70px rgba(0,0,0,0.35);
    backdrop-filter:blur(16px);
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.form-group-full{
    grid-column:1 / -1;
}

label{
    font-size:0.9rem;
    font-weight:700;
    margin-bottom:6px;
    color:#ffffff;
}

input,
select{
    width:100%;
    padding:13px 14px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,0.15);
    background:rgba(8,21,45,0.95);
    color:#fff;
    font-size:0.92rem;
}

input::placeholder{
    color:#8ea3bf;
}

input:focus,
select:focus{
    outline:none;
    border-color:#22c55e;
    box-shadow:0 0 0 3px rgba(34,197,94,0.15);
}

.form-footer{
    display:flex;
    gap:14px;
    flex-wrap:wrap;
    margin-top:26px;
    padding-top:22px;
    border-top:1px solid rgba(255,255,255,0.10);
}

.btn-primary{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:50px;
    padding:13px 24px;
    border:none;
    border-radius:999px;
    font-weight:800;
    cursor:pointer;
    text-decoration:none;
    color:#fff;
    background:linear-gradient(180deg,#6eff1f 0%,#38d600 45%,#19a500 100%);
    box-shadow:
        inset 0 2px 0 rgba(255,255,255,0.32),
        0 12px 24px rgba(34,197,94,0.28);
    transition:all 0.25s ease;
}

.btn-primary:hover{
    transform:translateY(-2px);
    box-shadow:
        inset 0 2px 0 rgba(255,255,255,0.35),
        0 16px 32px rgba(34,197,94,0.38);
}

@media(max-width:800px){
    body{
        padding:35px 5%;
    }

    .header{
        flex-direction:column;
    }

    .form-grid{
        grid-template-columns:1fr;
    }

    .actions,
    .form-footer{
        width:100%;
        flex-direction:column;
    }

    .btn,
    .btn-primary{
        width:100%;
    }
}
</style>

</head>

<body>

<div class="container">

    <div class="header">
        <div>
            <span class="badge">Registro de cliente</span>
            <h1>Registrar Cliente</h1>
            <p>Agrega un nuevo cliente al sistema de Benedetti Rent a Car.</p>
        </div>

        <div class="actions">
            <a href="index.php" class="btn btn-dark">← Volver al listado</a>
            <a href="../admin/dashboard.php" class="btn btn-dark">Dashboard</a>
        </div>
    </div>

    <div class="card">

        <form action="guardar.php" method="POST">

            <div class="form-grid">

                <div class="form-group">
                    <label for="tipo_documento">Tipo de documento</label>
                    <select name="tipo_documento" id="tipo_documento" required>
                        <option value="">Seleccione una opción</option>
                        <option value="CC">Cédula de ciudadanía</option>
                        <option value="CE">Cédula de extranjería</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="NIT">NIT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numero_documento">Número de documento</label>
                    <input type="text" name="numero_documento" id="numero_documento" placeholder="Ej: 1046709161" required>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre del cliente" required>
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" name="apellido" id="apellido" placeholder="Apellido del cliente" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" name="telefono" id="telefono" placeholder="Ej: 3001234567" required>
                </div>

                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" name="correo" id="correo" placeholder="cliente@correo.com">
                </div>

                <div class="form-group form-group-full">
                    <label for="direccion">Dirección</label>
                    <input type="text" name="direccion" id="direccion" placeholder="Dirección del cliente">
                </div>

                <div class="form-group form-group-full">
                    <label for="licencia_conduccion">Licencia de conducción</label>
                    <input type="text" name="licencia_conduccion" id="licencia_conduccion" placeholder="Número de licencia">
                </div>

            </div>

            <div class="form-footer">
                <button type="submit" class="btn-primary">
                    Guardar Cliente
                </button>

                <a href="index.php" class="btn btn-dark">
                    Cancelar
                </a>
            </div>

        </form>

    </div>

</div>

</body>
</html>