<?php

    class Models_DbTable_Certificadocompetencia extends Zend_Db_Table {
        protected $_schema   = 'produccion';
        protected $_name     = 'tbl_accesos';
        protected $_primary  = 'pk_acceso';
        protected $_sequence = true;
        
        public function init() {
            $this->SwapBytes_Array = new SwapBytes_Array();
        }
        
        public function getResultado($ci){
            
            $SQL = "SELECT CASE WHEN (sqt.Mat_Apr = (SELECT COUNT(*)
                                                     FROM tbl_asignaturas asig
                                                     JOIN tbl_pensums pen ON pen.pk_pensum = asig.fk_pensum
                                                     WHERE pen.fk_escuela = (SELECT DISTINCT pen.fk_escuela
                                                                             FROM tbl_pensums pen
                                                                             JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
                                                                             JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                                                                             JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                                                                             WHERE us.pk_usuario = {$ci}
                                                                             AND ins.fk_periodo = (SELECT ins.fk_periodo
                                                                                                   FROM tbl_usuarios us
                                                                                                   JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
                                                                                                   JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                                                                   WHERE us.pk_usuario = {$ci}
                                                                                                   ORDER BY 1 DESC
                                                                                                   LIMIT 1)
                                                                             )
                                                    AND pen.codigopropietario = 8
                                                    AND asig.fk_semestre IN (873,874,875,876,878,879,881)
                                                    AND asig.fk_materia NOT IN (1701,894,907) --materias pira, biblioteca y otra vaina
                                                    AND asig.fk_materia NOT IN ((SELECT fk_materia
							FROM tbl_asignaturas asig
							WHERE fk_materia NOT IN(9909,9738)
							and fk_pensum = pen.pk_pensum
							and fk_semestre = 881))
                                                    )
                    ) THEN true ELSE false END  as resultado
                    FROM
                    (SELECT COUNT(*) as Mat_Apr
                    FROM tbl_usuarios us		
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                    JOIN tbl_asignaturas asig ON asig.pk_asignatura = ra.fk_asignatura
                    JOIN tbl_pensums pen ON pen.pk_pensum = asig.fk_pensum
                    WHERE asig.fk_semestre IN (873,874,875,876,878,879,881)
                    AND us.pk_usuario = {$ci}
                    AND pen.codigopropietario = 8
                    AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (862,861))
                    AND ((ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861))))
                                               AND asig.fk_materia NOT IN (1701,894,907)
                    AND asig.fk_materia NOT IN ((SELECT fk_materia
							FROM tbl_asignaturas asig
							WHERE fk_materia NOT IN(9909,9738)
							and fk_pensum = pen.pk_pensum
							and fk_semestre = 881))
                    AND pen.fk_escuela = (SELECT DISTINCT pen.fk_escuela
					  FROM tbl_pensums pen
					  JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
				 	  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
				          JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
					  WHERE us.pk_usuario = {$ci}
					  AND ins.fk_periodo = (SELECT ins.fk_periodo
								FROM tbl_usuarios us
								JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
								JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
								WHERE us.pk_usuario = {$ci}
								ORDER BY 1 DESC
								LIMIT 1)
                                         )
                    )as sqt";
                    
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
                
        public function getUsuarios($ci){
           
            $SQL = "SELECT us.pk_usuario as cedula, us.nombre, us.apellido, esc.escuela, fk_estructura as estructura
                    FROM tbl_usuarios us
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN vw_escuelas esc ON esc.pk_atributo = ins.fk_atributo
                    WHERE us.pk_usuario = {$ci}
                    AND ins.fk_periodo = (SELECT ins.fk_periodo
                                          FROM tbl_usuarios us
                                          JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                                          JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                          WHERE us.pk_usuario = {$ci}
                                          ORDER BY 1 DESC
                                          LIMIT 1);";
                                          
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getMateriasAprobadas($ci){
            
            $SQL = "(SELECT asig.codigopropietario as codigo, mat.materia as materia, ra.calificacion as nota,
                    CASE WHEN (sem.valor = 'Primer Semestre') THEN 1
                    WHEN (sem.valor = 'Segundo Semestre') THEN 2 
                     WHEN (sem.valor = 'Tercer Semestre') THEN 3
                     WHEN (sem.valor = 'Cuarto Semestre') THEN 4
                     WHEN (sem.valor = 'Quinto Semestre') THEN 5
		     WHEN (sem.valor = 'Sexto Semestre') THEN 6
                     WHEN (sem.valor = 'Séptimo Semestre') THEN 7
                    END  as semestre
                    FROM tbl_usuarios us
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                    JOIN tbl_asignaturas asig ON asig.pk_asignatura = ra.fk_asignatura
                    JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                    JOIN vw_materias mat ON mat.pk_atributo = asig.fk_materia
                    JOIN vw_semestres sem ON sem.pk_atributo = asig.fk_semestre
                    WHERE us.pk_usuario = {$ci}
                    AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (862,861))
                    AND pen.codigopropietario = (SELECT pen.codigopropietario
                                                 FROM tbl_usuarios us 
                                                 JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                                                 JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                 JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                                                 WHERE us.pk_usuario = {$ci}
                                                 ORDER BY ins.fk_periodo DESC
                                                 limit 1)
                    AND asig.fk_semestre IN (873,874,875,876,878,879,881)
                    AND asig.fk_materia NOT IN (1701,894,907)
                    AND asig.fk_materia NOT IN ((SELECT fk_materia
							FROM tbl_asignaturas asig
							WHERE fk_materia NOT IN(9909,9738)
							and fk_pensum = pen.pk_pensum
							and fk_semestre = 881))
							 
                    AND ((ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861))))
                                                AND pen.fk_escuela = (SELECT DISTINCT pen.fk_escuela
					  FROM tbl_pensums pen
					  JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
				 	  JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
				          JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
					  WHERE us.pk_usuario = {$ci}
					  AND ins.fk_periodo = (SELECT ins.fk_periodo
								FROM tbl_usuarios us
								JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
								JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
								WHERE us.pk_usuario = {$ci}
								ORDER BY 1 DESC
								LIMIT 1)
                                         )
                    ORDER BY asig.fk_semestre, 1)
      ";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getMateriasFaltantes($ci){
            
            $SQL = "SELECT  asig.codigopropietario as codigo, mat.materia as materia
                    FROM tbl_asignaturas asig
                    JOIN tbl_pensums pen ON pen.pk_pensum = asig.fk_pensum
                    JOIN vw_materias mat ON mat.pk_atributo = asig.fk_materia
                    WHERE pen.fk_escuela = (SELECT DISTINCT pen.fk_escuela
                                            FROM tbl_pensums pen
                                            JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
                                            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                                            JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                                            WHERE us.pk_usuario = {$ci}
                                            AND ins.fk_periodo = (SELECT ins.fk_periodo
                                                                  FROM tbl_usuarios us
                                                                  JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
                                                                  JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                                  WHERE us.pk_usuario = {$ci}
                                                                  ORDER BY 1 DESC
                                                                  LIMIT 1)
                                            )
                    AND asig.fk_materia NOT IN (SELECT asig.fk_materia
                                                FROM tbl_usuarios us
                                                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                                                JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                                                JOIN tbl_asignaturas asig ON asig.pk_asignatura = ra.fk_asignatura
                                                JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                                                WHERE us.pk_usuario = {$ci}
                                                AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (862,861))
                                                AND pen.codigopropietario = (SELECT pen.codigopropietario
                                                                             FROM tbl_usuarios us 
                                                                             JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                                                                             JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                                             JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                                                                             WHERE us.pk_usuario = {$ci}
                                                                             ORDER BY ins.fk_periodo DESC
                                                                             limit 1)
				               AND ((ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861))))
                                               ORDER BY asig.fk_semestre, 1)
                    AND pen.codigopropietario = 8
                    AND asig.fk_semestre IN (873,874,875,876,878,879,881)	
                    AND asig.fk_materia NOT IN (1701,894,907)
                    AND asig.fk_materia NOT IN ((SELECT fk_materia
							FROM tbl_asignaturas asig
							WHERE fk_materia NOT IN(9909,9738)
							and fk_pensum = pen.pk_pensum
							and fk_semestre = 881))
           ORDER BY 1";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getCodigoPropietario($ci){
            
            $SQL = "SELECT pen.codigopropietario as codpro
                    FROM tbl_usuarios us 
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                    WHERE us.pk_usuario = {$ci}
                    ORDER BY ins.fk_periodo DESC
                    limit 1";
                    
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getUltimoPeriodo($ci){
            
            $SQL = "SELECT ins.fk_periodo
                    FROM tbl_usuarios us
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE us.pk_usuario = {$ci}
                    ORDER BY ins.fk_periodo DESC
                    limit 1";
                    
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getPkUsuarioGrupo($ci){
            
            $SQL = "SELECT pk_usuariogrupo
                    FROM tbl_usuariosgrupos
                    WHERE fk_usuario = {$ci}
                    AND fk_grupo = 855";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getPeriodoAcademicoVigente($fecha){
            
            $SQL = "SELECT pk_periodo
                    FROM tbl_periodos
                    WHERE '{$fecha}' BETWEEN fechainicio AND fechafin";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getPkUsuarioGrupoSolicitud($pk_usuariogrupo,$periodovigente,$numeropago,$fecha,$estructura){
            
            $SQL = "SELECT pk_usuariogruposolicitud
                    FROM tbl_usuariosgrupossolicitudes
                    WHERE fk_usuariogrupo = {$pk_usuariogrupo}
                    AND fk_periodo = {$periodovigente}
                    AND numeropago = {$numeropago}
                    AND fechasolicitud = '{$fecha}'
                    AND fk_estructura = {$estructura};";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getSolicitudesCertificado($periodo, $estructura, $escuela){
           
            $SQL = "SELECT docs.fk_usuariogruposolicitud as codigo, us.pk_usuario as ci, us.nombre as nombre, us.apellido as apellido, atr.valor as tipo, atr2.valor as estado, atr3.valor as impreso
                    FROM tbl_documentossolicitados docs
                    JOIN tbl_usuariosgrupossolicitudes usol ON usol.pk_usuariogruposolicitud = docs.fk_usuariogruposolicitud
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = usol.fk_usuariogrupo
                    JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                    JOIN tbl_atributos atr ON atr.pk_atributo = docs.fk_documento
                    JOIN tbl_atributos atr2 ON atr2.pk_atributo = docs.fk_estado
                    JOIN tbl_atributos atr3 ON atr3.pk_atributo = usol.fk_impreso
                    JOIN tbl_inscripciones ins ON ins.pk_inscripcion = (SELECT ins2.pk_inscripcion
                                                                        FROM tbl_inscripciones ins2
                                                                        JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = ins2.fk_usuariogrupo
                                                                        WHERE ug2.fk_usuario = us.pk_usuario
                                                                        ORDER BY ins2.fk_periodo DESC
                                                                        LIMIT 1)
                    JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                    WHERE docs.fk_documento = (SELECT pk_atributo FROM tbl_atributos WHERE valor = 'Certificación de Competencia')
                    AND usol.fk_periodo = {$periodo}
                    AND ins.fk_estructura = {$estructura}
                    AND docs.fk_escuela = {$escuela}
                    GROUP BY 1,2,3,4,5,6,7
                    ORDER BY 2;";
                    
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getSolicitudesCertificadoPorEstado($periodo, $estructura, $escuela,$estado){
           
            $SQL = "SELECT docs.pk_documentosolicitado as pkdocumento, us.pk_usuario as ci, us.nombre as nombre, us.apellido as apellido, atr.valor as tipo, atr2.valor as estado, atr3.valor as impreso ,docs.fk_usuariogruposolicitud as codigo
                    FROM tbl_documentossolicitados docs
                    JOIN tbl_usuariosgrupossolicitudes usol ON usol.pk_usuariogruposolicitud = docs.fk_usuariogruposolicitud
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = usol.fk_usuariogrupo
                    JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                    JOIN tbl_atributos atr ON atr.pk_atributo = docs.fk_documento
                    JOIN tbl_atributos atr2 ON atr2.pk_atributo = docs.fk_estado
                    JOIN tbl_atributos atr3 ON atr3.pk_atributo = usol.fk_impreso
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    WHERE docs.fk_documento = (SELECT pk_atributo FROM tbl_atributos WHERE valor = 'Certificación de Competencia')
                    AND usol.fk_periodo = {$periodo}
                    AND ins.fk_estructura = {$estructura}
                    AND docs.fk_escuela = {$escuela}
                    AND docs.fk_estado = {$estado}
		    GROUP BY 1,2,3,4,5,6,7,8
                    ORDER BY 2;";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getPkDocumentoSolicitado($solicitud){
           
            $SQL = "SELECT ds.pk_documentosolicitado 
                    FROM tbl_documentossolicitados ds
                    JOIN tbl_usuariosgrupossolicitudes ugs ON ugs.pk_usuariogruposolicitud = ds.fk_usuariogruposolicitud
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                    JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                    WHERE ds.fk_usuariogruposolicitud = {$solicitud}";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }

        public function getEstadoDocumento($pk_documento){
            
            $SQL = "SELECT fk_estado
                    FROM tbl_documentossolicitados
                    WHERE pk_documentosolicitado = {$pk_documento};";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getGrupos($ci){
            
            $SQL = "SELECT ug.fk_grupo 
                    FROM tbl_usuarios us
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    WHERE us.pk_usuario = {$ci}
                    ORDER BY 1;";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getUsuarioPorDocumento($pkdocumento){
            
            $SQL = "SELECT fk_usuario as cedula, doc.fk_escuela as escuela , doc.fk_usuariogruposolicitud as solicitud
                    FROM tbl_documentossolicitados doc
                    JOIN tbl_usuariosgrupossolicitudes ugs ON ugs.pk_usuariogruposolicitud = doc.fk_usuariogruposolicitud AND doc.pk_documentosolicitado = {$pkdocumento}
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo  = ugs.fk_usuariogrupo";
            
            $results = $this->_db->query($SQL);
            return $results->fetchAll();
            
        }
        
    
        public function getPkAtributo(){
            
            $SQL = "SELECT pk_atributo,valor from tbl_atributos a 
                    WHERE
                    (valor IN ('Impreso','No Impreso') AND fk_atributotipo = 41)
                    OR (valor IN ('Aprobado','Solicitado') AND fk_atributotipo = 46)
                    OR (valor ilike 'Direcci_n de escuela de %' AND fk_atributotipo = 5)
                    ORDER BY 2
                    ";
            
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function getUltimaEscuela($ci){
            
            $SQL = "SELECT DISTINCT pen.fk_escuela
                    FROM tbl_pensums pen
                    JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                    JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                    WHERE us.pk_usuario = {$ci}
                    AND ins.fk_periodo = (SELECT ins.fk_periodo
                                    FROM tbl_usuarios us
                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                    WHERE us.pk_usuario = {$ci}
                                    ORDER BY 1 DESC
                                    LIMIT 1)";
            
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function checkSolvenciaServicioComunitario($ci){
            
            $SQL = "SELECT CASE WHEN count(ra.calificacion) = 2 THEN true ELSE false END as solvencia
                    FROM tbl_usuarios us 
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                    JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
                    JOIN tbl_asignaturas asig ON asig.pk_asignatura = ra.fk_asignatura
                    JOIN vw_materias mat ON mat.pk_atributo = asig.fk_materia
                    JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                    WHERE us.pk_usuario = {$ci}
                    AND asig.fk_materia IN (9737,9738) --,8219,9897)
                    AND ((ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861))))
                    AND pen.codigopropietario = (SELECT pen.codigopropietario
                                                 FROM tbl_usuarios us 
                                                 JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
                                                 JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                 JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
                                                 WHERE us.pk_usuario = {$ci}
                                                 ORDER BY ins.fk_periodo DESC
                                                 limit 1)
                    AND pen.fk_escuela = (SELECT DISTINCT pen.fk_escuela
                                          FROM tbl_pensums pen
                                          JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
                                          JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
                                          JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                                          WHERE us.pk_usuario = {$ci}
                                          AND ins.fk_periodo = (SELECT ins.fk_periodo
                                                                FROM tbl_usuarios us
                                                                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
                                                                JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
                                                                WHERE us.pk_usuario = {$ci}
                                                                ORDER BY 1 DESC
                                                                LIMIT 1)
                                          )";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function checkBiblioteca($ci){
            
            $SQL = "SELECT *, CASE 
                    WHEN estado = 'Vacio' then 1
                    WHEN estado = 'Mora' then 2  
                    WHEN estado = 'Transito'then 3 
                    WHEN estado = 'Solvente' then 4 end as orden 
                    FROM (SELECT solicitud, pk_usuario, nombre, apellido, perfil,correo, estado,fecha_prestamo,numeroart 
                          FROM(SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,numeroart,
                               CASE WHEN mora > 0 THEN 'Mora'
                               WHEN mora = 0 AND prestamo > 0 THEN 'Transito'
                               WHEN mora = 0 AND prestamo = 0 AND devuelto > 0 THEN 'Solvente'
                               ELSE 'Vacio' END as estado
                               FROM(SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,numeroart,
                                    SUM(mora) as mora,
                                    SUM(prestamo) as prestamo,
                                    SUM(devuelto) as devuelto
                                    FROM(SELECT solicitud, pk_usuario, nombre, apellido,correo, perfil,fecha_prestamo,numeroart,
                                         CASE WHEN fk_asignacion = 8244 THEN 1 ELSE 0 END as mora,
                                         CASE WHEN fk_asignacion = 8242 THEN 1 ELSE 0 END as prestamo,
                                         CASE WHEN fk_asignacion = 8243 THEN 1 ELSE 0 END as devuelto
                                         FROM(SELECT p.pk_prestamo as solicitud , u.pk_usuario , u.nombre , u.apellido ,u.correo,gr.grupo as perfil, p.fecha_prestamo , preart.fk_asignacion , count(preart.pk_prestamoarticulo)as numeroart
                                              FROM tbl_usuarios u 
                                              JOIN tbl_usuariosgrupos gp ON gp.fk_usuario = u.pk_usuario
                                              JOIN tbl_prestamos p ON p.fk_usuariogrupo = gp.pk_usuariogrupo
                                              LEFT OUTER JOIN tbl_prestamosarticulos preart ON preart.fk_prestamo = p.pk_prestamo
                                              JOIN vw_grupos gr ON gr.pk_atributo = gp.fk_grupo
                                              GROUP BY 1,2,3,4,5,6,7,8
                                              ) as sqt
                                         ) as sqt2
                                         GROUP BY 1,2,3,4,5,6,7,8
                    ) as sqt3) as sqt4) as sqt5
                    WHERE pk_usuario = {$ci} 
                    AND ( estado ilike '%Mora%' OR estado ilike '%Transito%')
                    ORDER BY 10 ASC, 8 DESC";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function checkExistenciaSolicitud($ci){
            
            $SQL = "SELECT CASE WHEN (sqt.cuenta > 0) THEN true ELSE false END as resultado
                    FROM
                    (
                    SELECT COUNT(docs.pk_documentosolicitado) as cuenta
                    FROM tbl_documentossolicitados docs
                    JOIN tbl_usuariosgrupossolicitudes usol ON usol.pk_usuariogruposolicitud = docs.fk_usuariogruposolicitud
                    JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = usol.fk_usuariogrupo
                    JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
                    JOIN tbl_atributos atr ON atr.pk_atributo = docs.fk_documento
                    JOIN tbl_atributos atr2 ON atr2.pk_atributo = docs.fk_estado
                    WHERE docs.fk_documento = (SELECT pk_atributo FROM tbl_atributos WHERE valor = 'Certificación de Competencia')
                    AND us.pk_usuario = {$ci}
                    ) as sqt";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function setUsuariosGruposSolicitudes($pk_usuariogrupo,$periodovigente,$numeropago,$fecha,$estructura){
            
            $SQL = "INSERT INTO tbl_usuariosgrupossolicitudes(
                    fk_usuariogrupo, fk_periodo, numeropago,fechasolicitud, fk_estructura, fk_tipo, fk_impreso)
                    VALUES ({$pk_usuariogrupo}, {$periodovigente},'{$numeropago}', '{$fecha}', {$estructura}, 8266, 8249);";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        public function setDocumentosSolicitados($fk_usuariogruposolicitud,$fk_escuela){
            
            $SQL = "INSERT INTO tbl_documentossolicitados(
                    fk_usuariogruposolicitud, fk_documento, fk_estado, fk_escuela)
                    VALUES ({$fk_usuariogruposolicitud}, (SELECT pk_atributo FROM tbl_atributos WHERE valor = 'Certificación de Competencia'), (SELECT pk_atributo FROM tbl_atributos WHERE valor = 'Solicitado'), {$fk_escuela});";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
        //fk_estado = 14145 Aprobado; 14146 Solicitado;
        public function updateEstadoDocumento($fk_estado, $pk_documentosolicitado){
            
            $SQL = "UPDATE tbl_documentossolicitados
                    SET fk_estado = {$fk_estado}
                    WHERE pk_documentosolicitado = {$pk_documentosolicitado};";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
         public function updateEstadoImpreso($pk_usuario_solicitud){
            
            $SQL = "UPDATE tbl_usuariosgrupossolicitudes
                    SET fk_impreso = (SELECT pk_atributo FROM tbl_atributos WHERE valor = 'Impreso') 
                    WHERE pk_usuariogruposolicitud = {$pk_usuario_solicitud};";
            
            $results = $this->_db->query($SQL);
            return (array)$results->fetchAll();
            
        }
        
    }

?>
