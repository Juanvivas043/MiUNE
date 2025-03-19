<?php

class Models_DbTable_Repositorio extends Zend_Db_Table
{

    protected $_schema = 'produccion';
    protected $_name = 'tbl_datostesis';
    protected $_primary = 'pk_datotesis';
    protected $_sequence = false;
    private $searchParams = array('dt.titulo', 't.palabrasclave', 't.cota', 'autor.pk_usuario::text', 'autor');

    public function init()
    {
        $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }

    public function setSearch($searchData) {   

        $this->searchData = $searchData;
        
    }

    public function getRow($id){

    $SQL =  "SELECT *
        FROM (
        SELECT dt.pk_datotesis,
                        t.cota,
                        dt.titulo AS titulo,
                        t.palabrasclave,
                        COALESCE(escuela.valor, 'N/A') AS escuela,
                        COALESCE(linea.valor, 'N/A') AS linea,
                        COALESCE(tema.valor, 'N/A') AS tema,
                        COALESCE(autor.pk_usuario::text, 'N/A') AS cedula,
                        CASE
                            WHEN autor.nombre IS NULL OR autor.apellido IS NULL THEN 'N/A'
                            ELSE concat(autor.nombre, ' ', autor.apellido)
                        END AS autor,
                        CASE
                            WHEN tutor.pk_usuario::text = '0' THEN 'N/A'
                            WHEN tutor.pk_usuario::text IS NULL THEN 'N/A'
                            ELSE tutor.pk_usuario::text
                        END AS tutor,
                        CASE
                            WHEN tutor.nombre IS NULL OR tutor.apellido IS NULL THEN 'N/A'
                            ELSE concat(tutor.nombre, ' ', tutor.apellido)
                        END AS nombretutor,
                        COALESCE(t.fk_periodo::text, 'N/A') AS periodo,
                        publicado.valor as publicado,
                        ROW_NUMBER() OVER (ORDER BY dt.pk_datotesis) AS id
                        FROM tbl_datostesis dt
                              LEFT JOIN tbl_autorestesis at ON dt.pk_datotesis = at.fk_datotesis
                            LEFT JOIN tbl_usuariosgrupos ug ON at.fk_usuariogrupo = ug.pk_usuariogrupo
                            LEFT JOIN tbl_usuarios autor ON ug.fk_usuario = autor.pk_usuario
                            --Info del tutor
                            LEFT JOIN tbl_tutorestesis tt ON dt.pk_datotesis = tt.fk_datotesis
                            LEFT JOIN tbl_usuariosgrupos ug2 ON tt.fk_usuariogrupo = ug2.pk_usuariogrupo
                            LEFT JOIN tbl_usuarios tutor ON ug2.fk_usuario = tutor.pk_usuario
                            --Linea y tema de Investigacion
                            LEFT JOIN tbl_lineastemastesis ltt ON dt.fk_lineatematesis = ltt.pk_lineatematesis
                            LEFT JOIN tbl_atributos linea ON ltt.fk_lineainvestigacion = linea.pk_atributo
                            LEFT JOIN tbl_atributos tema ON ltt.fk_tema = tema.pk_atributo
                            --Info de la tesis cota,observacion, nota, escuela, etc.
                            LEFT JOIN tbl_tesis t ON dt.pk_datotesis = t.fk_datotesis
                            LEFT JOIN tbl_atributos escuela ON t.fk_escuela = escuela.pk_atributo
                            JOIN tbl_atributos publicado ON dt.fk_publicado = publicado.pk_atributo
                        LEFT JOIN tbl_atributos estado ON dt.fk_publicado = estado.pk_atributo
                        WHERE dt.fk_estado = 19962 --Filtra que se me muestren tesis aprobadas
                        ORDER BY dt.pk_datotesis ASC
            ) as tesis
               WHERE tesis.id = $id";
      
      $results = $this->_db->query($SQL);

      return (array)$results->fetch();

    }

    public function addRow($data)
    {
        $data = array_filter($data);
        $affected = $this->insert($data);

        return $affected;
    }

    public function deleteRow($id)
    {
        $affected = $this->delete('pk_recurso' . ' = ' . (int)$id);

        return $affected;
    }

    public function updateRow($id, $data)
    {
        $data = array_filter($data);
        $affected = $this->update($data, 'pk_recurso'. ' = ' . (int)$id);

        return $affected;
    }

    public function getRecursosAll($itemPerPage, $pageNumber){
        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

        $SQL =  "SELECT
                    t.cota,
                    dt.titulo AS titulo,
                    t.palabrasclave,
                    COALESCE(escuela.valor, 'N/A') AS escuela,
                    COALESCE(linea.valor, 'N/A') AS linea,
                    COALESCE(tema.valor, 'N/A') AS tema,
                    COALESCE(autor.pk_usuario::text, 'N/A') AS cedula,
                    CASE
                        WHEN autor.nombre IS NULL OR autor.apellido IS NULL THEN 'N/A'
                        ELSE concat(autor.nombre, ' ', autor.apellido)
                    END AS autor,
                    CASE
                        WHEN tutor.pk_usuario::text = '0' THEN 'N/A'
                        WHEN tutor.pk_usuario::text IS NULL THEN 'N/A'
                        ELSE tutor.pk_usuario::text
                    END AS cedulatutor,
                    CASE
                        WHEN tutor.nombre IS NULL OR tutor.apellido IS NULL THEN 'N/A'
                        ELSE concat(tutor.nombre, ' ', tutor.apellido)
                    END AS tutor,
                    COALESCE(t.fk_periodo::text, 'N/A') AS periodo,
                    publicado.valor as publicado,
                    ROW_NUMBER() OVER (ORDER BY dt.pk_datotesis) AS id
                        FROM tbl_datostesis dt
                        -- Info del autor
                        LEFT JOIN tbl_autorestesis at ON dt.pk_datotesis = at.fk_datotesis
                        LEFT JOIN tbl_usuariosgrupos ug ON at.fk_usuariogrupo = ug.pk_usuariogrupo
                        LEFT JOIN tbl_usuarios autor ON ug.fk_usuario = autor.pk_usuario
                        --Info del tutor
                        LEFT JOIN tbl_tutorestesis tt ON dt.pk_datotesis = tt.fk_datotesis
                        LEFT JOIN tbl_usuariosgrupos ug2 ON tt.fk_usuariogrupo = ug2.pk_usuariogrupo
                        LEFT JOIN tbl_usuarios tutor ON ug2.fk_usuario = tutor.pk_usuario
                        --Linea y tema de Investigacion
                        LEFT JOIN tbl_lineastemastesis ltt ON dt.fk_lineatematesis = ltt.pk_lineatematesis
                        LEFT JOIN tbl_atributos linea ON ltt.fk_lineainvestigacion = linea.pk_atributo
                        LEFT JOIN tbl_atributos tema ON ltt.fk_tema = tema.pk_atributo
                        --Info de la tesis cota,observacion, nota, escuela, etc.
                        LEFT JOIN tbl_tesis t ON dt.pk_datotesis = t.fk_datotesis
                        LEFT JOIN tbl_atributos escuela ON t.fk_escuela = escuela.pk_atributo
                        JOIN tbl_atributos publicado ON dt.fk_publicado = publicado.pk_atributo
                        WHERE dt.fk_estado = 19962 --Filtra que se me muestren tesis aprobadas
                        {$whereSearch}
                        ORDER BY dt.pk_datotesis ASC
                        LIMIT $itemPerPage OFFSET $pageNumber";

        $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
        }

    public function getRecursos($itemPerPage, $pageNumber, $periodo, $tiporecurso, $escuela, $estado){

        $pageNumber = ($pageNumber - 1) * $itemPerPage;
        $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($this->searchParams, $this->searchData);

            $SQL =  "SELECT re.pk_recurso, re.titulo, re.cota, autor.pk_usuario AS cedula, LTRIM(TO_CHAR(autor.pk_usuario, '99\".\"999\".\"999')::varchar, '0. ') as ci,
                    concat(autor.nombre,' ', autor.apellido) AS autor, tr.valor AS tiporecurso, concat(tutor.nombre, ' ', tutor.apellido) as tutor ,
                    c.valor AS escuela, estado.valor as estado, re.palabrasclave AS palabrasclave
                            FROM tbl_recursos re
                            JOIN tbl_usuariosgrupos ug ON re.fk_autor = ug.pk_usuariogrupo
                            JOIN tbl_usuarios autor ON ug.fk_usuario = autor.pk_usuario
                            JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                            JOIN tbl_atributos c ON re.fk_escuela = c.pk_atributo 
                            JOIN tbl_atributos estado ON re.fk_estado = estado.pk_atributo
                            JOIN tbl_usuariosgrupos ug2 ON re.fk_tutor = ug2.pk_usuariogrupo
                            JOIN tbl_usuarios tutor ON ug2.fk_usuario = tutor.pk_usuario
                            WHERE re.fk_periodo = $periodo AND tr.pk_atributo = $tiporecurso AND c.pk_atributo = $escuela AND re.fk_estado = $estado
                        {$whereSearch}
                        LIMIT $itemPerPage OFFSET $pageNumber";

            $results = $this->_db->query($SQL);

        return (array)$results->fetchAll();
        }

    public function getCountRecursosAll(){

            $SQL =  "SELECT COUNT(*)
                        FROM tbl_datostesis dt
                        -- Info del autor
                        LEFT JOIN tbl_autorestesis at ON dt.pk_datotesis = at.fk_datotesis
                        LEFT JOIN tbl_usuariosgrupos ug ON at.fk_usuariogrupo = ug.pk_usuariogrupo
                        LEFT JOIN tbl_usuarios autor ON ug.fk_usuario = autor.pk_usuario
                        --Info del tutor
                        LEFT JOIN tbl_tutorestesis tt ON dt.pk_datotesis = tt.fk_datotesis
                        LEFT JOIN tbl_usuariosgrupos ug2 ON tt.fk_usuariogrupo = ug2.pk_usuariogrupo
                        LEFT JOIN tbl_usuarios tutor ON ug2.fk_usuario = tutor.pk_usuario
                        --Linea y tema de Investigacion
                        LEFT JOIN tbl_lineastemastesis ltt ON dt.fk_lineatematesis = ltt.pk_lineatematesis
                        LEFT JOIN tbl_atributos linea ON ltt.fk_lineainvestigacion = linea.pk_atributo
                        LEFT JOIN tbl_atributos tema ON ltt.fk_tema = tema.pk_atributo
                        --Info de la tesis cota,observacion, nota, escuela, etc.
                        LEFT JOIN tbl_tesis t ON dt.pk_datotesis = t.fk_datotesis
                        LEFT JOIN tbl_atributos escuela ON t.fk_escuela = escuela.pk_atributo
                        JOIN tbl_atributos publicado ON dt.fk_publicado = publicado.pk_atributo
                        WHERE dt.fk_estado = 19962 --Filtra que se me muestren tesis aprobadas";
    
            $results = $this->_db->query($SQL);
            

        return $results->fetchAll();
    }

    public function getCountRecursos($periodo, $tiporecurso, $escuela, $estado){

        $SQL =  "SELECT COUNT(distinct re.pk_recurso)
                    FROM tbl_recursos re
                    JOIN tbl_usuariosgrupos ug ON re.fk_autor = ug.pk_usuariogrupo
                    JOIN tbl_usuarios autor ON ug.fk_usuario = autor.pk_usuario
                    JOIN tbl_atributos tr ON re.fk_tiporecurso = tr.pk_atributo
                    JOIN tbl_atributos c ON re.fk_escuela = c.pk_atributo 
                    JOIN tbl_atributos estado ON re.fk_estado = estado.pk_atributo
                    JOIN tbl_usuariosgrupos ug2 ON re.fk_tutor = ug2.pk_usuariogrupo
                    JOIN tbl_usuarios tutor ON ug2.fk_usuario = tutor.pk_usuario
                    WHERE re.fk_periodo = $periodo AND tr.pk_atributo = $tiporecurso AND c.pk_atributo = $escuela AND re.fk_estado = $estado";

        $results = $this->_db->query($SQL);
        

        return $results->fetchAll();
    }

    public function getCedula($pk_usuario){

        $SQL =  "SELECT pk_usuario 
                    FROM tbl_recursos re 
                    JOIN tbl_usuariosgrupos ug ON re.fk_autor = ug.pk_usuariogrupo
                    JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                    WHERE re.pk_recurso = $pk_usuario";
            
        return $this->_db->fetchOne($SQL);
    }

    public function getCedulaTutor($pk_usuario){

        $SQL =  "SELECT pk_usuario 
                    FROM tbl_recursos re 
                    JOIN tbl_usuariosgrupos ug ON re.fk_tutor = ug.pk_usuariogrupo
                    JOIN tbl_usuarios u ON ug.fk_usuario = u.pk_usuario
                    WHERE re.pk_recurso = $pk_usuario";
            
        return $this->_db->fetchOne($SQL);
    }

}