<?php
class UpdateUsernameRegularExpression extends Migration
{
    public function description()
    {
        return 'Alters configuration description to make clearer that it is used for creating new users';
    }

    public function up()
    {
        $db = DBManager::get();
        $stmt = $db->prepare("UPDATE `config`
            SET `description` = 'Regulärer Ausdruck für erlaubte Zeichen in Benutzernamen. Das Kommentarfeld kann genutzt werden, um eine Fehlermeldung anzugeben, die zum Beispiel im Registrierungsformular ausgegeben wird, wenn der Ausdruck nicht erfüllt wird.'
            WHERE `field` = 'USERNAME_REGULAR_EXPRESSION'");
        $stmt->execute();
    }

    public function down()
    {
        $db = DBManager::get();
        $stmt = $db->prepare("UPDATE `config`
            SET `description` = 'Regex for allowed characters in usernames'
            WHERE `field` = 'USERNAME_REGULAR_EXPRESSION'");
        $stmt->execute();

    }
}
