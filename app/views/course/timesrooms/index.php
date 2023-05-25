<? if (!empty($show['roomRequest'])) : ?>
    <!--Raumanfragen-->
    <?= $this->render_partial('course/timesrooms/_roomRequestInfo.php') ?>
<? endif ?>

<? if (Request::isXhr()): ?>
    <?= $this->render_partial('course/timesrooms/_select_semester_range.php') ?>
<? endif ?>

<? if (!empty($show['regular'])) : ?>
    <!--Regelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_regularEvents.php') ?>
<? endif ?>

<? if (!empty($show['irregular'])) : ?>
    <!--Unregelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_irregularEvents') ?>
<? endif ?>

<? if (!empty($show['roomRequest'])) : ?>
    <!--Raumanfrage-->
    <?= $this->render_partial('course/timesrooms/_roomRequest.php') ?>
<? endif ?>

