<?php

class Mail {
    // templates name

    const FORGOT_PASSWORD = "forgotpassword";

    protected $transport;
    protected $_viewSubject;
    protected $_viewContent;
    protected $templateVariables = array();
    protected $templateName;
    protected $_mail;
    protected $recipient;

    public function __construct() {
        $config = Zend_Registry::get('email')["transport"];
        $settings = array('ssl' => $config['type'],
            'port' => $config['port'],
            'auth' => $config['auth'],
            'username' => $config['username'],
            'password' => $config['password']);

        $this->transport = new Zend_Mail_Transport_Smtp($config['host'], $settings);
        $this->_mail = new Zend_Mail();
        $this->_viewSubject = new Zend_View();
        $this->_viewContent = new Zend_View();
    }

    /**
     * Set variables for use in the templates
     *
     * @param string $name  The name of the variable to be stored
     * @param mixed  $value The value of the variable
     */
    public function __set($name, $value) {
        $this->templateVariables[$name] = $value;
    }

    /**
     * Set the template file to use
     *
     * @param string $filename Template filename
     */
    public function setTemplate($filename) {
        $this->templateName = $filename;
    }

    /**
     * Set the recipient address for the email message
     * 
     * @param string $email Email address
     */
    public function setRecipient($email) {
        $this->recipient = $email;
    }

    /**
     * Send email
     *
     * @todo Add from name
     */
    public function send() {
        $config = Zend_Registry::get('email');
        $emailPath = $config['templatePath'];
        $templateVars = $config['template'];

        foreach ($templateVars as $key => $value) {
            if (!array_key_exists($key, $this->templateVariables)) {
                $this->{$key} = $value;
            }
        }

        $viewSubject = $this->_viewSubject->setScriptPath($emailPath);
        foreach ($this->templateVariables as $key => $value) {
            $viewSubject->{$key} = $value;
        }
        $subject = $viewSubject->render($this->templateName . '.subj.tpl');


        $viewContent = $this->_viewContent->setScriptPath($emailPath);
        foreach ($this->templateVariables as $key => $value) {
            $viewContent->{$key} = $value;
        }
        $html = $viewContent->render($this->templateName . '.tpl');

        $this->_mail->addTo($this->recipient);
        $this->_mail->setSubject($subject);
        $this->_mail->setBodyHtml($html);

        $this->_mail->send($this->transport);
    }

}