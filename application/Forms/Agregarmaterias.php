<?php 

class Forms_Agregarmaterias extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
  public function init() {

        $this->setMethod('post');
        $this->setName('agregarmaterias');
	$this->setOptions(array('escape' => true));
       	
         $addMateria = "$.getJSON(urlAjax + 'materiaadd/data/' + escape($('#agregarmaterias').find(':input').serialize()), function(data){executeCmdsFromJSON(data)});";
        
         $id   = new Zend_Form_Element_Hidden('id');
         $id->removeDecorator('label')
         ->removeDecorator('HtmlTag'); 
        
         
         $pk_materialibro  = new Zend_Form_Element_Hidden('pk_materialibro');
         $pk_materialibro->removeDecorator('label')
         ->removeDecorator('HtmlTag'); 
         
        $fk_materia = new Zend_Form_Element_Select('fk_materia'); 
        $fk_materia->setLabel('Materia')
                  ->setAttrib('style', 'width: 405px')
                  ->addDecorator('HtmlTag', array('tag' => 'dd',
                 'id'  => 'fk_materia' . '-element'))
                  ->addDecorator('Label', array('tag' => 'dt'));  
        
        $agregar_materia = new Zend_Form_Element_Button('agregar_materia');
        $agregar_materia->setLabel('+') 
        ->setDecorators(array( 'ViewHelper', array('HtmlTag', array('tag' => 'dd')) ))
        ->setAttrib('onclick', $addMateria);
        
      
       $this->addElements(array(
                                    $id,
                                    $pk_materialibro,
                                    $fk_materia,
                                    $agregar_materia
                                ));

    }
    
  public function addMateria($id,$pos){
          // super variables
           $materiatxt = 'fk_materia'.$id;
           $materia = 'fk_materia'.$id;
           $eliminar_materiatxt = 'eliminar_materia'.$id;
           $eliminar_materia = 'eliminar_materia'.$id;
           
            $eli_materia = "$.getJSON(urlAjax + 'deletemateria/id/'+$id , function(data){executeCmdsFromJSON(data)});";
           
           $$materia = new Zend_Form_Element_Select($materiatxt); 
           $$materia->setLabel('')
           ->setOrder($pos)     
           ->setAttrib('style', 'width: 405px')        
           ->addDecorator('HtmlTag', array('tag' => 'dd',
                    'id'  => $materia . '-element'))
           ->addDecorator('Label', array('tag' => 'dt'));
           
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

?>
