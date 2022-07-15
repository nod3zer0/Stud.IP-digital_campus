<?php

namespace Studip\OAuth2\Models;

/**
 * @property int $id
 * @property string $name
 * @property string|null $secret
 * @property string $redirect
 * @property bool $revoked
 * @property int $mkdate
 * @property int $chdate
 */
class Client extends \SimpleORMap
{
    use RevokedHelper;

    /** @var string $plainsecret This is only filled when creating a new Client via `Client::createClient`. */
    public $plainsecret;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'oauth2_clients';

        $config['belongs_to']['user'] = [
            'class_name'  => \User::class,
            'foreign_key' => 'user_id',
        ];

        $config['has_many']['auth_codes'] = [
            'class_name'        => AuthCode::class,
            'assoc_foreign_key' => 'client_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
            'order_by'          => 'ORDER BY chdate',
        ];

        $config['has_many']['access_tokens'] = [
            'class_name'        => AccessToken::class,
            'assoc_foreign_key' => 'client_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
            'order_by'          => 'ORDER BY chdate',
        ];

        parent::configure($config);
    }

    /**
     * Store a new client.
     *
     * @return static
     */
    public static function createClient(
        string $name,
        string $redirect,
        bool $confidential,
        string $owner,
        string $homepage,
        ?string $description,
        ?string $adminNotes
    ) {
        $secret = null;
        $plainsecret = null;
        if ($confidential) {
            $plainsecret = randomString(40);
            $secret = password_hash($plainsecret, PASSWORD_BCRYPT);
        }

        $client = self::create([
            'name' => $name,
            'secret' => $secret,
            'redirect' => $redirect,
            'revoked' => 0,
            'owner' => $owner,
            'homepage' => $homepage,
            'description' => $description,
            'admin_notes' => $adminNotes,
        ]);
        $client->plainsecret = $plainsecret;

        return $client;
    }

    /**
     * @param int|string $clientId
     *
     * @return ?static
     */
    public static function findActive($clientId)
    {
        $client = self::find($clientId);

        return $client && !$client->isRevoked() ? $client : null;
    }

    /**
     * @param string $clientId
     *
     * @return bool
     */
    public static function revoked($clientId): bool
    {
        return static::findActive($clientId) === null;
    }

    /**
     * @return bool
     */
    public function confidential(): bool
    {
        return !empty($this->secret);
    }

    /**
     * @return string[]
     */
    public function redirectURIs(): array
    {
        return explode(',', $this->redirect);
    }
}
