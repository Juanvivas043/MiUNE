<?php 

class Forms_Asignaciontutorps extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
        
        $SwapBytes_Jquery = new SwapBytes_Jquery();
        
        $changeProyecto = $SwapBytes_Jquery->fillSelect('fk_proyecto', 'proyecto', array('instituciones' => 'fk_institucion'));
                
        $this->setMethod('post');
        $this->setName('tutorps');
	$this->setOptions(array('escape' => true));
        	
        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
                
        $instituciones = new Zend_Form_Element_Select('fk_institucion');
        $instituciones->setLabel('Institución:')
             ->setAttrib('onchange', $changeProyecto)   
             ->setAttrib('style', 'width: 300px');         
        
        $proyectos = new Zend_Form_Element_Select('fk_proyecto');
        $proyectos->setLabel('Proyecto:')   
             ->setAttrib('style', 'width: 300px'); 
                
        $nombre_tutor = new Zend_Form_Element_Select('fk_usuariogrupo');
        $nombre_tutor->setLabel('Nombre del Tutor:')
                     ->setAttrib('style', 'width: 300px');   
     

        $this->addElements(array($id,
                                 $instituciones,
                                 $proyectos,
                                 $nombre_tutor));

    }
}
