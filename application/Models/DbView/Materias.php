<?php
class Models_DbView_Materias extends Zend_Db_Table {
    protected $_schema   = 'produccion';
    protected $_name     = 'vw_materias';
    protected $_primary  = 'pk_atributo';
    protected $_sequence = false;

    public function get() {
        $SQL = "SELECT {$this->_primary}, materia
                FROM {$this->_name}";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

     public function getMaterias($periodo,$sede){

        $sql = "SELECT DISTINCT atri.pk_atributo,atri.valor
                FROM tbl_usuariosgrupos ug
                JOIN tbl_asignaciones asig on ug.pk_usuariogrupo = asig.fk_usuariogrupo
                JOIN tbl_usuarios us on ug.fk_usuario = us.pk_usuario
                JOIN tbl_asignaturas  asi on asig.fk_asignatura = asi.pk_asignatura
                JOIN tbl_atributos atri on asi.fk_materia = atri.pk_atributo
                JOIN tbl_recordsacademicos re on re.fk_asignacion = asig.pk_asignacion
                JOIN tbl_inscripciones i on i.pk_inscripcion =  re.fk_inscripcion 
                WHERE i.fk_periodo = {$periodo} and i.fk_estructura = {$sede}
                ORDER BY 2";

        $results = $this->_db->query($sql);
        $results = $results->fetchAll();

        return $results;


    }




    public function getmateriascustom($semestre, $periodo, $sede, $escuela){
      if (empty($semestre)) return;
    if (empty($periodo)) return;
    if (empty($escuela)) return;
    if (empty($sede)) return;
      $SQL = "SELECT distinct a.pk_asignatura, m.materia
              FROM tbl_asignaturas a
              join tbl_pensums p on p.pk_pensum = a.fk_pensum
              JOIN vw_materias     m ON m.pk_atributo = a.fk_materia
              join tbl_asignaciones asi on asi.fk_asignatura = a.pk_asignatura
              join tbl_estructuras est on est.pk_estructura = asi.fk_estructura
              join tbl_estructuras est2 on est2.pk_estructura = est.fk_estructura
              where a.fk_semestre = {$semestre}
              and asi.fk_periodo = {$periodo}
              and est2.fk_estructura = {$sede}
              and p.fk_escuela = {$escuela}
              ORDER BY m.materia";

  $results = $this->_db->query($SQL); 

  return (array) $results->fetchAll(); 

}

  public function materiastesis($escuela){

    $SQL = "SELECT  pk_asignatura, case
        when valor like 'TESIS%' then valor || ' (1997)' 
        when valor like 'TRABAJO%' then valor || ' (2012)' 
        else valor
        end as valor
        from tbl_asignaturas asna
        join tbl_pensums pen on pen.pk_pensum = asna.fk_pensum
        join tbl_atributos atr on atr.pk_atributo = asna.fk_materia
        where    
          fk_escuela = {$escuela}
          and pk_pensum in (8,9,10,11,12,20,21,22,23,24,25)
  
  and atr.valor in( 'INVESTIGACIÓN Y DESARROLLO', 'TESIS DE GRADO I', 'TESIS DE GRADO II', 'SEMINARIO DE TRABAJO DE GRADO', 'TRABAJO DE GRADO I', 'TRABAJO DE GRADO II')

        order by valor
";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
  }



  
  
}

