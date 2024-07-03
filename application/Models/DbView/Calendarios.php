<?php

class Models_DbView_Calendarios extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_calendarios';
    protected $_primary  = 'pk_calendario';
    protected $_sequence = 'tbl_calendarios_pk_calendario_seq';

    private $searchParams = array('at.valor','cal.fechainicio','cal.fechafin');

    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
       // $this->logger = Zend_Registry::get('logger');
    }

    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }

    public function getCalendario($data){

        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL = "SELECT cal.pk_calendario,
                 cal.consecutivo,
                 at.valor,
                 TO_CHAR(cal.fechainicio, 'DD-MM-YYYY') as fechainicio,
                 TO_CHAR(cal.fechafin, 'DD-MM-YYYY') as fechafin,
                 cal.titulo,
                 cal.consecutivo::numeric as orden,
                 CASE WHEN cal.destacar = true THEN 'Si' ELSE 'No' END AS destacada
                 FROM tbl_calendarios cal
                 JOIN tbl_atributos at ON at.pk_atributo = cal.fk_actividad
                 WHERE cal.fk_periodo = {$data['periodo']}
                 {$whereSearch}
                 order by 7;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getact($pk){

        $SQL = "SELECT cal.pk_calendario,
                 cal.consecutivo,
                 at.valor,
                 cal.fechainicio,
                 cal.fechafin,
                 cal.titulo,
                 cal.consecutivo::numeric as orden
                 FROM tbl_calendarios cal
                 JOIN tbl_atributos at ON at.pk_atributo = cal.fk_actividad
                 WHERE cal.fk_actividad = {$pk}
                 order by 7;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getNextConsecutivo($periodo, $pk_titulo){

        $SQL ="select CASE WHEN position('.' in consecutivo) = 0 THEN '1' ELSE (coalesce(substring(consecutivo from position('.' in consecutivo)+1 for 2),'0')::integer + 1)::text END as next,
                consecutivo::numeric as orden
                from tbl_calendarios
                where fk_periodo = {$periodo}
                and consecutivo ilike (SELECT consecutivo
                                        from tbl_calendarios where pk_calendario = {$pk_titulo}) || '%'

                order by orden DESC limit 1;";

                //$this->logger->log($SQL,ZEND_LOG::ALERT);
          $results = $this->_db->query($SQL);
          return (array)$results->fetchAll();

    }

    public function getHijos($periodo, $pk_titulo){

        $SQL = "select pk_calendario
                from tbl_calendarios
                where consecutivo ilike (SELECT consecutivo
                                         from tbl_calendarios
                                         where pk_calendario = {$pk_titulo}) || '.%'
                       and fk_periodo = {$periodo};";

          $results = $this->_db->query($SQL);
          return (array)$results->fetchAll();

    }

    public function updateCascade($periodo, $pk_titulo){

        $SQL = "UPDATE tbl_calendarios cal
               SET consecutivo= sqt.moded
               FROM (select (consecutivo::numeric - 0.1)::text as moded, pk_calendario
                     from tbl_calendarios
                     where fk_periodo = {$periodo}
                       and consecutivo ilike (SELECT substring(consecutivo from 0 for 3) from tbl_calendarios where pk_calendario = {$pk_titulo}) || '%'
                       and consecutivo::numeric > (SELECT consecutivo::numeric from tbl_calendarios where pk_calendario = {$pk_titulo})) as sqt
               WHERE cal.pk_calendario = sqt.pk_calendario;";

        $results = $this->_db->query($SQL);
        return $results;
    }

    public function getActividades($renglon){

        $SQL = "SELECT  DISTINCT pk_atributo,
                        valor
                FROM tbl_atributos
                WHERE fk_atributo = {$renglon}
                order by 2;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getConsecutivo($id){
        $SQL = "SELECT consecutivo
                FROM tbl_calendarios cal
                WHERE cal.pk_calendario = {$id}
                order by 1;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getRetiroCountdown($per){

        $SQL = "SELECT cal.fechafin - current_date as restante, to_char(fechafin, 'dd-mm-YYYY') as fin
                FROM tbl_calendarios cal
                WHERE cal.fk_actividad = 1662
                  AND cal.fk_periodo = {$per}
                ;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getDocCountdown($per){

        $SQL = "SELECT cal.fechafin - current_date as restante, to_char(fechafin, 'dd-mm-YYYY') as fin
                FROM tbl_calendarios cal
                WHERE cal.fk_actividad = 1670
                  AND cal.fk_periodo = {$per}
                ;";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getRow($id) {
        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function updateRow($id, $data) {

        $data     = array_filter($data);

        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function deleteRow($id) {
		if(!is_numeric($id)) return null;

        $affected = $this->delete($this->_primary . ' = ' . (int) $id);

        return $affected;
    }

    public function copyRow($ids, $periodo){
        if(!is_numeric($periodo)) return null;
		if(!is_string($ids))         return;

        $SQL = "INSERT INTO tbl_calendarios(fk_periodo, fk_actividad, consecutivo, fechainicio,fechafin, fk_renglon)
                SELECT {$periodo},
                       fk_actividad,
                       consecutivo,
                       fechainicio,
                       fechafin,
                       fk_renglon
                FROM   tbl_calendarios
                WHERE pk_calendario IN ({$ids})";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getCalendarioPeriodo($grupo, $periodo) {

        $SQL = "SELECT distinct to_char(cal.fechainicio, 'DD-MM-YYYY') as fechai,
                                to_char(cal.fechafin, 'DD-MM-YYYY') as fechaf,
                                (cal.fechafin - current_date) as restante,
                                atr.valor as actividad,
                                (current_date - cal.fechainicio) as actual,
                                cal.fechainicio
                FROM tbl_calendarios cal
                JOIN tbl_atributos atr ON atr.pk_atributo = cal.fk_actividad
                JOIN tbl_actividadesgrupos actg ON actg.fk_actividad = cal.fk_actividad
                where actg.fk_grupo in ({$grupo})
                  and cal.fk_periodo = {$periodo}
                  and cal.fk_renglon = 1693
                order by cal.fechainicio;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;

    }

    public function getFeriados($grupo, $periodo) {

        $SQL = "SELECT distinct to_char(cal.fechainicio, 'DD-MM-YYYY') as fechai,
                to_char(cal.fechafin, 'DD-MM-YYYY') as fechafin,
                (cal.fechafin - current_date) as restante,
                atr.valor as actividad
                FROM tbl_calendarios cal
                JOIN tbl_atributos atr ON atr.pk_atributo = cal.fk_actividad
                JOIN tbl_actividadesgrupos actg ON actg.fk_actividad = cal.fk_actividad
                where actg.fk_grupo in ({$grupo})
                  and cal.fk_periodo = {$periodo}
                  and cal.fk_renglon = 1694

                  and cal.fechafin >= current_date
                order by 1 , 2;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getAllCuotas($periodo) {

        $SQL = "SELECT distinct to_char(cal.fechainicio, 'DD-MM-YYYY') as fechai,
                to_char(cal.fechafin, 'DD-MM-YYYY') as fechafin,
                (cal.fechafin - current_date) as restante,
                atr.valor as actividad, cal.fk_renglon
                FROM tbl_calendarios cal
                JOIN tbl_atributos atr ON atr.pk_atributo = cal.fk_actividad
                --JOIN tbl_actividadesgrupos actg ON actg.fk_actividad = cal.fk_actividad
                where cal.fk_periodo = {$periodo}
                  and cal.fk_renglon = 1692
                order by restante
                ;";

        $results = $this->_db->query($SQL);
        $results = $results->fetchAll();

        return $results;
    }

    public function getPeriodoActividad($periodo, $data) {
        $sql ="Select c.fechainicio,c.fechafin , CURRENT_DATE BETWEEN c.fechainicio AND c.fechafin as isvalid
            FROM tbl_asignaturas a
            JOIN tbl_pensums p ON a.fk_pensum = p.pk_pensum
            JOIN tbl_asignaturas_regimenes ar ON ar.fk_asignatura = a.pk_asignatura
            LEFT OUTER JOIN tbl_calendarios c ON c.fk_actividad = ar.fk_actividad_finalizar AND c.fk_periodo = {$periodo}
            WHERE p.fk_escuela = {$data['escuela']} AND  p.pk_pensum = {$data['pensum']}
            AND a.fk_materia = {$data['materia']} ORDER BY c.fechafin DESC LIMIT 1 ;";
        $results = $this->_db->query($sql);
        return (array)$results->fetchAll();
    }
}

?>
