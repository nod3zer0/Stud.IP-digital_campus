<?php

use Studip\Forms\Form;

class RegistrationController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Registrierung'));
    }

    public function index_action()
    {
        $new_user = new User();
        $new_user->perms = 'user';
        $new_user->auth_plugin = 'standard';
        $new_user->preferred_language = $_SESSION['_language'] ?? Config::get()->DEFAULT_LANGUAGE;
        $this->registrationform = Form::fromSORM(
            $new_user,
            [
                'legend' => _('Herzlich willkommen!'),
                'fields' => [
                    'username' => [
                        'label' => _('Benutzername'),
                        'required' => true,
                        'maxlength' => '63',
                        'attributes' => ['autocomplete' => 'off'],
                        'validate' => function ($value, $input) {
                            if (!preg_match(Config::get()->USERNAME_REGULAR_EXPRESSION, $value)) {
                                return Config::get()->getMetadata('USERNAME_REGULAR_EXPRESSION')['comment'] ?:
                                    _('Benutzername muss mindestens 4 Zeichen lang sein und darf nur aus Buchstaben, '
                                    . 'Ziffern, Unterstrich, @, Punkt und Minus bestehen.');
                            }
                            $user = User::findByUsername($value);
                            $context = $input->getContextObject();
                            if ($user && ($user->id !== $context->getId())) {
                                return _('Benutzername ist schon vergeben.');
                            }
                            return true;
                        }
                    ],
                    'password' => [
                        'label' => _('Passwort'),
                        'type' => 'password',
                        'required' => true,
                        'maxlength' => '31',
                        'minlength' =>  '8',
                        'attributes' => ['autocomplete' => 'new-password'],
                        'mapper' => function($value) {
                            $hasher = UserManagement::getPwdHasher();
                            return $hasher->HashPassword($value);
                        }
                    ],
                    'confirm_password' => [
                        'label' => _('Passwortbestätigung'),
                        'type' => 'password',
                        'required' => true,
                        'maxlength' => '31',
                        'minlength' =>  '8',
                        'attributes' => ['autocomplete' => 'new-password'],
                        ':pattern'    => "password.replace(/[.*+?^\${}()|[\\]\\\\]/g, '\\\\$&')", //mask special chars
                        'data-validation_requirement' => _('Passwörter stimmen nicht überein.'),
                        'store' => function() {}
                    ],
                    'title_front' => [
                        'label' => _('Titel'),
                        'type'  => 'datalist',
                        'attributes' => ['autocomplete' => 'honorific-prefix'],
                        'options' => $GLOBALS['TITLE_FRONT_TEMPLATE']
                    ],
                    'title_rear' => [
                        'label' => _('Titel nachgestellt'),
                        'type'  => 'datalist',
                        'attributes' => ['autocomplete' => 'honorific-suffix'],
                        'options' => $GLOBALS['TITLE_REAR_TEMPLATE'],
                    ],
                    'vorname' => [
                        'label' => _('Vorname'),
                        'attributes' => ['autocomplete' => 'given-name'],
                        'required' => true
                    ],
                    'nachname' => [
                        'label' => _('Nachname'),
                        'attributes' => ['autocomplete' => 'family-name'],
                        'required' => true
                    ],
                    'geschlecht' => [
                        'name' => 'geschlecht',
                        'value' => 0,
                        'label' => _('Geschlecht'),
                        'type' => 'radio',
                        'orientation' => 'horizontal',
                        'options' => [
                            '0' => _('keine Angabe'),
                            '1' => _('männlich'),
                            '2' => _('weiblich'),
                            '3' => _('divers'),
                        ],
                    ],
                    'email' => [
                        'label' => _('E-Mail'),
                        'required' => true,
                        'attributes' => ['autocomplete' => 'email'],
                        'validate' => function ($value, $input) {
                            $user = User::findOneByEmail($value);
                            $context = $input->getContextObject();
                            if ($user && ($user->id !== $context->getId())) {
                                return _('Diese Emailadresse ist bereits registriert.');
                            }
                            return true;
                        }
                    ],
                ]
            ]
        );
        $this->registrationform->setSaveButtonText(_('Registrierung abschließen'));
        $this->registrationform->setCancelButtonText(_('Abbrechen'));
        $this->registrationform->setCancelButtonName(URLHelper::getURL('index.php?cancel_login=1'));

        $this->registrationform->addStoreCallback(
            function (Form $form) {
                $new_user = $form->getLastPart()->getContextObject();

                $GLOBALS['sess']->regenerate_session_id(['auth']);
                $GLOBALS['auth']->unauth();
                $GLOBALS['auth']->auth['jscript'] = true;
                $GLOBALS['auth']->auth['perm']  = $new_user['perms'];
                $GLOBALS['auth']->auth['uname'] = $new_user['username'];
                $GLOBALS['auth']->auth['auth_plugin']  = $new_user['auth_plugin'];
                $GLOBALS['auth']->auth_set_user_settings($new_user->user_id);
                $GLOBALS['auth']->auth['uid'] = $new_user['user_id'];
                $GLOBALS['auth']->auth['exp'] = time() + (60 * $GLOBALS['auth']->lifetime);
                $GLOBALS['auth']->auth['refresh'] = time() + (60 * $GLOBALS['auth']->refresh);

                Seminar_Register_Auth::sendValidationMail($new_user);

                return 1;
            }
        );

        $this->registrationform->autoStore()->setURL(URLHelper::getURL('dispatch.php/start'));
    }
}
