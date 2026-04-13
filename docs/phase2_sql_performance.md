Fase 2: SQL y rendimiento

Consultas mas costosas observadas:

- Catalogos de items: filtran por tipo_id, deleted_at, estado y ordenan por id descendente.
- Solicitudes: listados por estado y fecha_solicitud, historial por usuario y conteos por fechas.
- Rankings de visitas: join entre items_biblioteca y visitas_items por item_id con filtro por fecha_visita.
- Generacion de codigos: conteo por tipo_id, categoria_id, anio y deleted_at.

Hallazgos de EXPLAIN sobre la base actual:

- items_biblioteca para catalogos estaba recorriendo el PRIMARY con where residual en lugar de usar un indice compuesto.
- solicitudes_prestamo para listados admin hacia full scan y filesort.
- visitas por rango usaba temporary y filesort; faltaba indice compuesto en visitas_items.
- conteos mensuales de prestamos estaban haciendo full scan al no tener indice por fecha.

Resultado de la ejecucion en la base local:

- Los indices de la fase 2 ya estaban presentes al momento de ejecutar la migracion.
- SHOW INDEX confirmo la presencia de los compuestos en items_biblioteca, solicitudes_prestamo, visitas_items y auditoria.
- EXPLAIN mejoro de forma clara en solicitudes admin usando idx_sp_estado_fecha_solicitud.
- EXPLAIN mejoro de forma clara en conteos mensuales usando idx_sp_fecha_prestamo_estado con acceso range.
- EXPLAIN ya usa idx_visitas_item_fecha para visitas_items; el costo restante en rankings viene del GROUP BY y ORDER BY por agregacion.
- El listado de catalogos de items sigue siendo resuelto por el optimizador con el PRIMARY para el ORDER BY id DESC; con el volumen actual observado no es un problema critico.

Cambios aplicados en codigo:

- app/Models/SolicitudesModel.php ahora usa rangos de fecha sargables en vez de DATE_FORMAT para filtros por mes.
- app/Models/LibrosModel.php, app/Models/PublicacionModel.php y app/Models/TesisModel.php fuerzan LIMIT entero positivo.

Restricciones unicas verificadas en el esquema actual:

- items_biblioteca.codigo ya es UNIQUE.
- usuarios.cedula ya es UNIQUE.
- usuarios.email ya es UNIQUE.
- categorias(tipo_id, nombre) ya es UNIQUE.

Script de migracion:

- sql/performance_phase2_indexes.sql agrega de forma idempotente los indices faltantes mas utiles para las consultas del proyecto.

Orden recomendado:

1. Respaldar la base.
2. Ejecutar sql/performance_phase2_indexes.sql en MySQL.
3. Correr EXPLAIN de nuevo sobre consultas criticas.
4. Medir tiempos de listados admin, dashboard y reportes.
5. Si el volumen de items_biblioteca crece bastante, reevaluar el plan del catalogo y considerar FORCE INDEX solo si EXPLAIN y tiempos reales lo justifican.

Riesgos y notas:

- Cada indice adicional acelera lectura pero hace un poco mas costosos los INSERT y UPDATE.
- No agregue nuevas restricciones unicas para codigos porque la principal ya existe y duplicarla seria redundante.
- La generacion de codigos por COUNT sigue siendo susceptible a colisiones bajo alta concurrencia; la unicidad la resuelve la restriccion UNIQUE actual, pero la aplicacion deberia reintentar si recibe duplicate key.