<label>
    <input type="checkbox"
           name="locked"
           <?= $folder->data_content && $folder->data_content['locked'] ? 'checked' : '' ?>
           value="1">
    <?= _('Upload für Studierende sperren') ?>
</label>
<?= _('Uploads sind weiterhin in entsprechenden Unterordnern möglich') ?>
