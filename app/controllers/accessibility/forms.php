<?php
class Accessibility_FormsController extends StudipController
{
    protected $with_session = true;

    public function report_barrier_action()
    {
        PageLayout::setTitle(_('Barriere melden'));

        $this->page = Request::get('page');

        $user = User::findCurrent();
        $user_salutation = '';
        if (!empty($user)) {
            if ($user->geschlecht == 1) {
                $user_salutation = _('Herr');
            } elseif ($user->geschlecht == 2) {
                $user_salutation = _('Frau');
            } elseif ($user->geschlecht == 3) {
                $user_salutation = _('divers');
            }
        }

        $this->form = \Studip\Forms\Form::create();
        $this->form->addInput(
            new \Studip\Forms\HiddenInput(
                'page',
                '',
                $this->page
            )
        );
        $details_part = new \Studip\Forms\Fieldset(_('Angaben zur gefundenen Barriere'));
        $details_part->addInput(
            new \Studip\Forms\SelectInput(
                'barrier_type',
                _('Um welche Art von Barriere handelt es sich?'),
                '',
                [
                    'options' => [
                        _('Inhalte auf dieser Seite (z.B. PDF, Bilder oder Lernmodule)') => _('Inhalte auf dieser Seite (z.B. PDF, Bilder oder Lernmodule)'),
                        _('Ein Problem mit der Seite selbst oder der Navigation') => _('Ein Problem mit der Seite selbst oder der Navigation'),
                        _('Sonstiges') => _('Sonstiges')
                    ]
                ]
            )
        )->setRequired();
        $details_part->addInput(
            new \Studip\Forms\TextareaInput(
                'barrier_details',
                _('Beschreiben Sie die Barriere'),
                ''
            )
        )->setRequired();
        $this->form->addPart($details_part);
        $personal_data_part = new \Studip\Forms\Fieldset(_('Ihre persönlichen Daten'));
        $personal_data_part->addText(sprintf('<p>%s</p>', _('Freiwillige Angaben Ihrer Kontaktdaten für etwaige Rückfragen.')));
        $personal_data_part->addInput(
            new \Studip\Forms\SelectInput(
                'salutation',
                _('Anrede'),
                $user_salutation,
                [
                    'options' => [
                        _('Keine Angabe') => _('Keine Angabe'),
                        _('Frau') => _('Frau'),
                        _('Herr') => _('Herr'),
                        _('divers') => _('divers')
                    ]
                ]
            )
        );
        $personal_data_part->addInput(
            new \Studip\Forms\TextInput(
                'name',
                _('Vorname und Nachname'),
                $user ? sprintf('%s %s', $user->vorname, $user->nachname) : ''
            )
        );
        $personal_data_part->addInput(
            new \Studip\Forms\TextInput(
                'phone_number',
                _('Telefonnummer'),
                $user ? ($user->privatcell ?: $user->privatnr) : ''
            )
        );
        $personal_data_part->addInput(
            new \Studip\Forms\TextInput(
                'email_address',
                _('E-Mail-Adresse'),
                $user ? $user->email : ''
            )
        );

        $personal_data_part->addText(sprintf('<p>%s</p>',
            _('Informationen zum Datenschutz dieses Formulars finden Sie in der Datenschutzerklärung.')));

        $privacy_url = Config::get()->PRIVACY_URL;

        if (is_internal_url($privacy_url)) {
            $personal_data_part->addLink(
                _('Datenschutzerklärung lesen'),
                URLHelper::getURL($privacy_url, ['cancel_login' => '1']),
                Icon::create('link-intern'),
                ['data-dialog' => 'size=big']
            );
        } else {
            $personal_data_part->addLink(
                _('Datenschutzerklärung lesen'),
                URLHelper::getURL($privacy_url),
                Icon::create('link-extern'),
                ['target' => '_blank']
            );
        }

        $this->form->addPart($personal_data_part);
        $this->form->setSaveButtonText(_('Barriere melden'));
        $this->form->setSaveButtonName('report');
        $this->form->setURL($this->report_barrierURL());
        $this->form->addStoreCallback(
            function ($form, $form_values) {
                $recipients = Config::get()->ACCESSIBILITY_RECEIVER_EMAIL;
                if (empty($recipients)) {
                    //Fallback: Use the UNI_CONTACT mail address:
                    $recipients = [$GLOBALS['UNI_CONTACT']];
                }
                //Get the sender and their language:
                $sender = User::findCurrent();
                //Default to the system default language:
                $lang = explode('_', Config::get()->DEFAULT_LANGUAGE ?? 'de_DE')[0];
                if ($sender) {
                    //Use the senders language since the choices in the form
                    //are in their language as well.
                    $lang = explode('_', getUserLanguage($sender->id))[0];
                }
                //Format the senders name according to the salutation.
                $formatted_name = '';
                if ($form_values['salutation'] === _('Keine Angabe')) {
                    $formatted_name = $form_values['name'];
                } elseif ($form_values['salutation'] === _('divers')) {
                    $formatted_name = sprintf('%s (%s)', $form_values['name'], $form_values['salutation']);
                } else {
                    $formatted_name = sprintf('%s %s', $form_values['salutation'], $form_values['name']);
                }
                //Build the mail text:
                $template = $GLOBALS['template_factory']->open("../locale/{$lang}/LC_MAILS/report_barrier.php");
                $template->set_attributes([
                    'sender' => $sender,
                    'page' => $form_values['page'],
                    'barrier_type' => $form_values['barrier_type'],
                    'barrier_details' => $form_values['barrier_details'],
                    'formatted_name' => $formatted_name,
                    'phone_number' => $form_values['phone_number'],
                    'email_address' => $form_values['email_address']
                ]);
                $mail_text = $template->render();

                foreach ($recipients as $mail_address) {
                    //Send the mail:
                    $mail = new StudipMail();
                    $mail->addRecipient($mail_address)
                        ->setReplyToEmail($form_values['email_address'])
                        ->setSubject(_('Meldung einer Barriere in Stud.IP'))
                        ->setBodyText($mail_text)
                        ->send();
                }

                $form->setSuccessMessage(
                    _('Ihre Meldung einer Barriere wurde weitergeleitet.') . ' ' .
                    sprintf(
                        '<a href="%1$s">%2$s %3$s</a>',
                        URLHelper::getLink($this->page),
                        Icon::create('link-intern', ['class' => 'text-bottom']),
                        _('Zurück')
                    )
                );
                return 1;
            }
        );
        $this->form->autoStore();
    }
}
