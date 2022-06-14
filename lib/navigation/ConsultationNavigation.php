<?php
class ConsultationNavigation extends Navigation
{
    /** @var Range */
    protected $range;

    public function __construct(Range $range)
    {
        $this->range = $range;

        parent::__construct($range->getConfiguration()->CONSULTATION_TAB_TITLE);
    }

    public function initItem()
    {
        parent::initItem();

        if ($this->range->isEditableByUser()) {
            $this->setURL('dispatch.php/consultation/admin');
        }
    }

    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        $active = parent::isActive();

        if ($active) {
            if ($this->range instanceof User) {
                URLHelper::addLinkParam('username', $this->range->username);
            } elseif ($this->range instanceof Course || $this->range instanceof Institute) {
                URLHelper::addLinkParam('cid', $this->range->id);
            }
        }

        return $active;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        if ($this->range->isEditableByUser()) {
            $this->addSubNavigation('admin', new Navigation(
                _('Verwaltung'),
                'dispatch.php/consultation/admin'
            ));

            return;
        }

        if (!ConsultationBooking::userMayCreateBookingForRange($this->range, User::findCurrent())) {
            return;
        }

        // Create visitor navigation
        $this->addSubNavigation('overview', new Navigation(_('Übersicht'), 'dispatch.php/consultation/overview'));
        $this->addSubNavigation('booked', new Navigation(_('Meine Buchungen'), 'dispatch.php/consultation/overview/booked'));
    }
}
