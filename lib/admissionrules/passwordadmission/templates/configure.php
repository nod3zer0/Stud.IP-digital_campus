<h3><?= htmlReady($rule->getName()) ?></h3>
<label>
    <?= _('Nachricht bei fehlgeschlagener Anmeldung') ?>:
    <textarea name="message" rows="4" cols="50"><?= htmlReady($rule->getMessage()) ?></textarea>
</label>
<label>
    <?= _('Zugangspasswort') ?>:
    <input type="password" name="password1" size="25" max="40" value="<?= htmlReady($rule->getPassword()) ?>" required>
</label>
<br/>
<label>
    <?= _('Passwort wiederholen') ?>:
    <input type="password" name="password2" size="25" max="40" value="<?= htmlReady($rule->getPassword()) ?>" required>
</label>
