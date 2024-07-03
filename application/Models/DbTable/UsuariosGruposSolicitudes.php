<?php
class Models_DbTable_UsuariosGruposSolicitudes extends Zend_Db_Table {

    protected $_schema   = 'produccion';
    protected $_name     = 'tbl_usuariosgrupossolicitudes';
    protected $_primary  = 'pk_usuariogruposolicitud';
    protected $_sequence = 'tbl_usuariosgrupossolicitudes_pk_usuariogruposolicitud_seq';

    private $searchParams = array('u.pk_usuario','u.nombre','u.apellido','esc.escuela','at.valor');
    
    public function init() {
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
       // $this->logger = Zend_Registry::get('logger');
    }

    public function setSearch($searchData) {
        $this->searchData = $searchData;
    }

    public function getRow($id) {
        if(empty($id)) return;

        $id = (int)$id;
        $row = $this->fetchRow($this->_primary . ' = ' . $id);
        if (!$row) {
            throw new Exception("No se puede conseguir el registro #: $id");
        }
        return $row->toArray();
    }

    public function addRow($data) {
        $data     = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function updateRow($id, $data) {

        $data     = array_filter($data);

        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function getsolicitudesDocumentosFilter($id){

        $SQL = "SELECT fk_periodo, 
                       fk_estructura, se.nombre,
                       (CASE WHEN 0 = pk_periodo THEN 'N/A' ELSE lpad(pk_periodo::text, 4, '0') || ', ' || to_char(p.fechainicio, 'MM-yyyy') || ' / ' ||  to_char(p.fechafin, 'MM-yyyy') END) as periodo
                FROM tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_periodos p ON p.pk_periodo = ugs.fk_periodo
                JOIN vw_sedes se ON se.pk_estructura = ugs.fk_estructura
                WHERE pk_usuariogruposolicitud = {$id};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getSolDocByPago($pago){

        $SQL = "SELECT *
                FROM tbl_usuariosgrupossolicitudes ugs
                WHERE numeropago = {$pago};";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getSolicitudesDocumentos($other){


        $SQL= "SELECT pk_usuariogruposolicitud,
               numeropago,
               fechasolicitud,
               CASE WHEN (SELECT SUM(estado)
                          FROM(
                          --SELECT CASE WHEN fk_estado =  8232 OR fk_estado = 8233 THEN 1 ELSE 0 END AS estado --local
                          SELECT CASE WHEN fk_estado =  8267 OR fk_estado = 8268 THEN 1 ELSE 0 END AS estado --prod
                          FROM tbl_documentossolicitados ds
                          where fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud)
                       as sqt) = 0 THEN 'Completada' ELSE 'Activa' END AS estado,
                ugs.fk_periodo as periodo,
               sed.nombre as sede


                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = ugs.fk_estructura
                WHERE ug.fk_usuario = {$other['cedula']}
                  and ug.fk_grupo = {$other['grupo']}
                  and ugs.fk_tipo = 8266
                ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getAllSolicitudesDoc($data){ 

        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL= "select DISTINCT ugs.pk_usuariogruposolicitud as num_sol,
                       u.pk_usuario as ced,
                       u.nombre,
                       u.apellido,
                       esc.escuela,
                       TO_CHAR(ugs.fechasolicitud,'DD-MM-YYYY') as fechasolicitud,
                       at.valor as doc, ds.pk_documentosolicitado
                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_documentossolicitados ds ON ds.fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
                JOIN tbl_atributos at ON at.pk_atributo = ds.fk_documento
                where ugs.fk_periodo = {$data['periodo']}
                  and ugs.fk_estructura = {$data['sede']}
                  and i.fk_atributo = (
                                    select distinct i_.fk_atributo
                                    from tbl_usuarios           u_
                                    join tbl_usuariosgrupos     ug_  on  ug_.fk_usuario = u_.pk_usuario
                                    join tbl_inscripciones      i_   on  i_.fk_usuariogrupo = ug_.pk_usuariogrupo
                                    where u_.pk_usuario = u.pk_usuario
                                    order by 1 desc limit 1
                  )
                  and ds.fk_estado = {$data['estado']}
                  {$whereSearch}
                  order by 1,6
                ;";

                  //echo $SQL;
         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getSolicitudesRetiroMaterias($other){


        $SQL= "select distinct pk_usuariogruposolicitud,
                       at.valor,
                       ugs.fk_periodo,
                      sed.nombre,
                      esc.escuela,
                      ugs.fk_periodo,
                      fn_xrxx_estudiante_sem_ubicacion_periodod(ug.fk_usuario, esc.pk_atributo, ugs.fk_periodo) as semubic,
                      TO_CHAR(ugs.fechasolicitud,'DD-MM-YYYY') as fechasolicitud,
                      CASE WHEN (SELECT COUNT(DISTINCT pk_materiaretirar)
                                 FROM tbl_materiasaretirar
                                 WHERE fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud) = 0 THEN 'Vacia' ELSE 'Completada' END AS estado
                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_atributos at ON at.pk_atributo = ugs.fk_tipo
                JOIN vw_sedes sed ON sed.pk_estructura = ugs.fk_estructura
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
                WHERE ug.fk_grupo = {$other['grupo']}
                  and ug.fk_usuario = {$other['cedula']}
          --local --and ugs.fk_tipo IN (8267,8268)
                  and ugs.fk_tipo IN (8246,8247)
                  and i.fk_periodo = ugs.fk_periodo


                ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }

    public function getSolicitudesRetiroMateriasCDE($estado,$periodo){

    $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL= "select distinct pk_usuariogruposolicitud,
                       at.valor,
                       ugs.fk_periodo,
                      sed.nombre,
                      esc.escuela,
                      ugs.fk_periodo,
                      fn_xrxx_estudiante_sem_ubicacion_periodod(ug.fk_usuario, esc.pk_atributo, ugs.fk_periodo) as semubic,
                      TO_CHAR(ugs.fechasolicitud,'DD-MM-YYYY') as fechasolicitud,
                      CASE WHEN (SELECT COUNT(DISTINCT pk_materiaretirar)
                                 FROM tbl_materiasaretirar
                                 WHERE fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud) = 0 THEN 'Vacia' ELSE 'Completada' END AS estado,
                      u.pk_usuario,
                      u.nombre as name,
                      u.apellido
                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_atributos at ON at.pk_atributo = ugs.fk_tipo
                JOIN vw_sedes sed ON sed.pk_estructura = ugs.fk_estructura
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
                WHERE fk_impreso = {$estado}
             --local     --AND ugs.fk_tipo IN (8267,8268
                  and ugs.fk_tipo IN (8246,8247)
                  AND (SELECT COUNT(DISTINCT pk_materiaretirar)
                                 FROM tbl_materiasaretirar
                                 WHERE fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud) > 0
                  and i.fk_periodo = ugs.fk_periodo
                  and ugs.fk_periodo = {$periodo}
                  {$whereSearch}


                ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
    }
    

    public function getSolicitudesFilter($pk){


        $SQL = "select DISTINCT ugs.pk_usuariogruposolicitud,
                       ugs.fk_periodo,
                       i.fk_periodo as per,
                       sed.nombre as sed,
                       u.nombre, u.apellido,
                       esc.escuela,
                       --sed.nombre,
                esc.pk_atributo as esc_cod, sed.pk_estructura as sede_cod,
                fn_xrxx_estudiante_sem_ubicacion_periodod(ug.fk_usuario, i.fk_atributo, i.fk_periodo) as sem_ubic
                from tbl_usuariosgrupossolicitudes ugs
                JOIN vw_sedes sed ON sed.pk_estructura = ugs.fk_estructura
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN tbl_usuarios u ON u.pk_usuario = ug.fk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN vw_escuelas esc ON esc.pk_atributo = i.fk_atributo
                WHERE pk_usuariogruposolicitud = {$pk}
                   AND i.fk_periodo = i.fk_periodo
                   ORDER BY i.fk_periodo desc limit 1;";

         $results = $this->_db->query($SQL);

        return $results->fetchAll();

    }

    public function getSolDocInfo($pk){

        $SQL= "SELECT pk_usuariogruposolicitud,
               numeropago,
               fechasolicitud,
               CASE WHEN (SELECT SUM(estado)
                          FROM(
                          SELECT CASE WHEN fk_estado =  8232 OR fk_estado = 8233 THEN 1 ELSE 0 END AS estado
                          FROM tbl_documentossolicitados ds
                          where fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud)
                       as sqt) = 0 THEN 'Completada' ELSE 'Activa' END AS estado,
                ugs.fk_periodo as periodo,
               sed.nombre as sede


                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN vw_sedes sed ON sed.pk_estructura = ugs.fk_estructura
                WHERE ugs.pk_usuariogruposolicitud = {$pk}
                ;";

         $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();

    }

    public function getSolTipo($pk){

        $SQL = "select fk_tipo, atr.valor
                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_atributos atr ON atr.pk_atributo = ugs.fk_tipo
                WHERE pk_usuariogruposolicitud = {$pk};";

         $results = $this->_db->query($SQL);

        return $results->fetchAll();

    }

    public function getSolicitudInfo($pk){

        $SQL = "SELECT DISTINCT pk_usuariogruposolicitud,
               ugs.numeropago,
               fechasolicitud,
               ugs.fk_periodo,
               atr.valor,
               e.escuela,
               sed.nombre,
               fn_xrxx_estudiante_sem_ubicacion_periodod(ug.fk_usuario, i.fk_atributo, ugs.fk_periodo) as semubic,
               CASE WHEN (SELECT COUNT(pk_materiaretirar)
                         FROM tbl_materiasaretirar mar
                         where mar.fk_usuariogruposolicitud = ugs.pk_usuariogruposolicitud) > 0 THEN 'Completada' ELSE 'Vacia' END AS estado


                from tbl_usuariosgrupossolicitudes ugs
                JOIN tbl_usuariosgrupos ug ON ug.pk_usuariogrupo = ugs.fk_usuariogrupo
                JOIN tbl_atributos atr ON atr.pk_atributo = ugs.fk_tipo
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN vw_escuelas e ON e.pk_atributo = i.fk_atributo
                JOIN vw_sedes sed ON sed.pk_estructura = ugs.fk_estructura
                WHERE ugs.pk_usuariogruposolicitud = {$pk};";

        $results = $this->_db->query($SQL);

        return $results->fetchAll();

    }
}

?>
