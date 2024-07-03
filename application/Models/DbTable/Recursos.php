<?php
class Models_DbTable_Recursos extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_recursos';
    protected $_primary  = 'pk_recurso';
    protected $_sequence = false;

    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
    }

    /**
     * Permite crear una clausula WHERE dependiendo de los parametros que se envien.
     *
     * @param <type> $Data
     * @param <type> $Keys
     */
    public function setData($Data) {
        $Where = array('AND fk_clase = ' => $Data['pk_clase']);

        $Where = array_filter($Where);
        $Where = $this->SwapBytes_Array->implode(' ', $Where);
        $Where = ltrim($Where, ' AND ');

        $this->Where = $Where;
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }
    
    /**
     * Obtiene un registro en especifico.
     *
     * @param int $id Clave primaria del registro.
     * @return array
     */
    public function getRow($id) {
		if(!isset($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow('pk_recurso ' . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function updateRow($id, $data) {
        $data     = array_filter($data);
        $affected = $this->update($data, 'pk_recurso ' . ' = ' . (int)$id);

        return $affected;
    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;
		
        $affected = $this->delete('pk_recurso ' . ' = ' . (int) $id);

        return $affected;
    }

	public function copyRow($ids, $asignatura) {
		if(!is_numeric($asignatura)) return null;
		if(!is_string($ids))         return;

        $SQL = "INSERT INTO tbl_clases (numero, fecha, descripcion, contenido, fk_tipoevaluacion, fk_tipoestrategia, puntaje, fk_asignacion)
			    SELECT numero,
                       fecha,
                       descripcion,
                       contenido,
                       fk_tipoevaluacion,
                       fk_tipoestrategia,
                       puntaje,
                       {$asignatura}
                FROM   tbl_clases
                WHERE pk_clase IN ({$ids})";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
	}

    public function getRecursos($pk_clase){
	   if(!is_numeric($pk_clase)) return null;
      $SQL = " 
         SELECT fk_clase
           , fk_tipo, CASE publico WHEN true THEN 'Si' ELSE 'No' END AS publico
           , archivo
           , contenido_html
           , ordinal
           , descripcion
           , dir_archivo
           , pk_recurso
           , pk_atributo
           , valor
           , id
           , fk_atributotipo
         FROM tbl_recursos r
         JOIN tbl_atributos tip ON tip.pk_atributo = r.fk_tipo
         WHERE fk_clase = {$pk_clase}
         ORDER BY ordinal;
         
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    
    }

    public function getFKAsignacion($pk_clase){
	   if(!is_numeric($pk_clase)) return null;
      $SQL = " 
         SELECT DISTINCT fk_asignacion
         FROM tbl_recursos r
         JOIN tbl_atributos tip ON tip.pk_atributo = r.fk_tipo
         JOIN tbl_clases cl ON r.fk_clase = cl.pk_clase
         WHERE fk_clase = {$pk_clase}
         LIMIT 1;
         
         ";

        return $this->_db->fetchOne($SQL);
    
    }
    
        public function getMime($ext){
	   if(!is_string($ext)) return null;
      $SQL = " 
         SELECT * FROM tbl_mimes WHERE extension ilike '$ext' LIMIT 1;
         
         ";

        $results = $this->_db->query($SQL);

	return (array)$results->fetchAll();
    
    }
    
      public function getPK($pk_clase) {
	$SQL = "SELECT pk_recurso
         FROM tbl_recursos r
         JOIN tbl_atributos tip ON tip.pk_atributo = r.fk_tipo
         WHERE fk_clase = {$pk_clase}
         ORDER BY ordinal;";

	return $this->_db->fetchOne($SQL);
  }

    public function getTiposRecursos(){
      $SQL = " 
         SELECT *
         FROM tbl_atributos
         WHERE fk_atributotipo = 88;
         ";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    
    }
    
    public function addArchivo($filename, $name){        

     $db = new PDO('pgsql:dbname=MiUNE host=localhost', 'MiUNE', 'dama16');
    $stmt = $db->prepare("INSERT INTO tbl_recursos(
            fk_clase, fk_tipo, archivo, pk_recurso, descripcion)
    VALUES (?, ?, ?, ?, ?);");

$fp = fopen($filename, 'rb');
$buffer = fread($fp, filesize($filename));
fclose($fp);
$buffer=pg_escape_bytea($buffer);

$db->beginTransaction();
$resutl = $stmt->execute(array('339411', '1719', $buffer, '99', $name));
$db->commit();
return $resutl;    
    }
    
    public function viewArchivo(){
        $conn  = pg_connect("user=MiUNE password=M1UN3@OWNER:k5p9q6vv4xklmu709vz dbname=MiUNE host=192.168.1.10");
$query = pg_query($conn, "SELECT archivo FROM tbl_recursos where pk_recurso = 99");
$row   = pg_fetch_row($query);
pg_close($conn);
if(!isset($row[0])){
 
    return 'ERROR';

    
}else {
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', "image/jpeg");
        //Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Disposition', "attachment; filename=$filename" );
        
    $image = pg_unescape_bytea($row[0]);
//        echo pg_unescape_bytea($row[0]);
//        echo base64_decode($image);       
    echo $image;

}
    }
    
    public function getReporteContenidos($id){
        $SQL = "
            SELECT 
                    initcap(us.nombre) as nombre_prof, 
                     initcap(us.apellido) as apellido_prof, 
                     vwmt.materia,
                     vws.valor as semestre,
                     vwse.valor as seccion,
                     vwe.escuela as escuela,
                        cl.pk_clase,
                       cl.numero,
                       cl.fecha,
                       cl.descripcion as descripcion_cl,
                       cl.contenido as contenido_cl,
                       vwes.valor AS tipo_estrategia,
                       vwev.valor AS tipo_evaluacion,
                       cl.puntaje,
                     fk_asignacion,
                     rec.*,
                     atr.valor AS tipo_recurso,
                     p.pk_periodo,
                     vwt.valor as turno,
                     es3.nombre as sede 
                FROM   tbl_clases cl
                JOIN tbl_asignaciones   asg ON asg.pk_asignacion   = cl.fk_asignacion
                JOIN tbl_usuariosgrupos usg ON usg.pk_usuariogrupo = asg.fk_usuariogrupo
                JOIN tbl_usuarios        us ON us.pk_usuario       = usg.fk_usuario
                JOIN tbl_asignaturas    ast ON ast.pk_asignatura   = asg.fk_asignatura
                JOIN tbl_periodos         p ON p.pk_periodo        = asg.fk_periodo
                JOIN tbl_estructuras    es1 ON es1.pk_estructura   = asg.fk_estructura
                JOIN tbl_estructuras    es2 ON es2.pk_estructura   = es1.fk_estructura
                JOIN tbl_estructuras    es3 ON es3.pk_estructura   = es2.fk_estructura
                JOIN tbl_pensums         pp ON pp.pk_pensum        = ast.fk_pensum
                JOIN vw_materias       vwmt ON vwmt.pk_atributo    = ast.fk_materia
                JOIN vw_evaluaciones   vwev ON vwev.pk_atributo    = cl.fk_tipoevaluacion
                JOIN vw_estrategias    vwes ON vwes.pk_atributo    = cl.fk_tipoestrategia
                JOIN vw_escuelas       vwe  ON vwe.pk_atributo     = pp.fk_escuela
                JOIN vw_turnos          vwt ON vwt.pk_atributo     = asg.fk_turno_alterado
                JOIN vw_semestres       vws ON vws.pk_atributo     = asg.fk_semestre
                JOIN vw_secciones      vwse ON vwse.pk_atributo    = asg.fk_seccion
                LEFT OUTER JOIN tbl_recursos       rec ON rec.fk_clase        = cl.pk_clase AND rec.publico = true
                LEFT OUTER JOIN tbl_atributos      atr ON atr.pk_atributo     = rec.fk_tipo
                WHERE 
               pk_asignacion = {$id}
                ORDER  BY cl.numero ASC, rec.ordinal ASC
            ;";
        
        $results = $this->_db->query($SQL);

	return (array)$results->fetchAll();
    }
    
    public function getReporteSINContenidos($id){
        $SQL = "
            SELECT 
                    initcap(us.nombre) as nombre_prof, 
                     initcap(us.apellido) as apellido_prof, 
                     vwmt.materia,
                     vws.valor as semestre,
                     vwse.valor as seccion,
                     vwe.escuela as escuela,
                     pk_asignacion,
                     p.pk_periodo,
                     vwt.valor as turno,
                     es3.nombre as sede 
                FROM   tbl_asignaciones asg
                JOIN tbl_usuariosgrupos usg ON usg.pk_usuariogrupo = asg.fk_usuariogrupo
                JOIN tbl_usuarios        us ON us.pk_usuario       = usg.fk_usuario
                JOIN tbl_asignaturas    ast ON ast.pk_asignatura   = asg.fk_asignatura
                JOIN tbl_periodos         p ON p.pk_periodo        = asg.fk_periodo
                JOIN tbl_estructuras    es1 ON es1.pk_estructura   = asg.fk_estructura
                JOIN tbl_estructuras    es2 ON es2.pk_estructura   = es1.fk_estructura
                JOIN tbl_estructuras    es3 ON es3.pk_estructura   = es2.fk_estructura
                JOIN tbl_pensums         pp ON pp.pk_pensum        = ast.fk_pensum
                JOIN vw_materias       vwmt ON vwmt.pk_atributo    = ast.fk_materia
                JOIN vw_escuelas       vwe  ON vwe.pk_atributo     = pp.fk_escuela
                JOIN vw_turnos          vwt ON vwt.pk_atributo     = asg.fk_turno_alterado
                JOIN vw_semestres       vws ON vws.pk_atributo     = asg.fk_semestre
                JOIN vw_secciones      vwse ON vwse.pk_atributo    = asg.fk_seccion
                WHERE 
               pk_asignacion = {$id}
            ;";
        
        $results = $this->_db->query($SQL);

	return (array)$results->fetchAll();
    }

}
