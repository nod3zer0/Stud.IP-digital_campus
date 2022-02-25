<h5><?= sprintf(_('Block-Typ: %s'), htmlReady($title)) ?></h5>
<h6><?= _('Block-Daten') . ': ' ?></h6>
<h6><?= htmlReady(_('Titel') . ' => ' . $payload['title']) ?></h6>
<h6><?= htmlReady(_('Quelle') . ' => ' . $payload['source']) ?></h6>
<h6><?= htmlReady(_('URL') . ' => ' . $payload['url']) ?></h6>

