<?php

use Studip\OAuth2\Models\Client;

class Api_Oauth2_ClientsController extends AuthenticatedController
{
    /**
     * @param string $action
     * @param string[] $args
     *
     * @return void
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');
    }

    public function add_action(): void
    {
        Navigation::activateItem('/admin/config/oauth2');
        PageLayout::setTitle(_('OAuth2-Client hinzufügen'));
    }

    public function store_action(): void
    {
        CSRFProtection::verifyUnsafeRequest();

        $this->redirect('admin/oauth2');

        list($valid, $data, $errors) = $this->validateCreateClientRequest();

        if (!$valid) {
            PageLayout::postError(_('Das Erstellen eines OAuth2-Clients war nicht erfolgreich.'), $errors);
            return;
        }

        $client = $this->createAuthCodeClient($data);
        $this->outputClientCredentials($client);
    }

    public function delete_action(Client $client): void
    {
        CSRFProtection::verifyUnsafeRequest();

        $clientId = $client['id'];
        $clientName = $client['name'];
        $client->delete();

        PageLayout::postSuccess(sprintf(_('Der OAuth2-Client #%d ("%s") wurde gelöscht.'), $clientId, $clientName));
        $this->redirect('admin/oauth2');
    }

    /**
     * Create a authorization code client.
     *
     * @param array<string, mixed>
     */
    private function createAuthCodeClient(array $data): Client
    {
        return Client::createClient(
            $data['name'],
            $data['redirect'],
            $data['confidential'],
            $data['owner'],
            $data['homepage'],
            $data['description'],
            $data['admin_notes']
        );
    }

    /**
     * Show feedback to the user depending on the confidentiality of the `$client`.
     */
    private function outputClientCredentials(Client $client): void
    {
        if ($client->confidential()) {
            PageLayout::postWarning(_('Der OAuth2-Client wurde erstellt.'), [
                sprintf(_('Die <em lang="en"> client_id </em> lautet: <pre>%s</pre>'), $client['id']),
                sprintf(_('Das <em lang="en"> client_secret </em> lautet: <pre>%s</pre>'), $client->plainsecret),
                _(
                    'Notieren Sie sich bitte das <em lang="en"> client_secret </em>. Es wird Ihnen nur <strong> dieses eine Mal </strong> angezeigt.'
                ),
            ]);
        } else {
            PageLayout::postSuccess(_('Der OAuth2-Client wurde erstellt.'), [
                sprintf(_('Die <em lang="en"> client_id </em> lautet: <pre>%s</pre>'), $client['id']),
            ]);
        }
    }

    /**
     * Validate the request parameters when creating a new client.
     *
     * @return array{0: bool, 1: array<string, mixed>, 2: string[]}
     */
    private function validateCreateClientRequest()
    {
        $valid = true;
        $data = [];
        $errors = [];

        // required
        $name = Request::get('name');
        $redirectURIs = Request::get('redirect');
        $confidentiality = Request::get('confidentiality');
        $owner = Request::get('owner');
        $homepage = Request::get('homepage');

        // optional
        $data['description'] = Request::get('description');
        $data['admin_notes'] = Request::get('admin_notes');

        foreach (compact('name', 'redirectURIs', 'confidentiality', 'owner', 'homepage') as $key => $value) {
            if (!isset($value)) {
                $errors[] = sprintf(_('Parameter "%s" fehlt.'), $key);
                $valid = false;
            }
        }

        // validate $name
        $data['name'] = trim($name);
        if ($name === '') {
            $errors[] = _('Der Parameter "name" darf nicht leer sein.');
            $valid = false;
        }

        // validate $redirectURIS
        $redirect = [];
        $redirectLines = preg_split("/[\n\r]/", $redirectURIs, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($redirectLines as $line) {
            $url = filter_var($line, FILTER_SANITIZE_URL);
            if (false === filter_var($url, FILTER_VALIDATE_URL)) {
                $errors = _('Der Parameter "redirect" darf nur gültige URLs enthalten.');
                $valid = false;
                break;
            }
            $redirect[] = $url;
        }
        $data['redirect'] = join(',', $redirect);

        // validate $confidentiality
        if (!in_array($confidentiality, ['public', 'confidential'])) {
            $errors[] = _('Der Parameter "confidentiality" darf nur gültige URLs enthalten.');
            $valid = false;
        }
        $data['confidential'] = $confidentiality === 'confidential';

        // validate $owner
        $data['owner'] = trim($owner);
        if ($owner === '') {
            $errors[] = _('Der Parameter "owner" darf nicht leer sein.');
            $valid = false;
        }

        // validate $homepage
        $data['homepage'] = filter_var($homepage, FILTER_SANITIZE_URL);
        if (false === filter_var($homepage, FILTER_VALIDATE_URL)) {
            $errors = _('Der Parameter "homepage" muss eine gültige URL enthalten.');
            $valid = false;
        }

        return [$valid, $data, $errors];
    }
}
