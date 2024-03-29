<?php
require_once __DIR__ . '/consultation_controller.php';

/**
 * Administration controller for the consultation app.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.3
 */
class Consultation_AdminController extends ConsultationController
{
    const SLOT_COUNT_THRESHOLD = 1000;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!$this->range || $action === 'not_found') {
            return;
        }

        if (!$this->range->isEditableByUser()) {
            $this->redirect('consultation/overview');
            return;
        }

        $this->activateNavigation('admin');
        if (Context::isCourse() || Context::isInstitute()) {
            PageLayout::setTitle(Context::get()->getFullname() . ' - ' . _('Verwaltung der Termine'));
        } else {
            PageLayout::setTitle(_('Verwaltung der Termine'));
        }

        $this->range_config = $this->range->getConfiguration();

        $this->setupSidebar($action, $this->range_config);

        // Show information about which user is edited when a deputy edits
        if ($this->range instanceof User && !$this->isOwnProfile()) {
            $message = sprintf(
                _('Daten von: %1$s (%2$s), Status: %3$s'),
                htmlReady($this->range->getFullName()),
                htmlReady($this->range->username),
                htmlReady($this->range->perms)
            );
            PageLayout::postMessage(
                MessageBox::info($message)
                , 'settings-user-anncouncement'
            );

        }
    }

    private function groupSlots(array $slots)
    {
        $blocks = [];
        foreach ($slots as $slot) {
            if (!isset($blocks[$slot->block_id])) {
                $blocks[$slot->block_id] = [
                    'block' => $slot->block,
                    'slots' => [],
                ];
            }
            $blocks[$slot->block_id]['slots'][] = $slot;
        }
        return $blocks;
    }

    public function index_action($page = 0)
    {
        $this->count = ConsultationSlot::countByRange($this->range);
        $this->limit = Config::get()->ENTRIES_PER_PAGE;

        if ($page >= ceil($this->count / $this->limit)) {
            $page = 0;
        }

        $this->page = max($page, 0);

        if ($GLOBALS['user']->cfg->CONSULTATION_SHOW_GROUPED) {
            $this->blocks = $this->groupSlots(ConsultationSlot::findByRange(
                $this->range,
                "ORDER BY start ASC LIMIT " . ($this->page * $this->limit) . ", {$this->limit}"
            ));
        } else {
            $this->blocks = ConsultationBlock::findByRange(
                $this->range,
                'ORDER BY start ASC'
            );
            $this->slots = ConsultationSlot::findByRange(
                $this->range,
                "ORDER BY start ASC LIMIT " . ($this->page * $this->limit) . ", {$this->limit}"
            );
        }

        $action = $GLOBALS['user']->cfg->CONSULTATION_SHOW_GROUPED ? 'index' : 'ungrouped';
        $this->render_action($action);
    }

    public function expired_action($page = 0)
    {
        $this->count = ConsultationSlot::countByRange($this->range, true);
        $this->limit = Config::get()->ENTRIES_PER_PAGE;

        if ($page >= ceil($this->count / $this->limit)) {
            $page = 0;
        }

        $this->page = max($page, 0);

        if ($GLOBALS['user']->cfg->CONSULTATION_SHOW_GROUPED) {
            $this->blocks = $this->groupSlots(ConsultationSlot::findByRange(
                $this->range,
                "ORDER BY start DESC LIMIT " . ($this->page * $this->limit) . ", {$this->limit}",
                true
            ));
        } else {
            $this->blocks = ConsultationBlock::findByRange(
                $this->range,
                'ORDER BY start DESC',
                true
            );
            $this->slots = ConsultationSlot::findByRange(
                $this->range,
                "ORDER BY start DESC LIMIT " . ($this->page * $this->limit) . ", {$this->limit}",
                true
            );
        }

        $action = $GLOBALS['user']->cfg->CONSULTATION_SHOW_GROUPED ? 'index' : 'ungrouped';
        $this->render_action($action);
    }

    public function create_action()
    {
        PageLayout::setTitle(_('Neue Terminblöcke anlegen'));

        $this->room = '';
        $this->responsible = false;

        // TODO: inst_default?
        if ($this->range instanceof User) {
            $rooms = $this->range->institute_memberships->pluck('Raum');
            $rooms = array_filter($rooms);
            $this->room = $rooms ? reset($rooms) : '';
        } elseif ($this->range instanceof Course) {
            $this->room = $this->range->ort;

            $block = new ConsultationBlock();
            $block->range = $this->range;
            $this->responsible = $block->getPossibleResponsibilites();
        } elseif ($this->range instanceof Institute) {
            $block = new ConsultationBlock();
            $block->range = $this->range;
            $this->responsible = $block->getPossibleResponsibilites();
        }
    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        try {
            $start = $this->getDateAndTime('start');
            $end = $this->getDateAndTime('end');

            if (date('Hi', $end) <= date('Hi', $start)) {
                throw new InvalidArgumentException(_('Die Endzeit liegt vor der Startzeit!'));
            }

            // Determine duration of a slot and pause times
            $duration = Request::int('duration');
            $pause_time = Request::bool('pause') ? Request::int('pause_time') : null;
            $pause_duration = Request::bool('pause') ? Request::int('pause_duration') : null;
            if ($pause_time && $pause_time < $duration) {
                throw new InvalidArgumentException(_('Die definierte Zeit bis zur Pause ist kleiner als die Dauer eines Termins.'));
            }

            if ($this->range instanceof Institute && !Request::getArray('responsibilities')) {
                throw new InvalidArgumentException(_('Es muss mindestens eine durchführende Person, Statusgruppe oder Einrichtung ausgewählt werden.'));
            }

            $slot_count = ConsultationBlock::countSlots(
                $start,
                $end,
                Request::int('day-of-week'),
                Request::int('interval'),
                Request::int('duration'),
                $pause_time,
                $pause_duration
            );
            if ($slot_count >= self::SLOT_COUNT_THRESHOLD && !Request::int('confirmed')) {
                $this->flash['confirm-many'] = $slot_count;
                throw new Exception('', -1);
            }

            $blocks = ConsultationBlock::generateBlocks(
                $this->range,
                $start,
                $end,
                Request::int('day-of-week'),
                Request::int('interval')
            );

            $stored = 0;
            foreach ($blocks as $block) {
                $block->room              = Request::get('room');
                $block->calendar_events   = Request::bool('calender-events', false);
                $block->show_participants = Request::bool('show-participants', false);
                $block->require_reason    = Request::option('require-reason');
                $block->mail_to_tutors    = Request::bool('mail-to-tutors', false);
                $block->confirmation_text = trim(Request::get('confirmation-text')) ?: null;
                $block->note              = Request::get('note');
                $block->size              = Request::int('size', 1);
                $block->lock_time         = Request::int('lock_time');

                $slots = $block->createSlots(Request::int('duration'), $pause_time, $pause_duration);
                if (count($slots) === 0) {
                    continue;
                }

                $block->slots->exchangeArray($slots);

                $stored += $block->store();

                // Store block responsibilites
                foreach (Request::getArray('responsibilities') as $type => $ids) {
                    foreach ($ids as $id) {
                        ConsultationResponsibility::create([
                            'block_id'   => $block->id,
                            'range_id'   => $id,
                            'range_type' => $type,
                        ]);
                    }
                }
            }
        } catch (OverlapException $e) {
            $this->keepRequest();

            PageLayout::postError($e->getMessage(), $e->getDetails());
            $this->redirect('consultation/admin/create');
            return;
        } catch (Exception $e) {
            $this->keepRequest();

            if ($e->getCode() !== -1) {
                PageLayout::postError($e->getMessage());
            }
            $this->redirect('consultation/admin/create');
            return;
        }

        if ($stored === 0) {
            PageLayout::postError(_('In dem von Ihnen gewählten Zeitraum konnten für den gewählten Wochentag keine Termine erzeugt werden.'));
        } else {
            PageLayout::postSuccess(_('Die Terminblöcke wurden erfolgreich angelegt.'));
        }
        $this->relocate('consultation/admin');
    }

    public function note_action($block_id, $slot_id, $page = 0)
    {
        PageLayout::setTitle(_('Anmerkung zu diesem Termin bearbeiten'));

        $this->block   = $this->loadBlock($block_id);
        $this->slot_id = $slot_id;
        $this->page    = $page;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $note = trim(Request::get('note'));

            $slot = $this->block->slots->find($slot_id);
            $slot->note = $note;
            if ($slot->store()) {
                PageLayout::postSuccess(_('Die Anmerkung wurde bearbeitet'));
            }

            if ($this->block->is_expired) {
                $this->redirect("consultation/admin/expired/{$page}#block-{$block_id}");
            } else {
                $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
            }
        }
    }

    public function remove_action($block_id, $slot_id = null, $page = 0)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        if (!$slot_id) {
            $block = $this->loadBlock($block_id);
            $is_expired = $block->is_expired;

            $invalid = 0;
            foreach ($block->slots as $slot) {
                if (!$slot->is_expired && $slot->has_bookings) {
                    $invalid += 1;
                } else {
                    $slot->delete();
                }
            }

            if ($invalid > 0) {
                PageLayout::postError(implode(' ', [
                    _('Sie können mindestens einen Termin nicht löschen, da er bereits belegt ist.'),
                    _('Bitte sagen Sie diese Termine erst ab.')
                ]));
            } else {
                $block->delete();
                PageLayout::postSuccess(_('Die Termine wurden gelöscht'));
            }

        } else {
            $this->slot = $this->loadSlot($block_id, $slot_id);
            $is_expired = $this->slot->is_expired;

            if (!$this->slot->is_expired && $this->slot->has_bookings) {
                PageLayout::postError(implode(' ', [
                    _('Sie können diesen Termin nicht löschen, da er bereits belegt ist.'),
                    _('Bitte sagen Sie den Termin erst ab.')
                ]));
            } else {
                $this->slot->delete();
                PageLayout::postSuccess(_('Der Termin wurde gelöscht'));
            }
        }

        if ($is_expired) {
            $this->redirect("consultation/admin/expired/{$page}#block-{$block_id}");
        } else {
            $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
        }
    }

    public function book_action($block_id, $slot_id, $page = 0)
    {
        PageLayout::setTitle(_('Termin reservieren'));

        $this->slot = $this->loadSlot($block_id, $slot_id);
        $this->page = $page;

        $permissions = ['user', 'autor', 'tutor'];
        if (Config::get()->CONSULTATION_ALLOW_DOCENTS_RESERVING) {
            $permissions[] = 'dozent';
        }

        $this->search_object = new PermissionSearch('user', _('Person suchen'), 'user_id', ['permission' => $permissions]);
        if ($this->range instanceof Course) {
            $this->search_object = new PermissionSearch('user_in_sem', _('Person suchen'), 'user_id', [
                'seminar_id' => $this->range->getRangeId(),
                'sem_perm'   => $permissions,
            ]);
        }

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            if ($this->slot->isOccupied()) {
                PageLayout::postError(_('Dieser Termin ist bereits belegt.'));
            } else {
                $booking = new ConsultationBooking();
                $booking->slot_id = $this->slot->id;
                $booking->user_id = Request::option('user_id');
                $booking->reason  = trim(Request::get('reason'));
                $booking->store();

                PageLayout::postSuccess(_('Der Termin wurde reserviert.'));
            }

            $this->redirect("consultation/admin/index/{$page}#slot-{$this->slot->id}");
        }
    }

    public function edit_action($block_id, $page = 0)
    {
        PageLayout::setTitle(_('Block bearbeiten'));

        $this->block = $this->loadBlock($block_id);
        $this->page  = $page;

        $this->responsible = false;
        if ($this->block->range instanceof Course || $this->block->range instanceof Institute) {
            $this->responsible = $this->block->getPossibleResponsibilites();
        }
    }

    public function store_edited_action($block_id, $page = 0)
    {
        CSRFProtection::verifyUnsafeRequest();

        $this->block = $this->loadBlock($block_id);
        $this->block->room = trim(Request::get('room'));
        $this->block->note = trim(Request::get('note'));
        $this->block->size = Request::int('size');
        $this->block->calendar_events = Request::bool('calender-events', false);
        $this->block->show_participants = Request::bool('show-participants', false);
        $this->block->require_reason = Request::option('require-reason');
        $this->block->mail_to_tutors = Request::bool('mail-to-tutors', false);
        $this->block->confirmation_text = trim(Request::get('confirmation-text'));
        $this->block->lock_time = Request::int('lock_time');

        foreach ($this->block->slots as $slot) {
            $slot->note = '';
        }

        // Store block responsibilites
        $responsibilities = array_merge(
            ['user' => [], 'statusgroup' => [], 'institute' => []],
            Request::getArray('responsibilities')
        );
        foreach ($responsibilities as $type => $ids) {
            $of_type = $this->block->responsibilities->filter(function ($responsibility) use ($type) {
                return $responsibility->range_type === $type;
            });

            // Delete removed responsibilites
            $of_type->each(function ($responsibility) use ($ids) {
                if (!in_array($responsibility->range_id, $ids)) {
                    $responsibility->delete();
                }
            });
            // Add new responsibilities
            foreach ($ids as $id) {
                if (!$of_type->findOneBy('range_id', $id)) {
                    ConsultationResponsibility::create([
                        'block_id'   => $this->block->id,
                        'range_id'   => $id,
                        'range_type' => $type,
                    ]);
                }
            }
        }


        $this->block->store();

        PageLayout::postSuccess(_('Der Block wurde gespeichert.'));

        if ($this->block->is_expired) {
            $this->redirect("consultation/admin/expired/{$page}#block-{$block_id}");
        } else {
            $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
        }
    }

    public function cancel_block_action($block_id, $page = 0)
    {
        PageLayout::setTitle(_('Termine absagen'));

        $this->block = $this->loadBlock($block_id);
        $this->page  = $page;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $reason = trim(Request::get('reason'));
            foreach ($this->block->slots as $slot) {
                foreach ($slot->bookings as $booking) {
                    $booking->cancel($reason);
                }
            }

            PageLayout::postSuccess(_('Die Termine wurden abgesagt.'));
            $this->redirect("consultation/admin/index/{$page}#block-{$block_id}");
        }
    }

    public function cancel_slot_action($block_id, $slot_id, $page = 0)
    {
        PageLayout::setTitle(_('Termin absagen'));

        $this->slot = $this->loadSlot($block_id, $slot_id);
        $this->page = $page;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $ids = false;
            if (count($this->slot->bookings) > 1) {
                $ids = Request::intArray('ids');
            }

            $removed = 0;
            $reason  = trim(Request::get('reason'));
            foreach ($this->slot->bookings as $booking) {
                if ($ids !== false && !in_array($booking->id, $ids)) {
                    continue;
                }

                $removed += $booking->cancel($reason);
            }

            if ($removed === count($this->slot->bookings)) {
                PageLayout::postSuccess(_('Der Termin wurde abgesagt.'));
            } elseif ($removed > 1) {
                PageLayout::postSuccess(sprintf(
                    _('Der Termin wurde für %u Personen abgesagt.'),
                    $removed
                ));
            } elseif ($removed === 1) {
                PageLayout::postSuccess(_('Der Termin wurde für eine Person abgesagt.'));
            }
            $this->redirect("consultation/admin/index/{$page}#slot-{$this->slot->id}");
        }
    }

    public function reason_action($block_id, $slot_id, $booking_id, $page = 0)
    {
        PageLayout::setTitle(_('Grund für die Buchung bearbeiten'));

        $this->booking = $this->loadBooking($block_id, $slot_id, $booking_id);
        $this->page    = $page;

        if (Request::isPost()) {
            CSRFProtection::tokenTag();

            $this->booking->reason = trim(Request::get('reason'));
            $this->booking->store();

            PageLayout::postSuccess(_('Der Grund für die Buchung wurde bearbeitet.'));

            if ($this->booking->slot->block->is_expired) {
                $this->redirect("consultation/admin/expired/{$page}#slot-{$this->booking->slot->id}");
            } else {
                $this->redirect("consultation/admin/index/{$page}#slot-{$this->booking->slot->id}");
            }
        }
    }

    public function toggle_action($what, $state, $expired = false)
    {
        if ($what === 'messages') {
            // TODO: Applicable     everywhere?
            $this->getUserConfig()->store(
                'CONSULTATION_SEND_MESSAGES',
                (bool) $state
            );
        } elseif ($what === 'garbage') {
            // TODO: Not available for course
            $this->range_config->store(
                'CONSULTATION_GARBAGE_COLLECT',
                (bool) $state
            );
        } elseif ($what === 'grouped') {
            $this->getUserConfig()->store(
                'CONSULTATION_SHOW_GROUPED',
                (bool) $state
            );
        }

        $this->redirect('consultation/admin/' . ($expired ? 'expired' : 'index'));
    }

    public function bulk_action($page, $expired = false)
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $this->slots = $this->getSlotsFromBulk();

        if (count($this->slots) === 0) {
            PageLayout::postInfo(_('Sie haben keine Termine gewählt.'));
        } elseif (Request::submitted('delete')) {
            $has_bookings = $this->slots->any(function ($slot) {
                return !$slot->is_expired
                    && $slot->has_bookings;
            });

            if (!$has_bookings) {
                $deleted = 0;
                foreach ($this->slots as $slot) {
                    $deleted += $slot->delete();
                }
                PageLayout::postSuccess(_('Die Termine wurden gelöscht'));
            } elseif (Request::option('delete') === 'skip') {
                $deleted = 0;
                foreach ($this->slots as $slot) {
                    if ($slot->is_expired || !$slot->has_bookings) {
                        $deleted += $slot->delete();
                    }
                }

                if ($deleted === 1) {
                    PageLayout::postSuccess(_('Der freie Termin wurde gelöscht'));
                } elseif ($deleted > 0) {
                    PageLayout::postSuccess(_('Die freien Termine wurden gelöscht'));
                }
            } elseif (Request::option('delete') === 'cancel' && Request::submitted('reason')) {
                $reason = trim(Request::get('reason'));

                $deleted = 0;
                foreach ($this->slots as $slot) {
                    if (!$slot->is_expired && $slot->has_bookings) {
                        foreach ($slot->bookings as $booking) {
                            $booking->cancel($reason);
                        }
                    }
                    $deleted += $slot->delete();
                }

                PageLayout::postSuccess(_('Die Termine wurden gelöscht'));
            } else {
                PageLayout::setTitle(_('Termine löschen'));
                $this->action = $this->bulk($page, $expired);
                $this->allow_delete = true;
                $this->mixed = $this->slots->any(function ($slot) {
                    return !$slot->has_bookings;
                });
                $this->render_action('cancel_slots');
                return;
            }
        } elseif (Request::submitted('cancel')) {
            if (!Request::submitted('reason')) {
                PageLayout::setTitle(_('Termine absagen'));
                $this->action = $this->bulk($page, $expired);
                $this->render_action('cancel_slots');
                return;
            } else {
                $reason = trim(Request::get('reason'));

                $canceled = 0;
                foreach ($this->slots as $slot) {
                    foreach ($slot->bookings as $booking) {
                        $canceled += $booking->cancel($reason);
                    }
                }

                if ($canceled === 1) {
                    PageLayout::postSuccess(_('Der Termin wurde abgesagt.'));
                } elseif ($canceled > 0) {
                    PageLayout::postSuccess(_('Die Termine wurden abgesagt.'));
                }
            }
        }

        if ($expired) {
            $this->relocate("consultation/admin/expired/{$page}");
        } else {
            $this->relocate("consultation/admin/index/{$page}");
        }
    }

    public function purge_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $deleted = ['current' => 0, 'expired' => 0];

        ConsultationSlot::findEachBySQL(
            function ($slot) use (&$deleted) {
                $index = $slot->is_expired ? 'expired' : 'current';

                $deleted[$index] += $slot->delete();
            },
            "JOIN consultation_blocks USING (block_id) WHERE range_id = ? AND range_type = ?",
            [$this->range->getRangeId(), $this->range->getRangeType()]
        );

        if (array_sum($deleted) > 0) {
            if ($deleted['current'] > 0) {
                PageLayout::postSuccess(sprintf(
                    _('%u aktuelle Termine wurden gelöscht'),
                    $deleted['current']
                ));
            }
            if ($deleted['expired'] > 0) {
                PageLayout::postSuccess(sprintf(
                    _('%u vergangene Termine wurden gelöscht'),
                    $deleted['expired']
                ));
            }
        }

        $this->redirect('consultation/admin/index');
    }

    public function mail_action($block_id, $slot_id = null)
    {
        // Get matching slots
        if ($block_id === 'bulk') {
            $slots = $this->getSlotsFromBulk();
        } else {
            $block = $this->loadBlock($block_id);
            $slots = [];
            foreach ($block->slots as $slot) {
                if ($slot_id && $slot->id != $slot_id) {
                    continue;
                }

                $slots[] = $slot;
            }
        }

        // Get user names and timestamps
        $p_rec = [];
        $timestamps = [];
        if(!empty($slots)) {
            foreach ($slots as $slot) {
                if (count($slot->bookings) === 0) {
                    continue;
                }

                $timestamps[] = $slot->start_time;
                foreach ($slot->bookings as $booking) {
                    $p_rec[] = $booking->user->username;
                }
            }
        }

        // Get unique usernames
        $p_rec = array_unique($p_rec);

        // Get correct default subject
        $default_subject = _('Termin');
        if (count($timestamps) === 1) {
            $default_subject = sprintf(
                _('Termin am %s um %s'),
                strftime('%x', $timestamps[0]),
                strftime('%R', $timestamps[0])
            );
        } else {
            $days = array_unique(array_map(function ($timestamp) {
                return strftime('%x', $timestamp);
            }, $timestamps));
            if (count($days) === 1) {
                $default_subject = sprintf(
                    _('Termin am %s'),
                    $days[0]
                );
            }
        }

        // Redirect to message write
        $_SESSION['sms_data'] = compact('p_rec');
        page_close();
        $this->redirect(URLHelper::getURL(
            'dispatch.php/messages/write',
            compact('default_subject')
        ));
    }

    public function tab_action($from_expired)
    {
        if (Request::isPost()) {
            $this->range_config->store('CONSULTATION_TAB_TITLE', Request::i18n('tab_title'));

            PageLayout::postSuccess(_('Der Name wurde gespeichert'));
            if ($from_expired) {
                $this->redirect('consultation/admin/expired');
            } else {
                $this->redirect('consultation/admin/index');
            }
            return;
        }

        $this->current_title = $this->range_config->getValue('CONSULTATION_TAB_TITLE');
        $this->from_expired  = $from_expired;
    }

    private function getSlotsFromBulk()
    {
        $slots = [];

        $block_ids = Request::intArray('block-id');
        $slot_ids  = Request::getArray('slot-id');

        foreach ($this->loadBlock($block_ids) as $block) {
            foreach ($block->slots as $slot) {
                $slots[$slot->id] = $slot;
            }
        }

        foreach ($slot_ids as $slot_id) {
            [$block_id, $slot_id] = explode('-', $slot_id);
            try {
                if ($slot = $this->loadSlot($block_id, $slot_id)) {
                    $slots[$slot->id] = $slot;
                }
            } catch (Exception $e) {
            }
        }

        return SimpleCollection::createFromArray($slots);
    }

    private function setupSidebar($action, $config)
    {
        $sidebar = Sidebar::get();

        $views = $sidebar->addWidget(new ViewsWidget());
        $views->addLink(
            _('Aktuelle Termine'),
            $this->indexURL()
        )->setActive($action !== 'expired');
        $views->addLink(
            _('Vergangene Termine'),
            $this->expiredURL()
        )->setActive($action === 'expired');

        $actions = $sidebar->addWidget(new ActionsWidget());
        $actions->addLink(
            _('Terminblöcke anlegen'),
            $this->createURL(),
            Icon::create('add')
        )->asDialog('size=auto');
        $actions->addLink(
            _('Namen des Reiters ändern'),
            $this->tabURL($action === 'expired'),
            Icon::create('edit')
        )->asDialog('size=auto');

        $actions->addLink(
            _('Alle Termine löschen'),
            $this->purgeURL(),
            Icon::create('trash'),
            ['onclick' => 'return STUDIP.Dialog.confirmAsPost(' . json_encode(_('Wollen Sie wirklich alle Termine löschen?')) . ', this.href);']
        );

        $options = $sidebar->addWidget(new OptionsWidget());
        $options->addCheckbox(
            _('Benachrichtungen über Buchungen'),
            $this->getUserConfig()->getValue('CONSULTATION_SEND_MESSAGES'),
            $this->toggleURL('messages', 1, $action === 'expired'),
            $this->toggleURL('messages', 0, $action === 'expired')
        );
        $options->addCheckbox(
            _('Abgelaufene Terminblöcke automatisch löschen'),
            $config->CONSULTATION_GARBAGE_COLLECT,
            $this->toggleURL('garbage', 1, $action === 'expired'),
            $this->toggleURL('garbage', 0, $action === 'expired')
        );
        $options->addCheckbox(
            _('Termine gruppiert anzeigen'),
            $this->getUserConfig()->getValue('CONSULTATION_SHOW_GROUPED'),
            $this->toggleURL('grouped', 1, $action === 'expired'),
            $this->toggleURL('grouped', 0, $action === 'expired')
        );

        $export = $sidebar->addWidget(new ExportWidget());
        $export->addLink(
            _('Anmeldungen exportieren'),
            $this->url_for('consultation/export/bookings', $action === 'expired'),
            Icon::create('export')
        );
        $export->addLink(
            _('Alle Termine exportieren'),
            $this->url_for('consultation/export/all', $action === 'expired'),
            Icon::create('export')
        );

        if ($action !== 'expired') {
            $share = new ShareWidget();
            $share->addCopyableLink(
                _('Link zur Terminübersicht kopieren'),
                $this->url_for('consultation/overview', [
                    'again'  => 'yes',
                ]),
                Icon::create('clipboard')
            );
            $sidebar->addWidget($share);
        }
    }

    private function getDateAndTime($index)
    {
        if (!Request::submitted("{$index}-date") || !Request::submitted("{$index}-time")) {
            throw new Exception("Date with index '{$index}' was not submitted properly");
        }

        return strtotime(implode(' ', [
            Request::get("{$index}-date"),
            Request::get("{$index}-time")
        ]));
    }

    private function getUserConfig(): RangeConfig
    {
        return $this->range instanceof User
             ? $this->range->getConfiguration()
             : $GLOBALS['user']->cfg;
    }

    private function isOwnProfile()
    {
        return $this->range->username === $GLOBALS['user']->username;
    }
}
