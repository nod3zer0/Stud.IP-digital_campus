<?php
/**
 * loginstyle.php - controller class for administration of login background pics
 *
 * @author    Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license   GPL2 or any later version
 * @category  Stud.IP
 * @package   admin
 * @since     4.0
 */

class Admin_LoginStyleController extends AuthenticatedController
{
    protected $_autobind = true;
    /**
     * common tasks for all actions
     *
     * @param String $action Action that has been called
     * @param Array  $args   List of arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');

        //setting title and navigation
        PageLayout::setTitle(_('Hintergrundbilder für den Startbildschirm'));
        Navigation::activateItem('/admin/locations/loginstyle');

        $views = new ViewsWidget();
        $views->addLink(
            _('Bilder'),
            $this->indexURL()
        )->setActive($action === 'index');

        $views->addLink(
            _('Hinweise zum Login'),
            $this->login_faqURL()
        )->setActive($action === 'login_faq');

        Sidebar::Get()->addWidget($views);
    }

    /**
     * Display all available background pictures
     */
    public function index_action()
    {
        // Setup sidebar
        $this->setSidebar('index');
        $this->pictures = LoginBackground::findBySQL("1 ORDER BY `background_id`");
    }

    /**
     * Provides a form for uploading a new picture.
     */
    public function newpic_action()
    {
    }

    /**
     * Adds a new picture ass possible login background.
     */
    public function add_pic_action()
    {
        CSRFProtection::verifyRequest();
        $success = 0;
        foreach ($_FILES['pictures']['name'] as $index => $filename) {
            if ($_FILES['pictures']['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            if (!in_array($extension, ['gif', 'jpeg', 'jpg', 'png'])) {
                continue;
            }

            $entry = new LoginBackground();
            $entry->filename = $filename;
            $entry->desktop = Request::int('desktop', 0);
            $entry->mobile = Request::int('mobile', 0);
            if ($entry->store()) {
                $destination = LoginBackground::getPictureDirectory() . DIRECTORY_SEPARATOR
                             . $entry->id . '.' . $extension;
                if (move_uploaded_file($_FILES['pictures']['tmp_name'][$index], $destination)) {
                    $success++;
                } else {
                    $entry->delete();
                }
            }
        }

        if ($success > 0) {
            PageLayout::postSuccess(sprintf(ngettext(
                'Ein Bild wurde hochgeladen.',
                '%u Bilder wurden hochgeladen',
                $success
            ), $success));
        }

        $fail = count($_FILES['pictures']['name']) - $success;
        if ($fail > 0) {
            PageLayout::postError(sprintf(ngettext(
                'Ein Bild konnte nicht hochgeladen werden.',
                '%u Bilder konnten nicht hochgeladen werden.',
                $fail
            ), $fail));
        }
        $this->relocate($this->indexURL());
    }

    /**
     * Deletes the given picture.
     * @param string $id the picture to delete
     */
    public function delete_pic_action($id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $pic = LoginBackground::find($id);
        if ($pic->in_release) {
            PageLayout::postError(_('Dieses Bild wird vom System mitgeliefert und kann daher nicht gelöscht werden.'));
        } elseif ($pic->delete()) {
            PageLayout::postSuccess(_('Das Bild wurde gelöscht.'));
        } else {
            PageLayout::postError(_('Das Bild konnte nicht gelöscht werden.'));
        }

        $this->relocate($this->indexURL());
    }

    /**
     * (De-)activate the given picture for given view.
     * @param string $id the picture to change activation for
     * @param string $view one of 'desktop', 'mobile', view to (de-) activate picture for
     * @param string $newStatus new activation status for given view.
     */
    public function activation_action($id, $view, $newStatus)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!in_array($view, ['desktop', 'mobile'])) {
            throw new InvalidArgumentException('You may not change this attribute.');
        }

        $pic = LoginBackground::find($id);
        $pic->$view = $newStatus;
        if ($pic->store()) {
            PageLayout::postSuccess(_('Der Aktivierungsstatus wurde gespeichert.'));
        } else {
            PageLayout::postSuccess(_('Der Aktivierungsstatus konnte nicht gespeichert werden.'));
        }
        $this->relocate($this->indexURL());
    }


    /**
     * FAQ part of login page
     */
    public function login_faq_action()
    {
        PageLayout::setTitle(_('Hinweise zum Login für den Startbildschirm'));

        $this->setSidebar('login_faq');
        $this->faq_entries = LoginFaq::findBySql('1');
    }

    public function edit_faq_action(LoginFaq $entry = null)
    {
        PageLayout::setTitle(
            $entry->isNew() ? _('Hinweistext hinzufügen') : _('Hinweistext bearbeiten')
        );
    }

    public function store_faq_action(LoginFaq $entry = null)
    {
        CSRFProtection::verifyRequest();

        $entry->setData([
            'title' => trim(Request::get('title')),
            'description' => trim(Request::get('description')),
        ]);

        if ($entry->store()) {
            PageLayout::postSuccess(_('Hinweistext wurde gespeichert.'));
        }

        $this->relocate($this->login_faqURL());
    }

    public function delete_faq_action(LoginFaq $entry)
    {
        CSRFProtection::verifyRequest();

        if ($entry->delete()) {
            PageLayout::postSuccess(_('Der Hinweistext wurde gelöscht.'));
        }

        $this->relocate($this->login_faqURL());
    }

    /**
     * Adds the content to sidebar
     */
    protected function setSidebar($action)
    {
        $links = new ActionsWidget();
        if ($action === 'index') {
            $links->addLink(
                _('Bild hinzufügen'),
                $this->newpicURL(),
                Icon::create('add')
            )->asDialog('size=auto');
        } else if ($action === 'login_faq') {
            $links->addLink(
                _('Hinweistext hinzufügen'),
                $this->edit_faqURL(),
                Icon::create('add')
            )->asDialog();
        }
        Sidebar::get()->addWidget($links);
    }
}
