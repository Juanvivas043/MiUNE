<?php
class Forms_Login extends Zend_Form {
    public function init() {
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('C.I.:')
                 ->addFilters(array('StringTrim', 'StringToLower'))
                 ->addValidator('Digits')
                 ->addValidator('StringLength', true, array(6, 20))
                 ->setRequired(true)
                 ->setAttrib('autocomplete', 'off')
                 ->setAttrib('size', 14);

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Contraseña:')
                 ->addFilters(array('StringTrim'))
                 ->addValidator('Alnum')
                 ->addValidator('StringLength', true, array(6, 20))
                 ->setRequired(true)
                 ->setAttrib('autocomplete', 'off')
                 ->setAttrib('size', 14);

        $login = new Zend_Form_Element_Submit('login');
        $login->setIgnore(true)
              ->setLabel('Iniciar sesión');

        $this->addElements(array($username,$password, $login));
    }
}
?>
