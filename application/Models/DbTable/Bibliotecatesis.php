<?php
class Models_DbTable_Bibliotecatesis extends Zend_Db_Table {
        
    private $institucion = 79; // $instucion local 75 , $instucion 79
    private $resumen = 80; // $resumen local 76 , omicron 80
    private $current = 'CURRENT_DATE'; // $current local '2011-06-01'   , omicron CURRENT_DATE
    
    public function init() {
        $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
        $this->SwapBytes_Array = new SwapBytes_Array();
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }
    
    
     public function setSearch($searchData) {
        $this->searchData = $searchData;
    }

    public function getMime($ext){
        if(!is_string($ext)) return null;
       $SQL = " 
          SELECT * FROM tbl_mimes WHERE extension ilike '$ext' LIMIT 1;
          
          ";
 
         $results = $this->_db->query($SQL);
 
     return (array)$results->fetchAll();
    }
    public function getSQLCount() {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        
        
        $SQL = "SELECT COUNT (pk_tesis)
                    FROM(
                        SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
                            t.fk_institucion,a2.valor as institucion,t.calificacion,
                            t.pagina,t.ubicacion,t.observacion, ep.estado,
                            (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                             from tbl_datostesis     dt_sub
                             join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                           from tbl_atributos      a 
                                                                                                                                                                           join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                           where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                           and a.valor ilike 'Aprobado%')
                             join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                             join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                             where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,
                            (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                             from tbl_datostesis     dt_sub
                             join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                             join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                             join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                             where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,
                            (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                             from tbl_datostesis         dt_sub
                             join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                                 from tbl_atributos      a 
                                                                                                                                                 join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                 where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                                 and a.valor ilike 'Principal')
                             join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                             join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                             where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado
                    from tbl_tesis                  t
                    join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                    left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                    left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                    left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                    join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                    where dt.fk_estado = (SELECT distinct a.pk_atributo
                                            from tbl_atributos      a 
                                            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                            where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                            and a.valor ilike 'Aprobado')
                    and tt.fk_estado = (select distinct a.pk_atributo
                                          from tbl_atributos      a 
                                          join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                          where at.nombre ilike '%Estado Tutores%'
                                          and a.valor ilike 'Aprobado%')
                    {$whereSearch})as sqt ;";

        return $this->_db->fetchOne($SQL);
    }

    public function getSQLCountFiltered($periodo, $sede, $escuela, $estado, $modalidad) {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        
        
        $SQL = "SELECT COUNT (pk_tesis)
                    FROM(
                        SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
                           t.fk_institucion,a2.valor as institucion,t.calificacion,
                           t.pagina,t.ubicacion,t.observacion, ep.estado,
                           (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                                from tbl_datostesis     dt_sub
                                join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                                    from tbl_atributos      a 
                                                                                                                                                                                    join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                                    where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                                    and a.valor ilike 'Aprobado%')
                                join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                                join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                                where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,
                    
                           (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                                from tbl_datostesis     dt_sub
                                join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                                join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                                join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                                where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,
                    
                           (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                                from tbl_datostesis         dt_sub
                                join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                                    from tbl_atributos      a 
                                                                                                                                                    join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                    where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                                    and a.valor ilike 'Principal')
                                join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                                join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                                where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado
                    
                            from tbl_tesis                  t
                            join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                            left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                            left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                            left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                            join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                            where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                    from tbl_atributos      a 
                                                    join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                    where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                    and a.valor ilike 'Aprobado')
                            and tt.fk_estado = (select distinct a.pk_atributo
                                                  from tbl_atributos      a 
                                                  join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                  where at.nombre ilike '%Estado Tutores%'
                                                  and a.valor ilike 'Aprobado%')
                            and t.fk_sede = {$sede}
                            and t.fk_escuela = {$escuela}
                            and t.fk_periodo = {$periodo}
                            and t.fk_publicado = {$estado} 
                            and t.fk_tiporecurso = {$modalidad}
                        {$whereSearch})as sqt;";

        return $this->_db->fetchOne($SQL);
    }

    public function getSQLCountFilteredByEstado($estado) {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        
        
        $SQL = "SELECT COUNT (pk_tesis)
        FROM(
            SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
               t.fk_institucion,a2.valor as institucion,t.calificacion,
               t.pagina,t.ubicacion,t.observacion, ep.estado,
               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                        from tbl_atributos      a 
                                                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                        where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                        and a.valor ilike 'Aprobado%')
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis         dt_sub
                    join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                        from tbl_atributos      a 
                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                        where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                        and a.valor ilike 'Principal')
                    join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                    join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                        from tbl_tesis                  t
                        join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                        left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                        left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                        left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                        join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                        where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                from tbl_atributos      a 
                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                and a.valor ilike 'Aprobado')
                        and tt.fk_estado = (select distinct a.pk_atributo
                                              from tbl_atributos      a 
                                              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                              where at.nombre ilike '%Estado Tutores%'
                                              and a.valor ilike 'Aprobado%')
                        and t.fk_publicado = {$estado}
                    {$whereSearch})as sqt;";

        return $this->_db->fetchOne($SQL);
    }

    public function getSQLCountFilteredByEstadoxModalidad($estado, $modalidad) {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        
        
        $SQL = "SELECT COUNT (pk_tesis)
        FROM(
            SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
               t.fk_institucion,a2.valor as institucion,t.calificacion,
               t.pagina,t.ubicacion,t.observacion, ep.estado,
               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                        from tbl_atributos      a 
                                                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                        where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                        and a.valor ilike 'Aprobado%')
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis         dt_sub
                    join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                        from tbl_atributos      a 
                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                        where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                        and a.valor ilike 'Principal')
                    join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                    join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                        from tbl_tesis                  t
                        join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                        left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                        left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                        left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                        join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                        where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                from tbl_atributos      a 
                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                and a.valor ilike 'Aprobado')
                        and tt.fk_estado = (select distinct a.pk_atributo
                                              from tbl_atributos      a 
                                              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                              where at.nombre ilike '%Estado Tutores%'
                                              and a.valor ilike 'Aprobado%')
                        and t.fk_publicado = {$estado}
                        and t.fk_tiporecurso = {$modalidad}
                    {$whereSearch})as sqt;";

        return $this->_db->fetchOne($SQL);
    }

    public function getSQLCountFilteredByEstadoxModalidadxPeriodo($estado, $modalidad, $periodo) {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        
        
        $SQL = "SELECT COUNT (pk_tesis)
        FROM(
            SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
               t.fk_institucion,a2.valor as institucion,t.calificacion,
               t.pagina,t.ubicacion,t.observacion, ep.estado,
               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                        from tbl_atributos      a 
                                                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                        where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                        and a.valor ilike 'Aprobado%')
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis         dt_sub
                    join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                        from tbl_atributos      a 
                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                        where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                        and a.valor ilike 'Principal')
                    join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                    join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                        from tbl_tesis                  t
                        join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                        left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                        left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                        left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                        join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                        where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                from tbl_atributos      a 
                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                and a.valor ilike 'Aprobado')
                        and tt.fk_estado = (select distinct a.pk_atributo
                                              from tbl_atributos      a 
                                              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                              where at.nombre ilike '%Estado Tutores%'
                                              and a.valor ilike 'Aprobado%')
                        and t.fk_publicado = {$estado}
                        and t.fk_tiporecurso = {$modalidad}
                        and t.fk_periodo = {$periodo}
                    {$whereSearch})as sqt;";

        return $this->_db->fetchOne($SQL);
    }

    public function getSQLCountFilteredByEstadoxModalidadxPeriodoxSede($estado, $modalidad, $periodo, $sede) {
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        
        
        $SQL = "SELECT COUNT (pk_tesis)
        FROM(
            SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
               t.fk_institucion,a2.valor as institucion,t.calificacion,
               t.pagina,t.ubicacion,t.observacion, ep.estado,
               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                        from tbl_atributos      a 
                                                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                        where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                        and a.valor ilike 'Aprobado%')
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis     dt_sub
                    join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                    join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                    join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                    from tbl_datostesis         dt_sub
                    join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                        from tbl_atributos      a 
                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                        where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                        and a.valor ilike 'Principal')
                    join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                    join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                    where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                        from tbl_tesis                  t
                        join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                        left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                        left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                        left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                        join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                        where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                from tbl_atributos      a 
                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                and a.valor ilike 'Aprobado')
                        and tt.fk_estado = (select distinct a.pk_atributo
                                              from tbl_atributos      a 
                                              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                              where at.nombre ilike '%Estado Tutores%'
                                              and a.valor ilike 'Aprobado%')
                        and t.fk_publicado = {$estado}
                        and t.fk_tiporecurso = {$modalidad}
                        and t.fk_periodo = {$periodo}
                        and t.fk_sede = {$sede}
                    {$whereSearch})as sqt;";

        return $this->_db->fetchOne($SQL);
    }

    public function getAtributoFiltro() {
        
        $SQL = "SELECT distinct pk_atributotipo
                from tbl_atributostipos
                where nombre ilike 'Filtro BibliotecaTesis'";

        return $this->_db->fetchOne($SQL);
    }   

    public function getAtributoFiltroCargadas() {
        
        $SQL = "SELECT distinct pk_atributo
                from tbl_atributostipos         at
                join tbl_atributos              a       on      a.fk_atributotipo       =       at.pk_atributotipo
                where at.nombre ilike 'Filtro BibliotecaTesis'
                and a.valor ilike 'Cargadas'";

        return $this->_db->fetchOne($SQL);
    } 


    public function getCotaExiste($busqueda) {
        
        $SQL = "SELECT count( distinct pk_tesis)
                from tbl_tesis 
                where cota ilike '%{$busqueda}%'";

        return $this->_db->fetchOne($SQL);
    }  

    public function getTituloExiste($busqueda) {
        
        $SQL = "SELECT count( distinct pk_datotesis)
                from tbl_datostesis     dt 
                where titulo ilike '%{$busqueda}%'";

        return $this->_db->fetchOne($SQL);
    }


    
    public function get_tesis($itemPerPage, $pageNumber){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        

 
        $SQL = "SELECT DISTINCT pk_tesis,cota, titulo, palabrasclaves,fk_escuela, escuela,
            fk_institucion, calificacion, pagina,ubicacion, observacion, estado as estado,
            (case when tutor is null then 'Tutor sin aprobar' else tutor end) as tutor, 
             (case when autor is null then 'Autor sin asignar' else autor end) as autor,
              (case when jurado is null then 'Jurado sin asignar' else jurado end) as jurado
            FROM(
            SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
               t.fk_institucion,a2.valor as institucion,t.calificacion,
               t.pagina,t.ubicacion,t.observacion, ep.estado,
               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                from tbl_datostesis     dt_sub
                join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                              from tbl_atributos      a 
                                                                                                                                                              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                              where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                              and a.valor ilike 'Aprobado%')
                join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                where dt_sub.pk_datotesis = dt.pk_datotesis
                ) as tutor,
               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                from tbl_datostesis     dt_sub
                join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                where dt_sub.pk_datotesis = dt.pk_datotesis
                ) as autor,
               (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                from tbl_datostesis         dt_sub
                join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                    from tbl_atributos      a 
                                                                                                                                    join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                    where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                    and a.valor ilike 'Principal')
                join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                where dt_sub.pk_datotesis = dt.pk_datotesis
                ) as jurado
        from tbl_tesis                  t
        join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
        left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
        left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
        left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
        join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
        where dt.fk_estado = (SELECT distinct a.pk_atributo
                                    from tbl_atributos      a 
                                    join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                    where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                    and a.valor ilike 'Aprobado')
        and tt.fk_estado = (select distinct a.pk_atributo
                              from tbl_atributos      a 
                              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                              where at.nombre ilike '%Estado Tutores%'
                              and a.valor ilike 'Aprobado%')
        {$whereSearch}
        ORDER BY cota DESC
        LIMIT {$itemPerPage} OFFSET {$pageNumber})as sqt";
        

        // echo '<pre>';
        // var_dump($SQL);
        // echo '</pre>';
        // die();
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();        
        
    }

    public function get_tesisFiltered($itemPerPage, $pageNumber, $periodo, $sede, $escuela, $estado, $modalidad){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        

 
        $SQL = "SELECT DISTINCT pk_tesis,cota, titulo, palabrasclaves,fk_escuela, escuela,
                    fk_institucion, calificacion, pagina,ubicacion, observacion, estado as estado,
                    (case when tutor is null then 'Tutor sin aprobar' else tutor end) as tutor, 
                    (case when autor is null then 'Autor sin asignar' else autor end) as autor,
                    (case when jurado is null then 'Jurado sin asignar' else jurado end) as jurado
                    FROM(
                    SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
                        t.fk_institucion,a2.valor as institucion,t.calificacion,
                        t.pagina,t.ubicacion,t.observacion, ep.estado,
                        (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                                where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                                and a.valor ilike 'Aprobado%')
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis
                        ) as tutor,
                        (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis
                        ) as autor,
                        (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis         dt_sub
                            join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                                        from tbl_atributos      a 
                                                                                                                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                        where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                                        and a.valor ilike 'Principal')
                            join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                            join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis
                        ) as jurado
                            from tbl_tesis                  t
                            join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                            left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                            left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                            left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                            join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                            where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                    from tbl_atributos      a 
                                                    join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                    where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                    and a.valor ilike 'Aprobado')
                            and tt.fk_estado = (select distinct a.pk_atributo
                                                    from tbl_atributos      a 
                                                    join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                    where at.nombre ilike '%Estado Tutores%'
                                                    and a.valor ilike 'Aprobado%')
                            and t.fk_sede = {$sede}
	                        and t.fk_escuela = {$escuela}
	                        and t.fk_periodo = {$periodo}
                            and t.fk_publicado = {$estado}
                            and t.fk_tiporecurso = {$modalidad}
                            {$whereSearch}
                            ORDER BY cota DESC
                            LIMIT {$itemPerPage} OFFSET {$pageNumber})as sqt;";

        
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();        
        
    }

    public function get_tesisFilteredByEstado($itemPerPage, $pageNumber, $estado){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        

 
        $SQL = "SELECT DISTINCT pk_tesis,cota, titulo, palabrasclaves, fk_escuela, escuela,
        fk_institucion, calificacion, pagina, ubicacion, observacion, estado as estado,
        (case when tutor is null then 'Tutor sin aprobar' else tutor end) as tutor, 
        (case when autor is null then 'Autor sin asignar' else autor end) as autor,
        (case when jurado is null then 'Jurado sin asignar' else jurado end) as jurado
                FROM(
                    SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
                       t.fk_institucion,a2.valor as institucion,t.calificacion,
                       t.pagina,t.ubicacion,t.observacion, ep.estado,
                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                                where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                                and a.valor ilike 'Aprobado%')
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis         dt_sub
                            join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                                and a.valor ilike 'Principal')
                            join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                            join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                                from tbl_tesis                  t
                                join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                                left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                                left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                                left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                                join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                                where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                        from tbl_atributos      a 
                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                        where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                        and a.valor ilike 'Aprobado')
                                and tt.fk_estado = (select distinct a.pk_atributo
                                                      from tbl_atributos      a 
                                                      join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                      where at.nombre ilike '%Estado Tutores%'
                                                      and a.valor ilike 'Aprobado%')
                                and t.fk_publicado = {$estado}
                            {$whereSearch}
                            ORDER BY 2 DESC
                            LIMIT {$itemPerPage} OFFSET {$pageNumber})as sqt;";

        
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();        
        
    }

    public function get_tesisFilteredByEstadoxModalidad($itemPerPage, $pageNumber, $estado, $modalidad){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        

 
        $SQL = "SELECT DISTINCT pk_tesis,cota, titulo, palabrasclaves, fk_escuela, escuela,
        fk_institucion, calificacion, pagina, ubicacion, observacion, estado as estado,
        (case when tutor is null then 'Tutor sin aprobar' else tutor end) as tutor, 
        (case when autor is null then 'Autor sin asignar' else autor end) as autor,
        (case when jurado is null then 'Jurado sin asignar' else jurado end) as jurado
                FROM(
                    SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
                       t.fk_institucion,a2.valor as institucion,t.calificacion,
                       t.pagina,t.ubicacion,t.observacion, ep.estado,
                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                                where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                                and a.valor ilike 'Aprobado%')
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis         dt_sub
                            join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                                and a.valor ilike 'Principal')
                            join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                            join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                                from tbl_tesis                  t
                                join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                                left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                                left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                                left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                                join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                                where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                        from tbl_atributos      a 
                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                        where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                        and a.valor ilike 'Aprobado')
                                and tt.fk_estado = (select distinct a.pk_atributo
                                                      from tbl_atributos      a 
                                                      join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                      where at.nombre ilike '%Estado Tutores%'
                                                      and a.valor ilike 'Aprobado%')
                                and t.fk_publicado = {$estado}
                                and t.fk_tiporecurso = {$modalidad}
                            {$whereSearch}
                            ORDER BY 2 DESC
                            LIMIT {$itemPerPage} OFFSET {$pageNumber})as sqt;";

        
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();        
        
    }

    public function get_tesisFilteredByEstadoxModalidadxPeriodo($itemPerPage, $pageNumber, $estado, $modalidad, $periodo){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        

 
        $SQL = "SELECT DISTINCT pk_tesis,cota, titulo, palabrasclaves, fk_escuela, escuela,
        fk_institucion, calificacion, pagina, ubicacion, observacion, estado as estado,
        (case when tutor is null then 'Tutor sin aprobar' else tutor end) as tutor, 
        (case when autor is null then 'Autor sin asignar' else autor end) as autor,
        (case when jurado is null then 'Jurado sin asignar' else jurado end) as jurado
                FROM(
                    SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
                       t.fk_institucion,a2.valor as institucion,t.calificacion,
                       t.pagina,t.ubicacion,t.observacion, ep.estado,
                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                                where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                                and a.valor ilike 'Aprobado%')
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis         dt_sub
                            join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                                and a.valor ilike 'Principal')
                            join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                            join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                                from tbl_tesis                  t
                                join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                                left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                                left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                                left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                                join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                                where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                        from tbl_atributos      a 
                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                        where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                        and a.valor ilike 'Aprobado')
                                and tt.fk_estado = (select distinct a.pk_atributo
                                                      from tbl_atributos      a 
                                                      join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                      where at.nombre ilike '%Estado Tutores%'
                                                      and a.valor ilike 'Aprobado%')
                                and t.fk_publicado = {$estado}
                                and t.fk_tiporecurso = {$modalidad}
                                and t.fk_periodo = {$periodo}
                            {$whereSearch}
                            ORDER BY 2 DESC
                            LIMIT {$itemPerPage} OFFSET {$pageNumber})as sqt;";

        
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();        
        
    }

    public function get_tesisFilteredByEstadoxModalidadxPeriodoxSede($itemPerPage, $pageNumber, $estado, $modalidad, $periodo, $sede){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearchFixed(array('t.cota', 'dt.titulo', 't.palabrasclaves', 've.escuela'), $this->searchData, true);        

 
        $SQL = "SELECT DISTINCT pk_tesis,cota, titulo, palabrasclaves, fk_escuela, escuela,
        fk_institucion, calificacion, pagina, ubicacion, observacion, estado as estado,
        (case when tutor is null then 'Tutor sin aprobar' else tutor end) as tutor, 
        (case when autor is null then 'Autor sin asignar' else autor end) as autor,
        (case when jurado is null then 'Jurado sin asignar' else jurado end) as jurado
                FROM(
                    SELECT t.pk_tesis,t.cota,dt.titulo,t.palabrasclaves,t.fk_escuela,ve.escuela,
                       t.fk_institucion,a2.valor as institucion,t.calificacion,
                       t.pagina,t.ubicacion,t.observacion, ep.estado,
                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                                where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                                and a.valor ilike 'Aprobado%')
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as tutor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis     dt_sub
                            join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis     =   dt_sub.pk_datotesis and at_sub.renuncia = false
                            join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   at_sub.fk_usuariogrupo
                            join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as autor,

                       (select btrim(array_agg(u_sub.primer_nombre ||' '|| u_sub.primer_apellido)::varchar,'NULL{}')
                            from tbl_datostesis         dt_sub
                            join tbl_evaluadorestesis   et_sub      on      et_sub.fk_datotesis     =   dt_sub.pk_datotesis and et_sub.fk_tipo = (select distinct a.pk_atributo
                                                                                                                                                from tbl_atributos      a 
                                                                                                                                                join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                where at.nombre ilike 'Tipo Evaluadores'
                                                                                                                                                and a.valor ilike 'Principal')
                            join tbl_usuariosgrupos     ug_sub      on      ug_sub.pk_usuariogrupo  =   et_sub.fk_usuariogrupo
                            join tbl_usuarios           u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                            where dt_sub.pk_datotesis = dt.pk_datotesis) as jurado

                                from tbl_tesis                  t
                                join tbl_datostesis             dt      on      dt.pk_datotesis     =       t.fk_datotesis
                                left outer join vw_escuelas     ve      on      ve.pk_atributo      =       t.fk_escuela    
                                left outer join tbl_atributos   a2      on      a2.pk_atributo      =       t.fk_institucion
                                left outer join tbl_tutorestesis tt     on      tt.fk_datotesis     =       dt.pk_datotesis
                                join vw_estadospublicacionestesis ep on t.fk_publicado = ep.pk_atributo
                                where dt.fk_estado = (SELECT distinct a.pk_atributo
                                                        from tbl_atributos      a 
                                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                        where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                                                        and a.valor ilike 'Aprobado')
                                and tt.fk_estado = (select distinct a.pk_atributo
                                                      from tbl_atributos      a 
                                                      join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                      where at.nombre ilike '%Estado Tutores%'
                                                      and a.valor ilike 'Aprobado%')
                                and t.fk_publicado = {$estado}
                                and t.fk_tiporecurso = {$modalidad}
                                and t.fk_periodo = {$periodo}
                                and t.fk_sede = {$sede}
                            {$whereSearch}
                            ORDER BY 2 DESC
                            LIMIT {$itemPerPage} OFFSET {$pageNumber})as sqt;";

        
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();        
        
    }

    public function getRowTesis($pk){
         $SQL = "SELECT t.pk_tesis,
                        t.cota,
                        t.serialrecurso,
                        t.fk_periodo,
                        dt.titulo,
                        t.resumen,
                        t.fecha,
                        t.palabrasclaves,
                        t.fk_escuela,
                        t.fk_tiporecurso,
                        t.fk_publicado,
                        (select distinct pk_usuario
                        from tbl_datostesis     dt_sub
                        join tbl_tutorestesis   tt_sub      on      tt_sub.fk_datotesis     =   dt_sub.pk_datotesis and tt_sub.renuncia = false and tt_sub.fk_estado = (select distinct a.pk_atributo
                                                                                                                                                                      from tbl_atributos      a 
                                                                                                                                                                      join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                                                                                                                                      where at.nombre ilike '%Estado Tutores%'
                                                                                                                                                                      and a.valor ilike 'Aprobado%')
                        join tbl_usuariosgrupos ug_sub      on      ug_sub.pk_usuariogrupo  =   tt_sub.fk_usuariogrupo
                        join tbl_usuarios       u_sub       on      u_sub.pk_usuario        =   ug_sub.fk_usuario
                        where dt_sub.pk_datotesis = dt.pk_datotesis
                        ) as fk_tutor, 
                        t.fk_institucion,
                        t.calificacion,
                        t.pagina,
                        t.ubicacion,
                        t.observacion, 
                        t.fk_sede,
                        t.archivo
                FROM tbl_tesis          t
                join tbl_datostesis     dt          on          dt.pk_datotesis     =   t.fk_Datotesis
                where t.pk_tesis = {$pk}";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function getRowAutor($pk){ //  fakta un or para saber si es el period actual
         
         
         $SQL = "select distinct ug.fk_usuario as fk_autor, i.fk_periodo
                from tbl_tesis                      t 
                join tbl_datostesis                 dt      on      dt.pk_datotesis     =   t.fk_Datotesis
                join tbl_autorestesis               at      on      at.fk_Datotesis     =   dt.pk_datotesis         and at.renuncia = false
                left outer join tbl_usuariosgrupos  ug      on      ug.pk_usuariogrupo  =   at.fk_usuariogrupo
                left outer join tbl_inscripciones   i       on      i.fk_usuariogrupo   =   ug.pk_usuariogrupo
                left outer join tbl_recordsacademicos ra    on      ra.fk_inscripcion   =   i.pk_inscripcion
                left outer join tbl_asignaturas     asig    on      asig.pk_asignatura  =   ra.fk_asignatura
                left outer join vw_materias         vma     on      vma.pk_atributo     =   asig.fk_materia
                left outer join tbl_periodos        pe      on      pe.pk_periodo       =   i.fk_periodo
                where vma.materia ilike 'TESIS DE GRADO II'
                AND ra.calificacion >= 10
                and t.pk_tesis ={$pk}";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function getRowJurado($pk){
         $SQL = "select pk_evaluadortesis as pk_evaluadortesis, ug.fk_usuario as fk_jurado, t.pk_tesis
                from tbl_tesis              t 
                join tbl_datostesis         dt      on      dt.pk_datotesis     =   t.fk_datotesis           
                join tbl_evaluadorestesis   et      on      et.fk_datotesis     =   dt.pk_datotesis
                join tbl_usuariosgrupos     ug      on      ug.pk_usuariogrupo  =   et.fk_usuariogrupo
                where t.pk_tesis = {$pk}
                and et.fk_tipo = (select distinct a.pk_atributo
                            from tbl_atributos      a 
                            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                            where at.nombre ilike 'Tipo Evaluadores'
                            and a.valor ilike 'Principal')
";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll(); 
    }

    public function getRowTutor($pk){
        $SQL = "select pk_tutortesis as pk_tutortesis, ug.fk_usuario as fk_tutor, t.pk_tesis
        from tbl_tesis              t 
        join tbl_datostesis         dt      on      dt.pk_datotesis     =   t.fk_datotesis           
        join tbl_tutorestesis   tt      on      tt.fk_datotesis     =   dt.pk_datotesis
        join tbl_usuariosgrupos     ug      on      ug.pk_usuariogrupo  =   tt.fk_usuariogrupo
        where t.pk_tesis = {$pk} and tt.fk_estado = (select distinct a.pk_atributo
                                                        from tbl_atributos      a 
                                                           join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                                        where at.nombre ilike '%Estado Tutores%'
                                                        and a.valor ilike 'Aprobado%')";
                   
       $results = $this->_db->query($SQL);

       return (array) $results->fetchAll(); 
   }
    
    public function getDatatesis($cota){
        
        $SQL = "SELECT t.pk_tesis,t.cota,t.fk_periodo,t.serialrecurso,dt.titulo,t.resumen,t.fk_escuela,ve.escuela,t.fk_tiporecurso,u1.pk_usuario as fk_tutor,(u1.nombre || ' '||u1.apellido)as nombre,  
                t.fk_institucion,a2.valor as institucion,t.calificacion,t.pagina,t.ubicacion,t.observacion, t.fk_sede,tt.fk_publicado, palabrasclaves, t.archivo
                FROM tbl_tesis                      t
                join  tbl_datostesis                dt      on      dt.pk_datotesis     =   t.fk_datotesis
                left outer join tbl_tutorestesis    tt      on      tt.fk_Datotesis     =   dt.pk_Datotesis
                left outer join tbl_usuariosgrupos  ug      on      ug.pk_usuariogrupo  =   tt.fk_usuariogrupo
                left outer join tbl_usuarios        u1      on      u1.pk_usuario       =   ug.fk_usuario
                LEFT OUTER JOIN vw_escuelas         ve      ON      ve.pk_atributo      =   t.fk_escuela
                LEFT OUTER JOIN  tbl_atributos      a2      ON      a2.pk_atributo      =   t.fk_institucion
                where t.cota = '{$cota}'";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function getAutor(){ //Tenia los parametros $periodo,$escuela No se usan de todas maneras...
         //AND '2011-05-03' between p.fechainicio and p.fechafin 
        
        $SQL = "SELECT sqt.pk_usuario, (sqt.nombre ||' '|| sqt.apellido)as nombre
                FROM(
                SELECT u.pk_usuario,ug.pk_usuariogrupo ,u.nombre , u.apellido
                from tbl_usuarios u 
                JOIN tbl_usuariosgrupos ug   ON u.pk_usuario      = ug.fk_usuario
                JOIN tbl_inscripciones i     ON i.fk_usuariogrupo = ug.pk_usuariogrupo 
                JOIN tbl_recordsacademicos re ON re.fk_inscripcion = i.pk_inscripcion
                JOIN tbl_asignaturas asig    ON asig.pk_asignatura = re.fk_asignatura 
                JOIN vw_materias m ON m.pk_atributo = asig.fk_materia
                JOIN tbl_periodos p ON p.pk_periodo = i.fk_periodo
                WHERE fk_materia in (9719,9723,9724,10621,830,834)
                AND ug.fk_grupo = 855
                --AND p.pk_periodo = {$periodo}
                --AND i.fk_atributo = {$escuela}
                )AS sqt
                group by 1,2
                order by 2 ";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function getNumAutores($pk){
        $SQL ="SELECT at.pk_autortesis , ug.fk_usuario as fk_autor , t.pk_tesis,u.pk_usuario,(u.nombre ||' '|| u.apellido)as nombre
                FROM tbl_tesis              t       
                left outer join tbl_datostesis         dt      on dt.pk_Datotesis          =   t.fk_datotesis
                left outer join tbl_autorestesis       at      ON dt.pk_datotesis          =   at.fk_Datotesis            and at.renuncia = false
                left outer join tbl_usuariosgrupos     ug      on ug.pk_usuariogrupo       =   at.fk_usuariogrupo
                left outer join tbl_usuarios            u       on u.pk_usuario             =   ug.fk_usuario
                WHERE t.pk_tesis = {$pk}";

               
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
    }

    public function get_escuela(){
         $SQL = "SELECT pk_atributo , escuela  
                 FROM vw_escuelas
                 order by 2";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function get_publicacion(){
       $SQL = "SELECT pk_atributo, estado  
               FROM vw_estadospublicacionestesis
               ORDER BY 2";
                   
       $results = $this->_db->query($SQL);

       return (array) $results->fetchAll();
   }

   public function get_tiporecurso(){
    $SQL = "SELECT pk_atributo, tipo  
            FROM vw_tiporecurso
            ORDER BY 2";
                
    $results = $this->_db->query($SQL);

    return (array) $results->fetchAll();
}
   
   
    public function get_jurado(){
         $SQL = "SELECT pk_usuario , (nombre || ' ' || apellido) as nombre
                    FROM(
                    SELECT  u.pk_usuario, ug.pk_usuariogrupo , nombre , apellido 
                    FROM tbl_usuarios u 
                    JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                    WHERE ug.fk_grupo = 854
                    )as sqt 
                    order by 2";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function get_institucion(){
          $SQL = "select pk_atributo , valor as institucion 
                  from tbl_atributos 
                  where fk_atributotipo = $this->institucion
                  order by 1 asc";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }
    
    public function get_resumen(){
          $SQL = "select pk_atributo , valor as resumen 
                  from tbl_atributos 
                  where fk_atributotipo = $this->resumen
                  order by 1 asc";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }

    public function update_tesis(){
        $SQL = "UPDATE tbl_libros
               SET cota='{$cota}', titulo='{$titulo}', edicion='{$edicion}', fk_pais={$pais}, fk_ciudad={$ciudad}, 
                   fk_editorial='{$editorial}', ano='{$ano}', pagina='{$pagina}', volumen='{$volumen}', ejemplar='{$ejemplar}', nota='{$nota}', 
                   coleccion='{$coleccion}', numero={$numero}
                   WHERE pk_libro = {$id};";
               
       $this->_db->query($SQL);
    }
   
    public function getPk_Tesis(){
         $SQL = "SELECT pk_tesis 
                 FROM tbl_tesis
                 ORDER by 1 DESC
                 LIMIT 1";
                    
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }


    
    public function Insert_tesis($cota,$titulo,$fk_escuela,$fk_tutor,$fk_institucion,$calificacion,$pagina,$ubicacion,$observacion,$sede){
        if($pagina == NULL){
            $pagina = 0;
        }
        if($ubicacion == ''){
            $ubicacion = '';
        }
         if($observacion == ''){
            $observacion = '';
        }
        
        $SQL = "INSERT INTO tbl_tesis(
                        cota, titulo, fk_escuela, fk_tutor, fk_institucion, 
                        calificacion, pagina, ubicacion, observacion,fk_sede)
                VALUES ('{$cota}','{$titulo}',{$fk_escuela},{$fk_tutor},{$fk_institucion},{$calificacion},{$pagina},'{$ubicacion}','{$observacion}', {$sede});";
        
                
        
        $this->_db->query($SQL); 
    } 
    
    public function deleteRow($id){
        //eliminar el autor
        $SQL1 =  "DELETE FROM tbl_autorestesis
                 WHERE fk_tesis = {$id}";
        $this->_db->query($SQL1);
       
       
        // elimino el jurado
        $SQL3 = "DELETE FROM tbl_juradotesis
                WHERE fk_tesis = {$id};";
        $this->_db->query($SQL3);
        
        
         // elimino el tesis
        $SQL2 = "DELETE FROM tbl_tesis
                WHERE pk_tesis = {$id};";
        $this->_db->query($SQL2);
                
                
    }
    
    public function Updatetesis($pk,$fk_sede,$fk_periodo,$fecha,$cota,$serialrecurso,$resumen,$palabrasclaves,$fk_escuela,$fk_tiporecurso,$fk_institucion,$calificacion,$pagina,$fk_publicado,$ubicacion,$observacion){
        if($pagina == NULL){
            $pagina = 0;
        }
        if($ubicacion == ''){
            $ubicacion = '';
        }
         if($observacion == ''){
            $observacion = '';
        }
        
        $SQL = "UPDATE tbl_tesis
        SET fk_sede={$fk_sede}, fk_periodo={$fk_periodo}, cota='{$cota}', fecha='{$fecha}',serialrecurso='{$serialrecurso}', resumen='{$resumen}' ,palabrasclaves='{$palabrasclaves}', fk_escuela={$fk_escuela}, fk_tiporecurso={$fk_tiporecurso},
            fk_institucion={$fk_institucion}, calificacion={$calificacion}, fk_publicado={$fk_publicado},pagina={$pagina}, ubicacion='{$ubicacion}', observacion='{$observacion}'
            WHERE pk_tesis ={$pk} ;";
        
                
             
        $this->_db->query($SQL); 
        
    }

    public function UpdateTitulo($pk,$titulo)
    {
        $SQL = "UPDATE tbl_datostesis
                set titulo = '{$titulo}'
                where pk_datotesis = {$pk}";

        $this->_db->query($SQL); 
    }

    public function getPkDatotesis($pk_tesis)
    {
        $SQL = "SELECT distinct pk_datotesis
                from tbl_tesis      t 
                join tbl_datostesis dt      on      dt.pk_Datotesis     =   t.fk_Datotesis
                where t.pk_tesis = {$pk_tesis}
                limit 1";

        return $this->_db->fetchOne($SQL);
    }

    public function getPKAutorTesis($fk_usuariogrupo = null, $fk_datotesis){
       $SQL =  "SELECT pk_autortesis FROM tbl_autorestesis
                WHERE fk_usuariogrupo = {$fk_usuariogrupo} AND fk_datotesis = {$fk_datotesis}";
       return $this->_db->fetchAll($SQL);
    }
    
    public function DeleteAutor($pk_autortesis){
         //eliminar el autor
        $SQL =  "DELETE FROM tbl_autorestesis
                 WHERE pk_autortesis = {$pk_autortesis}";
        $this->_db->query($SQL);
    }


    public function getPKEvaluadorTesis($fk_usuariogrupo, $fk_datotesis){
        $SQL =  "SELECT pk_evaluadortesis FROM tbl_evaluadorestesis
                 WHERE fk_usuariogrupo = {$fk_usuariogrupo} AND fk_datotesis = {$fk_datotesis}";
        return $this->_db->fetchAll($SQL);
     }

    public function DeleteJurado($pk_juradotesis){
         //eliminar el autor
        $SQL =  "DELETE FROM tbl_evaluadorestesis
                 WHERE pk_evaluadortesis = {$pk_juradotesis}";
        $this->_db->query($SQL);
    }
    
    public function UpdateAutor($pk_autortesis,$fk_usuariogrupoautor,$fk_datotesis){
        $SQL = "UPDATE tbl_autorestesis
                SET fk_usuariogrupo = {$fk_usuariogrupoautor}, fk_datotesis = {$fk_datotesis}
                WHERE pk_autortesis ={$pk_autortesis};";
             
        $this->_db->query($SQL);          
    }
    
    public function UpdateJurado($pk_juradotesis,$fk_usuariogrupojurado,$fk_datotesis){
        $SQL = "UPDATE tbl_evaluadorestesis
                SET fk_usuariogrupo={$fk_usuariogrupojurado}, fk_datotesis={$fk_datotesis}
                WHERE pk_evaluadortesis ={$pk_juradotesis};";
             
              
        $this->_db->query($SQL);          
    }

    public function UpdateTutor($pk_tutortesis,$fk_usuariogrupotutor,$fk_datotesis){
        $SQL = "UPDATE tbl_tutorestesis
                SET fk_usuariogrupo={$fk_usuariogrupotutor}, fk_datotesis={$fk_datotesis}
                WHERE pk_tutortesis ={$pk_tutortesis};";
             
              
        $this->_db->query($SQL);          
    }
    
    public function get_periodoautor($pk_usuario){
        $SQL = " SELECT p.pk_periodo
                from tbl_usuarios u 
                JOIN tbl_usuariosgrupos ug   ON u.pk_usuario      = ug.fk_usuario
                JOIN tbl_inscripciones i     ON i.fk_usuariogrupo = ug.pk_usuariogrupo 
                JOIN tbl_recordsacademicos re ON re.fk_inscripcion = i.pk_inscripcion
                JOIN tbl_asignaturas asig    ON asig.pk_asignatura = re.fk_asignatura 
                JOIN vw_materias m ON m.pk_atributo = asig.fk_materia
                JOIN tbl_periodos p ON p.pk_periodo = i.fk_periodo
                WHERE materia ilike 'TESIS DE GRADO II'
                AND u.pk_usuario = {$pk_usuario}
                order by 1 desc
                limit 1";
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();         
    }

    public function getsedeform(){
        $SQL = "SELECT pk_estructura , nombre as sede 
                FROM vw_sedes;";
        
       
         $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
    }    


    public function getUltimaCota(){
        
        $SQL = "select distinct cota
                from tbl_tesis
                order by cota desc limit 1";
        
       
         return $this->_db->fetchOne($SQL);
    } 


    public function addDatoTesis($titulo){
        

        $SQL ="INSERT INTO tbl_datostesis(
                        fk_estado, titulo)
                VALUES ((select distinct a.pk_atributo
                                  from tbl_atributostipos     at 
                                  join tbl_atributos        a       on    a.fk_atributotipo = at.pk_atributotipo
                                  where at.nombre ilike 'Estado Tesis'
                                  and a.valor ilike 'Aprobado'),'{$titulo}');";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  } 

  public function updateArchivoTesis($ruta_archivo, $pk_tesis){
        

    $SQL ="UPDATE tbl_tesis SET archivo = '$ruta_archivo' WHERE pk_tesis = $pk_tesis;";

    $results = $this->_db->query($SQL);
    return  $results->fetchAll();

} 

   public function getTesisByTitulo($titulo){
        
              
       if(empty($titulo))return;
      
      $SQL ="select distinct dt.pk_datotesis
            from tbl_datostesis     dt
            where dt.titulo = '{$titulo}'";

            
        return $this->_db->fetchOne($SQL);
      
  }


   public function getUsuariogrupo($cedula,$grupo = null){
         
              
       if(empty($cedula))return;
       
       ($grupo != null)? $filtro_grupo = " and fk_grupo in ({$grupo}) ":$filtro_grupo = "";
       
        $SQL ="select distinct pk_usuariogrupo
        from tbl_usuariosgrupos
        where fk_usuario = {$cedula} 
       " .$filtro_grupo."
         limit 1";

        return $this->_db->fetchOne($SQL);
      
  }

  public function addTesista($tesis,$usuario,$periodo){
        
        $SQL ="INSERT INTO tbl_autorestesis(
                        fk_datotesis, fk_usuariogrupo,renuncia,fk_periodo)
                VALUES ({$tesis}, {$usuario},false,{$periodo});";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }


  public function addJurado($fk_datotesis,$fk_usuariogrupo,$fk_periodo){

    $SQL ="INSERT INTO tbl_evaluadorestesis(
                    fk_datotesis, fk_usuariogrupo,fk_tipo,fk_periodo, fk_rol)
            VALUES ({$fk_datotesis}, {$fk_usuariogrupo},19965,{$fk_periodo}, null);";

    $results = $this->_db->query($SQL);
    return  $results->fetchAll();
}

  public function addEvaluadoresTesis($cod,$evaluador,$periodo){
          
          if(empty($cod))return;
          if(empty($evaluador))return;

          $SQL ="INSERT INTO tbl_evaluadorestesis(
                        fk_datotesis,fk_usuariogrupo, fk_tipo,fk_periodo)
                VALUES ({$cod},{$evaluador},".$this->getAtributoEvaluadorPrincipal().",{$periodo});";

          $results = $this->_db->query($SQL);
          return  $results->fetchAll();
    }


    public function getAtributoEvaluadorPrincipal(){
      
      $SQL ="select distinct a.pk_atributo
            from tbl_atributos      a 
            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
            where at.nombre ilike 'Tipo Evaluadores'
            and a.valor ilike 'Principal'";
               
      return $this->_db->fetchOne($SQL);      
    }


      public function addTesisBiblioteca($fk_datotesis,$periodo,$fecha,$fk_tiporecurso,$resumen,$serialrecurso,$palabrasclaves,$fk_institucion,$calificacion,$ubicacion,$pagina,$fk_publicado,$observacion,$cota,$sede,$escuela){
            
            if(empty($fk_datotesis))return;

            $SQL ="INSERT INTO tbl_tesis(
                          fk_datotesis,fk_periodo,fecha,fk_tiporecurso,resumen,serialrecurso,palabrasclaves,fk_institucion,calificacion,ubicacion,pagina,fk_publicado,observacion,cota,fk_sede,fk_escuela)
                  VALUES ({$fk_datotesis}
                            ,{$periodo}
                            ,{$fecha}
                            ,{$fk_tiporecurso}
                            ,'{$resumen}'
                            ,'{$serialrecurso}'
                            ,'{$palabrasclaves}'
                            ,{$fk_institucion}
                            ,{$calificacion}
                            ,'{$ubicacion}'
                            ,{$pagina}
                            ,{$fk_publicado}
                            ,'{$observacion}'
                            ,'{$cota}'
                            ,{$sede}
                            ,{$escuela}
                            );";
            
            $results = $this->_db->query($SQL);
            return  $results->fetchAll();
      } 
      

  public function getPeriodoActual(){
     
    $SQL ="select distinct pk_periodo
            from tbl_periodos
            where current_date between fechainicio and fechafin";

    return $this->_db->fetchOne($SQL);
      
  }  


  public function addTutor($tesis,$usuariogrupo,$periodo){
        
        if(empty($tesis))return;
        if(empty($usuariogrupo))return;

        $SQL ="INSERT INTO tbl_tutorestesis(
                        fk_periodo, fk_usuariogrupo, fk_datotesis, fk_estado, fk_tipo,renuncia)
                VALUES ({$periodo}, {$usuariogrupo}, {$tesis}, 
                        (select distinct a.pk_atributo
                            from tbl_atributostipos     at
                            join tbl_atributos      a   on  a.fk_atributotipo = at.pk_atributotipo
                            where at.nombre ilike '%Estado Tutores%'
                            and a.valor ilike 'Aprobado'),".$this->getTutorInterno().",false);";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }  

    public function getTutorInterno(){
      
      $SQL ="select distinct a.pk_atributo
              from tbl_atributos      a 
              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
              where at.nombre ilike '%Tipo Tutor%'
              and a.valor ilike 'Interno%'";
               
      return $this->_db->fetchOne($SQL);      
    }  

    public function addMencionTesis($cod){
        if(empty($cod))return;

        $SQL = "INSERT into tbl_mencionestesis (fk_datotesis,fk_mencion) values ({$cod},".$this->getAtributoMencionNinguna().")";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }

    public function getAtributoMencionNinguna(){
      
      $SQL ="select distinct a.pk_atributo
            from tbl_atributos      a 
            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
            where at.nombre ilike 'Menciones Tesis'
            and a.valor ilike 'Ninguna'";
                 
      return $this->_db->fetchOne($SQL);      
    }


    public function getAtributoAgregarTesisBiblioteca(){
      
      $SQL ="select distinct a.pk_atributo
            from tbl_atributos      a 
            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
            where at.pk_atributotipo = 5
            and a.valor ilike 'Agregar Tesis Biblioteca'";
               
      return $this->_db->fetchOne($SQL);      
    }       
}
?>
