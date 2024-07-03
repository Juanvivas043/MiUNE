<?php
class Forms_Changepass extends Zend_Form {
    public function init() {

        $this->setName('Cambiar Contraseña');
        $this->setMethod('post');
        
        $passwordNew = new Zend_Form_Element_Password('passwordNew');
        $passwordNew->setLabel('Nueva Contraseña:')
                    ->addFilters(array('StringTrim'))
                    ->addValidator('Alnum')
                    ->addValidator('StringLength', true, array(6, 15))
                    ->setRequired(true)
                    ->setAttrib('autocomplete', 'off')
                    ->setAttrib('size', 14);

        $passwordRep = new Zend_Form_Element_Password('passwordRep');
        $passwordRep->setLabel('Repita la Nueva Contraseña:')
                    ->addFilters(array('StringTrim'))
                    ->addValidator('Alnum')
                    ->addValidator('StringLength', true, array(6, 15))
                    ->addValidator('Identical', false, array('token' => 'passwordNew'))
                    ->addErrorMessage('No Coincide')
                    ->setRequired(true)
                    ->setAttrib('autocomplete', 'off')
                    ->setAttrib('size', 14);

       

        $this->addElements(array($passwordNew, $passwordRep));
    }
}
?>
