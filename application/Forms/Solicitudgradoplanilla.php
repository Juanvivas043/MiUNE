<?php
class Forms_Solicitudgradoplanilla extends Zend_Form {
	
	public function init() {

		$this->setMethod('post');
        $this->setName('solicitudgradoplanilla');
	    $this->setOptions(array('escape' => true));

        $trabajoAction = '$(".trabajo:text").attr("disabled", !$(".trabajo:text").attr("disabled"));';

	    $id = new Zend_Form_Element_Hidden('id');
        $id->removeDecorator('label')
           ->removeDecorator('HtmlTag'); 

        $trabajo = new Zend_Form_Element_Checkbox('trabajo');
        $trabajo->setLabel('¿Trabaja actualmente?')
        ->setAttrib('enabled', 'true')
        ->setAttrib('onchange', $trabajoAction);;
        
        $cargo = new Zend_Form_Element_Text('cargo');
        $cargo->setLabel('Cargo:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80)
            ->setAttrib('class','trabajo')
            ->setAttrib('disabled', 'true');

        $empresa = new Zend_Form_Element_Text('empresa');
        $empresa->setLabel('Empresa:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 80)
            ->setAttrib('class','trabajo')
            ->setAttrib('disabled', 'true');

        $telefono = new Zend_Form_Element_Text('teloficina');
        $telefono->setLabel('Teléfono de la oficina:')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(11, 15))
            ->setAttrib('size', 16)
            ->setAttrib('maxlength', 15)
            ->setAttrib('class','trabajo')
            ->setAttrib('disabled', 'true');

        $titulo = new Zend_Form_Element_Text('tesis');
        $titulo->setLabel('Titulo de la Tesis de grado:')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty') 
            ->setAttrib('size', 35)
            ->setAttrib('maxlength', 300);

        $pais = new Zend_Form_Element_Select('pais');
        $pais->setLabel('Lugar de nacimiento: ')
                ->setAttrib('style', 'width: 150px')
                ->setRegisterInArrayValidator(false)
                ->addValidator('NotEmpty');

        $this->addElements(array($id,$trabajo,$empresa,$cargo,$telefono,$pais,$titulo));

    }


public function toogleRequired($required){
    
    $this->empresa->setRequired($required);
    
    $this->cargo->setRequired($required);
   
    $this->teloficina->setRequired($required);
    
}

}
