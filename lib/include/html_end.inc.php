    <!-- Footer template -->
    <?= $GLOBALS['template_factory']->render('footer', ['header_template' => $header_template ?? null]) ?>
<!-- Ende Page -->

    <?= SkipLinks::getHTML() ?>

</body>
</html>
