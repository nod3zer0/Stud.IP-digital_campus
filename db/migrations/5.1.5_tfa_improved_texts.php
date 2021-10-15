<?php
final class TfaImprovedTexts extends Migration
{
    public function description()
    {
        return 'TIC #11510: Improve texts for two factor authentication';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`, `description`
                  ) VALUES(
                    :id, :text, 'i18n', 'global', 'Zwei-Faktor-Authentifizierung',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description
                  )";
        DBManager::get()->execute($query, [
            ':id'          => 'TFA_TEXT_INTRODUCTION',
            ':text'        => $this->getIntroductionText('de'),
            ':description' => 'Text, der als Einleitung beim Einrichten der Zwei-Faktor-Authentisierung angezeigt wird',
        ]);
        DBManager::get()->execute($query, [
            ':id'          => 'TFA_TEXT_APP',
            ':text'        => $this->getAppText('de'),
            ':description' => 'Text, der als Einleitung beim Einrichten der Zwei-Faktor-Authentisierung via App angezeigt wird',
        ]);

        $query = "INSERT IGNORE INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`)
                  VALUES (MD5(:id), 'config', 'value', 'en_GB', :text)";
        DBManager::get()->execute($query, [
            ':id'   => 'TFA_TEXT_INTRODUCTION',
            ':text' => $this->getIntroductionText('en'),
        ]);
        DBManager::get()->execute($query, [
            ':id'   => 'TFA_TEXT_APP',
            ':text' => $this->getAppText('en'),
        ]);
    }

    protected function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN ('TFA_TEXT_INTRODUCTION', 'TFA_TEXT_APP')";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `i18n`
                  WHERE `object_id` IN (MD5('TFA_TEXT_INTRODUCTION'), MD5('TFA_TEXT_APP'))
                    AND `table` = 'config'
                    AND `field` = 'value'";
        DBManager::get()->exec($query);
    }

    private function getIntroductionText($language)
    {
        if ($language === 'en') {
            return 'Using two-factor authentication you can protect your account by '
                 . 'entering a token on each login. '
                 . 'You get that token either via E-Mail or by using an appropriate '
                 . 'authenticator app.';
        } else {
            return 'Mittels Zwei-Faktor-Authentifizierung können Sie Ihr Konto schützen, '
                 . 'indem bei jedem Login ein Token von Ihnen eingegeben werden muss. '
                 . 'Dieses Token erhalten Sie entweder per E-Mail oder können es über '
                 . 'eine geeignete Authenticator-App erzeugen lassen.';
        }
    }

    private function getAppText($language)
    {
        if ($language === 'en') {
            $result = "Set up a suitable OTP authenticator app for this purpose. "
                    . "Here you will find a list of known and compatible apps:\n";
        } else {
            $result = "Richten Sie dafür eine geeignete OTP-Authenticator-App ein. Hier "
                    . "finden Sie eine Liste bekannter und kompatibler Apps:\n";
        }
        return $result . $this->getAuthenticatorList();
    }

    private function getAuthenticatorList()
    {
        return implode("\n", [
            '- [Authy]https://authy.com/',
            '- [FreeOTP]https://freeotp.github.io/',
            '- Google Authenticator: [Android]https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2 oder [iOS]https://apps.apple.com/app/google-authenticator/id388497605',
            '- [LastPass Authenticator]https://lastpass.com/auth/',
            '- [Microsoft Authenticator]https://www.microsoft.com/authenticator',
        ]);
    }
}
