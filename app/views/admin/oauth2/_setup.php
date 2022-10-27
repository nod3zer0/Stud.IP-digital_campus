<?php
/**
 * @var  Studip\OAuth2\SetupInformation $setup
 */
?>
<ul>
    <li>
        <? $privateKey = $setup->privateKey(); ?>
        <b lang="en">Private Key</b> (<?= htmlReady($privateKey->filename()) ?>)
        <?= $this->render_partial('admin/oauth2/_setup_key.php', ['key' => $privateKey]) ?>
    </li>
    <li>
        <? $publicKey = $setup->publicKey(); ?>
        <b lang="en">Public Key</b> (<?= htmlReady($publicKey->filename()) ?>)
        <?= $this->render_partial('admin/oauth2/_setup_key.php', ['key' => $publicKey]) ?>
    </li>
    <li>
        <? $encryptionKey = $setup->encryptionKey(); ?>
        <b lang="en">Encryption Key</b> (<?= htmlReady($encryptionKey->filename()) ?>)
        <?= $this->render_partial('admin/oauth2/_setup_key.php', ['key' => $encryptionKey]) ?>
    </li>
</ul>
