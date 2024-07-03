<?php

class Models_DbTable_Solicitudgrado extends Zend_Db_Table
{

	private $documento = 'Solicitud de Grado';
	private $fk_tipo = 'Documentos';
	private $fk_impreso = 'No Impreso';
	private $tecnico = 19759;
	private $secretaria = 19751;
	private $Coordinacion = 19752;
	private $biblioteca = 19750;
	private $data = array('19773', '19772', '19771');
	private $egresado = 20029;

	public function init() {
		$this->SwapBytes_Array = new SwapBytes_Array();
	}

	public function getUsuarios($ci) {

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

	/* getSolvenciaAcademica devuelve true si está solvente y false si no */
	public function getSolvenciaAcademica($ci,$inscritas = true) {

		$SQL = "SELECT CASE count(sqt.fk_materia) WHEN 0 THEN TRUE  --vacio ya paso todo TRUE solo
			ELSE FALSE END AS RESUL
				FROM(
					SELECT a.fk_materia
					FROM tbl_asignaturas a
					JOIN tbl_pensums  p ON a.fk_pensum    = p.pk_pensum AND p.pk_pensum = (SELECT I1.fk_pensum
					FROM tbl_inscripciones I1
					JOIN tbl_usuariosgrupos UG1 ON UG1.pk_usuariogrupo = I1.fk_usuariogrupo AND UG1.fk_usuario = {$ci}
					ORDER BY I1.fk_periodo DESC LIMIT 1 )
					JOIN vw_materias  m ON m.pk_atributo  = a.fk_materia AND a.fk_materia NOT IN (1701,894,907)

					--asignaturas CURSADAS O INSCRITAS por persona
					EXCEPT

					SELECT a.fk_materia
					FROM tbl_inscripciones  i
					JOIN tbl_usuariosgrupos ug ON ug .pk_usuariogrupo = i.fk_usuariogrupo AND fk_usuario = {$ci}
					JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion AND ((ra.calificacion >= 10 AND ra.fk_atributo  = 862) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861
					";
		if($inscritas){
			$SQL .= " ,864";
		}
		$SQL.="))))
			JOIN tbl_asignaturas a ON a.pk_asignatura = ra.fk_asignatura
			JOIN tbl_pensums p ON p.pk_pensum = a.fk_pensum AND p.pk_pensum = (SELECT I1.fk_pensum
			FROM tbl_inscripciones I1
			JOIN tbl_usuariosgrupos UG1 ON UG1.pk_usuariogrupo = I1.fk_usuariogrupo AND ug1.fk_usuario = {$ci} 
			ORDER BY I1.fk_periodo DESC LIMIT 1 )
		)as sqt;";

		//            $SQL = "SELECT CASE WHEN (
		//                                            sqt.Mat_Apr = 	(
		//                                                                    SELECT count(DISTINCT asig.fk_materia)
		//                                                                    FROM tbl_asignaturas asig
		//                                                                    JOIN tbl_pensums pen ON pen.pk_pensum = asig.fk_pensum
		//                                                                    JOIN vw_materias mat ON mat.pk_atributo = asig.fk_materia
		//                                                                    WHERE pen.fk_escuela = 		(
		//                                                                                                            SELECT DISTINCT pen.fk_escuela
		//                                                                                                            FROM tbl_pensums pen
		//                                                                                                            JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
		//                                                                                                            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
		//                                                                                                            JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
		//                                                                                                            WHERE us.pk_usuario = {$ci}
		//                                                                                                            AND ins.fk_periodo = 	(
		//                                                                                                                                            SELECT ins.fk_periodo
		//                                                                                                                                            FROM tbl_usuarios us
		//                                                                                                                                            JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
		//                                                                                                                                            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
		//                                                                                                                                            WHERE us.pk_usuario = {$ci}
		//                                                                                                                                            ORDER BY 1 DESC
		//                                                                                                                                            LIMIT 1
		//                                                                                                                                    )
		//                                                                                                    )
		//                                                                    AND pen.codigopropietario = 	(
		//                                                                                                            SELECT pen.codigopropietario
		//                                                                                                            FROM tbl_usuarios us
		//                                                                                                            JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
		//                                                                                                            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
		//                                                                                                            JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
		//                                                                                                            WHERE us.pk_usuario = {$ci}
		//                                                                                                            ORDER BY 1 DESC
		//                                                                                                            LIMIT 1	
		//                                                                                                    )
		//                                                                    AND asig.fk_materia NOT IN (1701,894,907) --PIRA, BIBLIOTECA Y PREPARADURIA.
		//                                                            )	
		//                                    ) THEN true ELSE false END  as solvenciaacademica
		//                    FROM
		//                    (
		//                            SELECT COUNT(*) as Mat_Apr
		//                            FROM tbl_usuarios us		
		//                            JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
		//                            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
		//                            JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
		//                            JOIN tbl_asignaturas asig ON asig.pk_asignatura = ra.fk_asignatura
		//                            JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
		//                            WHERE us.pk_usuario = {$ci}
		//                            AND pen.codigopropietario = 	(
		//                                                                    SELECT pen.codigopropietario
		//                                                                    FROM tbl_usuarios us
		//                                                                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
		//                                                                    JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
		//                                                                    JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
		//                                                                    WHERE us.pk_usuario = {$ci}
		//                                                                    ORDER BY 1 DESC
		//                                                                    LIMIT 1	
		//                                                            )
		//                            AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (862,861,864))
		//                            AND ((ra.calificacion >= 10 AND ra.fk_atributo  IN (862,864)) OR (ra.calificacion = 0 AND (ra.fk_atributo IN (SELECT pk_atributo FROM vw_reconocimientos) OR ra.fk_atributo IN (861,864))))
		//                            AND asig.fk_materia NOT IN (1701,894,907)
		//                            AND pen.fk_escuela = 	(
		//                                                            SELECT DISTINCT pen.fk_escuela
		//                                                            FROM tbl_pensums pen
		//                                                            JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
		//                                                            JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
		//                                                            JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
		//                                                            WHERE us.pk_usuario = {$ci}
		//                                                            AND ins.fk_periodo = 	(
		//                                                                                            SELECT ins.fk_periodo
		//                                                                                            FROM tbl_usuarios us
		//                                                                                            JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
		//                                                                                            JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
		//                                                                                            WHERE us.pk_usuario = {$ci}
		//                                                                                            ORDER BY 1 DESC
		//                                                                                            LIMIT 1	
		//                                                                                        )
		//                                                     )
		//                    )as sqt;";

		$results = $this->_db->query($SQL);
		return (array)$results->fetchAll();

	}

	/* getSolvenciaTesis devuelve true si está solvente y false si no */
	public function getSolvenciaTesis($ci)
	{

		$SQL = "SELECT CASE WHEN (count(*)>=1) THEN true ELSE false END as solvenciatesis
			FROM tbl_usuarios us 
			JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
			JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
			JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = ins.pk_inscripcion
			JOIN tbl_asignaturas asig ON asig.pk_asignatura = ra.fk_asignatura
			JOIN tbl_pensums pen ON pen.pk_pensum = ins.fk_pensum
			WHERE asig.fk_materia IN (834,9724)
			AND us.pk_usuario = {$ci}
			AND ra.fk_atributo IN (864,862)
			AND pen.fk_escuela = 	(
				(SELECT DISTINCT pen.fk_escuela
				FROM tbl_pensums pen
				JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
				JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
				JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
				WHERE us.pk_usuario = {$ci}
				AND ins.fk_periodo = (    
					SELECT ins.fk_periodo
					FROM tbl_usuarios us
					JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
					JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
					WHERE us.pk_usuario = {$ci}
					ORDER BY 1 DESC
					LIMIT 1
				)
			)
		);";

		$results = $this->_db->query($SQL);
		return (array)$results->fetchAll();

	}
//cambio
	/*
	 * getSolicitud verifica la existencia de solicitudes por usuario
	 * para mostrar el botón generar solicitud y filtrar que no se pueda
	 * generar mas de una solicitud por usuario.
	 */
	public function getUltimaSolicitudDeGrado($ci,$periodo = NULL) {

		$SQL = "SELECT (docs.pk_documentosolicitado) as documentoid,usol.fk_periodo
			FROM tbl_usuarios us
			JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario
			JOIN tbl_usuariosgrupossolicitudes usol ON usol.fk_usuariogrupo = ug.pk_usuariogrupo
			JOIN tbl_documentossolicitados docs ON docs.fk_usuariogruposolicitud = usol.pk_usuariogruposolicitud
			WHERE us.pk_usuario = {$ci}";
		if ($periodo) {
		$SQL .= "AND usol.fk_periodo = {$periodo}";
		}
		$SQL.= "AND docs.fk_documento = (
				SELECT pk_atributo
				FROM tbl_atributos
				WHERE valor = ('$this->documento')
			) ORDER BY usol.fk_periodo DESC LIMIT 1;";

		$results = $this->_db->query($SQL);
		return (array)$results->fetchAll();

	}

	public function getUltimoPeriodoVigente($fecha){

		$SQL = "SELECT pk_periodo
			FROM tbl_periodos
			WHERE '{$fecha}' BETWEEN fechainicio AND fechafin;
		";

		return $this->_db->fetchOne($SQL);

	}

	/*
	 * getSolicitudesGrado trae las solicitudes de grado por período secretaria.
	 */
	public function getSolicitudesGrado($periodo,$sede,$escuela)
	{
		$SQL = "
			SELECT docs.pk_documentosolicitado as codigo, u.pk_usuario as cedula, u.nombre, u.apellido,
			(SELECT CASE (SELECT count(pk_documentorequisito)          
			FROM tbl_documentosrequisitos WHERE fk_solicitud = docs.pk_documentosolicitado AND fk_documento = $this->tecnico)

			WHEN 0 THEN (SELECT CASE count(*) WHEN 0 THEN 'Solvente' 
			ELSE 'No Solvente' END as secretaria
				FROM (SELECT pk_atributo
				FROM tbl_atributos WHERE fk_atributo in ($this->secretaria) and fk_atributotipo = 81 AND pk_atributo != $this->tecnico

				EXCEPT

				SELECT pk_atributo FROM tbl_documentosrequisitos dc
				JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo in ($this->secretaria) AND fk_atributotipo = 81
				WHERE fk_solicitud = docs.pk_documentosolicitado
				AND dc.fk_estado = 14145
			) as sqt1
		) 

		ELSE  (SELECT CASE count(*) WHEN 0 THEN 'Solvente' 
			ELSE 'No Solvente' END as secretaria
				FROM
				((SELECT pk_atributo
				FROM tbl_atributos WHERE fk_atributo in ($this->secretaria) and fk_atributotipo = 81

				EXCEPT

				SELECT pk_atributo FROM tbl_documentosrequisitos dc
				JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo in ($this->secretaria) AND fk_atributotipo = 81
				WHERE fk_solicitud = docs.pk_documentosolicitado 
				AND dc.fk_estado = 14145
			)) as sqt2

		) END AS ESTADO) AS secretaria,
		(SELECT CASE count(*) WHEN 0 THEN 'Solvente' 
		ELSE 'No Solvente' END as Coordinacion
			FROM
			(SELECT pk_atributo
			FROM tbl_atributos WHERE fk_atributo = $this->Coordinacion and fk_atributotipo = 81

			EXCEPT

			SELECT pk_atributo FROM tbl_documentosrequisitos dc
			JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo = $this->Coordinacion and fk_atributotipo = 81
			WHERE fk_solicitud = docs.pk_documentosolicitado 
			AND dc.fk_estado = 14145
		) as req2) as Coordinacion,
		(SELECT CASE count(*) WHEN 0 THEN 'Solvente' 
		ELSE 'No Solvente' END as Biblioteca
			FROM
			(SELECT pk_atributo
			FROM tbl_atributos WHERE fk_atributo = $this->biblioteca and fk_atributotipo = 81

			EXCEPT

			SELECT pk_atributo FROM tbl_documentosrequisitos dc
			JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo = $this->biblioteca and fk_atributotipo = 81
			WHERE fk_solicitud = docs.pk_documentosolicitado
			AND dc.fk_estado = 14145
		) AS req3) as Biblioteca,(  SELECT CASE count(pk_documentorequisito) WHEN 0 THEN 'NO'
		ELSE 'SI' END as TSU
			FROM tbl_documentosrequisitos WHERE fk_solicitud = docs.pk_documentosolicitado AND fk_documento = $this->tecnico) AS tsu,
			(select CASE count(pk_documentorequisito) WHEN 0 THEN 'NO' ELSE 'SI' END
			from tbl_documentosrequisitos dr
			where dr.fk_solicitud = docs.pk_documentosolicitado) as revisado

			FROM tbl_usuarios u
			JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
			JOIN tbl_usuariosgrupossolicitudes usol ON usol.fk_usuariogrupo = ug.pk_usuariogrupo
			JOIN tbl_documentossolicitados docs ON docs.fk_usuariogruposolicitud = usol.pk_usuariogruposolicitud
			WHERE usol.fk_periodo = {$periodo}
			AND usol.fk_estructura = {$sede}
			AND docs.fk_escuela = {$escuela}
			AND docs.fk_documento =  (
				SELECT DISTINCT docs.fk_documento
				FROM tbl_documentossolicitados docs
				JOIN tbl_atributos a ON a.pk_atributo = docs.fk_documento
				WHERE a.valor = ('$this->documento')
			)
			ORDER BY revisado,nombre,apellido,cedula
			;";

		$results = $this->_db->query($SQL);

		return (array)$results->fetchAll();

	}

	public function getRequisitos($id, $op, $bool)
	{

		$string = $this->fillString($op);

		if ($bool == true) {
			$SQL = "SELECT
				CASE WHEN count(*)>0 THEN TRUE ELSE FALSE END as estado
					FROM tbl_documentosrequisitos 
					WHERE fk_documento = (SELECT pk_atributo FROM tbl_atributos WHERE valor ilike '{$string}')
					AND fk_solicitud = {$id}";
			$results = $this->_db->fetchOne($SQL);
		} else {
			$SQL = "SELECT
				CASE WHEN count(pk_documentorequisito)>0 THEN TRUE ELSE FALSE END as estado,(SELECT atr2.valor FROM tbl_atributos atr1 
					JOIN tbl_atributos atr2 ON atr1.fk_atributo = atr2.pk_atributo WHERE atr1.valor ilike '{$string}' ) as tipo
					FROM tbl_documentosrequisitos 
					WHERE fk_documento = (SELECT pk_atributo FROM tbl_atributos WHERE valor ilike '{$string}')
					AND fk_solicitud = {$id}
					AND fk_estado = (SELECT pk_atributo FROM tbl_atributos WHERE valor ilike 'Aprobado' AND fk_atributotipo = 46)";
			$results = $this->_db->query($SQL);
			$results = $results->fetchAll();
			$results[0]['Requisito'] = $string;
		}

		return $results;
	}

	public function fillString($op)
	{
		$string = "";
		if ($op == 1) {
			$string = 'Fotocopia fondo negro del título de bachiller con sus respectivas estampillas y debidamente autenticado';
		} else if ($op == 2) {
			$string = 'Fotocopia fondo negro de las calificaciones de bachillerato con sus respectivas estampillas debidamente autenticado';
		} else if ($op == 3) {
			$string = 'Constancia de inscripción en el sistema nacional de ingreso a la educación universitaria';
		} else if ($op == 4) {
			$string = 'Fotocopia de la partida de nacimiento';
		} else if ($op == 5) {
			$string = 'Fotocopia de la cédula de identidad';
		} else if ($op == 6) {
			$string = 'Timbre fiscal equivalente al 30% de la unidad tributaria';
		} else if ($op == 7) {
			$string = 'Verificación de firma de jurado en el tomo';
		} else if ($op == 8) {
			$string = 'Entrega de tomo en biblioteca';
		}
		return $string;
	}

	public function updateEstadoRequisito($id, $op, $s)
	{

		$string = $this->fillString($op);

		$SQL = "   UPDATE tbl_documentosrequisitos
			SET fk_estado = (SELECT pk_atributo FROM tbl_atributos WHERE valor ilike '$s' AND fk_atributotipo = 46)
			WHERE fk_solicitud = {$id}
			AND fk_documento = (SELECT pk_atributo FROM tbl_atributos WHERE valor ilike '$string');
		";
		//$results = $this->_db->fetchOne($SQL); SE COMENTO ESTA LINEA Y SE AGREGO LA SIGUIENTE PARA LA ACTUALIZACION A PHP 7.2
		$results = $this->_db->query($SQL);
		return $results;
	}

	public function insertRequisito($id, $op)
	{

		$string = $this->fillString($op);

		$SQL = "   INSERT INTO tbl_documentosrequisitos(fk_solicitud,fk_documento,fk_estado)
			VALUES ({$id},
				(SELECT pk_atributo FROM tbl_atributos WHERE valor ilike '{$string}'),
	(SELECT pk_atributo FROM tbl_atributos WHERE valor ilike 'Aprobado' AND fk_atributotipo = 46)
);";
		//$results = $this->_db->fetchOne($SQL); SE COMENTO ESTA LINEA Y SE AGREGO LA SIGUIENTE PARA LA ACTUALIZACION A PHP 7.2
		$results = $this->_db->query($SQL);
		return $results;
	}

	public function setUsuariosGruposSolicitudes($ci, $fecha)
	{

		$SQL = "INSERT INTO tbl_usuariosgrupossolicitudes(
			fk_usuariogrupo, fk_periodo, fechasolicitud, fk_estructura, fk_tipo, fk_impreso)
			VALUES 
			(
				(
					SELECT ug.pk_usuariogrupo
					FROM tbl_usuarios u 
					JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
					JOIN tbl_atributos a ON a.pk_atributo = ug.fk_grupo
					WHERE u.pk_usuario = {$ci}
					AND ug.fk_grupo = 	(
						SELECT DISTINCT ug.fk_grupo
						FROM tbl_usuariosgrupos ug 
						JOIN tbl_atributos a ON a.pk_atributo = ug.fk_grupo
						WHERE a.valor = ('Estudiante')
					)

				), 
				(
					SELECT pk_periodo
					FROM tbl_periodos
					WHERE '{$fecha}' BETWEEN fechainicio AND fechafin
				), 
				'{$fecha}', 
				(
					SELECT DISTINCT i.fk_estructura 
					FROM tbl_usuarios u 
					JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario 
					JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
					WHERE u.pk_usuario = {$ci}
					AND i.fk_periodo =      (
						SELECT i.fk_periodo
						FROM tbl_usuarios u 
						JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario 
						JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
						WHERE u.pk_usuario = {$ci}
						ORDER BY 1 DESC
						LIMIT 1
					)
				), 
				(SELECT DISTINCT pk_atributo FROM tbl_atributos WHERE valor = '$this->fk_tipo'), 
(SELECT DISTINCT pk_atributo FROM tbl_atributos WHERE valor = '$this->fk_impreso')
);";
		//var_dump($SQL);
		$results = $this->_db->query($SQL);
		return (array)$results->fetchAll();

	}

	public function setDocumentosSolicitados($ci)
	{

		$SQL = "INSERT INTO tbl_documentossolicitados(
			fk_usuariogruposolicitud, fk_documento, fk_estado, fk_escuela)
			VALUES (
				(
					SELECT usol.pk_usuariogruposolicitud
					FROM tbl_usuarios u
					JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
					JOIN tbl_usuariosgrupossolicitudes usol ON usol.fk_usuariogrupo = ug.pk_usuariogrupo
					WHERE u.pk_usuario = {$ci}
					ORDER BY 1 DESC
					LIMIT 1
				), 
				(SELECT pk_atributo FROM tbl_atributos WHERE valor = '$this->documento'), 
(SELECT pk_atributo FROM tbl_atributos WHERE valor = 'Solicitado'), 
(SELECT DISTINCT pen.fk_escuela
FROM tbl_pensums pen
JOIN tbl_inscripciones ins ON ins.fk_pensum = pen.pk_pensum
JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ins.fk_usuariogrupo
JOIN tbl_usuarios us ON us.pk_usuario = ug.fk_usuario
WHERE us.pk_usuario = {$ci}
AND ins.fk_periodo = (SELECT ins.fk_periodo
FROM tbl_usuarios us
JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = us.pk_usuario								       
JOIN tbl_inscripciones ins ON ins.fk_usuariogrupo = ug.pk_usuariogrupo
WHERE us.pk_usuario = {$ci} AND ins.fk_periodo != 0
ORDER BY 1 DESC
LIMIT 1)
)
);";
		//var_dump($SQL);
		$results = $this->_db->query($SQL);
		return (array)$results->fetchAll();

	}

	public function getPkDocumentoTipoPorNombre($Nombre)
	{
		$SQL = "SELECT pk_atributotipo FROM tbl_atributostipos WHERE nombre = '{$Nombre}'";
		$results = $this->_db->fetchOne($SQL);
		return $results;
	}

	public function getPkDocumentoPorNombre($Nombre, $atributotipo)
	{
		$SQL = "SELECT pk_atributo FROM tbl_atributos WHERE valor ilike('{$Nombre}') and fk_atributotipo = {$atributotipo}";
		$results = $this->_db->fetchOne($SQL);
		return $results;
	}

	public function getPkRequisitoPorNombre($Nombre, $atributotipo)
	{
		$SQL = "SELECT pk_atributo FROM tbl_atributos WHERE valor ilike('{$Nombre}') and fk_atributotipo = {$atributotipo}";
		$results = $this->_db->fetchOne($SQL);
		return $results;
	}

	public function getEstadoAtributo($valor, $atributotipo)
	{
		$SQL = "SELECT pk_atributo
			FROM tbl_atributos WHERE valor ilike ('$valor') AND fk_atributotipo = {$atributotipo};";
		$results = $this->_db->fetchOne($SQL);
		return $results;
	}

	public function getSolicitudesPorDocumentoPeriodo($Documento, $periodo, $Aprobado, $Requisito,$sede,$solvenciacoordinacion = false) {
		//var_dump($Documento,$periodo,$Aprobado,$Requisito);die;
		$SQL = "SELECT doc.pk_documentosolicitado as pkdocumento,pk_usuario as ci,nombre,apellido , CASE docre.fk_estado WHEN {$Aprobado} THEN 'Aprobado'
			ELSE 'Solicitado' END as estado 	
				FROM tbl_documentossolicitados doc
				JOIN tbl_usuariosgrupossolicitudes us ON pk_usuariogruposolicitud = fk_usuariogruposolicitud AND fk_documento = {$Documento} AND fk_periodo = {$periodo} AND us.fk_estructura = {$sede}
				JOIN tbl_usuariosgrupos ON pk_usuariogrupo = fk_usuariogrupo
				JOIN tbl_atributos atr ON atr.pk_atributo =  doc.fk_estado
				JOIN tbl_usuarios ON pk_usuario = fk_usuario
				LEFT OUTER JOIN tbl_documentosrequisitos docre on fk_solicitud = doc.pk_documentosolicitado AND docre.fk_documento = {$Requisito}";
		if($solvenciacoordinacion){
			$SQL .= "join tbl_documentosrequisitos docre1 on docre1.fk_solicitud = doc.pk_documentosolicitado and docre1.fk_documento = 19748"; 
		}
		$SQL .="ORDER BY apellido,nombre,ci;";
		$results = $this->_db->query($SQL);
		$results = $results->fetchAll();
		return $results;
	}

	public function getValorEstadoSolicitud($Solicitud, $Documento)
	{
		$SQL = "SELECT pk_documentorequisito
			FROM tbl_documentosrequisitos
			WHERE fk_solicitud =  {$Solicitud} AND fk_documento = {$Documento}";
		$results = $this->_db->fetchOne($SQL);
		return $results;
	}

	public function InsertarRequisito($Solicitud, $Documento, $Estado)
	{
		$SQL = "INSERT into tbl_documentosrequisitos (fk_solicitud,fk_documento,fk_estado) VALUES ({$Solicitud},{$Documento},{$Estado});";
		//$results = $this->_db->fetchOne($SQL); SE COMENTO ES TA LINEA Y SE ANADIERON LAS 2 SIGUIENTES PARA PHP 7.2
		$results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
	}

	public function updateEstadoReq($id, $string)
	{
		$SQL = "UPDATE tbl_documentosrequisitos
			set fk_estado = (SELECT pk_atributo FROM tbl_atributos WHERE valor ilike '{$string}' AND fk_atributotipo = 46) 
			WHERE pk_documentorequisito ={$id};";
		$results = $this->_db->fetchOne($SQL);
		return $results;
	}

	public function DeleteRequisito($Documento)
	{
		$SQL = "DELETE FROM tbl_documentosrequisitos WHERE pk_documentorequisito = {$Documento};";
		//$results = $this->_db->fetchOne($SQL); SE COMENTO ES TA LINEA Y SE ANADIERON LAS 2 SIGUIENTES PARA PHP 7.2
		$results = $this->_db->query($SQL);
        return (array)$results->fetchAll();
	}

	public function GetDocumentoExistente($pk)
	{
		$SQL = "SELECT CASE doc.fk_estado WHEN 14145 THEN TRUE
			ELSE FALSE END AS ESTADO
				FROM tbl_documentossolicitados doc
				JOIN tbl_documentosrequisitos dr ON dr.fk_solicitud = doc.pk_documentosolicitado
				WHERE pk_documentorequisito = {$pk}; ";
		$results = $this->_db->fetchOne($SQL);
		return $results;
	}

	public function getTecnicoReq($pkdocumento, $opcion)
	{

		$SQL = " SELECT dc.pk_documentorequisito, atr.valor,atr.pk_atributo,CASE dc.fk_estado WHEN '14146' THEN 'Moroso'
			WHEN '14145' THEN 'Solvente' END as estado 

			FROM tbl_documentosrequisitos dc
			JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo = $this->tecnico AND fk_atributotipo = 81
			WHERE fk_solicitud = {$pkdocumento}";

		if ($opcion) {

			$SQL = $SQL . "AND dc.fk_estado = 14145";
		}
		$SQL = $SQL . ";";
		$results = $this->_db->query($SQL);
		return (array)$results->fetchAll();

	}

	public function getRequisitosTecfaltante($pk)
	{
		# code...
		$SQL = "SELECT atr.pk_atributo,atr.valor FROM(

			SELECT pk_atributo FROM tbl_atributos atr WHERE fk_atributo = $this->tecnico

			EXCEPT

			SELECT pk_atributo FROM tbl_documentosrequisitos dc
			JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo = $this->tecnico AND fk_atributotipo = 81
			WHERE fk_solicitud = {$pk}) as sqt
			JOIN tbl_atributos atr ON atr.pk_atributo = sqt.pk_atributo";
		$results = $this->_db->query($SQL);
		$results = $results->fetchAll();
		return $results;
	}

	public function DeleteTecnicoReqs($pk)
	{

		$SQL = " DELETE FROM tbl_documentosrequisitos WHERE pk_documentorequisito IN (SELECT pk_documentorequisito FROM tbl_documentosrequisitos dc
			JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo = $this->tecnico AND fk_atributotipo = 81
			WHERE fk_solicitud = {$pk})";

		$results = $this->_db->fetchOne($SQL);

		$SQL = "DELETE FROM tbl_documentosrequisitos WHERE pk_documentorequisito IN (SELECT pk_documentorequisito FROM tbl_documentosrequisitos dc
			JOIN tbl_atributos atr ON atr.pk_atributo = fk_documento AND atr.fk_atributo = $this->secretaria AND fk_atributotipo = 81 AND fk_documento = $this->tecnico
			WHERE fk_solicitud = {$pk})";
		$results2 = $this->_db->fetchOne($SQL);

		return $results2;
	}

	public function getRequisitostotales($tipo)
	{

		$SQL = "SELECT pk_atributo FROM tbl_atributos WHERE fk_atributotipo = 81 AND fk_atributo = {$tipo}";

		$results = $this->_db->query($SQL)->fetchAll();
		return $results;
	}


	public function getSolvenciaDocumentos($pk)
	{

		$SQL = "(SELECT pk_atributo FROM tbl_atributos WHERE fk_atributotipo = 81
			AND pk_atributo NOT IN (SELECT fk_documento FROM  tbl_documentosrequisitos WHERE fk_solicitud = {$pk} AND fk_estado = 14145)
			AND (pk_atributo != $this->tecnico AND fk_atributo !=$this->tecnico))

			";
		$results = $this->_db->query($SQL)->fetchAll();

		$tecnico = $this->getValorEstadoSolicitud($pk,$this->tecnico);

		if($tecnico){

			$SQL = "(SELECT pk_atributo
				FROM tbl_atributos WHERE fk_atributotipo = 81 AND fk_atributo = $this->tecnico and
				pk_atributo not in(SELECT fk_documento FROM tbl_documentosrequisitos WHERE fk_solicitud = {$pk}
				AND fk_estado = 14145
				AND fk_documento IN(SELECT pk_atributo FROM tbl_atributos WHERE fk_atributotipo = 81 AND fk_atributo = $this->tecnico)
			)
		)";
			$req =$this->_db->query($SQL)->fetchAll();
			$results = array_merge($results,$req);
		}

		return $results;

	}

	public function getUsuarioData($pk)
	{

		$SQL = "select  CASE a.valor
			WHEN 'Trabajo' then 'empresa'
			WHEN 'Telefono de Oficina' then 'teloficina'
			WHEN 'Lugar de Nacimiento' then 'pais'
			WHEN 'Titulo de tesis' THEN 'tesis'
			WHEN 'Cargo' THEN 'cargo' 
else a.valor END as dato,
	au.valor
	from vw_atributosusuarios au
	join tbl_atributos a on a.pk_atributo = au.fk_atributo and a.fk_atributotipo = '84'
	where au.Fk_usuario = {$pk}
	";

$results = $this->_db->query($SQL)->fetchAll();
foreach ($results as $key => $value) {
	# code... cambiando el formato del array resultado
	$resul[strtolower($value['dato'])] = $value['valor'];
}
return $resul;
	}
/**
 *Inserta en la tabla atributos claves los datos adicionales para la planilla de solicitud de grado
 *@cedula cedula del estudiante
 *@data data proveniente del formulario modal
 *Puede Mejorarse pero estoy apurado Hoy
 **/
	public function setUsuariosDatos($cedula,$data){

		$actualData = $this->getUsuarioData($cedula);
	//Si no tiene trabajo borramos	
		if ($data['trabajo'] == '0'){
			$DELETE = "DELETE FROM tbl_atributosclaves WHERE clavetabla = 'tbl_usuarios' AND clavecampo = 'pk_usuario' 
			AND clavevalor={$cedula} AND fk_campotipo IN (19775,19776,19778);";
		$this->_db->fetchOne($DELETE);
		}
	//Luego Actualizamos la data que ya tenemos si se actualiza se unset del arreglo data para no 
	//insertar luego 	
		foreach ($actualData as $key => $value) {
			# code...
			$UPDATE = '';
			switch ($key) {

			case 'pais':
				# code...
				$valor = $data['pais'] ; $valorTipo = 'fk_atributo';  $campoTipo = '19774';  
				unset($data['pais']);
				break;
			case 'teloficina':
				# code...
				$valor = $data['teloficina']; $valorTipo = 'VARCHAR'; $campoTipo = '19776';
				unset($data['teloficina']);
				break;
			case 'empresa':
				# code...
				$valor = $data['empresa']; $valorTipo = 'VARCHAR'; $campoTipo = '19775';
				unset($data['empresa']);
				break;
			case 'tesis':
				# code...
				$valor = $data['tesis']; $valorTipo = 'VARCHAR'; $campoTipo = '19777';
				unset($data['tesis']);
				break;
			case 'cargo':
				# code...
				$valor = $data['cargo']; $valorTipo = 'VARCHAR'; $campoTipo = '19778';
				unset($data['cargo']);
				$break;	
			}
  	  if ($valor <> '' || $valor <> NULL){
		$UPDATE = "UPDATE tbl_atributosclaves  SET valor = '{$valor}', valordato = '{$valorTipo}' 
		where clavetabla = 'tbl_usuarios' AND clavecampo = 'pk_usuario' 
		AND clavevalor={$cedula} AND fk_campotipo = {$campoTipo}";	  
		//Error FetchOne
		//$this->_db->fetchOne($UPDATE);
		$this->_db->fetchAll($UPDATE);
    	}
	}	
		//Insertamos la data que no existe en la bd
		
		foreach ($data as $key => $value){
		$valor = '';
			switch($key){
			
			case 'pais':
				# code...
				$valor = $data['pais'] ; $valorTipo = 'fk_atributo';  $campoTipo = '19774';  
				break;
			case 'teloficina':
				# code...
				$valor = $data['teloficina']; $valorTipo = 'VARCHAR'; $campoTipo = '19776';
				break;
			case 'empresa':
				# code...
				$valor = $data['empresa']; $valorTipo = 'VARCHAR'; $campoTipo = '19775';
				break;
			case 'tesis':
				# code...
				$valor = $data['tesis']; $valorTipo = 'VARCHAR'; $campoTipo = '19777';
				break;
			case 'cargo':
				# code...
				$valor = $data['cargo']; $valorTipo = 'VARCHAR'; $campoTipo = '19778';
				$break;	
			}
			if($valor <> '' || $valor <> NULL){

				$INSERT = "INSERT INTO tbl_atributosclaves(clavetabla,clavecampo,clavevalor,fk_campotipo,valor,valordato) VALUES 
				('tbl_usuarios','pk_usuario',{$cedula},{$campoTipo},'{$valor}','{$valorTipo}')"; 
				//$this->_db->fetchOne($INSERT);
				$this->_db->fetchAll($INSERT);
			}	
		}	
		//$results = $this->_db->fetchOne($INSERT);
	return $results;
	}

	public function getSelectCiudades($ciudad = NULL)
	{

		$SQL = "
	SELECT * from(
			SELECT pk_atributo,valor ,CASE valor WHEN '{$ciudad}' THEN 1 else 0 END as selected

			FROM tbl_atributos WHERE fk_atributotipo = 34 and fk_atributo = 1752

			AND pk_atributo > 19000
			
			)as sqt
			order by 3 DESC ,sqt.valor	
			
			";

		$results = $this->_db->query($SQL)->fetchAll();
		return $results;
	}

	public function getMoraoPorRevisar($pk)
	{

		$SQL ="(select CASE count(pk_documentorequisito) WHEN 0 THEN 'Por Revisar' ELSE 'Mora' END as revisado
			from tbl_documentosrequisitos dr
			where dr.fk_solicitud = {$pk})";

		$results = $this->_db->fetchOne($SQL);

		return $results;
	}
	public function setDocumentoEstado($pk,$estado) {
		// body...
		$UPDATE = array(
			'fk_estado'=>"{$estado}"
		);
		$this->_db->update('tbl_documentossolicitados',$UPDATE,"pk_documentosolicitado = {$pk}");
	}

	public function isEgresado($ci) {
		$sql="SELECT pk_usuariogrupo from tbl_usuariosgrupos 
		where fk_usuario = {$ci} AND fk_grupo = {$this->egresado}";
		$results = 	$this->_db->fetchOne($sql);
		return $results;
	}
}

?>
