<?php
// auditoria/log_actividades.php
include_once "../auth.php";
include_once "../db.php";

// Registrar acceso al módulo
registrarActividad('ACCESO', 'AUDITORIA', 'Acceso al log de actividades', null, null);

// Filtros
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : "";
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : "";
$usuario = isset($_GET['usuario']) ? trim($_GET['usuario']) : "todos";
$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : "todos";
$accion = isset($_GET['accion']) ? $_GET['accion'] : "todos";
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";

$condiciones = array();

if ($usuario !== "todos") {
    $condiciones[] = "usuario = " . $conexion->quote($usuario);
}

if ($modulo !== "todos") {
    $condiciones[] = "modulo = " . $conexion->quote($modulo);
}

if ($accion !== "todos") {
    $condiciones[] = "accion = " . $conexion->quote($accion);
}

if (!empty($fecha_desde)) {
    $condiciones[] = "DATE(fecha_hora) >= '$fecha_desde'";
}

if (!empty($fecha_hasta)) {
    $condiciones[] = "DATE(fecha_hora) <= '$fecha_hasta'";
}

if (!empty($buscar)) {
    $condiciones[] = "(descripcion LIKE " . $conexion->quote('%'.$buscar.'%') . " OR usuario LIKE " . $conexion->quote('%'.$buscar.'%') . ")";
}

$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

// Obtener logs con paginación
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$registros_por_pagina = 50;
$offset = ($pagina - 1) * $registros_por_pagina;

$sentencia = $conexion->prepare("
    SELECT * FROM log_actividades 
    $where_clause
    ORDER BY fecha_hora DESC
    LIMIT $registros_por_pagina OFFSET $offset
");
$sentencia->execute();
$logs = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Contar total de registros
$sentenciaTotal = $conexion->prepare("SELECT COUNT(*) FROM log_actividades $where_clause");
$sentenciaTotal->execute();
$total_registros = $sentenciaTotal->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Obtener usuarios únicos
$sentenciaUsuarios = $conexion->prepare("SELECT DISTINCT usuario FROM log_actividades ORDER BY usuario ASC");
$sentenciaUsuarios->execute();
$usuarios = $sentenciaUsuarios->fetchAll(PDO::FETCH_COLUMN);

// Obtener módulos únicos
$sentenciaModulos = $conexion->prepare("SELECT DISTINCT modulo FROM log_actividades ORDER BY modulo ASC");
$sentenciaModulos->execute();
$modulos = $sentenciaModulos->fetchAll(PDO::FETCH_COLUMN);

// Obtener acciones únicas
$sentenciaAcciones = $conexion->prepare("SELECT DISTINCT accion FROM log_actividades ORDER BY accion ASC");
$sentenciaAcciones->execute();
$acciones = $sentenciaAcciones->fetchAll(PDO::FETCH_COLUMN);

// Convertir a JSON
$logsJSON = json_encode($logs);
$usuariosJSON = json_encode($usuarios);
$modulosJSON = json_encode($modulos);
$accionesJSON = json_encode($acciones);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log de Actividades</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <link href="../css/listados.css" rel="stylesheet">
    <style>
        .main-content { background: #2c3e50 !important; color: white; }
        
        .accion-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
        }
        
        .accion-crear { background: linear-gradient(45deg, #27ae60, #2ecc71); color: white; }
        .accion-editar { background: linear-gradient(45deg, #3498db, #2980b9); color: white; }
        .accion-eliminar { background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; }
        .accion-login { background: linear-gradient(45deg, #9b59b6, #8e44ad); color: white; }
        .accion-logout { background: linear-gradient(45deg, #95a5a6, #7f8c8d); color: white; }
        .accion-acceso { background: linear-gradient(45deg, #f39c12, #f1c40f); color: #2c3e50; }
        .accion-error { background: linear-gradient(45deg, #e74c3c, #c0392b); color: white; }
        .accion-apertura-caja { background: linear-gradient(45deg, #27ae60, #16a085); color: white; }
        .accion-cierre-caja { background: linear-gradient(45deg, #e67e22, #d35400); color: white; }
        .accion-default { background: linear-gradient(45deg, #34495e, #2c3e50); color: white; }
        
        .modulo-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: bold;
            display: inline-block;
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
            border: 1px solid rgba(52, 152, 219, 0.5);
        }
        
        .expandable-row {
            display: none;
            background: rgba(241, 196, 15, 0.05) !important;
        }
        
        .expandable-row.show {
            display: table-row;
            animation: slideDown 0.3s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .details-container {
            padding: 20px;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            margin: 10px;
        }
        
        .details-section {
            background: rgba(52, 152, 219, 0.1);
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            border: 1px solid rgba(52, 152, 219, 0.3);
        }
        
        .details-title {
            color: #3498db;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .json-data {
            background: rgba(0,0,0,0.4);
            padding: 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.75rem;
            color: #2ecc71;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .expand-btn {
            background: transparent !important;
            border: none !important;
            color: #f1c40f !important;
            cursor: pointer !important;
            padding: 4px 8px !important;
            border-radius: 4px !important;
            transition: all 0.3s ease !important;
            font-size: 0.7rem !important;
        }
        
        .expand-btn:hover {
            background: rgba(241, 196, 15, 0.1) !important;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            background: rgba(241, 196, 15, 0.1);
            border: 2px solid rgba(241, 196, 15, 0.3);
            border-radius: 6px;
            color: #f1c40f;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .pagination a:hover {
            background: rgba(241, 196, 15, 0.2);
            border-color: #f1c40f;
            transform: translateY(-2px);
        }
        
        .pagination .current {
            background: linear-gradient(45deg, #f39c12, #f1c40f);
            color: #2c3e50;
            border-color: #f1c40f;
        }
        
        .estadisticas-resumen {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card-small {
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            border: 2px solid rgba(241, 196, 15, 0.3);
        }
        
        .stat-number-small {
            font-size: 1.8rem;
            font-weight: bold;
            color: #f1c40f;
            margin-bottom: 5px;
        }
        
        .stat-label-small {
            color: #ecf0f1;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            if (!mainContent) return console.error('No .main-content');

            const logs = <?php echo $logsJSON; ?>;
            const usuarios = <?php echo $usuariosJSON; ?>;
            const modulos = <?php echo $modulosJSON; ?>;
            const acciones = <?php echo $accionesJSON; ?>;
            const paginaActual = <?php echo $pagina; ?>;
            const totalPaginas = <?php echo $total_paginas; ?>;
            const totalRegistros = <?php echo $total_registros; ?>;
            
            const formatDate = (dateStr) => {
                const d = new Date(dateStr);
                return d.toLocaleString('es-PY', { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            };
            
            const getAccionClass = (accion) => {
                const map = {
                    'CREAR': 'accion-crear',
                    'EDITAR': 'accion-editar',
                    'ELIMINAR': 'accion-eliminar',
                    'LOGIN': 'accion-login',
                    'LOGOUT': 'accion-logout',
                    'ACCESO': 'accion-acceso',
                    'ERROR': 'accion-error',
                    'APERTURA_CAJA': 'accion-apertura-caja',
                    'CIERRE_CAJA': 'accion-cierre-caja'
                };
                return map[accion] || 'accion-default';
            };
            
            const getAccionIcon = (accion) => {
                const icons = {
                    'CREAR': '',
                    'EDITAR': '',
                    'ELIMINAR': '',
                    'LOGIN': '',
                    'LOGOUT': '',
                    'ACCESO': '',
                    'ERROR': '',
                    'APERTURA_CAJA': '',
                    'CIERRE_CAJA': ''
                };
                return icons[accion] || '';
            };
            
            const formatJSON = (jsonStr) => {
                if (!jsonStr) return 'N/A';
                try {
                    const obj = JSON.parse(jsonStr);
                    return JSON.stringify(obj, null, 2);
                } catch (e) {
                    return jsonStr;
                }
            };
            
            let logsHTML = '';
            
            if (logs && logs.length > 0) {
                logs.forEach((log, index) => {
                    const accionClass = getAccionClass(log.accion);
                    const accionIcon = getAccionIcon(log.accion);
                    
                    const detallesHTML = `
                        <div class="details-container">
                            <div class="details-section">
                                <div class="details-title">Informacion General</div>
                                <p><strong>ID:</strong> ${log.id}</p>
                                <p><strong>Usuario:</strong> ${log.usuario}</p>
                                <p><strong>Acción:</strong> ${log.accion}</p>
                                <p><strong>Módulo:</strong> ${log.modulo}</p>
                                <p><strong>Fecha/Hora:</strong> ${formatDate(log.fecha_hora)}</p>
                                <p><strong>IP:</strong> ${log.ip_address || 'N/A'}</p>
                                <p><strong>User Agent:</strong> ${log.user_agent ? log.user_agent.substring(0, 100) + '...' : 'N/A'}</p>
                            </div>
                            
                            <div class="details-section">
                                <div class="details-title">Descripción</div>
                                <p>${log.descripcion || 'Sin descripción'}</p>
                            </div>
                            
                            ${log.datos_anteriores ? `
                                <div class="details-section">
                                    <div class="details-title">Datos Anteriores</div>
                                    <div class="json-data">${formatJSON(log.datos_anteriores)}</div>
                                </div>
                            ` : ''}
                            
                            ${log.datos_nuevos ? `
                                <div class="details-section">
                                    <div class="details-title">Datos Nuevos</div>
                                    <div class="json-data">${formatJSON(log.datos_nuevos)}</div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                    
                    logsHTML += `
                        <tr>
                            <td><strong>#${log.id}</strong></td>
                            <td>${formatDate(log.fecha_hora)}</td>
                            <td><strong>${log.usuario}</strong></td>
                            <td><span class="accion-badge ${accionClass}">${accionIcon} ${log.accion}</span></td>
                            <td><span class="modulo-badge">${log.modulo}</span></td>
                            <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${log.descripcion || '-'}</td>
                            <td>
                                <button class="expand-btn" onclick="toggleDetails(${index})">
                                    <span id="icon-${index}">▼</span> Ver Más
                                </button>
                            </td>
                        </tr>
                        <tr class="expandable-row" id="details-${index}">
                            <td colspan="7">${detallesHTML}</td>
                        </tr>
                    `;
                });
            } else {
                logsHTML = '<tr><td colspan="7" class="no-results">No se encontraron registros</td></tr>';
            }
            
            const usuariosOpts = '<option value="todos">-- TODOS --</option>' + 
                usuarios.map(u => `<option value="${u}" ${u === '<?php echo $usuario; ?>' ? 'selected' : ''}>${u}</option>`).join('');
            
            const modulosOpts = '<option value="todos">-- TODOS --</option>' + 
                modulos.map(m => `<option value="${m}" ${m === '<?php echo $modulo; ?>' ? 'selected' : ''}>${m}</option>`).join('');
            
            const accionesOpts = '<option value="todos">-- TODAS --</option>' + 
                acciones.map(a => `<option value="${a}" ${a === '<?php echo $accion; ?>' ? 'selected' : ''}>${a}</option>`).join('');
            
            let paginacionHTML = '';
            if (totalPaginas > 1) {
                paginacionHTML = '<div class="pagination">';
                
                if (paginaActual > 1) {
                    paginacionHTML += `<a href="?pagina=${paginaActual - 1}&fecha_desde=<?php echo $fecha_desde; ?>&fecha_hasta=<?php echo $fecha_hasta; ?>&usuario=<?php echo $usuario; ?>&modulo=<?php echo $modulo; ?>&accion=<?php echo $accion; ?>&buscar=<?php echo urlencode($buscar); ?>">← Anterior</a>`;
                }
                
                for (let i = Math.max(1, paginaActual - 2); i <= Math.min(totalPaginas, paginaActual + 2); i++) {
                    if (i === paginaActual) {
                        paginacionHTML += `<span class="current">${i}</span>`;
                    } else {
                        paginacionHTML += `<a href="?pagina=${i}&fecha_desde=<?php echo $fecha_desde; ?>&fecha_hasta=<?php echo $fecha_hasta; ?>&usuario=<?php echo $usuario; ?>&modulo=<?php echo $modulo; ?>&accion=<?php echo $accion; ?>&buscar=<?php echo urlencode($buscar); ?>">${i}</a>`;
                    }
                }
                
                if (paginaActual < totalPaginas) {
                    paginacionHTML += `<a href="?pagina=${paginaActual + 1}&fecha_desde=<?php echo $fecha_desde; ?>&fecha_hasta=<?php echo $fecha_hasta; ?>&usuario=<?php echo $usuario; ?>&modulo=<?php echo $modulo; ?>&accion=<?php echo $accion; ?>&buscar=<?php echo urlencode($buscar); ?>">Siguiente →</a>`;
                }
                
                paginacionHTML += '</div>';
            }
            
            const contentHTML = `
                <div class="list-container">
                    <h1 class="list-title">Log de Actividades del Sistema</h1>
                    
                    <div class="estadisticas-resumen">
                        <div class="stat-card-small">
                            <div class="stat-number-small">${totalRegistros}</div>
                            <div class="stat-label-small">Total Registros</div>
                        </div>
                        <div class="stat-card-small">
                            <div class="stat-number-small">${logs.length}</div>
                            <div class="stat-label-small">En Esta Página</div>
                        </div>
                        <div class="stat-card-small">
                            <div class="stat-number-small">${totalPaginas}</div>
                            <div class="stat-label-small">Total Páginas</div>
                        </div>
                    </div>
                    
                    <div class="filter-container">
                        <form method="GET" action="">
                            <label class="label">Filtrar Logs</label>
                            <div class="search-controls">
                                <div class="search-field">
                                    <label>Desde:</label>
                                    <input type="date" name="fecha_desde" class="search-input" value="<?php echo $fecha_desde; ?>">
                                </div>
                                <div class="search-field">
                                    <label>Hasta:</label>
                                    <input type="date" name="fecha_hasta" class="search-input" value="<?php echo $fecha_hasta; ?>">
                                </div>
                                <div class="search-field">
                                    <label>Usuario:</label>
                                    <div class="select">
                                        <select name="usuario" class="search-input">${usuariosOpts}</select>
                                    </div>
                                </div>
                                <div class="search-field">
                                    <label>Módulo:</label>
                                    <div class="select">
                                        <select name="modulo" class="search-input">${modulosOpts}</select>
                                    </div>
                                </div>
                                <div class="search-field">
                                    <label>Acción:</label>
                                    <div class="select">
                                        <select name="accion" class="search-input">${accionesOpts}</select>
                                    </div>
                                </div>
                                <div class="search-field">
                                    <label>Buscar:</label>
                                    <input type="text" name="buscar" class="search-input" placeholder="Descripción..." value="<?php echo htmlspecialchars($buscar); ?>">
                                </div>
                                <div class="search-field">
                                    <button type="submit" class="button" style="margin-top: 22px;">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="table is-fullwidth custom-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>FECHA/HORA</th>
                                    <th>USUARIO</th>
                                    <th>ACCIÓN</th>
                                    <th>MÓDULO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>DETALLES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${logsHTML}
                            </tbody>
                        </table>
                    </div>
                    
                    ${paginacionHTML}
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
            
            window.toggleDetails = function(index) {
                const row = document.getElementById(`details-${index}`);
                const icon = document.getElementById(`icon-${index}`);
                
                if (row.classList.contains('show')) {
                    row.classList.remove('show');
                    icon.textContent = '▼';
                } else {
                    row.classList.add('show');
                    icon.textContent = '▲';
                }
            };
        });
    </script>
</body>
</html>
