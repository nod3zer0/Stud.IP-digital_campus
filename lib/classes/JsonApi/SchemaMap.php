<?php

namespace JsonApi;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SchemaMap
{
    public function __invoke(): array
    {
        return [
            \Slim\Routing\Route::class => Schemas\SlimRoute::class,

            \JsonApi\Models\ScheduleEntry::class => Schemas\ScheduleEntry::class,

            \BlubberComment::class => Schemas\BlubberComment::class,
            \BlubberStatusgruppeThread::class => Schemas\BlubberStatusgruppeThread::class,
            \BlubberThread::class => Schemas\BlubberThread::class,

            \CalendarDateAssignment::class => Schemas\CalendarEvent::class,
            \ConsultationBlock::class => Schemas\ConsultationBlock::class,
            \ConsultationBooking::class => Schemas\ConsultationBooking::class,
            \ConsultationSlot::class => Schemas\ConsultationSlot::class,
            \ConfigValue::class => Schemas\ConfigValue::class,
            \ContentTermsOfUse::class => Schemas\ContentTermsOfUse::class,
            \Course::class => Schemas\Course::class,
            \CourseMember::class => Schemas\CourseMember::class,
            \CourseDate::class => Schemas\CourseEvent::class,
            \CourseExDate::class => Schemas\CourseEvent::class,
            \FeedbackElement::class => Schemas\FeedbackElement::class,
            \FeedbackEntry::class => Schemas\FeedbackEntry::class,
            \JsonApi\Models\ForumCat::class => Schemas\ForumCategory::class,
            \JsonApi\Models\ForumEntry::class => Schemas\ForumEntry::class,
            \Institute::class => Schemas\Institute::class,
            \InstituteMember::class => Schemas\InstituteMember::class,
            \LtiTool::class => Schemas\LtiTool::class,
            \Message::class => Schemas\Message::class,
            \SemClass::class => Schemas\SemClass::class,
            \Semester::class => Schemas\Semester::class,
            \SemType::class => Schemas\SemType::class,
            \SeminarCycleDate::class => Schemas\SeminarCycleDate::class,
            \Statusgruppen::class => Schemas\StatusGroup::class,
            \StockImage::class => Schemas\StockImage::class,
            \JsonApi\Models\Studip::class => Schemas\Studip::class,
            \JsonApi\Models\StudipProperty::class => Schemas\StudipProperty::class,
            \StudipComment::class => Schemas\StudipComment::class,
            \StudipNews::class => Schemas\StudipNews::class,
            \StudipTreeNode::class => Schemas\TreeNode::class,
            \WikiPage::class => Schemas\WikiPage::class,
            \Studip\Activity\Activity::class => Schemas\Activity::class,
            \User::class => Schemas\User::class,
            \File::class => Schemas\File::class,
            \FileRef::class => Schemas\FileRef::class,
            \FolderType::class => Schemas\Folder::class,

            \Courseware\Block::class => Schemas\Courseware\Block::class,
            \Courseware\BlockComment::class => Schemas\Courseware\BlockComment::class,
            \Courseware\BlockFeedback::class => Schemas\Courseware\BlockFeedback::class,
            \Courseware\Clipboard::class => Schemas\Courseware\Clipboard::class,
            \Courseware\Container::class => Schemas\Courseware\Container::class,
            \Courseware\Instance::class => Schemas\Courseware\Instance::class,
            \Courseware\PublicLink::class => Schemas\Courseware\PublicLink::class,
            \Courseware\StructuralElement::class => Schemas\Courseware\StructuralElement::class,
            \Courseware\StructuralElementComment::class => Schemas\Courseware\StructuralElementComment::class,
            \Courseware\StructuralElementFeedback::class => Schemas\Courseware\StructuralElementFeedback::class,
            \Courseware\Task::class => Schemas\Courseware\Task::class,
            \Courseware\TaskFeedback::class => Schemas\Courseware\TaskFeedback::class,
            \Courseware\TaskGroup::class => Schemas\Courseware\TaskGroup::class,
            \Courseware\Template::class => Schemas\Courseware\Template::class,
            \Courseware\Unit::class => Schemas\Courseware\Unit::class,
            \Courseware\UserDataField::class => Schemas\Courseware\UserDataField::class,
            \Courseware\UserProgress::class => Schemas\Courseware\UserProgress::class,
        ];
    }
}
