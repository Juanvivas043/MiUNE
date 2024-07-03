<?php
class Models_DbTable_Tesis extends Zend_Db_Table {
    
    protected $_schema = 'produccion';
    protected $_name = 'tbl_datostesis';
    protected $_primary = 'pk_datotesis';

//IMPORTANTE: por ningun motivo hacer la insercion en cualquiera de las tablas implicadas con el modulo calculando con el ultimo pk, ya que para eso existe una secuencia
    public function init(){
      $this->AuthSpace = new Zend_Session_Namespace('Zend_Auth');
      $this->SwapBytes_Array = new SwapBytes_Array();
      $this->SwapBytes_Crud_Db_Table = new SwapBytes_Crud_Db_Table();
    }


    public function getEvaluadorRoles(){
      
      $SQL = "select a.pk_atributo, a.valor as rol  
              from tbl_atributos  a
              join tbl_Atributostipos at  on  at.pk_Atributotipo = a.fk_atributotipo
              where at.nombre ilike 'Rol Evaluadores'
              order by a.pk_atributo asc";

      $results = $this->_db->query($SQL);

      return (array) $results->fetchAll();                
    }


    public function getPerfilTutorTesis(){
      
      $SQL ="select distinct pk_atributo from tbl_atributos where valor ilike '%Tutor Tesis%'";
               
      return $this->_db->fetchOne($SQL);       
    }


     public function getAtributoLinea(){
       
       $SQL = "select distinct pk_atributotipo
                from tbl_atributostipos
                where nombre ilike  '%Linea de investigacion%'";


        return $this->_db->fetchOne($SQL);  
        
     }

     public function getAtributoTema(){
       
       $SQL = "select distinct pk_atributotipo
                from tbl_atributostipos
                where nombre ilike  '%Tema de investigacion%'";


        return $this->_db->fetchOne($SQL);  
        
     }

    public function getAtributoMateriaReprobada(){
      
      $SQL ="select distinct pk_atributo
            from vw_materiasestados
            where valor ilike 'Reprobada'";
               
      return $this->_db->fetchOne($SQL);      
    }

    public function getAtributoMateriaCursada(){
      
      $SQL ="select distinct pk_atributo
            from vw_materiasestados
            where valor ilike 'Cursada'";
               
      return $this->_db->fetchOne($SQL);      
    }


    public function getAtributoMencionNinguna(){
      
      $SQL ="select distinct a.pk_atributo
            from tbl_atributos      a 
            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
            where at.nombre ilike 'Menciones Tesis'
            and a.valor ilike 'Ninguna'";
               
      return $this->_db->fetchOne($SQL);      
    }

    public function getAtributoEvaluadorSecundario(){
      
      $SQL ="select distinct a.pk_atributo
            from tbl_atributos      a 
            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
            where at.nombre ilike 'Tipo Evaluadores'
            and a.valor ilike 'Secundario'";
               
      return $this->_db->fetchOne($SQL);      
    }  

    public function getAtributoEvaluadorPrincipal(){
      
      $SQL ="select distinct a.pk_atributo
            from tbl_atributos      a 
            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
            where at.nombre ilike 'Tipo Evaluadores'
            and a.valor ilike 'Principal'";
               
      return $this->_db->fetchOne($SQL);      
    }      


    public function getEstadoTutorAprobado(){
      
      $SQL ="select distinct a.pk_atributo
              from tbl_atributos      a 
              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
              where at.nombre ilike '%Estado Tutores%'
              and a.valor ilike 'Aprobado%'";
               
      return $this->_db->fetchOne($SQL);      
    }

    public function getTutorInterno(){
      
      $SQL ="select distinct a.pk_atributo
              from tbl_atributos      a 
              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
              where at.nombre ilike '%Tipo Tutor%'
              and a.valor ilike 'Interno%'";
               
      return $this->_db->fetchOne($SQL);      
    }      


    public function getEstadoTesisAprobado(){
      
      $SQL ="SELECT distinct a.pk_atributo
            from tbl_atributos      a 
            join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
            where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
            and a.valor ilike 'Aprobado'";
               
      return $this->_db->fetchOne($SQL);      
    }  


    public function getAtributotipoTutores(){
      
      $SQL ="select distinct pk_atributotipo
              from tbl_atributostipos
              where nombre ilike '%Estado Tutores%'";
               
      return $this->_db->fetchOne($SQL);      
    } 

    public function getAtributotipoCondicionTutor(){
      
      $SQL ="select distinct pk_atributotipo
              from tbl_atributostipos
              where nombre ilike '%Condicion Tutor%'";
               
      return $this->_db->fetchOne($SQL);      
    }

    public function getAtributotipoEvaluadores(){
      
      $SQL ="select distinct pk_atributotipo
              from tbl_atributostipos
              where nombre ilike '%Estado Evaluadores%'";
               
      return $this->_db->fetchOne($SQL);      
    }  


    public function getAtributotipoDefensa(){
      
      $SQL ="select distinct pk_atributotipo
            from tbl_atributostipos
            where nombre ilike '%Estado Defensa%'";
               
      return $this->_db->fetchOne($SQL);      
    }

    public function getCondicionTutorPuede(){
      $SQL ="select distinct a.pk_atributo
              from tbl_atributostipos     at 
              join tbl_atributos          a       on        a.fk_atributotipo = at.pk_atributotipo
              where at.nombre ilike '%Condicion Tutor%'
              and a.valor ilike 'Normal'";
               
      return $this->_db->fetchOne($SQL); 
    }

    public function getCondicionTutorNoPuede(){
      $SQL ="select distinct a.pk_atributo
              from tbl_atributostipos     at 
              join tbl_atributos          a       on        a.fk_atributotipo = at.pk_atributotipo
              where at.nombre ilike '%Condicion Tutor%'
              and a.valor ilike 'Caso Especial'";
               
      return $this->_db->fetchOne($SQL); 
    }

    public function getEstadoEvaluadorAsignado(){
      
      $SQL ="select distinct a.pk_atributo
              from tbl_atributostipos     at 
              join tbl_atributos          a       on        a.fk_atributotipo = at.pk_atributotipo
              where at.nombre ilike '%Estado Evaluadores%'
              and a.valor ilike 'Asignado'";
               
      return $this->_db->fetchOne($SQL);      
    }                     



    public function getTesisInscrito($cedula,$pensum){
      

       if(empty($cedula))return;

       if(!empty($pensum)){
           if($pensum == 8){
              $calificacion = 15;
           }else{
              $calificacion = 10;
           } 
       }else{
          $calificacion = 10;
       }


      $SQL ="select count(sqt.pk_recordacademico)
            from(
                    SELECT ra.*, ug.fk_usuario
                    FROM tbl_inscripciones       ins
                    JOIN tbl_recordsacademicos   ra  ON ra.fk_inscripcion  =     ins.pk_inscripcion
                    JOIN tbl_asignaturas         asi ON asi.pk_asignatura  =     ra.fk_asignatura
                    JOIN tbl_pensums             pe  ON pe.pk_pensum       =     asi.fk_pensum
                    JOIN tbl_usuariosgrupos      ug  ON ug.pk_usuariogrupo =     ins.fk_usuariogrupo
                    WHERE ug.fk_usuario = {$cedula}
                    and asi.fk_materia IN (
                            519, --diseño de tesis
                            10621, --innovacion e investigacion
                            9719, --seminario de trabajo de grado
                            830, --tesis de grado i
                            9723, --trabajo de grado I
                            834, --tesis de grado ii
                            9724 --trabajo de grado II                            
                    )
                    -- and ug.fk_usuario not in (
                    --         SELECT distinct ug.fk_usuario
                    --         FROM tbl_inscripciones       ins
                    --         JOIN tbl_recordsacademicos   ra  ON ra.fk_inscripcion  =     ins.pk_inscripcion
                    --         JOIN tbl_asignaturas         asi ON asi.pk_asignatura  =     ra.fk_asignatura
                    --         JOIN tbl_pensums             pe  ON pe.pk_pensum       =     asi.fk_pensum
                    --         JOIN tbl_usuariosgrupos      ug  ON ug.pk_usuariogrupo =     ins.fk_usuariogrupo
                    --         WHERE asi.fk_materia IN (
                    --                834, --tesis de grado ii
                    --                9724 --trabajo de grado II
                    --         )
                    --         and (ra.fk_atributo = 864
                    --         or (ra.fk_atributo = 862 and ra.calificacion >= 10) )
                    -- )
            ) as sqt
            where (sqt.fk_atributo = 864
                  or (sqt.fk_atributo = 862 and sqt.calificacion >= ".$calificacion.") )";
               
      return $this->_db->fetchOne($SQL);
      
  }

    public function getTG2Inscrito($cedula){
      

       if(empty($cedula))return;
        
      $SQL ="select count(sqt.pk_recordacademico)
            from(
                    SELECT ra.pk_recordacademico, ug.fk_usuario
                    FROM tbl_inscripciones       ins
                    JOIN tbl_recordsacademicos   ra  ON ra.fk_inscripcion  =     ins.pk_inscripcion
                    JOIN tbl_asignaturas         asi ON asi.pk_asignatura  =     ra.fk_asignatura
                    JOIN tbl_pensums             pe  ON pe.pk_pensum       =     asi.fk_pensum
                    JOIN tbl_usuariosgrupos      ug  ON ug.pk_usuariogrupo =     ins.fk_usuariogrupo
                    WHERE ug.fk_usuario = {$cedula}
                    AND asi.fk_materia IN (
                                   834, --tesis de grado ii
                                   9724 --trabajo de grado II
                            )
                    AND (ra.fk_atributo = 864)
            ) as sqt
            ";
               
      return $this->_db->fetchOne($SQL);
      
  }

  public function getTG2Aprobado($periodo,$escuela){
      

        
      $SQL ="select count(sqt.pk_recordacademico)
            from(
                    SELECT ra.pk_recordacademico, ug.fk_usuario
                    FROM tbl_inscripciones       ins
                    JOIN tbl_recordsacademicos   ra  ON ra.fk_inscripcion  =     ins.pk_inscripcion
                    JOIN tbl_asignaturas         asi ON asi.pk_asignatura  =     ra.fk_asignatura
                    JOIN tbl_pensums             pe  ON pe.pk_pensum       =     asi.fk_pensum
                    JOIN tbl_usuariosgrupos      ug  ON ug.pk_usuariogrupo =     ins.fk_usuariogrupo
                    JOIN tbl_autorestesis        at  ON at.fk_usuariogrupo =     ug.pk_usuariogrupo      and at.renuncia = false
                    JOIN tbl_datostesis           dt ON dt.pk_datotesis    =     at.fk_datotesis
                    WHERE ins.fk_periodo = {$periodo}
                    AND ins.fk_atributo = {$escuela}
                    AND asi.fk_materia IN (
                                   834, --tesis de grado ii
                                   9724 --trabajo de grado II
                            )
                    AND (ra.fk_atributo = 862 or ra.fk_atributo = 1699)
            ) as sqt
            ";
               
      return $this->_db->fetchOne($SQL);
      
  }

  public function getUsuarioTesistaDatos($cedula){

    if(empty($cedula))return;
      
      $SQL = "select distinct u.pk_usuario,
                      u.primer_nombre,
                      u.segundo_nombre,
                      u.primer_apellido,
                      u.segundo_apellido
              from tbl_usuarios     u
              join tbl_usuariosgrupos   ug    on  ug.fk_usuario = u.pk_usuario
              where u.pk_usuario = {$cedula}
              and ug.fk_grupo = 855";

      $results = $this->_db->query($SQL);

      return (array) $results->fetchAll();      

  } 


  public function getUsuarioTesistaCount($cedula){

    if(empty($cedula))return;
      
      $SQL = "select count(u.pk_usuario)
              from tbl_usuarios     u
              join tbl_usuariosgrupos   ug    on  ug.fk_usuario = u.pk_usuario
              where u.pk_usuario = {$cedula}
              and ug.fk_grupo = 855";

      return $this->_db->fetchOne($SQL);      

  } 


  public function getUsuarioTutorDatos($cedula){

    if(empty($cedula))return;
      
      $SQL = "select distinct u.pk_usuario,
                      u.primer_nombre,
                      u.segundo_nombre,
                      u.primer_apellido,
                      u.segundo_apellido, ug.fk_grupo
              from tbl_usuarios     u
              join tbl_usuariosgrupos   ug    on  ug.fk_usuario = u.pk_usuario
              where u.pk_usuario = {$cedula}
              --and ug.fk_grupo in (854,1745,".$this->getPerfilTutorTesis().")
              order by ug.fk_grupo desc limit 1";

      $results = $this->_db->query($SQL);

      return (array) $results->fetchAll();      

  }


  public function getTutorTituloAcademico($tutor,$id){

      if(!empty($id)){
        $filtro_datotesis = " and tt.fk_datotesis = ".$id;
      }else{
        $filtro_datotesis = " ";
      }
      
      $SQL = "select distinct titulo_academico
              from tbl_tutorestesis     tt
              join tbl_usuariosgrupos   ug    on  ug.pk_usuariogrupo  = tt.fk_usuariogrupo
              where ug.fk_usuario = {$tutor}
               ".$filtro_datotesis;
              
      $results = $this->_db->query($SQL);

      return (array) $results->fetchAll();      

  }

  public function getTutorTituloAcademicoByPk($id){
 
      $SQL = "select distinct titulo_academico
              from tbl_tutorestesis     tt
              where tt.pk_tutortesis = {$id}";
              
      $results = $this->_db->query($SQL);

      return (array) $results->fetchAll();      

  }


    public function TipoEvaluadores(){

      
      $SQL = "select distinct a.pk_atributo,a.valor as tipo
              from tbl_atributostipos     at 
              join tbl_atributos          a       on        a.fk_atributotipo = at.pk_atributotipo
              where at.nombre ilike '%Tipo Evaluadores%'";

      $results = $this->_db->query($SQL);

      return (array) $results->fetchAll();      

  } 



     public function getUsuarioTutorCount($cedula){

    if(empty($cedula))return;
      
      $SQL = "select sqt.count
              from(
                  select  count(u.pk_usuario) as cuenta,ug.fk_grupo
                  from tbl_usuarios     u
                  join tbl_usuariosgrupos   ug    on  ug.fk_usuario = u.pk_usuario
                  where u.pk_usuario = {$cedula}
                  --and ug.fk_grupo in (854,1745,".$this->getPerfilTutorTesis().")
                  group by 2
                  order by ug.fk_grupo desc limit 1
              ) as sqt";

      return $this->_db->fetchOne($SQL);      

  }


  public function getTiempoGraduado($cedula){

      if(empty($cedula))return;
      
      $SQL = "select distinct  ((current_date - sqt.fechafin) / 365) as tiempo_graduado
              from(
              select distinct  pe.fechafin, pe.pk_periodo
              from tbl_inscripciones   i
              join tbl_usuariosgrupos   ug    on    ug.pk_usuariogrupo = i.fk_usuariogrupo
              join tbl_periodos       pe    on    pe.pk_periodo = i.fk_periodo
              where ug.fk_usuario = {$cedula}
              order by 1 desc limit 1
              )as sqt";


      return $this->_db->fetchOne($SQL);                
  }  

  
  public function getDatosEstudiante($usuario){
        
      $SQL ="select distinct ug.fk_usuario as usuario, i.fk_atributo as escuela, pe.codigopropietario as pensum, i.fk_periodo as periodo
            from tbl_pensums		pe
            join tbl_inscripciones		i	on	i.fk_pensum		=	pe.pk_pensum
            join tbl_usuariosgrupos		ug	on	ug.pk_usuariogrupo	=	i.fk_usuariogrupo
            where ug.fk_usuario = {$usuario}
            order by 4 desc limit 1";
            
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
  }
  
  
  
  public function getUCA($usuario, $escuela, $pensum, $periodo){
        
      $SQL ="select fn_xrxx_estudiante_calcular_ucac_escuela_pensum_periodo({$usuario},{$escuela},{$pensum},{$periodo}) as UCA";
            
        return $this->_db->fetchOne($SQL);
  }
  
 
  
    public function getTesisNombre($cedula,$periodo = null,$cod = null){
        
              
       if(empty($cedula))return;
       
       if($periodo != null){
          $filtro_periodo = " and tt.fk_periodo = {$periodo} ";
       }else{
           $filtro_periodo = "";
       }

       if($cod != null){
          $filtro_cod = " and dt.pk_datotesis = {$cod} ";
       }else{
           $filtro_cod = "";
       }
       
      
      $SQL ="select distinct dt.pk_datotesis,dt.titulo, ug_t.fk_usuario,ug_t.pk_usuariogrupo, atri.valor,tt.pk_tutortesis,atri_tu.valor as valortutor,(u_t.apellido ||', '|| u_t.nombre) as nombre,(u_a.apellido ||', '|| u_a.nombre) as nombreautor,at.fk_periodo, tt.fk_periodo as periodotutor
            from tbl_datostesis                 dt
            join tbl_autorestesis               at      on  at.fk_datotesis         =   dt.pk_datotesis
            join tbl_usuariosgrupos             ug_a    on  ug_a.pk_usuariogrupo    =   at.fk_usuariogrupo
            join tbl_usuarios                   u_a     on  u_a.pk_usuario          =   ug_a.fk_usuario
            join tbl_atributos                  atri    on  atri.pk_atributo        =   dt.fk_estado
            left outer join tbl_tutorestesis    tt      on  tt.fk_datotesis         =   dt.pk_datotesis and tt.renuncia = false ".$filtro_periodo."
            left outer join tbl_atributos       atri_tu on  atri_tu.pk_atributo     =   tt.fk_estado
            left outer join tbl_usuariosgrupos  ug_t    on  ug_t.pk_usuariogrupo    =   tt.fk_usuariogrupo
            left outer join tbl_usuarios        u_t     on  u_t.pk_usuario          =   ug_t.fk_usuario            
            where ug_a.fk_usuario = {$cedula}
            and at.renuncia = false " . $filtro_cod . " order by 1 desc";
            
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
      
  }

  public function getTesisNombre_v2($cedula,$periodo = null,$cod = null){
        
              
       if(empty($cedula))return;
       
       if($periodo != null){
          $filtro_periodo = " and tt.fk_periodo = {$periodo} ";
       }else{
           $filtro_periodo = "";
       }

       if($cod != null){
          $filtro_cod = " and dt.pk_datotesis = {$cod} ";
       }else{
           $filtro_cod = "";
       }
       
      
      $SQL ="select distinct dt.pk_datotesis,dt.titulo, ug_t.fk_usuario,ug_t.pk_usuariogrupo, atri.valor,tt.pk_tutortesis,atri_tu.valor as valortutor,(u_t.apellido ||', '|| u_t.nombre) as nombre,(u_a.apellido ||', '|| u_a.nombre) as nombreautor,at.fk_periodo, tt.fk_periodo as periodotutor
            from tbl_datostesis                 dt
            join tbl_autorestesis               at      on  at.fk_datotesis         =   dt.pk_datotesis
            join tbl_usuariosgrupos             ug_a    on  ug_a.pk_usuariogrupo    =   at.fk_usuariogrupo
            join tbl_usuarios                   u_a     on  u_a.pk_usuario          =   ug_a.fk_usuario
            join tbl_atributos                  atri    on  atri.pk_atributo        =   dt.fk_estado
            left outer join tbl_tutorestesis    tt      on  tt.fk_datotesis         =   dt.pk_datotesis ".$filtro_periodo."
            left outer join tbl_atributos       atri_tu on  atri_tu.pk_atributo     =   tt.fk_estado
            left outer join tbl_usuariosgrupos  ug_t    on  ug_t.pk_usuariogrupo    =   tt.fk_usuariogrupo
            left outer join tbl_usuarios        u_t     on  u_t.pk_usuario          =   ug_t.fk_usuario            
            where ug_a.fk_usuario = {$cedula}
            and at.renuncia = false " . $filtro_cod . " order by 1 desc";
            
        $results = $this->_db->query($SQL);

        return (array) $results->fetchAll();
      
  }

  
      public function getTesisExiste($titulo){
        
              
       if(empty($titulo))return;
      
      $SQL ="select count(distinct dt.titulo)
            from tbl_datostesis     dt
            where dt.titulo ilike  '{$titulo}'";

            
        return $this->_db->fetchOne($SQL);
      
  }
  
  
      public function getTesisEstado($id){
        
              
       if(empty($id))return;
      
      $SQL ="select distinct fk_estado, a.valor as estado
            from tbl_datostesis     dt
            join tbl_atributos      a   on  a.pk_atributo = dt.fk_estado
            where pk_datotesis = {$id};";

            
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  
  
      public function getTesisByTitulo($titulo){
        
              
       if(empty($titulo))return;
      
      $SQL ="select distinct dt.pk_datotesis
            from tbl_datostesis     dt
            where dt.titulo = '{$titulo}'";

            
        return $this->_db->fetchOne($SQL);
      
  } 

  
      public function getTesisCambios($id){
         
        if(empty($id))return;

        $SQL ="select distinct pt.pk_pasotesis, 
                      pt.descripcion    as descripcion_tesis, 
                      pt.observaciones  as observacion_tesis
              from tbl_datostesis                 dt
              left outer join tbl_pasostesis      pt    on  pt.fk_datotesis         =  dt.pk_datotesis
              where dt.pk_datotesis = {$id}";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }


  public function getTutorCambios($id){
         
        if(empty($id))return;

        $SQL ="select  
                      (case 
                        when (tt.observaciones is not null) and (tt.observaciones <> '')  then 'observacion tutor'
                        else null 
                      end) as descripcion_tutor,
                      tt.observaciones    as observacion_tutor
              from tbl_datostesis                 dt
              left outer join tbl_tutorestesis    tt        on  tt.fk_datotesis         = dt.pk_datotesis
              where dt.pk_datotesis = {$id}
              and tt.renuncia = false";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  

     public function getTutoresPeriodo($periodo,$sede,$escuela,$estado = null,$busqueda, $cod = null){ 

      if($estado != null){
        $filtro_estado = " and tt.fk_estado = {$estado} "; 
      }else{
        $filtro_estado = " ";
      }

      if($cod != null){
          $filtro_cod = " and dt.pk_datotesis = {$cod} "; 
       }else{
           $filtro_cod = "";
       }

      if(!empty($busqueda)){$filtro_busqueda = " and u.pk_usuario = {$busqueda}";}    

      $SQL = "select distinct tt.pk_tutortesis,
                  (select btrim(array_agg(u_sub.primer_apellido ||', '|| u_sub.primer_nombre)::varchar,'{}.')
                  from tbl_datostesis     dt_sub
                  join tbl_autorestesis   at_sub      on      at_sub.fk_datotesis   = dt.pk_datotesis     and at_sub.renuncia = false
                  join tbl_usuariosgrupos   ug_sub      on      at_sub.fk_usuariogrupo  = ug_sub.pk_usuariogrupo
                  join tbl_usuarios       u_sub       on      u_sub.pk_usuario    = ug_sub.fk_usuario
                  where dt_sub.pk_datotesis = dt.pk_datotesis) as tesistas,
                  dt.titulo,
                  dt.pk_datotesis,
                  u.pk_usuario,
                  u.nombre,
                  u.apellido,
                  (dt.pk_datotesis||', '||u.pk_usuario) as pk,
                  (u.nombre || ', ' || u.apellido) as nombretutor
              from tbl_datostesis       dt 
              join tbl_tutorestesis     tt    on    tt.fk_datotesis   =   dt.pk_datotesis     and tt.renuncia = false
              join tbl_tesis            te    on    te.fk_datotesis   =   dt.pk_datotesis
              join tbl_usuariosgrupos   ug    on    ug.pk_usuariogrupo  =   tt.fk_usuariogrupo
              join tbl_usuarios         u     on    u.pk_usuario    =   ug.fk_usuario
              where tt.renuncia = false 
              and dt.fk_estado = (SELECT distinct a.pk_atributo
                              from tbl_atributos      a 
                              join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                              where at.pk_atributotipo = (select pk_atributotipo from tbl_atributostipos where nombre ilike 'Estado Tesis' )
                              and a.valor ilike 'Aprobado')

              and tt.fk_periodo = {$periodo}
              and te.fk_sede = {$sede}
              and te.fk_escuela = {$escuela}
              ".$filtro_cod.$filtro_estado. $filtro_busqueda;
         
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }
    
  
  
      public function getTutorNombre($id){
         
        

        $SQL ="select u.pk_usuario, (u.apellido ||', '|| u.nombre) as nombre, a.valor
                from tbl_usuarios		       u
                join tbl_usuariosgrupos		 ug	     on	 ug.fk_usuario       =   u.pk_usuario
                join tbl_tutorestesis		   tt	     on	 tt.fk_usuariogrupo  =   ug.pk_usuariogrupo
                join tbl_atributos         a       on  a.pk_atributo       =   tt.fk_estado
                where tt.fk_datotesis = {$id}
                and tt.renuncia = false";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }


      public function getTutorEstado($cedula,$cod){
        
              
       if(empty($cedula))return;
       if(empty($cod))return;
      
      $SQL ="select tt.fk_estado
              from tbl_datostesis     dt 
              join tbl_tutorestesis   tt      on      tt.fk_datotesis   = dt.pk_datotesis
              join tbl_usuariosgrupos   ug      on      ug.pk_usuariogrupo  = tt.fk_usuariogrupo
              where ug.fk_usuario = {$cedula}
              and tt.fk_datotesis = {$cod}";

            
        return $this->_db->fetchOne($SQL);
      
  } 


    public function getTutorDatos($id){
         
        if(empty($id))return;

        $SQL ="select distinct u.pk_usuario, (u.apellido ||', '|| u.nombre) as nombre, dt.titulo, tt.pk_tutortesis,a.valor as valortutor, dt.pk_datotesis
                from tbl_usuarios           u
                join tbl_usuariosgrupos     ug      on  ug.fk_usuario       =   u.pk_usuario
                join tbl_tutorestesis       tt      on  tt.fk_usuariogrupo  =   ug.pk_usuariogrupo
                join tbl_datostesis         dt      on  dt.pk_datotesis     =   tt.fk_datotesis
                join tbl_atributos          a       on  a.pk_atributo       =   tt.fk_estado
                where tt.pk_tutortesis = {$id} 
                and tt.renuncia = false";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }

  public function getAllTutor($id){
      if(empty($id))return;

        $SQL ="select *
                from tbl_tutorestesis
                where pk_tutortesis = {$id}";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }

      public function getTutorTipo($id){
         

        $SQL ="select distinct u.pk_usuario,
                        (case
                                when	ug.fk_grupo  in  (855,854,1745) then (select distinct pk_atributo
                                                                                                from tbl_atributostipos		at
                                                                                                join tbl_atributos		a	on	a.fk_atributotipo = at.pk_atributotipo
                                                                                                where at.nombre ilike 'Tipo Tutor'
                                                                                                and a.valor ilike 'Interno')
                                else
                                        (select distinct pk_atributo
                                        from tbl_atributostipos		at
                                        join tbl_atributos		a	on	a.fk_atributotipo = at.pk_atributotipo
                                        where at.nombre ilike 'Tipo Tutor'
                                        and a.valor ilike 'Externo')
                                end
                        ) as tipo 
                from tbl_usuarios               u
                join tbl_usuariosgrupos         ug      on      ug.fk_usuario       =   u.pk_usuario
                where u.pk_usuario = {$id}
                order by tipo asc limit 1
                ";


                
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  
      public function getTutorTipoExterno(){
         

        $SQL ="select distinct pk_atributo
                from tbl_atributostipos		at
                join tbl_atributos		a	on	a.fk_atributotipo = at.pk_atributotipo
                where at.nombre ilike 'Tipo Tutor'
                and a.valor ilike 'Externo'";
        
        return $this->_db->fetchOne($SQL);
      
  }
  
  
    public function getTutoresTipos($tipo){
        
        if(!empty($tipo)) {
          $filtro_tipo = " and a.pk_atributo = {$tipo} ";
        }else{
          $filtro_tipo = " ";
        }

        $SQL ="select a.pk_atributo, a.valor
                from tbl_atributostipos		at
                join tbl_atributos		a	on	a.fk_atributotipo	=	at.pk_atributotipo
                where at.nombre ilike 'Tipo Tutor' ".$filtro_tipo."
                order by 1 desc";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }


      public function getTutorByPk($id){
         
        if(empty($id))return;

        $SQL ="select distinct u.pk_usuario
                from tbl_usuarios           u
                join tbl_usuariosgrupos     ug  on  ug.fk_usuario       =   u.pk_usuario
                join tbl_tutorestesis       tt  on  tt.fk_usuariogrupo  =   ug.pk_usuariogrupo
                where tt.pk_tutortesis = {$id}";

        return $this->_db->fetchOne($SQL);
      
  }

      public function getTutorNumeroTesis($id,$periodo){
         
        if(empty($id))return;
        if(empty($periodo))return;

        $SQL ="select count(distinct tt.pk_tutortesis)
                from tbl_datostesis     dt
                join tbl_tutorestesis   tt  on  tt.fk_datotesis     =   dt.pk_datotesis
                join tbl_usuariosgrupos ug  on  ug.pk_usuariogrupo  =   tt.fk_usuariogrupo
                join tbl_usuarios       u   on  u.pk_usuario        =   ug.fk_usuario
                join tbl_autorestesis   at  on  at.fk_datotesis     =   dt.pk_datotesis       and   at.renuncia = false  
                where tt.fk_periodo = {$periodo}
                and tt.fk_estado = ".$this->getEstadoTutorAprobado()." 
                and u.pk_usuario = {$id}
                and tt.renuncia = false";

        return $this->_db->fetchOne($SQL);
      
  }

  public function getTutorObservacion($id){
        
        if(empty($id))return;

        $SQL ="select distinct pk_tutortesis, observaciones
              from tbl_tutorestesis 
              where pk_tutortesis = {$id};";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }
  
        public function getQueEs($cedula){
         
        if(empty($cedula))return;

        $SQL ="select distinct fk_grupo
                from tbl_usuariosgrupos	ug
                join tbl_usuarios	u	on	u.pk_usuario = ug.fk_usuario
                where u.pk_usuario = {$cedula}
                order by 1 desc";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  

    public function getTemaAprobado($id){

        if(empty($id))return;

        $SQL ="select distinct dt.fk_estado, a.valor
                from tbl_datostesis     dt
                join tbl_atributos      a   on  a.pk_atributo = dt.fk_estado
                where pk_datotesis = {$id}";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  
    public function getTesisLinea($escuela,$busqueda = null){

        if($busqueda != null){
          $filtro_busqueda = " and a.valor ilike '%{$busqueda}%'";
        }else{
          $filtro_busqueda = " ";
        }

        $SQL ="select distinct a.pk_atributo, a.valor as lineainvestigacion
                from tbl_lineastemastesis	ltt
                join tbl_atributos		a	on	a.pk_Atributo = ltt.fk_lineainvestigacion
                where ltt.fk_escuela = {$escuela}
                ".$filtro_busqueda."
                order by 2 asc";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  
  
    public function getTesisTema($lineainvestigacion,$busqueda = null){
        
        if($lineainvestigacion != null){
          $filtro_tema = " where ltt.fk_lineainvestigacion = {$lineainvestigacion} ";
        }else{
          $filtro_tema = " ";
        }


        if($busqueda != null){
          $filtro_busqueda = " and a.valor ilike '%{$busqueda}%' ";
        }else{
          $filtro_busqueda = " ";
        }


        $SQL ="select distinct a.pk_atributo, a.valor as tema, ltt.pk_lineatematesis
                from tbl_lineastemastesis	ltt
                join tbl_atributos		a	on	a.pk_Atributo = ltt.fk_tema
                ".$filtro_tema.$filtro_busqueda."
                order by 2 asc";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }

    public function getTesisTemaEscuela($escuela){
        

        $SQL ="select distinct a.pk_atributo, a.valor as tema
                from tbl_lineastemastesis ltt
                join tbl_atributos    a on  a.pk_atributo = ltt.fk_tema
                where ltt.fk_escuela = {$escuela}
                order by 2 asc";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }  


    public function getTesisEScuelaPeriodo($escuela,$periodo,$estado,$busqueda,$sede){

        if(empty($busqueda)){$filtro_busqueda = "";}else{$filtro_busqueda = " and u_a.pk_usuario in (".$busqueda.")";}

        $SQL ="select sqt.pk_datotesis,sqt.titulo, btrim(sqt.cedula::varchar,'{}"."') as cedula, btrim(sqt.autor::varchar,'{}"."') as autor, sqt.estado
              , (case
                when sqt.tutor <> '' then sqt.tutor
                else 'Ninguno'
                end) as tutor
                from (

                  select  dt.pk_datotesis,
                          dt.titulo,
                          (select array_agg(sqt_sub.pk_usuario)
                          from(
                          select u_sub.pk_usuario
                          from tbl_usuariosgrupos ug_sub
                          join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo and at_sub.renuncia = false
                          join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                          where at_sub.fk_datotesis = dt.pk_datotesis
                          order by 1 asc)as sqt_sub) as cedula, 
                          (select array_agg(sqt_sub.nombre)
                          from(
                          select u_sub.pk_usuario,(u_sub.primer_apellido ||' '||u_sub.primer_nombre) as nombre
                          from tbl_usuariosgrupos ug_sub
                          join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo and at_sub.renuncia = false
                          join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                          where at_sub.fk_datotesis = dt.pk_datotesis
                          order by 1 asc)as sqt_sub) as autor, 
                          atri.valor as estado,
                          (u_t.primer_apellido ||' '||u_t.primer_nombre)as tutor
                  from tbl_datostesis                 dt
                  join tbl_lineastemastesis           ltt   on  ltt.pk_lineatematesis   =   dt.fk_lineatematesis
                  join tbl_autorestesis               at    on  at.fk_datotesis         =   dt.pk_datotesis       and at.renuncia = false
                  full outer join tbl_tutorestesis    tt    on  tt.fk_datotesis         =   dt.pk_datotesis       and tt.renuncia = false
                  join tbl_usuariosgrupos             ug_a  on  ug_a.pk_usuariogrupo    =   at.fk_usuariogrupo
                  join tbl_usuarios                   u_a   on  u_a.pk_usuario          =   ug_a.fk_usuario
                  full outer join tbl_usuariosgrupos  ug_t  on  ug_t.pk_usuariogrupo    =   tt.fk_usuariogrupo
                  full outer join tbl_usuarios        u_t   on  u_t.pk_usuario          =   ug_t.fk_usuario
                  join tbl_atributos                  atri  on  atri.pk_atributo        =   dt.fk_estado
                  join tbl_tesis                      te    on  te.fk_datotesis         =   dt.pk_datotesis
                  where te.fk_escuela = {$escuela}  
                  and te.fk_periodo = {$periodo}
                  and dt.fk_estado = {$estado}
                  and te.fk_sede = {$sede}
                  
                  ".$filtro_busqueda." 
                  group by 1,2,5,6

                ) as sqt";
      
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }


  public function getTesisEScuelaPeriodoParaDefensa($escuela,$periodo,$busqueda,$mod,$estado = null,$sede){

      if(empty($busqueda)){$filtro_busqueda = "";}else{$filtro_busqueda = " and u_a.pk_usuario in (".$busqueda.")";}
      
      if($estado == 'Asignado'){
        $filtro_estado = ' where sqt.cant_evaluadores >= 1 ';
      }elseif($estado == 'No Asignado'){
        $filtro_estado = ' where sqt.cant_evaluadores = 0 ';
      }else{
        $filtro_estado = ' ';
      }

      if($mod == 'Evaluadores'){//si busco por el modulo de evaluadores, la escuela debe pertenecer al estudiante
        $filtro_mod_escuela = ' join tbl_inscripciones         i           on         i.fk_usuariogrupo   =     ug_a.pk_usuariogrupo
                        where i.fk_atributo = ';

        $filtro_mod_sede = ' and i.fk_estructura = '. $sede;                           

        $join = ' full outer join ';
      }



      $SQL ="select sqt.pk_datotesis,sqt.titulo, btrim(sqt.cedula::varchar,'{}"."') as cedula, btrim(sqt.autor::varchar,'{}"."') as autor, sqt.estado
            , (case
              when sqt.tutor <> '' then sqt.tutor
              else 'Ninguno'
              end) as tutor,
              cant_evaluadores,
              sqt.mencion,
              (case when sqt.calificacion is not null then sqt.calificacion else 0 end ) as calificacion,
              sqt.evaluador_tecnico,
              sqt.evaluador_investigacion
              from (

                select  dt.pk_datotesis,
                        dt.titulo,
                        (select array_agg(sqt_sub.pk_usuario)
                        from(
                        select u_sub.pk_usuario
                        from tbl_usuariosgrupos ug_sub
                        join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo and at_sub.renuncia = false
                        join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                        where at_sub.fk_datotesis = dt.pk_datotesis
                        order by 1 asc)as sqt_sub) as cedula, 
                        (select array_agg(sqt_sub.nombre)
                        from(
                        select u_sub.pk_usuario,(u_sub.primer_apellido ||' '||u_sub.primer_nombre) as nombre
                        from tbl_usuariosgrupos ug_sub
                        join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo  and at_sub.renuncia = false
                        join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                        where at_sub.fk_datotesis = dt.pk_datotesis
                        order by 1 asc)as sqt_sub) as autor, 
                        atri.valor as estado,
                        (u_t.primer_apellido ||' '||u_t.primer_nombre)as tutor,
                        (select count(sub_eva.pk_evaluadortesis)
                          from tbl_evaluadorestesis  sub_eva
                          where sub_eva.fk_datotesis = dt.pk_datotesis
                          and sub_eva.fk_periodo = {$periodo}) as cant_evaluadores,
                        mt.fk_mencion as mencion,
                        te.calificacion,
                        (select distinct (u_sub.primer_apellido ||', '|| u_sub.primer_nombre) 
                        from tbl_usuarios               u_sub
                        full outer join tbl_usuariosgrupos      ug_sub    on    ug_sub.fk_usuario   =   u_sub.pk_usuario
                        full outer join tbl_evaluadorestesis    et_sub    on    et_sub.fk_usuariogrupo  =   ug_sub.pk_usuariogrupo    
                        full outer join tbl_datostesis        dt_sub    on    dt_sub.pk_datotesis   =   et_sub.fk_datotesis
                        where dt_sub.pk_datotesis = dt.pk_datotesis 
                        and et_sub.fk_periodo = {$periodo} 
                        and et_sub.fk_rol = ".$this->getAtributoEvaluadorTecnico()." limit 1) as evaluador_tecnico,
                        (select distinct (u_sub.primer_apellido ||', '|| u_sub.primer_nombre)
                        from tbl_usuarios               u_sub
                        full outer join tbl_usuariosgrupos      ug_sub    on    ug_sub.fk_usuario   =   u_sub.pk_usuario
                        full outer join tbl_evaluadorestesis    et_sub    on    et_sub.fk_usuariogrupo  =   ug_sub.pk_usuariogrupo    
                        full outer join tbl_datostesis        dt_sub    on    dt_sub.pk_datotesis   =   et_sub.fk_datotesis
                        where dt_sub.pk_datotesis = dt.pk_datotesis 
                        and et_sub.fk_periodo = {$periodo}
                        and et_sub.fk_rol = ".$this->getAtributoEvaluadorInvestigacion()." limit 1) as evaluador_investigacion
                from tbl_datostesis                 dt
                join tbl_mencionestesis             mt    on  mt.fk_datotesis         =   dt.pk_datotesis
                full outer join tbl_tesis           te    on  te.fk_datotesis         =   dt.pk_datotesis
                join tbl_lineastemastesis           ltt   on  ltt.pk_lineatematesis   =   dt.fk_lineatematesis
                join tbl_autorestesis               at    on  at.fk_datotesis         =   dt.pk_datotesis           and at.renuncia = false
                full outer join tbl_tutorestesis    tt    on  tt.fk_datotesis         =   dt.pk_datotesis           and tt.renuncia = false
                ".$join."  tbl_defensastesis        deft  on  deft.fk_datotesis     =   dt.pk_datotesis             
                join tbl_usuariosgrupos             ug_a  on  ug_a.pk_usuariogrupo    =   at.fk_usuariogrupo
                join tbl_usuarios                   u_a   on  u_a.pk_usuario          =   ug_a.fk_usuario
                full outer join tbl_usuariosgrupos  ug_t  on  ug_t.pk_usuariogrupo    =   tt.fk_usuariogrupo
                full outer join tbl_usuarios        u_t   on  u_t.pk_usuario          =   ug_t.fk_usuario
                join tbl_atributos                  atri  on  atri.pk_atributo        =   dt.fk_estado
                 ".$filtro_mod_escuela." {$escuela}
                 ".$filtro_mod_sede."
                and i.fk_periodo = {$periodo}
                and dt.fk_estado =  ".$this->getEstadoTesisAprobado()."  
                and tt.fk_estado =  ".$this->getEstadoTutorAprobado()." 
                and u_a.pk_usuario in (
                                  select  ug_sub2.fk_usuario
                                 from tbl_asignaturas    asi_sub2
                                  join tbl_recordsacademicos  ra_sub2 on  ra_sub2.fk_asignatura   = asi_sub2.pk_asignatura
                                  join tbl_inscripciones    i_sub2  on  i_sub2.pk_inscripcion   = ra_sub2.fk_inscripcion
                                  join tbl_usuariosgrupos   ug_sub2 on  ug_sub2.pk_usuariogrupo = i_sub2.fk_usuariogrupo
                                  where ug_sub2.fk_usuario = u_a.pk_usuario
                                  and asi_sub2.fk_materia in (
                                  9724,
                                  834
                                  )
                                  --and ra_sub2.fk_atributo = 864
                                  --and ra_sub2.calificacion = 0
                                  and i_sub2.fk_periodo = {$periodo}

               )
                ".$filtro_busqueda. 
                "group by 1,2,5,6,7,8,9,10,11

              ) as sqt ".$filtro_estado ." order by 1 asc";
                //var_dump($SQL);die;
      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();
  }


  public function getTesisparaPlanilla($periodo,$sede,$escuela){


      $SQL = "select distinct dt.pk_datotesis,
                  dt.titulo,
                  mt.fk_mencion as mencion,
                  (select btrim(array_agg(sqt_sub.nombre)::varchar,'{}"."') as cedula
                      from(
                      select u_sub.pk_usuario,(u_sub.primer_apellido ||' '||u_sub.primer_nombre) as nombre
                      from tbl_usuariosgrupos   ug_sub
                      join tbl_autorestesis     at_sub    on  at_sub.fk_usuariogrupo  = ug_sub.pk_usuariogrupo  and at_sub.renuncia = false
                      join tbl_usuarios         u_sub     on  u_sub.pk_usuario        = ug_sub.fk_usuario
                      where at_sub.fk_datotesis = dt.pk_datotesis
                      order by 1 asc)as sqt_sub) as autor
              from tbl_datostesis         dt 
              join tbl_tesis              te      on      te.fk_datotesis     =   dt.pk_datotesis
              join tbl_autorestesis       at      on      at.fk_datotesis     =   dt.pk_datotesis     and at.renuncia = false
              join tbl_tutorestesis       tt      on      tt.fk_datotesis     =   dt.pk_datotesis     and tt.renuncia = false
              join tbl_mencionestesis     mt      on      mt.fk_datotesis     =   dt.pk_datotesis
              join tbl_usuariosgrupos     ug      on      ug.pk_usuariogrupo  =   at.fk_usuariogrupo    
              where te.fk_escuela = {$escuela}  
              and te.fk_sede = {$sede}
              and dt.fk_estado = ".$this->getEstadoTesisAprobado()." 
              and tt.fk_estado = ".$this->getEstadoTutorAprobado()." 
              and ug.fk_usuario in (

                select  ug_sub2.fk_usuario
                from tbl_asignaturas        asi_sub2
                join tbl_recordsacademicos  ra_sub2     on  ra_sub2.fk_asignatura   = asi_sub2.pk_asignatura
                join tbl_inscripciones      i_sub2      on  i_sub2.pk_inscripcion   = ra_sub2.fk_inscripcion
                join tbl_usuariosgrupos     ug_sub2     on  ug_sub2.pk_usuariogrupo = i_sub2.fk_usuariogrupo
                where ug_sub2.fk_usuario = ug.fk_usuario
                and asi_sub2.fk_materia in (
                9724,
                834
                )
                and ra_sub2.fk_atributo = 864
                --and ra_sub2.calificacion = 0
                and i_sub2.fk_periodo = {$periodo}
                and i_sub2.fk_atributo = {$escuela}
                and i_sub2.fk_estructura = {$sede}

              )";

               
      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();
  }





      public function getTesisConDefensa($escuela,$periodo,$busqueda,$mod,$cod,$sede){

        if(empty($busqueda)){$filtro_busqueda = "";}else{$filtro_busqueda = " and u_a.pk_usuario in (".$busqueda.")";}

        if(empty($cod)){$filtro_cod = "";}else{$filtro_cod = " and dt.pk_datotesis in (".$cod.")";}
        
        if($mod == 'Asignado'){
          $filtro_mod = ' where sqt.cant_defensa >= 1 ';
        }elseif($mod == 'No Asignado'){
          $filtro_mod = ' where sqt.cant_defensa = 0 ';
        }else{
          $filtro_mod = ' ';
        }




        $SQL ="select sqt.pk_datotesis,sqt.titulo, btrim(sqt.cedula::varchar,'{}"."') as cedula, btrim(sqt.autor::varchar,'{}"."') as autor, sqt.estado
            , (case
              when sqt.tutor <> '' then sqt.tutor
              else 'Ninguno'
              end) as tutor,
              cant_defensa,
              sqt.mencion,
              (case when sqt.calificacion is not null then sqt.calificacion else 0 end ) as calificacion,
              sqt.evaluador_tecnico,
              sqt.evaluador_investigacion
              from (

                select  dt.pk_datotesis,
                        dt.titulo,
                        (select array_agg(sqt_sub.pk_usuario)
                        from(
                        select u_sub.pk_usuario
                        from tbl_usuariosgrupos ug_sub
                        join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo and at_sub.renuncia = false
                        join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                        where at_sub.fk_datotesis = dt.pk_datotesis
                        order by 1 asc)as sqt_sub) as cedula, 
                        (select array_agg(sqt_sub.nombre)
                        from(
                        select u_sub.pk_usuario,(u_sub.primer_apellido ||' '||u_sub.primer_nombre) as nombre
                        from tbl_usuariosgrupos ug_sub
                        join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo  and at_sub.renuncia = false
                        join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                        where at_sub.fk_datotesis = dt.pk_datotesis
                        order by 1 asc)as sqt_sub) as autor, 
                        atri.valor as estado,
                        (u_t.primer_apellido ||' '||u_t.primer_nombre)as tutor,
                        (select count(sub_def.pk_defensatesis)
                          from tbl_defensastesis  sub_def
                          where sub_def.fk_datotesis = dt.pk_datotesis
                          and sub_def.fk_periodo = {$periodo}) as cant_defensa,
                        mt.fk_mencion as mencion,
                        te.calificacion,
                        (select distinct (u_sub.primer_apellido ||', '|| u_sub.primer_nombre) 
                        from tbl_usuarios               u_sub
                        full outer join tbl_usuariosgrupos      ug_sub    on    ug_sub.fk_usuario   =   u_sub.pk_usuario
                        full outer join tbl_evaluadorestesis    et_sub    on    et_sub.fk_usuariogrupo  =   ug_sub.pk_usuariogrupo    
                        full outer join tbl_datostesis        dt_sub    on    dt_sub.pk_datotesis   =   et_sub.fk_datotesis
                        where dt_sub.pk_datotesis = dt.pk_datotesis 
                        and et_sub.fk_rol = ".$this->getAtributoEvaluadorTecnico()." limit 1) as evaluador_tecnico,
                        (select distinct (u_sub.primer_apellido ||', '|| u_sub.primer_nombre)
                        from tbl_usuarios               u_sub
                        full outer join tbl_usuariosgrupos      ug_sub    on    ug_sub.fk_usuario   =   u_sub.pk_usuario
                        full outer join tbl_evaluadorestesis    et_sub    on    et_sub.fk_usuariogrupo  =   ug_sub.pk_usuariogrupo    
                        full outer join tbl_datostesis        dt_sub    on    dt_sub.pk_datotesis   =   et_sub.fk_datotesis
                        where dt_sub.pk_datotesis = dt.pk_datotesis 
                        and et_sub.fk_rol = ".$this->getAtributoEvaluadorInvestigacion()." limit 1) as evaluador_investigacion
                from tbl_datostesis                 dt
                join tbl_mencionestesis             mt    on  mt.fk_datotesis         =   dt.pk_datotesis
                full outer join tbl_tesis           te    on  te.fk_datotesis         =   dt.pk_datotesis
                join tbl_lineastemastesis           ltt   on  ltt.pk_lineatematesis   =   dt.fk_lineatematesis
                join tbl_autorestesis               at    on  at.fk_datotesis         =   dt.pk_datotesis           and at.renuncia = false
                full outer join tbl_tutorestesis    tt    on  tt.fk_datotesis         =   dt.pk_datotesis           and tt.renuncia = false           
                join tbl_usuariosgrupos             ug_a  on  ug_a.pk_usuariogrupo    =   at.fk_usuariogrupo
                join tbl_usuarios                   u_a   on  u_a.pk_usuario          =   ug_a.fk_usuario
                full outer join tbl_usuariosgrupos  ug_t  on  ug_t.pk_usuariogrupo    =   tt.fk_usuariogrupo
                full outer join tbl_usuarios        u_t   on  u_t.pk_usuario          =   ug_t.fk_usuario
                join tbl_atributos                  atri  on  atri.pk_atributo        =   dt.fk_estado
                join tbl_inscripciones              i     on  i.fk_usuariogrupo       =   ug_a.pk_usuariogrupo
                and te.fk_escuela = {$escuela}
                and te.fk_sede = {$sede}
                and i.fk_periodo = {$periodo}
                 ".$filtro_cod." 
                and dt.fk_estado =  ".$this->getEstadoTesisAprobado()."  
                and tt.fk_estado =  ".$this->getEstadoTutorAprobado()." 
                and u_a.pk_usuario in (
                                  select  ug_sub2.fk_usuario
                                 from tbl_asignaturas    asi_sub2
                                  join tbl_recordsacademicos  ra_sub2 on  ra_sub2.fk_asignatura   = asi_sub2.pk_asignatura
                                  join tbl_inscripciones    i_sub2  on  i_sub2.pk_inscripcion   = ra_sub2.fk_inscripcion
                                  join tbl_usuariosgrupos   ug_sub2 on  ug_sub2.pk_usuariogrupo = i_sub2.fk_usuariogrupo
                                  where ug_sub2.fk_usuario = u_a.pk_usuario
                                  and asi_sub2.fk_materia in (
                                  9724,
                                  834
                                  )
                                  --and ra_sub2.fk_atributo = 864
                                  --and ra_sub2.calificacion = 0
                                  and i_sub2.fk_periodo = {$periodo}

               )
                ".$filtro_busqueda. 
                "group by 1,2,5,6,7,8,9,10,11

              ) as sqt"
                .$filtro_mod ." order by 1 asc";
                
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }





    public function getTesisByPk($id){
 
        if(empty($id))return;

        $SQL ="select sqt.pk_datotesis,sqt.titulo, btrim(sqt.cedula::varchar,'{}"."') as cedula, btrim(sqt.autor::varchar,'{}"."') as autor, sqt.estado
              , (case
                when sqt.tutor <> '' then sqt.tutor
                else 'Ninguno'
                end) as tutor
                from (

                  select  dt.pk_datotesis,
                          dt.titulo,
                          (select array_agg(sqt_sub.pk_usuario)
                          from(
                          select u_sub.pk_usuario
                          from tbl_usuariosgrupos ug_sub
                          join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo
                          join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                          where at_sub.fk_datotesis = dt.pk_datotesis
                          order by 1 asc)as sqt_sub) as cedula, 
                          (select array_agg(sqt_sub.nombre)
                          from(
                          select u_sub.pk_usuario,(u_sub.primer_apellido ||' '||u_sub.primer_nombre) as nombre
                          from tbl_usuariosgrupos ug_sub
                          join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo
                          join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                          where at_sub.fk_datotesis = dt.pk_datotesis
                          order by 1 asc)as sqt_sub) as autor, 
                          atri.valor as estado,
                          (u_t.primer_apellido ||' '||u_t.primer_nombre)as tutor
                  from tbl_datostesis                 dt
                  join tbl_lineastemastesis           ltt   on  ltt.pk_lineatematesis   = dt.fk_lineatematesis
                  join tbl_autorestesis               at    on  at.fk_datotesis         =   dt.pk_datotesis         and at.renuncia = false
                  full outer join tbl_tutorestesis    tt    on  tt.fk_datotesis         =   dt.pk_datotesis         and tt.renuncia = false
                  join tbl_usuariosgrupos             ug_a  on  ug_a.pk_usuariogrupo    = at.fk_usuariogrupo
                  join tbl_usuarios                   u_a   on  u_a.pk_usuario          = ug_a.fk_usuario
                  full outer join tbl_usuariosgrupos  ug_t  on  ug_t.pk_usuariogrupo    = tt.fk_usuariogrupo
                  full outer join tbl_usuarios        u_t   on  u_t.pk_usuario          = ug_t.fk_usuario
                  join tbl_atributos                  atri  on  atri.pk_atributo        =  dt.fk_estado
                  where dt.pk_datotesis = {$id}
                  group by 1,2,5,6

                ) as sqt";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }




      public function getTesisObservaciones($id){
        
        if(empty($id))return;

        $SQL ="select distinct pk_pasotesis, observaciones,fecha
              from tbl_pasostesis
              where  fk_datotesis = {$id}
              and descripcion ilike 'observacion tesis'
              order by fecha desc limit 1";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }


  public function getTesisByFK($id){
    
    

    $SQL ="SELECT distinct pk_tesis 
          from tbl_tesis
          where fk_datotesis = {$id}";

    return $this->_db->fetchOne($SQL);
  }  


  public function getUltimaCota(){
    
    

    $SQL ="SELECT distinct cota
            from tbl_tesis 
            where cota is not null 
            order by 1 desc limit 1";
            
    return $this->_db->fetchOne($SQL);
  }   



  public function getCountDefensa($id){
        
        if(empty($id))return;

        $SQL ="select count(distinct pk_defensatesis)
                from tbl_defensastesis
                where fk_datotesis = {$id}";
                
        return $this->_db->fetchOne($SQL);
  }





      public function getLineaTemaTesis($id,$usuario){
        
        if(empty($id))return;
        
        if(!empty($usuario)){
          $filtro_autor = " and ug.fk_usuario = {$usuario} ";
        }else{
          $filtro_autor = " ";
        }

        $SQL ="SELECT distinct dt.pk_datotesis,dt.titulo, ltt.fk_lineainvestigacion,linea. valor as linea, ltt.fk_tema,tema.valor as tema, ltt.fk_escuela
              from tbl_datostesis           dt
              join tbl_lineastemastesis     ltt         on  ltt.pk_lineatematesis   =   dt.fk_lineatematesis
              join tbl_atributos            linea       on  linea.pk_atributo       =   ltt.fk_lineainvestigacion
              join tbl_atributos            tema        on  tema.pk_atributo        =   ltt.fk_tema
              join tbl_autorestesis         at          on  at.fk_datotesis         =   dt.pk_datotesis               and at.renuncia = false
              join tbl_usuariosgrupos       ug          on  ug.pk_usuariogrupo      =   at.fk_usuariogrupo
              where dt.pk_datotesis = {$id} " .$filtro_autor;

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }
  
  
    public function getLineaPk($linea,$tema){
        
        if(empty($linea))return;
        if(empty($tema))return;

        $SQL ="select distinct pk_lineatematesis 
               from tbl_lineastemastesis
               where fk_lineainvestigacion = {$linea}
               and fk_tema = {$tema};";

        return $this->_db->fetchOne($SQL);
  }


    public function getEvaluadores($periodo,$sede){


      $SQL = "SELECT DISTINCT ug.pk_usuariogrupo, 
               (u.apellido||', '|| u.nombre) as initcap
        FROM tbl_usuarios         u 
        JOIN tbl_usuariosgrupos   ug    on    ug.fk_usuario       =   u.pk_usuario
        -- join tbl_asignaciones     asi   on    asi.fk_usuariogrupo =   ug.fk_usuario
        where ug.fk_grupo = 854 --docente
        -- and asi.fk_periodo = {$periodo}
        -- and asi.fk_sede = {$sede}
        ORDER BY initcap ASC;";
        
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }



    public function getEvaluadorRow($tesis, $periodo, $evaluador){


      $SQL = "SELECT *
              from tbl_evaluadorestesis
              where fk_datotesis = {$tesis}
              and fk_usuariogrupo = {$evaluador}
              and fk_periodo = {$periodo}";
        
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }


    public function getEvaluadoresDefensa($cod,$periodo){


      $SQL = "select distinct u.pk_usuario as cedula, (u.apellido || ', ' || u.nombre) as evaluador, et.pk_evaluadortesis, fk_tipo, ug.pk_usuariogrupo, atri.valor as rol, et.fk_rol, atri_tipo.valor as tipo, atri_tipo.pk_atributo as pk_atributo_tipo
              from tbl_evaluadorestesis         et
              join tbl_usuariosgrupos           ug          on        ug.pk_usuariogrupo    =   et.fk_usuariogrupo
              join tbl_usuarios                 u           on        u.pk_usuario          =   ug.fk_usuario
              full outer join tbl_atributos     atri        on        atri.pk_atributo      =   et.fk_rol
              full outer join tbl_atributos     atri_tipo   on        atri_tipo.pk_atributo =   et.fk_tipo
              where et.fk_datotesis = {$cod}
              and et.fk_periodo = {$periodo}
              order by 6,4 asc";
        
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    } 


    public function getEvaluadoresPrincipales($cod,$periodo){


      $SQL = "select distinct u.pk_usuario as cedula, (u.apellido || ', ' || u.nombre) as evaluador,
      et.pk_evaluadortesis, 
      fk_tipo, 
      ug.pk_usuariogrupo,
      et.fk_rol
              from tbl_evaluadorestesis         et
              join tbl_usuariosgrupos           ug          on        ug.pk_usuariogrupo    =   et.fk_usuariogrupo
              join tbl_usuarios                 u           on        u.pk_usuario          =   ug.fk_usuario
              where et.fk_datotesis = {$cod}
              and et.fk_periodo = {$periodo}";
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }  


    public function getEvaluadorDatosbyPk($cod){

      if(empty($cod))return;

      $SQL = "select distinct pk_evaluadortesis, fk_periodo, fk_datotesis, u.nombre, u.apellido
              from tbl_evaluadorestesis       et
              join tbl_usuariosgrupos         ug      on      ug.pk_usuariogrupo  = et.fk_usuariogrupo
              join tbl_usuarios               u       on      u.pk_usuario        = ug.fk_usuario
              where et.pk_evaluadortesis in ({$cod})";
        
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }      
 
      
   public function getTesistaNombre($id, $cedula){
       
       if(empty($id))return;
       if(empty($cedula))return;
      
        $SQL ="select u.pk_usuario,(u.apellido || ', ' || u.nombre) as nombre
              from tbl_datostesis       dt
              join tbl_autorestesis     at  on  at.fk_datotesis     =   dt.pk_datotesis
              join tbl_usuariosgrupos   ug  on  ug.pk_usuariogrupo  =   at.fk_usuariogrupo
              join tbl_usuarios         u   on  u.pk_usuario        = ug.fk_usuario
              where dt.pk_datotesis = {$id}
              and u.pk_usuario not in ({$cedula})
              and at.renuncia = false";
              
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  
  
 public function getTesistasParaCalificacion($id){
       
       if(empty($id))return;
      
        $SQL ="select distinct ra.pk_recordacademico,u.pk_usuario
              from tbl_datostesis         dt
              join tbl_autorestesis       at  on  at.fk_datotesis     =   dt.pk_datotesis           and at.renuncia = false
              join tbl_usuariosgrupos     ug  on  ug.pk_usuariogrupo  =   at.fk_usuariogrupo
              join tbl_usuarios           u   on  u.pk_usuario        =   ug.fk_usuario
              join tbl_inscripciones      i   on  i.fk_usuariogrupo   =   ug.pk_usuariogrupo
              join tbl_recordsacademicos  ra  on  ra.fk_inscripcion   =   i.pk_inscripcion
              join tbl_asignaturas        a   on  a.pk_asignatura     =   ra.fk_asignatura
              join vw_materias            vma on  vma.pk_atributo     =   a.fk_materia
              where dt.pk_datotesis = {$id}
              and ra.fk_atributo = 864
              and vma.pk_atributo in (
                9724, --trabajo de grado ii
                834  --tesis de grado II
               )";
              
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }
  
  
   public function getTesistaEscuela($cedula){
         
              
       if(empty($cedula))return;
       
        $SQL ="select i.fk_periodo, i.fk_atributo
                from tbl_inscripciones		i
                join tbl_usuariosgrupos		ug	on	ug.pk_usuariogrupo	=	i.fk_usuariogrupo
                where ug.fk_usuario = {$cedula}
                order by fk_periodo desc limit 1";

        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  } 
  
      public function getTesistasCount($usuario){
        
              
        if(empty($usuario))return;
      
        $SQL ="select count(distinct dt.pk_datotesis) 
              from tbl_usuarios   u   
              join tbl_usuariosgrupos   ug  on  ug.fk_usuario   = u.pk_usuario
              join tbl_autorestesis     at  on  at.fk_usuariogrupo  = ug.pk_usuariogrupo
              join tbl_datostesis       dt  on  dt.pk_datotesis   = at.fk_datotesis
              where u.pk_usuario = {$usuario}
              and at.renuncia = false";

            
        return $this->_db->fetchOne($SQL);
      
  }


   public function getTesistasPeriodo($periodo,$busqueda = null){
         
              
       if(empty($periodo))return;

        if($busqueda != null){
          $filtro_busqueda = " and u.pk_usuario = {$busqueda} ";
        }else{
          $filtro_busqueda = " ";
        }       
       
        $SQL ="select distinct u.pk_usuario, u.nombre, u.apellido, dt.titulo, dt.pk_datotesis, (dt.pk_datotesis || ', ' ||u.pk_usuario) as pk
              from tbl_datostesis         dt 
              join tbl_autorestesis       at          on        at.fk_datotesis      =      dt.pk_datotesis   and at.renuncia = false
              join tbl_usuariosgrupos     ug          on        ug.pk_usuariogrupo   =      at.fk_usuariogrupo
              join tbl_usuarios           u           on        u.pk_usuario         =      ug.fk_usuario
              where at.fk_periodo = {$periodo} 
              and dt.fk_estado = ".$this->getEstadoTesisAprobado() . $filtro_busqueda
              . " order by 4 desc ";
              
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
      
  }    




    public function getTesistas($periodo,$sede,$escuela = null,$pensum = null,$materia = null,$busqueda = null){
        if($escuela != null){
          $filtro_escuela = " and i.fk_atributo = {$escuela} ";
        }else{
          $filtro_escuela = " and i.fk_atributo =(
                                                  select sqt_sub.fk_atributo
                                                  from (
                                                    select i_sub.fk_periodo, i_sub.fk_atributo
                                                    from tbl_pensums  pe_sub
                                                    join tbl_inscripciones  i_sub on  i_sub.fk_pensum = pe_sub.pk_pensum
                                                    join tbl_usuariosgrupos ug_sub  on  ug_sub.pk_usuariogrupo = i_sub.fk_usuariogrupo
                                                    where ug_sub.fk_usuario = u.pk_usuario
                                                    order by i_sub.fk_periodo desc limit 1
                                                  )as sqt_sub
                               ) ";
        }

        //en caso de que el pensum no este definida
        if($pensum != null){
          $filtro_pensum = " and i.fk_pensum = {$pensum} ";
        }else{
          $filtro_pensum = " and i.fk_pensum =(select i_sub.fk_pensum
                            from tbl_pensums  pe_sub
                            join tbl_inscripciones  i_sub on  i_sub.fk_pensum = pe_sub.pk_pensum
                            join tbl_usuariosgrupos ug_sub  on  ug_sub.pk_usuariogrupo = i_sub.fk_usuariogrupo
                            where ug_sub.fk_usuario = u.pk_usuario
                            and i_sub.fk_periodo = i.fk_periodo
                            order by i_sub.fk_pensum desc limit 1) ";
        }


        //en caso de que la materia no este definida
        if($materia != null){
          $filtro_materia = " and asi.fk_materia in ({$materia})";
        }else{
          $filtro_materia = " and asi.fk_materia IN (
                             519, --diseño de tesis
                             10621, --innovacion e investigacion
                             830, --tesis de grado i
                             9723, --trabajo de grado I
                             834, --tesis de grado ii
                             9724 --trabajo de grado II
                              ) ";
        }

        //en caso de que la busqueda no este definida
        if($busqueda != null){
          $filtro_busqueda = " and u.pk_usuario = {$busqueda} ";
        }else{
          $filtro_busqueda = "";
        }
        

        $SQL ="select distinct u.pk_usuario, u.nombre, u.apellido
                from tbl_asignaturas        asi
                join tbl_recordsacademicos  ra    on  ra.fk_asignatura    = asi.pk_asignatura
                join tbl_inscripciones      i     on  i.pk_inscripcion    = ra.fk_inscripcion
                join tbl_usuariosgrupos     ug    on  ug.pk_usuariogrupo  = i.fk_usuariogrupo
                join tbl_usuarios           u     on  u.pk_usuario        = ug.fk_usuario
                where i.fk_periodo = {$periodo}
                and i.fk_estructura = {$sede}
                ".$filtro_escuela."
                ".$filtro_pensum."
                ".$filtro_materia."
                and (ra.fk_atributo = 864
                -- or  (ra.fk_atributo = 862 and ra.calificacion >= 10) 
                )
                ".$filtro_busqueda."
                ";
                 
        $results = $this->_db->query($SQL);
        return (array) $results->fetchAll();
  }

  public function getTesistasByMateria($periodo,$sede,$escuela,$pensum,$materia,$busqueda = null){

    $SQL = "SELECT  tu.pk_usuario,
                    tu.nombre,
                    tu.apellido
            FROM tbl_usuarios tu 
            JOIN tbl_usuariosgrupos tg ON tu.pk_usuario = tg.fk_usuario
            JOIN tbl_inscripciones ti ON tg.pk_usuariogrupo = ti.fk_usuariogrupo
            JOIN tbl_recordsacademicos tr ON ti.pk_inscripcion = tr.fk_inscripcion
            JOIN tbl_asignaturas ts ON tr.fk_asignatura = ts.pk_asignatura
            WHERE ti.fk_estructura = {$sede}
            AND ti.fk_periodo = {$periodo}
            AND ti.fk_atributo = {$escuela}
            AND ti.fk_pensum = {$pensum}
            AND ts.pk_asignatura IN (SELECT pk_asignatura
                  FROM tbl_asignaturas 
                  WHERE fk_materia = {$materia}
                  AND fk_pensum = {$pensum}) \n";

    if($busqueda != null){
      $searchParams = array('pk_usuario', 'nombre', 'apellido', "LTRIM(TO_CHAR(pk_usuario, '99\".\"999\".\"999')::varchar, '0. ')");
      $whereSearch = $this->SwapBytes_Crud_Db_Table->getSearch($searchParams, $busqueda);
      $SQL .= "{$whereSearch}";
    }

    $results = $this->_db->query($SQL);
    return (array) $results->fetchAll();
  }

    public function getTesistasByTesis($pk_datotesis,$excluded){


      if(!empty($excluded)){
        $filtro_excluded = " and u.pk_usuario not in (".$excluded.") ";
      }else{
        $filtro_excluded = " ";
      }
      
      $SQL = "select distinct u.pk_usuario, u.nombre, u.apellido, i.fk_atributo
              from tbl_usuarios       u 
              join tbl_usuariosgrupos ug      on    ug.fk_usuario       =     u.pk_usuario
              join tbl_inscripciones  i       on    i.fk_usuariogrupo   =     ug.pk_usuariogrupo
              join tbl_autorestesis   at      on    at.fk_usuariogrupo  =     ug.pk_usuariogrupo    and at.renuncia = false
              join tbl_datostesis     dt      on    dt.pk_datotesis     =     at.fk_datotesis
              where dt.pk_datotesis = {$pk_datotesis} " .$filtro_excluded; 

      $results = $this->_db->query($SQL);
      return (array) $results->fetchAll();

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
  
   public function getGrupoTutor(){
         
        
        $SQL ="select distinct pk_atributo 
                from vw_grupos
                where grupo ilike 'Tutor Tesis'";

        return $this->_db->fetchOne($SQL);
      
  }
  
  
  
  public function getPeriodoActual(){
         
        $SQL ="select distinct pk_periodo
                from tbl_periodos
                where current_date between fechainicio and fechafin";

        return $this->_db->fetchOne($SQL);
      
  }

  public function getPeriodoAnterior(){
         
        $SQL ="select  pk_periodo - 1
                from tbl_periodos
                order by pk_periodo desc limit 1";

        return $this->_db->fetchOne($SQL);
      
  }

  public function getPeriodo($periodo){
        
        $SQL ="select *
                from tbl_periodos
                where pk_periodo = {$periodo}";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();

  }

  public function getSedeEstudiante($usuario){
    
    $SQL ="select distinct i.fk_estructura
    from tbl_inscripciones    i
    join tbl_usuariosgrupos   ug    on  ug.pk_usuariogrupo  = i.fk_usuariogrupo
    where ug.fk_usuario = {$usuario}
    AND i.pk_inscripcion = (
     SELECT pk_inscripcion 
     FROM (
       SELECT pk_inscripcion,fk_periodo
       FROM tbl_inscripciones i2
       JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = i2.fk_usuariogrupo
       WHERE ug2.fk_usuario = ug.fk_usuario
       ORDER BY fk_periodo DESC limit 1 
       ) as sqt
)";

return $this->_db->fetchOne($SQL);

}  

  public function getFaseTrabajodeGrado($cedula){
     
    if(empty($cedula))return;


    $SQL = "select  (case 
                when (pk_atributo = 519 or pk_atributo = 10621 or pk_atributo = 9719)   then  'Trabajo de Grado Fase Inicial'
                when (pk_atributo = 830 or pk_atributo = 9723)              then  'Trabajo de Grado Fase Intermedia'
                when (pk_atributo = 834 or pk_atributo = 9724)              then  'Trabajo de Grado Fase Final'
                else                                      null
              end) as fase,
              (case 
                when (pk_atributo = 519 or pk_atributo = 10621 or pk_atributo = 9719)   then  1
                when (pk_atributo = 830 or pk_atributo = 9723)              then  2
                when (pk_atributo = 834 or pk_atributo = 9724)              then  3
                else                                      null
              end) as mod
          from (
              select distinct i.fk_periodo,vma.pk_atributo,vma.materia
              from tbl_asignaturas      a
              join tbl_recordsacademicos    ra      on      ra.fk_asignatura  = a.pk_asignatura
              join tbl_inscripciones      i       on      i.pk_inscripcion  = ra.fk_inscripcion
              join tbl_usuariosgrupos     ug      on      ug.pk_usuariogrupo  = i.fk_usuariogrupo
              join vw_materias        vma     on      vma.pk_atributo   = a.fk_materia
              where ug.fk_usuario = {$cedula}
              and a.fk_materia IN (
              519, --diseño de tesis
              10621, --innovacion e investigacion
              9719, --seminario de trabajo de grado
              830, --tesis de grado i
              9723, --trabajo de grado I
              834, --tesis de grado ii
              9724 --trabajo de grado II
              )
              and (ra.fk_atributo = 864 or (ra.fk_atributo = 862 and ra.calificacion >= 10))

              order by 1 asc
          ) as sqt
          group by 1,2
          order by 2 desc";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();
  }
  
  
  public function getDatosDefensa($pk_datotesis,$periodo){
    
        if(empty($pk_datotesis))return;
        if(empty($periodo))return;  
        
        $SQL = "SELECT distinct dt.pk_datotesis, 
                                (case when def_t.fecha is not null then def_t.fecha else 'No tiene' end) as fecha, 
                                (case when (ho.horainicio||' - '||ho.horafin)::text is not null then (ho.horainicio||' - '||ho.horafin)::text else 'No tiene' end) as horainicio, 
                                (case when e2.nombre::text is not null then e2.nombre::text else 'No tiene' end) as edif, 
                                (case when e1.nombre::text is not null then e1.nombre::text else 'No tiene' end) as aula, 
                                (case when btrim(array_agg(u.nombre || ', '||u.apellido)::text,'{}"."') is not null
                                  then btrim(array_agg(u.nombre || ', '||u.apellido)::text,'{}"."')
                                  else 'No tiene' 
                                end ) as evaluador
                FROM tbl_datostesis                   dt
                full outer join tbl_defensastesis                def_t         on  def_t.fk_datotesis  = dt.pk_datotesis
                full outer join tbl_estructuras                  e1            on  e1.pk_estructura    = def_t.fk_estructura
                full outer join tbl_estructuras                  e2            on  e2.pk_estructura    = e1.fk_estructura  
                full outer join tbl_horarios                     ho            on  ho.pk_horario       = def_t.fk_horario
                full outer join tbl_evaluadorestesis  et            on  et.fk_datotesis     = dt.pk_datotesis
                full outer join tbl_usuariosgrupos    ug            on  ug.pk_usuariogrupo  = et.fk_usuariogrupo
                full outer join tbl_usuarios          u             on  u.pk_usuario        = ug.fk_usuario 
                where dt.pk_datotesis =  {$pk_datotesis}
                and def_t.fk_periodo = {$periodo}
                 and et.fk_tipo in (".$this->getAtributoEvaluadorPrincipal().")
                group by 1,2,3,4,5";  

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();

  }

  
  public function addTesis($linea,$titulo){
        
        if(empty($linea))return;
        if(empty($titulo))return;

        $SQL ="INSERT INTO tbl_datostesis(
            fk_lineatematesis, fk_estado, titulo)
    VALUES ({$linea}, (select distinct a.pk_atributo
                      from tbl_atributostipos     at 
                      join tbl_atributos        a       on    a.fk_atributotipo = at.pk_atributotipo
                      where at.nombre ilike 'Estado Tesis'
                      and a.valor ilike 'Por Aprobar'),'{$titulo}');";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }

  public function addTesisBiblioteca($pk_datotesis,$periodo){
        
        if(empty($pk_datotesis))return;

        $SQL ="INSERT INTO tbl_tesis(
                      fk_datotesis,fk_periodo)
              VALUES ({$pk_datotesis},{$periodo});";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }  


  
  
  public function addTesista($tesis,$usuario,$periodo){
        
        if(empty($tesis))return;
        if(empty($usuario))return;

        $SQL ="INSERT INTO tbl_autorestesis(
                        fk_datotesis, fk_usuariogrupo, fk_periodo,renuncia)
                VALUES ({$tesis}, {$usuario}, {$periodo},false);
               ";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  } 
  
  
  public function addUsuarioTutor($dataRow){
        
        $SQL ="INSERT INTO tbl_usuarios(
                        pk_usuario, nombre, apellido, direccion, 
                        fechanacimiento, correo, passwordhash,telefono,
                        telefono_movil, primer_nombre, segundo_nombre, 
                        primer_apellido, segundo_apellido)
                VALUES ({$dataRow['pk_usuario']}, '{$dataRow['nombre']}', '{$dataRow['apellido']}', '{$dataRow['direccion']}',
                        '{$dataRow['fechanacimiento']}', '{$dataRow['correo']}', '{$dataRow['passwordhash']}', '{$dataRow['telefono']}' ,
                        '{$dataRow['telefono_movil']}', '{$dataRow['primer_nombre']}', '{$dataRow['segundo_nombre']}', 
                        '{$dataRow['primer_apellido']}', '{$dataRow['segundo_apellido']}');";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }  
  

  
  public function addTutor($tesis,$usuariogrupo,$titulo_academico,$periodo,$tipo){
        
        if(empty($tesis))return;
        if(empty($usuariogrupo))return;

        $SQL ="INSERT INTO tbl_tutorestesis(
                        fk_periodo, fk_usuariogrupo, fk_datotesis, fk_estado, fk_tipo,renuncia,titulo_academico)
                VALUES ({$periodo}, {$usuariogrupo}, {$tesis}, 
                        (select distinct a.pk_atributo
                            from tbl_atributostipos		at
                            join tbl_atributos		a	on	a.fk_atributotipo = at.pk_atributotipo
                            where at.nombre ilike '%Estado Tutores%'
                            and a.valor ilike 'Por Aprobar'),{$tipo},false,'{$titulo_academico}');";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }
  
  

  

  public function addObservacionesTesis($id,$periodo,$usuariogrupo, $observaciones){
        
        if(empty($id))return;

        $SQL ="INSERT INTO tbl_pasostesis(
                      fk_datotesis, fk_periodo, fk_usuariogrupo, 
                      descripcion, observaciones, fecha)
              VALUES ({$id}, {$periodo}, {$usuariogrupo}, 
                      'observacion tesis', '{$observaciones}', (select current_date));";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }

  public function addEstadoDefensa($id,$estado){
        
        if(empty($id))return;

        $SQL ="INSERT INTO tbl_defensastesis(
                      fk_datotesis,fk_estado)
              VALUES ({$id},{$estado});";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }
  
  
  public function addEvaluadoresTesis($cod,$periodo,$evaluador,$tipo,$rol){
          
          if(empty($cod))return;
          if(empty($evaluador))return;

          $SQL ="INSERT INTO tbl_evaluadorestesis(
                        fk_datotesis,fk_periodo,fk_usuariogrupo, fk_tipo,fk_rol)
                VALUES ({$cod},{$periodo},{$evaluador},{$tipo},{$rol});";

          $results = $this->_db->query($SQL);
          return  $results->fetchAll();
    }

    public function addDefensaTesis($periodo, $estructura, $horario, $tesis, $fecha){
        
        if(empty($periodo))return;
        if(empty($estructura))return;
        if(empty($horario))return;
        if(empty($tesis))return;
        if(empty($fecha))return;


        $SQL ="INSERT INTO tbl_defensastesis(
                        fk_periodo, fk_estructura, fk_horario, fk_datotesis,fecha)
                VALUES ({$periodo}, {$estructura}, {$horario}, {$tesis},'{$fecha}');";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }

    public function addMencionTesis($cod,$mencion){
        if(empty($cod))return;
        if(empty($mencion))return;

        $SQL = "INSERT into tbl_mencionestesis (fk_datotesis,fk_mencion) values ({$cod},{$mencion})";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }


    public function addLineaInvestigacionAtributo($linea){
        
        

        $SQL = "INSERT INTO tbl_atributos(pk_atributo, fk_atributotipo, valor)
                    VALUES((select (pk_atributo + 1) as cod from tbl_atributos order by 1 desc limit 1), 
                            (select pk_atributotipo from tbl_atributostipos where nombre ilike '%Linea de Investigacion%'), 
                            '{$linea}');";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }

    public function addTemaInvestigacionAtributo($tema){
        
        

        $SQL = "INSERT INTO tbl_atributos(pk_atributo, fk_atributotipo, valor)
                    VALUES((select (pk_atributo + 1) as cod from tbl_atributos order by 1 desc limit 1), 
                            (select pk_atributotipo from tbl_atributostipos where nombre ilike '%Tema de Investigacion%'), 
                            '{$tema}');";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }    


    public function addLineaInvestigacion($linea,$tema,$escuela){
        
        if($tema != null){
          $filtro_tema = "{$tema}";
        }else{
          $filtro_tema = 'null';
        }
        

        $SQL = "INSERT INTO tbl_lineastemastesis(
                        pk_lineatematesis,fk_lineainvestigacion, fk_tema, fk_escuela)
                VALUES ((select pk_lineatematesis + 1 from tbl_lineastemastesis order by pk_lineatematesis desc limit 1),{$linea}, ".$filtro_tema.", {$escuela});";

                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }



    public function updateMencionTesis($cod,$mencion){

        if(empty($cod))return;
        if(empty($mencion))return;

        $SQL = "UPDATE tbl_mencionestesis set fk_mencion = {$mencion} where fk_datotesis = {$cod}";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }


    public function updateTesisBiblioteca($id,$cota,$sede,$escuela){

        $SQL = "UPDATE tbl_tesis 
                set 
                  cota = '{$cota}',
                  fk_sede = {$sede},
                  fk_escuela = {$escuela}

                where pk_tesis = {$id}";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }  


    public function updateTesisEscuela($fk_datotesis,$escuela){

        $SQL = "UPDATE tbl_tesis 
                set 
                  fk_escuela = {$escuela}

                where fk_datotesis = {$fk_datotesis}";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();   
    }        


    public function updateDefensaTesis($cod,$periodo,$estructura, $horario, $fecha){
        
        $SQL ="UPDATE tbl_defensastesis
                SET
                fk_estructura = {$estructura},
                fk_horario = {$horario},
                fecha = '{$fecha}'
                WHERE fk_datotesis = {$cod} 
                AND fk_periodo = {$periodo};";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }    

    public function updateTesis($id, $titulo, $lineatematesis) {

        if(empty($id))return;
        if(empty($lineatematesis))return;

        $SQL ="UPDATE tbl_datostesis
                set
                titulo = '{$titulo}',
                fk_lineatematesis = {$lineatematesis}
                ,fk_estado = (select distinct a.pk_atributo
                              from tbl_atributostipos     at 
                              join tbl_atributos        a       on    a.fk_atributotipo = at.pk_atributotipo
                              where at.nombre ilike 'Estado Tesis'
                              and a.valor ilike 'Por Aprobar')
                where pk_datotesis = {$id};";
                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }
    

      public function updateObservacionesTesis($id, $observaciones){
        
        if(empty($id))return;

        $SQL ="update tbl_pasostesis
                set
                observaciones = '{$observaciones}',
                fecha = (select current_date)
                where pk_pasotesis = {$id}";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }

      public function updateObservacionesTutor($id, $observaciones){
        
        if(empty($id))return;

        $SQL ="update tbl_tutorestesis
                set
                observaciones = '{$observaciones}'
                where pk_tutortesis = {$id}";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
  }

    public function updateRow($id, $data) {

        $data     = array_filter($data);

        $affected = $this->update($data, $this->_primary . ' = ' . (int)$id);

        return $affected;
    }

    public function updateUsuarioTutor($dataRow) {

        $SQL ="UPDATE tbl_usuarios
                SET nombre='{$dataRow['nombre']}', apellido='{$dataRow['apellido']}', 
                    direccion='{$dataRow['direccion']}', fechanacimiento='{$dataRow['fechanacimiento']}', correo='{$dataRow['correo']}', 
                    telefono='{$dataRow['telefono']}', telefono_movil='{$dataRow['telefono_movil']}', primer_nombre='{$dataRow['primer_nombre']}', 
                    segundo_nombre='{$dataRow['segundo_nombre']}', primer_apellido='{$dataRow['primer_apellido']}', segundo_apellido='{$dataRow['segundo_apellido']}'
              WHERE pk_usuario = {$dataRow['pk_usuario']};";
             
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }  

    public function updateTutor($datotesis,$periodo,$usuariogrupo,$titulo_academico) {

        $SQL ="UPDATE tbl_tutorestesis
                set
                fk_usuariogrupo = {$usuariogrupo},
                titulo_academico = '{$titulo_academico}',
                fk_periodo = {$periodo},
                fk_estado = (select distinct a.pk_atributo
                            from tbl_atributostipos   at
                            join tbl_atributos    a on  a.fk_atributotipo = at.pk_atributotipo
                            where at.nombre ilike '%Estado Tutores%'
                            and a.valor ilike 'Por Aprobar')
              where fk_datotesis = {$datotesis}
              and renuncia = false"; 
             
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }  
    
    public function updateEstadoTutor($id, $estado) {

        if(empty($id))return;

        $SQL ="update tbl_tutorestesis 
                set fk_estado = {$estado} 
                where pk_tutortesis = {$id};";
                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }


    public function updateTituloTutor($id, $titulo_academico) {

        if(empty($id))return;

        $SQL ="update tbl_tutorestesis 
                set titulo_academico = '{$titulo_academico}'
                where fk_datotesis = {$id};";
                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }


    public function updateEstadoDefensa($id, $estado) {

        if(empty($id))return;

        $SQL ="update tbl_defensastesis
                set fk_estado = {$estado} 
                where fk_datotesis = {$id};";
                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }
    
    
    public function updateTesistaCalificacion($recordacademico, $calificacion) {

        if(empty($recordacademico))return;
        

        if($calificacion < 15){
          $filtro_estado = ", fk_atributo = " .$this->getAtributoMateriaReprobada();
        }else{
          $filtro_estado = ", fk_atributo = ". $this->getAtributoMateriaCursada();
        }

        $SQL ="update tbl_recordsacademicos
                set
                calificacion = {$calificacion}
                ".$filtro_estado."
                where pk_recordacademico = {$recordacademico}";

                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }

    public function updateTesisCalificacion($cod,$calificacion) {

        if(empty($cod))return;
        if(empty($calificacion))return;


        $SQL ="update tbl_tesis
                set
                calificacion = {$calificacion}
                where fk_datotesis = {$cod}";
                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }    

    public function updateEvaluadortipo($id,$tipo){

      if(empty($id))return;

      $SQL = "update tbl_evaluadorestesis
              set fk_tipo = {$tipo}
              where pk_evaluadortesis in({$id})";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    } 


    public function updateEvaluadoresPeriodo($cod,$periodo,$tipo){

      if(empty($cod))return;
      if(empty($periodo))return;
      if(empty($tipo))return;

      $SQL = "update tbl_evaluadorestesis
              set fk_tipo = {$tipo}
              where fk_datotesis = {$cod}
              and fk_periodo =  {$periodo}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }

    public function updateEvaluadoresDeTesis($usuariogrupo, $pk_evaluadortesis){
       $SQL = "update tbl_evaluadorestesis
              set fk_usuariogrupo = {$usuariogrupo}
              where pk_evaluadortesis = {$pk_evaluadortesis}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }

    public function updateLineaInvestigacionAtributo($pk_atributo,$valor)
    {
      $SQL = "UPDATE tbl_atributos
              set 
                  valor = '{$valor}'
              where pk_atributo = {$pk_atributo}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();  
    }


    public function deleteTutor($id){
      
      if(empty($id))return;

      $SQL = "update tbl_tutorestesis set renuncia = true where pk_tutortesis={$id}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();

    }

    public function deleteTesisBiblioteca($id){
      
      if(empty($id))return;

      $SQL = "delete from tbl_tesis where fk_datotesis = {$id}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();

    }


    public function deleteTutorbyTesis($id){
      
      if(empty($id))return;

      $SQL = "update tbl_tutorestesis set renuncia = true where fk_datotesis = {$id}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();

    }  

    public function deleteRealTutorbyTesis($id){
      
      if(empty($id))return;

      $SQL = "delete from tbl_tutorestesis where fk_datotesis = {$id}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();

    }   

    public function deletePasostesis($cod){

      $SQL = "delete from tbl_pasostesis          where fk_datotesis = {$cod};";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }

    public function deleteMenciontesis($cod){

      $SQL = "delete from tbl_mencionestesis          where fk_datotesis = {$cod};";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }

    public function deleteEvaluadorestesis($cod){

      $SQL = "delete from tbl_evaluadorestesis    where fk_datotesis = {$cod};";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }

    public function deleteEvaluadoresSecundarios($cod, $periodo){

      $SQL = "delete from tbl_evaluadorestesis  where fk_datotesis = {$cod} and fk_periodo = {$periodo} and fk_tipo = " . $this->getAtributoEvaluadorSecundario();

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }

    public function deleteDefensastesis($cod){

      $SQL = "delete from tbl_defensastesis       where fk_datotesis = {$cod};";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }
     


    public function deleteAutorestesis($cod){

      $SQL = "update tbl_autorestesis set renuncia = true   where fk_datotesis = {$cod};";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    } 

    public function deleteRealAutorestesis($cod){

      $SQL = "delete from  tbl_autorestesis where fk_datotesis = {$cod};";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }

    public function deleteDatostesis($cod){

      $SQL = "delete from tbl_datostesis          where pk_datotesis = {$cod};";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    } 


    public function deletetemainvestigacion($tema){

      $SQL = "delete from tbl_lineastemastesis          where fk_tema = {$tema}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    } 

    public function deletelineainvestigacion($linea){

      $SQL = "delete from tbl_lineastemastesis          where fk_lineainvestigacion = {$linea}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              
    }                


    public function getTutortesis($usuariogrupo,$periodo,$pk_datotesis){
      
      

      $SQL = "select distinct pk_tutortesis
              from tbl_tutorestesis
              where fk_usuariogrupo =  {$usuariogrupo}
              and fk_periodo = {$periodo}
              and fk_datotesis = {$pk_datotesis} 
              order by 1 desc";

      return $this->_db->fetchOne($SQL);

    }     


    public function deleteTesista($id){
      
      if(empty($id))return;

      $SQL = "update tbl_autorestesis set renuncia = true where pk_autortesis={$id}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();

    }

    public function deleteRealTesista($id){
      
      if(empty($id))return;

      $SQL = "delete from  tbl_autorestesis where pk_autortesis={$id}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();

    }

    public function deleteAtributo($id){
      

      $SQL = "delete from tbl_atributos where pk_atributo = {$id}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();

    }



    public function getAutortesis($usuariogrupo,$periodo,$pk_datotesis){
      
      if($periodo == null){
        $filtro_periodo = ' ';
      }else{
        $filtro_periodo = ' and fk_periodo in ('.$periodo.') ';
      }

      $SQL = "select distinct pk_autortesis
              from tbl_autorestesis
              where fk_usuariogrupo =  {$usuariogrupo}
              ".$filtro_periodo."
              and fk_datotesis = {$pk_datotesis}
              ORDER BY 1 desc";
              
      return $this->_db->fetchOne($SQL);

    }    


    public function verificarTutor($id,$cedula){
      
      if(empty($id))return;
      if(empty($cedula))return;

      $SQL = "select tt.*
              from tbl_datostesis     dt 
              join tbl_tutorestesis   tt      on    tt.fk_datotesis     =   dt.pk_datotesis
              join tbl_autorestesis   at      on    at.fk_datotesis     =   dt.pk_datotesis
              join tbl_usuariosgrupos ug      on    ug.pk_usuariogrupo  =   at.fk_usuariogrupo
              where tt.pk_tutortesis = {$id}
              and ug.fk_usuario = {$cedula}";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();              

    }


    public function horas_academicas($hora){

      if($hora == null){
        $filtro_hora = '';
      }else{
        $filtro_hora = ' and pk_horario in ('.$hora.')';
      }

      $SQL = "select pk_horario, horainicio as hora, horafin 
              from tbl_horarios
              where pk_horario not in (11) " . $filtro_hora . " order by 2 asc";

      $results = $this->_db->query($SQL);
      return  $results->fetchAll(); 

    }

    public function dias_de_semana($dia){


      if(!empty($dia)){
        $filtro_dia = 'and pk_atributo = '.$dia;
      }else{
        $filtro_dia = '';
      }

      $SQL = "SELECT distinct pk_atributo, valor as dia
              FROM tbl_atributos
              where fk_atributotipo = 1
              and pk_atributo not in (893,6,7) 
              ".$filtro_dia." 
              order by 1 asc";


      $results = $this->_db->query($SQL);
      return  $results->fetchAll();               
    }

    public function horario_tesistas($periodo,$pk_datotesis,$dia){

      if(empty($periodo))return;
      if(empty($pk_datotesis))return;

      $SQL = "select distinct (ho.horainicio ||'-'||ho.horafin || '/' ||vd.dia)as id,
                                (e1.nombre||' - '||'Aula: '||e.nombre)as datos_materia,
                                (sqt.primer_apellido ||', '|| sqt.primer_nombre) as nombre_estudiante,
                                'TESISTA' as tipo
              from(
              select  distinct asig.pk_asignatura, u_est.primer_apellido, u_est.primer_nombre
                      from tbl_usuarios   u_est
                      join tbl_usuariosgrupos     ug      on  ug.fk_usuario           =   u_est.pk_usuario
                      join tbl_inscripciones      i       on  i.fk_usuariogrupo       =   ug.pk_usuariogrupo
                      join tbl_recordsacademicos  ra      on  ra.fk_inscripcion       =   i.pk_inscripcion
                      join tbl_asignaciones       asi     on  asi.pk_asignacion       =   ra.fk_asignacion
                      join tbl_asignaturas        asig    on  asig.pk_asignatura      =   ra.fk_asignatura
                      join tbl_pensums            pe      on  pe.pk_pensum            =   asig.fk_pensum
                      join tbl_autorestesis       at      on  at.fk_usuariogrupo      =   ug.pk_usuariogrupo    and at.renuncia = false
                      join tbl_datostesis         dt      on  dt.pk_datotesis         =   at.fk_datotesis  
                      join vw_dias                vd      on  vd.pk_atributo          =   asi.fk_dia
                      where i.fk_periodo  = {$periodo}
                      --and i.fk_atributo  = {$escuela}
                      --and pe.codigopropietario = {$pensum}
                      and dt.pk_datotesis = {$pk_datotesis}   
                      --and asi.fk_dia = {$dia}       
                      
                            
              ) as sqt 
              join tbl_asignaciones     asi     on  asi.fk_asignatura         = sqt.pk_asignatura
              join tbl_asignaturas      asig    on  asig.pk_asignatura        = sqt.pk_asignatura
              join tbl_pensums            pe      on  pe.pk_pensum            = asig.fk_pensum
              join tbl_horarios           ho      on  ho.pk_horario           = asi.fk_horario
              join vw_materias            vma     on  vma.pk_atributo         = asig.fk_materia
              join tbl_usuariosgrupos     ug_prof on  ug_prof.pk_usuariogrupo = asi.fk_usuariogrupo
              join tbl_usuarios           u_prof  on  u_prof.pk_usuario       = ug_prof.fk_usuario
              join vw_dias                vd      on  vd.pk_atributo          = asi.fk_dia
              join vw_semestres           vse     on  vse.pk_atributo         = asig.fk_semestre
              join tbl_estructuras        e       on  e.pk_estructura         = asi.fk_estructura
              join tbl_estructuras        e1      on  e1.pk_estructura        = e.fk_estructura
              join tbl_estructuras        e2      on  e2.pk_estructura        = e1.fk_estructura
              join vw_escuelas            ve      on  ve.pk_atributo          = pe.fk_escuela
              join vw_secciones           vsec    on  vsec.pk_atributo        = asi.fk_seccion
              where asi.fk_periodo = {$periodo}
              --and asi.fk_dia = {$dia} 
              --and pe.fk_escuela = {$escuela}
              --and pe.codigopropietario = {$pensum}
              and ho.pk_horario not in (
                11
              )
              and vma.pk_atributo not in (
                1394, --;PASANTIA I
                1395, --;PASANTIA II
                716, --;PASANTIA PROFESIONAL I
                717, --;PASANTIA PROFESIONAL II
                848, --;PASANTIA PROFESIONAL I y II
                1396,--;PASANTIAS I
                1397, --;PASANTIAS II
                718, --;PASANTIA SOCIAL I
                719, --;PASANTIA SOCIAL II
                913, --;PASANTIA SOCIAL I y II
                9897, --;SERVICIO COMUNITARIO
                9737, --;SERVICIO COMUNITARIO I
                9738, --;SERVICIO COMUNITARIO II
                1193,--;SERVICIO SOCIAL COMUNITARIO
                8219 --;TALLER DE SERVICIO COMUNITARIO


              )
              order by 3 desc

    ";        


      $results = $this->_db->query($SQL);
      return  $results->fetchAll();               
    }





public function horario_evaluadores($periodo,$pk_evaluadortesis,$dia){

      if(empty($periodo))return;

      if(count($pk_evaluadortesis)>= 2){
          foreach ($pk_evaluadortesis as $value) {
            if($value == end($pk_evaluadortesis)){
              $filtro_evaluador .= $value;  
            }else{
              $filtro_evaluador .= $value .', ';
            }
            
          }  
        }else{
          $filtro_evaluador = $pk_evaluadortesis;
        }
      
      $SQL = "select distinct (ho.horainicio ||'-'||ho.horafin ||'/'||vd.dia)as id,
                                              (e1.nombre||' - '||'Aula: '||e.nombre)as datos_materia,
                                              (u.primer_apellido ||', '|| u.primer_nombre) as nombre_profesor,
                                              'EVALUADOR' as tipo
              from tbl_usuarios           u
              join tbl_usuariosgrupos     ug        on  ug.fk_usuario       = u.pk_usuario
              join tbl_evaluadorestesis   et        on  et.fk_usuariogrupo  = ug.pk_usuariogrupo
              join tbl_asignaciones       asi       on  asi.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_asignaturas        asig      on  asig.pk_asignatura  = asi.fk_asignatura
              join tbl_pensums            pe        on  pe.pk_pensum        = asig.fk_pensum
              join tbl_horarios           ho        on  ho.pk_horario       = asi.fk_horario
              join vw_materias            vma       on  vma.pk_atributo     = asig.fk_materia
              join tbl_estructuras        e         on  e.pk_estructura     = asi.fk_estructura
              join tbl_estructuras        e1        on  e1.pk_estructura    = e.fk_estructura
              join tbl_estructuras        e2        on  e2.pk_estructura    = e1.fk_estructura
              join vw_semestres           vse       on  vse.pk_atributo     = asi.fk_semestre
              join vw_dias                vd        on  vd.pk_atributo      = asi.fk_dia
              join vw_escuelas            ve        on  ve.pk_atributo      = pe.fk_escuela
              join vw_secciones           vsec      on  vsec.pk_atributo    = asi.fk_seccion
              where asi.fk_periodo = {$periodo} 
              and et.pk_evaluadortesis in(".$filtro_evaluador.")  
              --and asi.fk_dia = {$dia}
              and vma.pk_atributo not in (
                              1394, --;PASANTIA I
                              1395, --;PASANTIA II
                              716, --;PASANTIA PROFESIONAL I
                              717, --;PASANTIA PROFESIONAL II
                              848, --;PASANTIA PROFESIONAL I y II
                              1396,--;PASANTIAS I
                              1397, --;PASANTIAS II
                              718, --;PASANTIA SOCIAL I
                              719, --;PASANTIA SOCIAL II
                              913, --;PASANTIA SOCIAL I y II
                              9897, --;SERVICIO COMUNITARIO
                              9737, --;SERVICIO COMUNITARIO I
                              9738, --;SERVICIO COMUNITARIO II
                              1193,--;SERVICIO SOCIAL COMUNITARIO
                              8219 --;TALLER DE SERVICIO COMUNITARIO


                )
    ";        
    

      $results = $this->_db->query($SQL);
      return  $results->fetchAll();               
    } 


    public function horario_clases($periodo, $edificio, $aula,$dia){

      $SQL = "select distinct (ho.horainicio ||'-'||ho.horafin || '/' ||vd.dia)as id,
                                              (e1.nombre||' - '||'Aula: '||e.nombre)as datos_materia,
                                              (u.primer_apellido ||', '|| u.primer_nombre) as nombre_profesor,
                                              'CLASES' as tipo
              from tbl_usuarios           u
              join tbl_usuariosgrupos     ug        on  ug.fk_usuario       = u.pk_usuario
              join tbl_asignaciones       asi       on  asi.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_asignaturas        asig      on  asig.pk_asignatura  = asi.fk_asignatura
              join tbl_pensums            pe        on  pe.pk_pensum        = asig.fk_pensum
              join tbl_horarios           ho        on  ho.pk_horario       = asi.fk_horario
              join vw_materias            vma       on  vma.pk_atributo     = asig.fk_materia
              join tbl_estructuras        e         on  e.pk_estructura     = asi.fk_estructura
              join tbl_estructuras        e1        on  e1.pk_estructura    = e.fk_estructura
              join tbl_estructuras        e2        on  e2.pk_estructura    = e1.fk_estructura
              join vw_semestres           vse       on  vse.pk_atributo     = asi.fk_semestre
              join vw_dias                vd        on  vd.pk_atributo      = asi.fk_dia
              join vw_escuelas            ve        on  ve.pk_atributo      = pe.fk_escuela
              join vw_secciones           vsec      on  vsec.pk_atributo    = asi.fk_seccion
              where asi.fk_periodo = {$periodo} 
              and e.pk_estructura = {$aula}
              and e1.pk_estructura = {$edificio}
              --and asi.fk_dia  = {$dia}
              and vma.pk_atributo not in (
                              1394, --;PASANTIA I
                              1395, --;PASANTIA II
                              716, --;PASANTIA PROFESIONAL I
                              717, --;PASANTIA PROFESIONAL II
                              848, --;PASANTIA PROFESIONAL I y II
                              1396,--;PASANTIAS I
                              1397, --;PASANTIAS II
                              718, --;PASANTIA SOCIAL I
                              719, --;PASANTIA SOCIAL II
                              913, --;PASANTIA SOCIAL I y II
                              9897, --;SERVICIO COMUNITARIO
                              9737, --;SERVICIO COMUNITARIO I
                              9738, --;SERVICIO COMUNITARIO II
                              1193,--;SERVICIO SOCIAL COMUNITARIO
                              8219 --;TALLER DE SERVICIO COMUNITARIO


                )";
    
      $results = $this->_db->query($SQL); 
      return  $results->fetchAll(); 
    }


    public function horario_tutores($periodo, $tutor){

      $SQL = "select distinct (ho.horainicio ||'-'||ho.horafin || '/' ||vd.dia)as id,
                                              (e1.nombre||' - '||'Aula: '||e.nombre)as datos_materia,
                                              (u.primer_apellido ||', '|| u.primer_nombre) as nombre_profesor,
                                              'TUTOR' as tipo
              from tbl_usuarios           u
              join tbl_usuariosgrupos     ug        on  ug.fk_usuario       = u.pk_usuario
              join tbl_asignaciones       asi       on  asi.fk_usuariogrupo = ug.pk_usuariogrupo
              join tbl_asignaturas        asig      on  asig.pk_asignatura  = asi.fk_asignatura
              join tbl_pensums            pe        on  pe.pk_pensum        = asig.fk_pensum
              join tbl_horarios           ho        on  ho.pk_horario       = asi.fk_horario
              join vw_materias            vma       on  vma.pk_atributo     = asig.fk_materia
              join tbl_estructuras        e         on  e.pk_estructura     = asi.fk_estructura
              join tbl_estructuras        e1        on  e1.pk_estructura    = e.fk_estructura
              join tbl_estructuras        e2        on  e2.pk_estructura    = e1.fk_estructura
              join vw_semestres           vse       on  vse.pk_atributo     = asi.fk_semestre
              join vw_dias                vd        on  vd.pk_atributo      = asi.fk_dia
              join vw_escuelas            ve        on  ve.pk_atributo      = pe.fk_escuela
              join vw_secciones           vsec      on  vsec.pk_atributo    = asi.fk_seccion
              where asi.fk_periodo = {$periodo}
              and u.pk_usuario = {$tutor} 
              and vma.pk_atributo not in (
                              1394, --;PASANTIA I
                              1395, --;PASANTIA II
                              716, --;PASANTIA PROFESIONAL I
                              717, --;PASANTIA PROFESIONAL II
                              848, --;PASANTIA PROFESIONAL I y II
                              1396,--;PASANTIAS I
                              1397, --;PASANTIAS II
                              718, --;PASANTIA SOCIAL I
                              719, --;PASANTIA SOCIAL II
                              913, --;PASANTIA SOCIAL I y II
                              9897, --;SERVICIO COMUNITARIO
                              9737, --;SERVICIO COMUNITARIO I
                              9738, --;SERVICIO COMUNITARIO II
                              1193,--;SERVICIO SOCIAL COMUNITARIO
                              8219 --;TALLER DE SERVICIO COMUNITARIO


                )";
    
      $results = $this->_db->query($SQL);
      return  $results->fetchAll(); 
    }


    public function horario_defensas($periodo,$aula,$fechainicial,$fechafinal,$horario,$cod){


      if(!empty($horario)){
        $filtro_horario = ' and dte.fk_horario = '.$horario;
      }else{
        $filtro_horario = ' ';
      }

      if(!empty($cod)){
        $filtro_cod = ' and dte.fk_datotesis not in ('.$cod.') ';
      }else{
        $filtro_cod = ' ';
      }

      if(!empty($aula)){
        $filtro_aula = ' and dte.fk_estructura =  '.$aula;  
      }else{
        $filtro_aula = ' '; 
      }


      $SQL = "select distinct (ho.horainicio ||'-'||ho.horafin)as id,
                  (e1.nombre||' - '||'Aula: '||e.nombre)as datos_defensa,
                  btrim(array_agg(u.primer_apellido ||', '||u.primer_nombre)::varchar,'{}"."') as tesistas,
                  (select btrim(array_agg(u_sub.primer_apellido ||', '||u_sub.primer_nombre)::varchar,'{}"."') as evaluadores
                  from tbl_datostesis         dt_sub
                  join tbl_evaluadorestesis   et_sub    on    et_sub.fk_datotesis     = dt_sub.pk_datotesis
                  join tbl_usuariosgrupos     ug_sub    on    ug_sub.pk_usuariogrupo  = et_sub.fk_usuariogrupo
                  join tbl_usuarios           u_sub     on    u_sub.pk_usuario        = ug_sub.fk_usuario
                  where dt_sub.pk_datotesis = dt.pk_datotesis
                  and et_sub.fk_tipo = ".$this->getAtributoEvaluadorPrincipal().") as evaluadores,
                  'DEFENSA'  as tipo,
                  dt.pk_datotesis,
                  ho.pk_horario,
                  dte.fecha

              from tbl_datostesis       dt 
              join tbl_autorestesis     at      on      at.fk_datotesis     =   dt.pk_datotesis     and at.renuncia = false
              join tbl_defensastesis    dte     on      dte.fk_datotesis    =   dt.pk_datotesis     
              join tbl_usuariosgrupos   ug      on      ug.pk_usuariogrupo  = at.fk_usuariogrupo
              join tbl_usuarios         u       on      u.pk_usuario        = ug.fk_usuario
              join tbl_horarios         ho      on      ho.pk_horario      = dte.fk_horario
              join tbl_estructuras      e       on      e.pk_estructura     =   dte.fk_estructura
              join tbl_estructuras      e1      on      e1.pk_estructura    =   e.fk_estructura
              join tbl_estructuras      e2      on      e2.pk_estructura    =   e1.fk_estructura
              where dte.fecha between '{$fechainicial}' and '{$fechafinal}'
              and dte.fk_periodo = {$periodo}
               ".$filtro_aula." 
              ".$filtro_horario." 
              ".$filtro_cod." 
              group by ho.horainicio,ho.horafin,e1.nombre,e.nombre,dt.pk_datotesis,ho.pk_horario,dte.fecha";

              
      $results = $this->_db->query($SQL);
      return  $results->fetchAll();
    }    

    public function horario_defensas_v2($periodo,$aula,$fecha,$horario,$cod){


      if(!empty($horario)){
        $filtro_horario = ' and dte.fk_horario = '.$horario;
      }else{
        $filtro_horario = ' ';
      }

      if(!empty($cod)){
        $filtro_cod = ' and dte.fk_datotesis not in ('.$cod.') ';
      }else{
        $filtro_cod = ' ';
      }

      $SQL = "select distinct (ho.horainicio ||'-'||ho.horafin)as id,
                  (e1.nombre||' - '||'Aula: '||e.nombre)as datos_defensa,
                  btrim(array_agg(u.primer_apellido ||', '||u.primer_nombre)::varchar,'{}"."') as tesistas,
                  (select btrim(array_agg(u_sub.primer_apellido ||', '||u_sub.primer_nombre)::varchar,'{}"."') as evaluadores
                  from tbl_datostesis         dt_sub
                  join tbl_evaluadorestesis   et_sub    on    et_sub.fk_datotesis     = dt_sub.pk_datotesis
                  join tbl_usuariosgrupos     ug_sub    on    ug_sub.pk_usuariogrupo  = et_sub.fk_usuariogrupo
                  join tbl_usuarios           u_sub     on    u_sub.pk_usuario        = ug_sub.fk_usuario
                  where dt_sub.pk_datotesis = dt.pk_datotesis
                  and et_sub.fk_tipo = ".$this->getAtributoEvaluadorPrincipal().") as evaluadores,
                  'DEFENSA'  as tipo,
                  dt.pk_datotesis,
                  ho.pk_horario,
                  dte.fecha

              from tbl_datostesis       dt 
              join tbl_autorestesis     at      on      at.fk_datotesis     =   dt.pk_datotesis     and at.renuncia = false
              join tbl_defensastesis    dte     on      dte.fk_datotesis    =   dt.pk_datotesis     
              join tbl_usuariosgrupos   ug      on      ug.pk_usuariogrupo  = at.fk_usuariogrupo
              join tbl_usuarios         u       on      u.pk_usuario        = ug.fk_usuario
              join tbl_horarios         ho      on      ho.pk_horario      = dte.fk_horario
              join tbl_estructuras      e       on      e.pk_estructura     =   dte.fk_estructura
              join tbl_estructuras      e1      on      e1.pk_estructura    =   e.fk_estructura
              join tbl_estructuras      e2      on      e2.pk_estructura    =   e1.fk_estructura
              where dte.fecha ilike '{$fecha}'
              and dte.fk_periodo = {$periodo}
              and dte.fk_estructura = {$aula}  
              ".$filtro_horario." 
              ".$filtro_cod." 
              group by ho.horainicio,ho.horafin,e1.nombre,e.nombre,dt.pk_datotesis,ho.pk_horario,dte.fecha";

              
      $results = $this->_db->query($SQL);
      return  $results->fetchAll();
    }


    public function getEdificios($sede = 7){
       
       

       $SQL = "select distinct e1.pk_estructura, e1.nombre as edificio
                from tbl_estructuras  e
                join tbl_estructuras  e1    on    e1.pk_estructura  = e.fk_estructura
                join tbl_estructuras  e2    on    e2.pk_estructura  = e1.fk_estructura
                where e2.pk_estructura = {$sede}
                order by 1 asc";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();

     }   

     public function getAulas($edificio){

      if(empty($edificio))return;
       
       $SQL = "select distinct e.pk_estructura, e.nombre as aula
                from tbl_estructuras  e
                join tbl_estructuras  e1    on    e1.pk_estructura  = e.fk_estructura
                join tbl_estructuras  e2    on    e2.pk_estructura  = e1.fk_estructura
                where e1.pk_estructura = {$edificio} 
                order by 2 asc";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();                

     }


     public function getAsignaciones($periodo,$horario,$dia,$estructura){

       $SQL = "select distinct pk_asignacion, 'clases' as alias
                from tbl_asignaciones
                where fk_periodo = {$periodo}
                and fk_horario = {$horario}
                and fk_dia = {$dia}
                and fk_estructura = {$estructura}";
                
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();                

     }


     public function getDefensaDetallada($periodo,$horario,$fecha,$estructura,$cod){

       $SQL = "select distinct pk_defensatesis, 'defensa' as alias
                from tbl_defensastesis
                where fk_periodo = {$periodo}
                and fk_horario = {$horario}
                and fk_estructura = {$estructura}
                and fecha ilike '{$fecha}' 
                and fk_datotesis not in ({$cod}) ";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();                

     }     

     public function getCountEvalPrincipal($cod,$periodo){

      $SQL = "select count(pk_evaluadortesis)
              from tbl_evaluadorestesis
              where fk_datotesis = {$cod} 
              and fk_periodo = {$periodo}
              and fk_tipo = " . $this->getAtributoEvaluadorPrincipal();

      return $this->_db->fetchOne($SQL);  
     }


     public function getCountEval($cod,$periodo){

      $SQL = "select count(pk_evaluadortesis)
              from tbl_evaluadorestesis
              where fk_datotesis = {$cod} 
              and fk_periodo = {$periodo}";

      return $this->_db->fetchOne($SQL);  
     }


     public function fechaValida($fecha,$periodo){

      $SQL = "select distinct pk_periodo
              from tbl_periodos
              where '{$fecha}' between fechainicio and fechafin
              and pk_periodo = {$periodo}";

      return $this->_db->fetchOne($SQL);
     }

     public function getEstructura($pk_estructura){

       $SQL = "select distinct e.pk_estructura, e.nombre, e1.pk_estructura as pk_estructura_1, e1.nombre as nombre_1
                from tbl_estructuras  e
                join tbl_estructuras  e1    on    e1.pk_estructura = e.fk_estructura 
                where e.pk_estructura = {$pk_estructura}";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
     }


     public function getDefensa($cod,$periodo){

       $SQL = "select *
                from tbl_defensastesis
                where fk_datotesis = {$cod}
                and fk_periodo = {$periodo}";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
     }

     public function getCountTutorPeriodo($periodo,$tutor){

        $SQL = "select count(distinct tt.pk_tutortesis)
                from tbl_tutorestesis           tt
                join tbl_usuariosgrupos         ug          on          ug.pk_usuariogrupo  =   tt.fk_usuariogrupo
                where tt.fk_periodo = {$periodo}
                and ug.fk_usuario = {$tutor}
                and tt.renuncia = false
                and tt.fk_estado = ". $this->getEstadoTutorAprobado();


        return $this->_db->fetchOne($SQL); 

     }

     public function getCountEvaluadorPeriodo($periodo,$evaluador){

        $SQL = "select count(distinct et.pk_evaluadortesis)
                from tbl_evaluadorestesis       et
                join tbl_usuariosgrupos         ug          on          ug.pk_usuariogrupo  =   et.fk_usuariogrupo
                where et.fk_periodo = {$periodo}
                and ug.pk_usuariogrupo = {$evaluador}
                and et.fk_tipo = ". $this->getAtributoEvaluadorPrincipal();
                
        return $this->_db->fetchOne($SQL); 

     }   


     public function ultimoTesista($cod){

      $SQL = "select count(distinct at.pk_autortesis)
              from tbl_autorestesis           at      
              join tbl_usuariosgrupos         ug      on      ug.pk_usuariogrupo      =   at.fk_usuariogrupo and at.renuncia = false
              where at.fk_datotesis = {$cod}
              and at.renuncia = false";


      return $this->_db->fetchOne($SQL); 
     }  


    public function getMenciones(){

       $SQL = "select a.pk_atributo, a.valor as mencion
              from tbl_atributos              a       
              join tbl_atributostipos         at              on              at.pk_atributotipo = a.fk_atributotipo
              where at.nombre ilike '%menciones tesis%'
              order by 1 asc";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
    }


    public function getMencionTesis($cod){

      $SQL = " select count(distinct pk_menciontesis)
               from tbl_mencionestesis 
               where fk_datotesis in ($cod)";

      return $this->_db->fetchOne($SQL); 
     }


     public function getEstadosTesis(){
       
       $SQL = "select distinct a.pk_atributo, a.valor
              from tbl_atributostipos     at 
              join tbl_atributos        a       on    a.fk_atributotipo = at.pk_atributotipo
              where at.nombre ilike 'Estado Tesis'";


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();              
     }


     public function getPkTutor($cod){
      
        $SQL = "select distinct tt.pk_tutortesis
                from tbl_datostesis     dt 
                join tbl_tutorestesis   tt        on      tt.fk_datotesis = dt.pk_datotesis
                where dt.pk_datotesis = {$cod}
                and tt.renuncia = false";

        return $this->_db->fetchOne($SQL); 

     }

     public function getAtributo($atributo){

       $SQL = "select *
              from tbl_atributos
              where valor ilike '{$atributo}'";

        return $this->_db->fetchOne($SQL);

     }


     public function getAtributoPK($pk_atributo){

       $SQL = "select *
              from tbl_atributos
              where pk_atributo = {$pk_atributo}";

        $results = $this->_db->query($SQL);
        return  $results->fetchAll();

     }     

     public function getGrupoAutoridad(){
      
        $SQL = "select distinct pk_atributo
                from vw_grupos
                where grupo ilike 'Autoridad'
                order by 1 desc limit 1";

        return $this->_db->fetchOne($SQL);
     }


     public function getMateriaActual($cedula){

       $SQL = "SELECT distinct ins.fk_periodo, vma.pk_atributo,vma.materia
                    FROM tbl_inscripciones       ins
                    JOIN tbl_recordsacademicos   ra  ON ra.fk_inscripcion  =     ins.pk_inscripcion
                    JOIN tbl_asignaturas         asi ON asi.pk_asignatura  =     ra.fk_asignatura
                    JOIN vw_materias             vma ON vma.pk_atributo    =     asi.fk_materia 
                    JOIN tbl_pensums             pe  ON pe.pk_pensum       =     asi.fk_pensum
                    JOIN tbl_usuariosgrupos      ug  ON ug.pk_usuariogrupo =     ins.fk_usuariogrupo
                    WHERE ug.fk_usuario = {$cedula}
                    and asi.fk_materia IN (
                            519, --diseño de tesis
                            10621, --innovacion e investigacion
                            9719, --seminario de trabajo de grado
                            830, --tesis de grado i
                            9723, --trabajo de grado I
                            834, --tesis de grado ii
                            9724 --trabajo de grado II                            
                    )
                    and ra.fk_atributo not in (
                      863, --retirada
                      1699 --reprobada  
                    ) 
                    order by ins.fk_periodo desc limit 1";
                    
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();

     }  

     public function getCountDefensaEvaluador($cedula){
     
        $SQL = "SELECT count(*) 
                from tbl_datostesis dt 
                full outer join tbl_defensastesis deft on deft.fk_datotesis = dt.pk_datotesis 
                full outer join tbl_evaluadorestesis et on et.fk_usuariogrupo = dt.pk_datotesis 
                full outer join tbl_usuariosgrupos ug on ug.pk_usuariogrupo = et.fk_usuariogrupo 
                where ug.fk_usuario = {$cedula} 
                and et.fk_periodo = ". $this->getPeriodoActual().
                " and et.fk_tipo = ". $this->getAtributoEvaluadorPrincipal();
                
       return $this->_db->fetchOne($SQL);

     }


     public function getEvaluadorTesisparaDefensa($periodo,$evaluador){


       $SQL = "SELECT   sqt.pk_datotesis,
                        sqt.titulo, 
                        btrim(sqt.cedula::varchar,'{}.') as cedula,     
                        btrim(sqt.autor::varchar,'{}.') as autor,
                        btrim(sqt.tutor::varchar,'{}.') as tutor,
                        sqt.fecha,
                        sqt.horario,
                        sqt.aula,
                        sqt.edificio,
                        (u.nombre || ', ' || u.apellido) as otro_evaluador
                from(
                    select distinct dt.pk_datotesis,
                                    dt.titulo,
                                    (select array_agg(sqt_sub.pk_usuario)
                                    from(
                                    select u_sub.pk_usuario
                                    from tbl_usuariosgrupos ug_sub
                                    join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo and at_sub.renuncia = false
                                    join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                                    where at_sub.fk_datotesis = dt.pk_datotesis
                                    order by 1 asc)as sqt_sub) as cedula, 
                                    (select array_agg(sqt_sub.nombre)
                                    from(
                                    select u_sub.pk_usuario,(u_sub.primer_apellido ||' '||u_sub.primer_nombre) as nombre
                                    from tbl_usuariosgrupos ug_sub
                                    join tbl_autorestesis at_sub  on  at_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo and  at_sub.renuncia = false
                                    join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                                    where at_sub.fk_datotesis = dt.pk_datotesis
                                    order by 1 asc)as sqt_sub) as autor,
                                    (select array_agg(sqt_sub.nombre)
                                    from(
                                    select u_sub.pk_usuario,(u_sub.primer_apellido ||' '||u_sub.primer_nombre) as nombre
                                    from tbl_usuariosgrupos ug_sub
                                    join tbl_tutorestesis tt_sub  on  tt_sub.fk_usuariogrupo = ug_sub.pk_usuariogrupo and  tt_sub.renuncia = false
                                    join tbl_usuarios u_sub on  u_sub.pk_usuario = ug_sub.fk_usuario
                                    where tt_sub.fk_datotesis = dt.pk_datotesis
                                    order by 1 asc)as sqt_sub) as tutor, 
                                    deft.fecha, 
                                    (ho.horainicio || ' - '|| ho.horafin) as horario,
                                    e1.nombre as aula,
                                    e2.nombre as edificio

                    from tbl_datostesis             dt 
                    join tbl_defensastesis          deft        on          deft.fk_datotesis   =       dt.pk_datotesis
                    join tbl_horarios               ho          on          ho.pk_horario       =       deft.fk_horario
                    join tbl_estructuras            e1          on          e1.pk_estructura    =       deft.fk_estructura
                    join tbl_estructuras            e2          on          e2.pk_estructura    =       e1.fk_estructura
                    join tbl_estructuras            e3          on          e3.pk_estructura    =       e2.fk_estructura
                    join tbl_evaluadorestesis       et          on          et.fk_datotesis     =       dt.pk_datotesis
                    join tbl_usuariosgrupos         ug          on          ug.pk_usuariogrupo  =       et.fk_usuariogrupo
                    where ug.fk_usuario = {$evaluador}
                    and et.fk_periodo  = {$periodo}
                    and et.fk_tipo = (select distinct a.pk_atributo
                                        from tbl_atributos      a 
                                        join tbl_atributostipos at      on      at.pk_atributotipo = a.fk_atributotipo
                                        where at.nombre ilike 'Tipo Evaluadores'
                                        and a.valor ilike 'Principal')
                ) as sqt
                join tbl_evaluadorestesis     et      on      et.fk_datotesis     = sqt.pk_datotesis     and et.fk_tipo = ".$this->getAtributoEvaluadorPrincipal()."
                join tbl_usuariosgrupos       ug      on      ug.pk_usuariogrupo  = et.fk_usuariogrupo
                join tbl_usuarios             u       on      u.pk_usuario        = ug.fk_usuario
                where u.pk_usuario not in ({$evaluador})";
                


        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
     }


     public function getTutorTesisparaDefensa($periodo,$cedula){


       $SQL = "SELECT DISTINCT u.nombre, 
                               u.apellido,
                               m.materia,
                               array_to_string(ARRAY_AGG(u2.nombre ||' '||u2.apellido),', ') as tesistas,
                               dt.titulo,
                               tat.valor as estado

                FROM tbl_usuarios u
                JOIN tbl_usuariosgrupos ug ON ug.fk_usuario = u.pk_usuario
                JOIN tbl_tutorestesis tt ON tt.fk_usuariogrupo = ug.pk_usuariogrupo
                JOIN tbl_datostesis dt ON dt.pk_datotesis = tt.fk_datotesis
                JOIN tbl_autorestesis aut ON aut.fk_datotesis = dt.pk_datotesis
                JOIN tbl_usuariosgrupos ug2 ON ug2.pk_usuariogrupo = aut.fk_usuariogrupo
                JOIN tbl_usuarios u2 ON u2.pk_usuario = ug2.fk_usuario
                JOIN tbl_inscripciones i ON i.fk_usuariogrupo = ug2.pk_usuariogrupo
                JOIN tbl_recordsacademicos ra ON ra.fk_inscripcion = i.pk_inscripcion
                JOIN tbl_asignaturas asi ON asi.pk_asignatura = ra.fk_asignatura
                JOIN vw_materias m ON m.pk_atributo = asi.fk_materia
                JOIN tbl_atributos tat on tt.fk_estado = tat.pk_atributo
                WHERE u.pk_usuario = {$cedula}
                AND i.fk_periodo = {$periodo}
                AND tt.renuncia = false
                AND aut.renuncia = false
                AND tt.fk_estado in (19969,19970,19971)
                AND asi.fk_materia IN (9723,9724,1412,830,834,9719,1384,10621,1385)
                GROUP BY u.nombre, u.apellido, m.materia,dt.titulo,tat.valor
                ORDER BY m.materia";
  
    //var_dump($SQL);die;
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
     }



     public function getUltimoPensum($cedula){


       $SQL = "SELECT i.fk_periodo, i.fk_pensum,p.nombre,escuela
                from tbl_inscripciones  i
                join tbl_usuariosgrupos ug    on    ug.pk_usuariogrupo  = i.fk_usuariogrupo
                join tbl_pensums p on p.pk_pensum = i.fk_pensum
                join vw_escuelas esc on esc.pk_atributo = p.fk_escuela
                where ug.fk_usuario = {$cedula}
                order by i.fk_periodo desc limit 1";
  
    
        $results = $this->_db->query($SQL);
        return  $results->fetchAll();
     }


     public function getPensumCodigopropietario($pk_pensum){


       $SQL = "SELECT DISTINCT codigopropietario
              from tbl_pensums
              where pk_pensum = {$pk_pensum}";
  
    
        return $this->_db->fetchOne($SQL);

     }


     public function getAtributoEvaluadorTecnico(){

       $SQL = "SELECT distinct a.pk_atributo
                from tbl_atributos      a 
                join tbl_atributostipos at    on    at.pk_Atributotipo    =   a.fk_atributotipo
                where at.nombre ilike 'Rol Evaluadores'
                and a.valor ilike 'Tecnico'";

        return $this->_db->fetchOne($SQL);
     }


     public function getAtributoEvaluadorInvestigacion(){

       $SQL = "SELECT distinct a.pk_atributo
                from tbl_atributos      a 
                join tbl_atributostipos at    on    at.pk_Atributotipo    =   a.fk_atributotipo
                where at.nombre ilike 'Rol Evaluadores'
                and a.valor ilike 'De Investigacion'";

        return $this->_db->fetchOne($SQL);
     }     



}
?>
