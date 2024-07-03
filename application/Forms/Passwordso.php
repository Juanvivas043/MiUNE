<?php
class Forms_Passwordso extends Zend_Form {
    public function init() {
        $passwordOld = new Zend_Form_Element_Password('password');
        $passwordOld->setLabel('Contraseña de inicio de sesión:')
                    ->addFilters(array('StringTrim'))
                    ->addValidator('Alnum')
                    ->addValidator('StringLength', true, array(6, 20))
                    ->setRequired(true)
                    ->setAttrib('autocomplete', 'off')
                    ->setAttrib('size', 14);

        $passwordNew = new Zend_Form_Element_Password('passwordNew');
        $passwordNew->setLabel('Contraseña de Operaciones Especiales Nueva:')
                    ->addFilters(array('StringTrim'))
                    ->addValidator('Alnum')
                    ->addValidator('StringLength', true, array(6, 20))
                    ->setRequired(true)
                    ->setAttrib('autocomplete', 'off')
                    ->setAttrib('size', 14);

        $passwordRep = new Zend_Form_Element_Password('passwordRep');
        $passwordRep->setLabel('Repita Contraseña de Operaciones Especiales:')
                    ->addFilters(array('StringTrim'))
                    ->addValidator('Alnum')
                    ->addValidator('StringLength', true, array(6, 20))
                    ->setRequired(true)
                    ->setAttrib('autocomplete', 'off')
                    ->setAttrib('size', 14);

        $change = new Zend_Form_Element_Submit('btnChange');
        $change->setIgnore(true)
              ->setLabel('Cambiar');

        $this->addElements(array($passwordOld,$passwordNew, $passwordRep, $change));
    }
}
?>
