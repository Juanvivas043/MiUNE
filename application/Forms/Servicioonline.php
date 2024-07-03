<?php

class Forms_Servicioonline extends Zend_Form  {
  
    public function init() {
        
        
        $this->setMethod('post');
        $this->setName('servicioonline');
	$this->setOptions(array('escape' => true));
        

        $id   = new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label')
                   ->removeDecorator('HtmlTag');


       $proyecto = new Zend_Form_Element_Text('proyecto');
       $proyecto->setLabel('Proyecto:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 50);
       
       $institucion = new Zend_Form_Element_Text('institucion');
       $institucion->setLabel('Institucion:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 50);
       
       $tutoracademico = new Zend_Form_Element_Text('tutoracademico');
       $tutoracademico->setLabel('Tutor Academico:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 50);
       
       $tutorinstitucional = new Zend_Form_Element_Text('tutorinstitucional');
       $tutorinstitucional->setLabel('Tutor Institucional:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 50);
       $horario = new Zend_Form_Element_Text('horario');
       $horario->setLabel('Horario:')
               ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 50);
       
       $this->addElements(array($id,
                                $proyecto,
                                $institucion,
                                $tutoracademico,
                                $tutorinstitucional,
                                $horario));
        
    }

}

?>
