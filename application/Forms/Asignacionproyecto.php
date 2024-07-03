<?php 
class Forms_Asignacionproyecto extends Zend_Form {
    /**
     * NOTA: Para que los campos del formulario funcionen perfectamente con la
     * interaccion de la Base de Datos, debe colocar el mismo nombre de la col-
     * umna al elemento del formulario.
     */
    public function init() {
		/*
		 * Asignamos los eventos a los SELECT para que llene a otros SELECT en
		 * forma de cascada.
		 */
		$SwapBytes_Jquery = new SwapBytes_Jquery();



        $this->setMethod('post');
        $this->setName('pasantiasocial');

        $id = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');
		
        $filtro = new Zend_Form_Element_Hidden('filters');
		$filtro->removeDecorator('label')
               ->removeDecorator('HtmlTag');

        $page = new Zend_Form_Element_Hidden('page');
		$page->removeDecorator('label')
             ->removeDecorator('HtmlTag');

		/*
		 * Campos del formulario.
		 */
        
        $proyecto = new Zend_Form_Element_Select('fk_proyecto');
        $proyecto->setLabel('Proyectos:')
                 ->setAttrib('style', 'width: 300px');

        $tipohorario = new Zend_Form_Element_Select('fk_tipohorario');
        $tipohorario->setLabel('Horario:')
                    ->setAttrib('style', 'width: 200px');
      

        $cupo = new Zend_Form_Element_Text('cupos');
        $cupo->setLabel('Cupos:')
			 ->setValue(20)
             ->setRequired(true)
             ->addFilter('StripTags')
             ->addFilter('StringTrim')
             ->addValidator('StringLength', true, array(1, 3))
             ->addValidator('GreaterThan', true, array('min' => 0))
             ->setAttrib('size', 3)
             ->setAttrib('maxlength', 3);

        $this->addElements(array($id,
			$filtro,
                        $page,
			$proyecto,
                        $tipohorario,
			$cupo));
    }
}
