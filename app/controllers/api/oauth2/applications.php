<?php
use Studip\OAuth2\Models\AccessToken;
use Studip\OAuth2\Models\Scope;

/**
 * @property array $applications
 */
class Api_Oauth2_ApplicationsController extends AuthenticatedController
{
    public function index_action(): void
    {
        Navigation::activateItem('/profile/settings/oauth2');
        PageLayout::setTitle(_('Autorisierte Drittanwendungen'));
        Helpbar::get()->addPlainText(
            _('Autorisierte Drittanwendungen'),
            _("Sie können Ihren Stud.IP-Zugang über OAuth mit Anwendungen von Drittanbietern verbinden.\n\nWenn Sie eine OAuth-App autorisieren, sollten Sie sicherstellen, dass Sie der Anwendung vertrauen, überprüfen, wer sie entwickelt hat, und die Art der Informationen überprüfen, auf die die Anwendung zugreifen möchte.")
        );

        $user = User::findCurrent();
        $this->applications = $this->getApplications($user);
    }

    public function details_action(AccessToken $accessToken): void
    {
        $user = User::findCurrent();
        if ($accessToken['user_id'] !== $user->id) {
            throw new AccessDeniedException();
        }

        PageLayout::setTitle(_('Autorisierte OAuth2-Drittanwendung'));
        $this->application = $this->formatApplication($accessToken);

        if (!$this->application) {
            throw new Trails_Exception(500, 'Error finding client.');
        }
    }

    public function revoke_action(): void
    {
        CSRFProtection::verifyUnsafeRequest();

        $user = User::findCurrent();
        $accessToken = AccessToken::find(Request::option('application'));
        if (!$accessToken) {
            throw new Trails_Exception(404);
        }
        if ($accessToken['user_id'] !== $user->id) {
            throw new AccessDeniedException();
        }

        $accessToken->revoke();

        $this->redirect('api/oauth2/applications');
    }

    private function getApplications(User $user): array
    {
        return array_reduce(
            AccessToken::findValidTokens($user),
            function ($applications, $accessToken) {
                $application = $this->formatApplication($accessToken);
                if ($application) {
                    $applications[] = $application;
                }

                return $applications;
            },
            []
        );
    }

    private function formatApplication(AccessToken $accessToken): ?array
    {
        $allScopes = Scope::scopes();

        if (!$accessToken->client) {
            return null;
        }

        return [
            'id'          => $accessToken['id'],
            'name'        => $accessToken->client['name'],
            'description' => $accessToken->client['description'],
            'owner'       => $accessToken->client['owner'],
            'homepage'    => $accessToken->client['homepage'],
            'created'     => new DateTime('@' . $accessToken->client['mkdate']),

            'scopes' => array_reduce(
                json_decode($accessToken['scopes']),
                function ($scopes, $scopeIdentifier) use ($allScopes) {
                    if (isset($allScopes[$scopeIdentifier])) {
                        $scopes[] = $allScopes[$scopeIdentifier];
                    }

                    return $scopes;
                },
                []
            )
        ];
    }
}
