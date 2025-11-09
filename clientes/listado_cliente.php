<?php
// Procesamiento de datos al inicio
include_once __DIR__ . "/../auth.php";include_once "../db.php";

$estado = isset($_GET['estado']) ? $_GET['estado'] : "99";
$tipo_busqueda = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : "todos";
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : "";

// Construir condiciones WHERE
$condiciones = array();

// Filtro por estado
if ($estado !== "99") {
    $condiciones[] = "c.estado_cliente = " . intval($estado);
}

// Filtro por búsqueda
if (!empty($busqueda) && $tipo_busqueda !== "todos") {
    switch ($tipo_busqueda) {
        case "nombre":
            $condiciones[] = "c.nombre_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "apellido":
            $condiciones[] = "c.apellido_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "ci_ruc":
            $condiciones[] = "c.ci_ruc_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "telefono":
            $condiciones[] = "c.telefono_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "correo":
            $condiciones[] = "c.correo_cliente LIKE '%" . $busqueda . "%'";
            break;
    }
}

// Construir WHERE clause
$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

$sentencia = $conexion->prepare("
    SELECT c.* 
    FROM clientes c
    $where_clause
    ORDER BY c.nombre_cliente ASC
");

$sentencia->execute();
$clientes = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Convertir a JSON para JavaScript
$clientesJSON = json_encode($clientes);
$estadoActual = isset($_GET['estado']) ? $_GET['estado'] : '99';
$tipoBusquedaActual = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : 'todos';
$busquedaActual = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Clientes</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const clientes = <?php echo $clientesJSON; ?>;
            const estadoActual = '<?php echo $estadoActual; ?>';
            const tipoBusquedaActual = '<?php echo $tipoBusquedaActual; ?>';
            const busquedaActual = '<?php echo addslashes($busquedaActual); ?>';
            
            let clientesHTML = '';
            if (clientes && clientes.length > 0) {
                clientes.forEach(cliente => {
                    const estado = cliente.estado_cliente == 1 
                        ? '<span class="status-active">ACTIVO</span>' 
                        : '<span class="status-inactive">INACTIVO</span>';
                    
                    clientesHTML += `
                        <tr>
                            <td><strong>${cliente.id}</strong></td>
                            <td>${cliente.nombre_cliente || '-'}</td>
                            <td>${cliente.apellido_cliente || '-'}</td>
                            <td>${cliente.ci_ruc_cliente || '-'}</td>
                            <td>${cliente.telefono_cliente || '-'}</td>
                            <td>${cliente.correo_cliente || '-'}</td>
                            <td>${cliente.direccion_cliente || '-'}</td>
                            <td>${estado}</td>
                            <td>
                                <a href='frm_editar_cliente.php?id=${cliente.id}' class='edit-link'>
                                    EDITAR
                                </a>
                            </td>
                        </tr>
                    `;
                });
            } else {
                clientesHTML = `
                    <tr>
                        <td colspan="9" class="no-results">
                            No se encontraron clientes con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>Listado de Clientes</h1>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>Buscar Clientes</label>
                            <div class='search-controls'>
                                <div class='search-field' style='min-width: 200px;'>
                                    <label>Buscar por:</label>
                                    <div class='select'>
                                        <select name='tipo_busqueda' class='search-input'>
                                            <option value='todos' ${tipoBusquedaActual == 'todos' ? 'selected' : ''}>-- TODOS --</option>
                                            <option value='nombre' ${tipoBusquedaActual == 'nombre' ? 'selected' : ''}>Nombre</option>
                                            <option value='apellido' ${tipoBusquedaActual == 'apellido' ? 'selected' : ''}>Apellido</option>
                                            <option value='ci_ruc' ${tipoBusquedaActual == 'ci_ruc' ? 'selected' : ''}>CI / RUC</option>
                                            <option value='telefono' ${tipoBusquedaActual == 'telefono' ? 'selected' : ''}>Teléfono</option>
                                            <option value='correo' ${tipoBusquedaActual == 'correo' ? 'selected' : ''}>Correo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='flex: 1; min-width: 250px;'>
                                    <label>Término de búsqueda:</label>
                                    <input type='text' name='busqueda' class='search-input' placeholder='Escribe aquí para buscar...' value='${busquedaActual}'>
                                </div>
                                
                                <div class='search-field' style='min-width: 150px;'>
                                    <label>Estado:</label>
                                    <div class='select'>
                                        <select name='estado' class='search-input'>
                                            <option value='99' ${estadoActual == '99' ? 'selected' : ''}> -- TODOS --</option>
                                            <option value='1' ${estadoActual == '1' ? 'selected' : ''}>ACTIVO</option>
                                            <option value='0' ${estadoActual == '0' ? 'selected' : ''}>INACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='min-width: auto;'>
                                    <button type='submit' class='button' style='margin-top: 22px; padding: 12px 20px; height: 44px;'>
                                        Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div style='overflow-x: auto;'>
                        <table class='table is-fullwidth custom-table'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NOMBRE</th>
                                    <th>APELLIDO</th>
                                    <th>CI / RUC</th>
                                    <th>TELÉFONO</th>
                                    <th>CORREO</th>
                                    <th>DIRECCIÓN</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${clientesHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>