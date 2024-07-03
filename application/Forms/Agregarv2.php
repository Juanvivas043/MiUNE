<?php 

class Forms_Agregarv2 extends Zend_Form {
    
    public function init() {
          
        $this->setMethod('post');
        $this->setName('Agregarv2');
	$this->setOptions(array('escape' => true));
        $SwapBytes_Jquery = new SwapBytes_Jquery();
         
         $changePais = $SwapBytes_Jquery->fillSelect('fk_ciudad','cpais',array('pais'=>'fk_pais'));
         $addAutor = "$.getJSON(urlAjax + 'autoradd/data/' + escape($('#Agregarv2').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
         $addMateria = "$.getJSON(urlAjax + 'materiaadd/data/' + escape($('#Agregarv2').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
         $changeCota = "$.getJSON(urlAjax + 'exists/data/' + escape($('#Agregarv2').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
         $auto_completar = "$.getJSON(urlAjax + 'auto_autor/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
         $auto_completar_mat = "$.getJSON(urlAjax + 'auto_materia/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
         $auto_completar_editorial = "$.getJSON(urlAjax + 'auto_editorial/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";


        $auto_completar_pais = "$.getJSON(urlAjax + 'auto_pais/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
        $auto_completar_ciudad = "$.getJSON(urlAjax + 'auto_ciudad/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";


         $guardar_autor = "$.getJSON(urlAjax + 'guardar_autor/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
         $guardar_materia = "$.getJSON(urlAjax + 'guardar_materia/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
         $guardar_editorial = "$.getJSON(urlAjax + 'guardar_editorial/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";


        $guardar_pais = "$.getJSON(urlAjax + 'guardar_pais/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
        $guardar_ciudad = "$.getJSON(urlAjax + 'guardar_ciudad/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";

        // $eli_autor = "$.getJSON(urlAjax + 'deleteautor/id//data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
         
         $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
                 
          $fk_sede = new Zend_Form_Element_Select('fk_sede'); 
          $fk_sede->setLabel('Sede');      
                
         $cota = new Zend_Form_Element_Text('cota');
         $cota->setLabel('Cota')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('StringLength', true, array(4, 50))
            ->setAttrib('size', 30)
            ->setAttrib('maxlength', 50)
            ->setAttrib('onchange', $changeCota);
        
         $titulo = new Zend_Form_Element_Text('titulo');
         $titulo->setLabel('Titulo')
            ->setAttrib('style', 'width: 405px')
            ->setRequired(true);
         
        $fk_autor = new Zend_Form_Element_Text('fk_autor'); 
        $fk_autor->setLabel('Autor:')
                 ->setAttrib('style', 'width: 405px')
                 ->setAttrib('style', 'width: 200px')
                 ->setRequired(false)
                 ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_autor' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dt'))
                  ->setAttrib('onKeyUp', $auto_completar)
                  ->setAttrib('onchange',$guardar_autor) ;
                  
        
        $fk_principal = new Zend_Form_Element_Select('fk_principal'); 
        $fk_principal->setLabel('')
                 ->setAttrib('style', 'width: 405px')
                 ->setAttrib('style', 'width: 200px')
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_principal' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dd'));  

        $agregar_autor = new Zend_Form_Element_Button('agregar_autor');
        $agregar_autor->setLabel('+') 
        ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
        ->setAttrib('onclick', $addAutor); 
                  
       /* $eliminar_autor = new Zend_Form_Element_Button('eliminar_autor');
        $eliminar_autor->setLabel('-')     
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $eli_autor); */
        
        $fk_editorial = new Zend_Form_Element_Text('fk_editorial'); 
        $fk_editorial->setLabel('Editorial')
                  ->setRequired(true)
                  ->setAttrib('style', 'width: 405px')
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_editorial' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dt'))
                ->setAttrib('onKeyUp', $auto_completar_editorial)
                ->setAttrib('onchange',$guardar_editorial) ;
                
        
        $fk_materia = new Zend_Form_Element_Text('fk_materia'); 
        $fk_materia->setLabel('Materia')
                  ->setRequired(false)
                  ->setAttrib('style', 'width: 405px')
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_materia' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dt'))
                  ->setAttrib('onKeyUp', $auto_completar_mat)
                  ->setAttrib('onchange',$guardar_materia);  
        
        $agregar_materia = new Zend_Form_Element_Button('agregar_materia');
        $agregar_materia->setLabel('+') 
        ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
        ->setAttrib('onclick', $addMateria);
        
        
        
        
        
        // $pais = new Zend_Form_Element_Select('fk_pais'); 
        // $pais->setLabel('Pais')
        // ->setAttrib('onclick', $changePais);


        $pais = new Zend_Form_Element_Text('fk_pais'); 
        $pais->setLabel('Pais')
                  ->setRequired(false)
                  ->setAttrib('style', 'width: 405px')
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_pais' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dt'))
                  ->setAttrib('onKeyUp', $auto_completar_pais)
                  ->setAttrib('onchange',$guardar_pais);         
        
        // $ciudad = new Zend_Form_Element_Select('fk_ciudad'); 
        // $ciudad->setLabel('Ciudad');

        $ciudad = new Zend_Form_Element_Text('fk_ciudad'); 
        $ciudad->setLabel('Ciudad')
                  ->setRequired(false)
                  ->setAttrib('style', 'width: 405px')
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_ciudad' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dt'))
                  ->setAttrib('onKeyUp', $auto_completar_ciudad)
                  ->setAttrib('onblur',$guardar_ciudad);  

        
        $ano = new Zend_Form_Element_Select('fk_ano'); 
        $ano->setLabel('Año');
        
        $edicion = new Zend_Form_Element_Text('edicion'); 
        $edicion->setLabel('Edicion');
        
        $pagina = new Zend_Form_Element_Text('pagina');
        $pagina->setLabel('Pagina');
        
        $nota = new Zend_Form_Element_Text('nota');
        $nota->setLabel('Nota');
        
        $ejemplar = new Zend_Form_Element_Text('ejemplar');
        $ejemplar->setLabel('Ejemplares');
        
        $volumen = new Zend_Form_Element_Text('volumen');
        $volumen->setLabel('Volumen');
        
        $coleccion = new Zend_Form_Element_Text('coleccion');
        $coleccion->setLabel('Coleccion');
        
        $numero = new Zend_Form_Element_Text('numero');
        $numero->setLabel('Numero');
        
          $this->addElements(array(
                                   $id,
                                   $fk_sede,
                                   $titulo,
                                   $fk_autor,
                                   $fk_principal,
                                   $agregar_autor,
                                   //$eliminar_autor,
                                   $fk_editorial,
                                   $edicion,
                                   $pais,
                                   $ciudad,
                                   $ano,
                                   $pagina,
                                   $nota,
                                   $ejemplar,
                                   $volumen,
                                   $coleccion,
                                   $numero,
                                   $fk_materia,
                                   $agregar_materia,
                                   $cota,
                                )); 
       
     }    
  
    


     public function addAutor($id,$pos){
         
           // super variables
           $autortxt = 'fk_autor'.$id;
           $autor = 'fk_autor'.$id;
           $principaltxt = 'fk_principal'.$id;
           $principal = 'fk_principal'.$id;
           $eliminar_autortxt = 'eliminar_autor'.$id;
           $eliminar_autor = 'eliminar_autor'.$id;
           //$agregar_autortxt = 'agregar_autor'.$id;
          // $agregar_autor = 'agregar_autor'.$id;
           
          // $addAutor = "$.getJSON(urlAjax + 'autoradd/data/' + escape($('#Agregarv2').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
           $eli_autor = "$.getJSON(urlAjax + 'deleteautor/id/$id', function(data){executeCmdsFromJSON(data)});";
           $auto_completar = "$.getJSON(urlAjax + 'auto_autor/id/$id/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
           $guardar_autor = "$.getJSON(urlAjax + 'guardar_autor/id/$id/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
           
           $$autor = new Zend_Form_Element_Text($autortxt); 
           $$autor->setLabel('')
           ->setRequired(true)
           ->setAttrib('style', 'width: 200px')
           ->setOrder($pos)      
           ->addDecorator('HtmlTag', array('tag' => 'dd',
                    'id'  => $autortxt . '-element'))
           ->addDecorator('Label', array('tag' => 'dt'))
           ->setAttrib('onKeyUp', $auto_completar)
           ->setAttrib('onchange',$guardar_autor);
           
           
           
            $$principal = new Zend_Form_Element_Select($principaltxt); 
            $$principal->setLabel('')
                 ->setAttrib('style', 'width: 200px')
                 ->setOrder($pos+1)   
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => $principaltxt . '-element'))
                  ->addDecorator('Label', array('tag' => 'dd'));
            
       /*  $$agregar_autor = new Zend_Form_Element_Button($agregar_autortxt);
         $$agregar_autor->setLabel('+') 
         ->setOrder($pos+2)   
        ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
        ->setAttrib('onclick', $addAutor); */
            
            $$eliminar_autor = new Zend_Form_Element_Button($eliminar_autortxt);
            $$eliminar_autor->setLabel('-') 
            ->setOrder($pos+2)        
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $eli_autor); 
            
            
             $this->addElements(array($$autor,
                                      $$principal,
                                      //$$agregar_autor
                                      $$eliminar_autor
                                )); 
             
            
          return $this;
            
   }
   
     
      public function addMateria($id,$pos){
          // super variables
           $materiatxt = 'fk_materia'.$id;
           $materia = 'fk_materia'.$id;
           $eliminar_materiatxt = 'eliminar_materia'.$id;
           $eliminar_materia = 'eliminar_materia'.$id;
           
            $eli_materia = "$.getJSON(urlAjax + 'deletemateria/id/'+$id , function(data){executeCmdsFromJSON(data)});";
            $auto_completar = "$.getJSON(urlAjax + 'auto_materia/id/$id/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
            $guardar_materia = "$.getJSON(urlAjax + 'guardar_materia/id/$id/data/'+ escape($('#Agregarv2').find(':input').serialize()) , function(data){executeCmdsFromJSON(data)});";
          
            $$materia = new Zend_Form_Element_Text($materiatxt); 
           $$materia->setLabel('')
           ->setRequired(true)
           ->setOrder($pos)     
           ->setAttrib('style', 'width: 405px')        
           ->addDecorator('HtmlTag', array('tag' => 'dd',
                    'id'  => $materia . '-element'))
           ->addDecorator('Label', array('tag' => 'dt'))
           ->setAttrib('onKeyUp', $auto_completar)
            ->setAttrib('onchange',$guardar_materia);
           
            $$eliminar_materia = new Zend_Form_Element_Button($eliminar_materiatxt);
            $$eliminar_materia->setLabel('-') 
            ->setOrder($pos+1)        
            ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
            ->setAttrib('onclick', $eli_materia); 
           
            $this->addElements(array($$materia,
                                      $$eliminar_materia
                                )); 
             
           
           
      }
     
}