<?php
/**
 * @var FeedbackElement $feedback
 * @var FeedbackEntry|null $entry
 */
?>
<? if ($feedback->mode != FeedbackElement::MODE_NO_RATING) : ?>
<?php
    $n = 5;
    if ($feedback->mode == FeedbackElement::MODE_10STAR_RATING) {
        $n = 10;
    }
?>
<div class="rating">
    <p><?= _('Bewertung') ?></p>
    <? for ($i = 1; $i < $n+1; $i++) : ?>
    <label class="star-rating undecorated <?= (isset($entry) && $i <= $entry->rating) || $i === 1 ? ' checked' : '' ?>">
        <input class="star-rating-input" name="rating" value="<?= $i ?>" type="radio"
               required
               <? if (isset($entry) && $i == $entry->rating) echo 'selected'; ?>>
        <?= Icon::create('star') ?>
    </label>
    <? endfor; ?>
</div>
<? endif; ?>
<? if ($feedback->commentable) : ?>
<label>
    <?= _('Kommentar') ?>
    <textarea name="comment"><?= htmlReady(isset($entry) ? $entry->comment : '') ?></textarea>
</label>
<? endif; ?>
<? if ($feedback->anonymous_entries) : ?>
<label>
    <input type="checkbox" name="anonymous" value="1" <?= $entry->anonymous ? 'checked' : '' ?> >
    <?= _('Kommentar anonym abgeben') ?>
</label>
<? endif; ?>
<div>
    <?= Studip\Button::createAccept(_('Absenden'), 'add', ['class' => 'feedback-entry-submit']) ?>
    <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['class' => 'feedback-entry-cancel']) ?>
</div>
