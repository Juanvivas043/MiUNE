<?php 

class Forms_Bibliotecatesis extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
  public function init() {
             
     $this->setMethod('post');
     $this->setName('bibliotecatesis');
	$this->setOptions(array('escape' => true));
     $this->setAttrib('enctype', 'multipart/form-data');

        $SwapBytes_Jquery = new SwapBytes_Jquery();
         
        $changecota    = "$.getJSON(urlAjax + 'exists/data/' + escape($('#bibliotecatesis').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
        $addAutor      = "$.getJSON(urlAjax + 'autoradd/data/' + escape($('#bibliotecatesis').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
        $addJurado     = "$.getJSON(urlAjax + 'juradoadd/data/' + escape($('#bibliotecatesis').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
        $changeEscuela = $SwapBytes_Jquery->fillSelect('fk_autor','cescuela',array('escuela'=>'fk_escuela'));
        $deleteAutores = "$.getJSON(urlAjax + 'deleteallautores/data/' + escape($('#bibliotecatesis').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";      
                
      $id   = new Zend_Form_Element_Hidden('id');
      
      $id->removeDecorator('label')
         ->removeDecorator('HtmlTag');


      $fk_sede = new Zend_Form_Element_Select('fk_sede'); 
      $fk_sede->setLabel('Sede')
              ->setRegisterInArrayValidator(false);  

     $fk_periodo = new Zend_Form_Element_Select('fk_periodo');
     $fk_periodo->setLabel('Periodo: ')
             ->setAttrib('style', 'width: 175px');
        
      $cota = new Zend_Form_Element_Text('cota');
      $cota->setLabel('Cota :')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(4, 50))
            ->setAttrib('size', 30)
            ->setAttrib('maxlength', 50)
            ->setAttrib('onchange', $changecota);

      $serialrecurso = new Zend_Form_Element_Text('serialrecurso');
      $serialrecurso->setLabel('Serial :')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(4, 20))
            ->setAttrib('size', 30)
            ->setAttrib('maxlength', 50);
      
                
        $titulo = new Zend_Form_Element_Textarea('titulo');
        $titulo->setLabel('Titulo:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('cols', 35)
            ->setAttrib('rows', 4)    
            ->setAttrib('maxlength', 300);

       $resumen = new Zend_Form_Element_Textarea('resumen');
       $resumen->setLabel('Resumen :')
           ->addFilter('StripTags')
           ->addFilter('StringTrim')   
           ->setAttrib('cols', 35)
           ->setAttrib('rows', 4)  
           ->setAttrib('maxlength', 500);

       $palabrasclaves = new Zend_Form_Element_Textarea('palabrasclaves');
       $palabrasclaves->setLabel('Palabras claves:')
           ->addFilter('StripTags')
           ->addFilter('StringTrim')   
           ->setAttrib('cols', 35)
           ->setAttrib('rows', 4)  
           ->setAttrib('maxlength', 150);
       

           $autor = new Zend_Form_Element_Select('fk_autor'); 
           $autor->setLabel('Autor:')
            ->setAttrib('style', 'width: 300px')
            ->setRegisterInArrayValidator(false)
            ->addDecorator('HtmlTag', array('tag' => 'dd',
                     'id'  => 'fk_autor' . '-element'))
                      ->addDecorator('Label', array('tag' => 'dt')); 
           
           $agregar_autor = new Zend_Form_Element_Button('agregar_autor');
           $agregar_autor->setLabel('+') 
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $addAutor);
    
           
           $jurado = new Zend_Form_Element_Select('fk_jurado'); 
           $jurado->setLabel('Jurado :')
                     ->setAttrib('style', 'width: 300px')
                     ->addDecorator('HtmlTag', array('tag' => 'dd',
                     'id'  => 'fk_autor' . '-element'))
                      ->addDecorator('Label', array('tag' => 'dt')); 
           
           $agregar_jurado = new Zend_Form_Element_Button('agregar_jurado');
           $agregar_jurado->setLabel('+') 
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $addJurado);
          
           $tutor = new Zend_Form_Element_Select('fk_tutor'); 
           $tutor->setLabel('Tutor:')
                     ->setAttrib('style', 'width: 300px');
       
       $institucion = new Zend_Form_Element_Select('fk_institucion'); 
       $institucion->setLabel('Institucion:') 
                 ->setAttrib('style', 'width: 300px'); 
       
       $escuela = new Zend_Form_Element_Select('fk_escuela'); 
       $escuela->setLabel('Escuela :')
                 ->setAttrib('style', 'width: 300px')
                 // ->setAttrib('onchange', $changeEscuela)
                 ->setAttrib('onclick', $deleteAutores);

       $fk_tiporecurso = new Zend_Form_Element_Select('fk_tiporecurso'); 
       $fk_tiporecurso->setLabel('Tipo Recurso:') 
                 ->setAttrib('style', 'width: 300px'); 
       
       
       $calificacion = new Zend_Form_Element_Select('fk_calificacion'); 
       $calificacion->setLabel('Calificacion :')
                 ->setAttrib('style', 'width: 300px');
     
       $publicacion = new Zend_Form_Element_Select('fk_publicado'); 
       $publicacion->setLabel('Publicacion:')
                   ->setAttrib('style', 'width: 300px');
       
       $pagina = new Zend_Form_Element_Text('pagina');
       $pagina->setLabel('Pagina:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80);
       
       
       $ubicacion = new Zend_Form_Element_Text('ubicacion');
       $ubicacion->setLabel('Ubicacion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80); 
       
       $observacion = new Zend_Form_Element_Text('observacion');
       $observacion->setLabel('Observacion:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80); 

       $archivo = new Zend_Form_Element_Text('archivo');
       $archivo->setLabel('Archivo:')
               ->setAttrib('id', 'archivo')
               ->setAttrib('style', 'width: 254px');
       
     
       $boton_subir = new Zend_Form_Element_Button('boton_subir');
       $boton_subir->setLabel('Subir Archivo')  // Establece la etiqueta del botón
             ->setAttrib('id', 'boton')  // Establece un ID para el botón
             ->setAttrib('class', 'btn btn-primary')  // Establece una clase para los estilos CSS
             ->setAttrib('type', 'button');  // Establece el tipo del botón
               
        
       $this->addElements(array($id,
                                $fk_sede,
                                $fk_periodo,
                                $serialrecurso,
                                 $titulo,
                                 $resumen,
                                 $palabrasclaves,
                                 $autor,
                                $agregar_autor,
                                $jurado,
                                $agregar_jurado,
                                $tutor,
                                 $escuela, 
                                 $fk_tiporecurso,  
                                 $institucion,
                                 $calificacion,
                                 $publicacion,
                                 $ubicacion,
                                 $pagina,
                                 $observacion,
                                 $cota,
                                 $archivo, 
                                 $boton_subir
                                ));

    }

    
    public function addAutor($id,$pos){
         
           
           // super variables
           $autortxt = 'fk_autor'.$id;
           $autor = 'fk_autor'.$id;
           $eliminar_autortxt = 'eliminar_autor'.$id;
           $eliminar_autor = 'eliminar_autor'.$id;
           
           
           $eli_autor = "$.getJSON(urlAjax + 'deleteautor/id/'+$id , function(data){executeCmdsFromJSON(data)});";
           
           $$autor = new Zend_Form_Element_Select($autortxt); 
           $$autor->setLabel('')
           ->setAttrib('style', 'width: 300px')
           ->setOrder($pos)
           ->addDecorator('HtmlTag', array('tag' => 'dd',
                    'id'  => $autortxt . '-element'))
           ->addDecorator('Label', array('tag' => 'dt'));
            
            $$eliminar_autor = new Zend_Form_Element_Button($eliminar_autortxt);
            $$eliminar_autor->setLabel('-') 
            ->setOrder($pos+1)        
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $eli_autor); 
            
            
             $this->addElements(array($$autor,
                                      $$eliminar_autor
                                )); 
             
            
          return $this;
            
   }
   
     
      public function addJurado($id,$pos){
          // super variables
           $juradotxt = 'fk_jurado'.$id;
           $jurado = 'fk_jurado'.$id;
           $eliminar_juradotxt = 'eliminar_jurado'.$id;
           $eliminar_jurado = 'eliminar_materia'.$id;
           
            $eli_jurado = "$.getJSON(urlAjax + 'deletejurado/id/'+$id , function(data){executeCmdsFromJSON(data)});";
           
           $$jurado = new Zend_Form_Element_Select($juradotxt); 
           $$jurado->setLabel('')
           ->setOrder($pos)     
           ->setAttrib('style', 'width: 300px')        
           ->addDecorator('HtmlTag', array('tag' => 'dd',
                    'id'  => $jurado . '-element'))
           ->addDecorator('Label', array('tag' => 'dt'));
           
            $$eliminar_jurado = new Zend_Form_Element_Button($eliminar_juradotxt);
            $$eliminar_jurado->setLabel('-') 
            ->setOrder($pos+1)        
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $eli_jurado); 
           
            $this->addElements(array($$jurado,
                                      $$eliminar_jurado
                                )); 
             
           
           
      } 
}


