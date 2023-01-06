<?php

namespace Studip\Activity;

use Courseware\Block;
use Courseware\BlockComment;
use Courseware\BlockFeedback;
use Courseware\Container;
use Courseware\StructuralElement;
use Courseware\StructuralElementComment;
use Courseware\StructuralElementFeedback;
use Courseware\Task;
use Courseware\TaskFeedback;

class CoursewareProvider implements ActivityProvider
{
    public function getActivityDetails($activity)
    {
        $structural_element = StructuralElement::find($activity->object_id);
        if (!$structural_element) {
            return false;
        }

        $activity->content = formatReady($activity->getValue('content'));

        if ($activity->context == 'course') {
            $url =
                \URLHelper::getURL('dispatch.php/course/courseware/?cid=') .
                $activity->context_id .
                '#/structural_element/' .
                $structural_element->id;
            $activity->object_url = [
                $url => _('Zur Courseware in der Veranstaltung'),
            ];
        } elseif ($activity->context == 'user') {
            $url =
                \URLHelper::getURL('dispatch.php/contents/my_contents') .
                '#/structural_element/' .
                $structural_element->id;
            $activity->object_url = [
                $url => _('Zur eigenen Courseware'),
            ];
        }

        return true;
    }

    public static function getLexicalField()
    {
        return _('einen Courseware-Inhalt');
    }

    /**
     * TODO
     *
     * @param String  $event a notification for an activity
     * @param \SimpleORMap  $resource
     */
    public static function postActivity($event, $resource)
    {
        $structuralElement = null;
        $rangeType = null;
        if ($resource instanceof StructuralElement) {
            $structuralElement = $resource;
        }
        if ($resource instanceof Task ||
            $resource instanceof StructuralElementComment ||
            $resource instanceof StructuralElementFeedback
        ) {
            $structuralElement = $resource['structural_element'];
        }
        if ($resource instanceof Block ||
            $resource instanceof BlockComment ||
            $resource instanceof BlockFeedback ||
            $resource instanceof Container ||
            $resource instanceof TaskFeedback
        ) {
            $structuralElement = $resource->getStructuralElement();
        }

        if ($structuralElement !== null) {
            $rangeType = $structuralElement->range_type;
        }

        if ($rangeType === 'courses' || $rangeType === 'course') {
            $data = null;
            switch ($event) {
                case Block::class . 'DidCreate':
                    /**
                     * @var \Courseware\Block $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $blockType = $resource->type;
                    $blockTitle = $blockType->getTitle() ?: $resource->getBlockType();
                    $content = _('Ein Block vom Typ "%1$s" wurde auf der Seite "%2$s" eingefügt.');
                    $content = sprintf($content, $blockTitle, $structuralElement->title);
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $content,
                        'actor_type' => 'user',
                        'actor_id' => $resource->owner_id,
                        'verb' => 'created',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case Block::class . 'DidUpdate':
                    /**
                     * @var \Courseware\Block $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    if (!$resource->edit_blocker_id) {
                        $blockType = $resource->type;
                        $blockTitle = $blockType->getTitle() ?? $resource->getBlockType();
                        $content = _('Ein Block vom Typ "%1$s" wurde auf der Seite "%2$s" verändert.');
                        $content = sprintf($content, $blockTitle, $structuralElement->title);
                        $data = [
                            'provider' => self::class,
                            'context' => $structuralElement->range_type,
                            'context_id' => $structuralElement->range_id,
                            'content' => $content,
                            'actor_type' => 'user',
                            'actor_id' => $resource->editor_id,
                            'verb' => 'edited',
                            'object_id' => $structuralElement->id,
                            'object_type' => 'courseware',
                            'mkdate' => time(),
                        ];
                    }
                    break;

                case Block::class . 'DidDelete':
                    /**
                     * @var \Courseware\Block $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $blockType = $resource->type;
                    $blockTitle = $blockType->getTitle() ?: $resource->getBlockType();
                    $content = _('Ein Block vom Typ "%1$s" wurde auf der Seite "%2$s" gelöscht.');
                    $content = sprintf($content, $blockTitle, $structuralElement->title);
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $content,
                        'actor_type' => 'user',
                        'actor_id' => $resource->editor_id,
                        'verb' => 'voided',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case BlockComment::class . 'DidCreate':
                    /**
                     * @var \Courseware\BlockComment $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $structuralElement = $resource->getStructuralElement();
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $resource->comment,
                        'actor_type' => 'user',
                        'actor_id' => $resource->user_id,
                        'verb' => 'interacted',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case BlockFeedback::class . 'DidCreate':
                    /**
                     * @var \Courseware\BlockFeedback $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $resource->feedback,
                        'actor_type' => 'user',
                        'actor_id' => $resource->user_id,
                        'verb' => 'answered',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case Container::class . 'DidCreate':
                    /**
                     * @var \Courseware\Container $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $containerType = $resource->type;
                    $containerTitle = $containerType->getTitle() ?: _('unbekannt');
                    $content = _('Ein Abschnitt vom Typ "%1$s" wurde auf der Seite "%2$s" eingefügt.');
                    $content = sprintf($content, $containerTitle, $structuralElement->title);
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $content,
                        'actor_type' => 'user',
                        'actor_id' => $resource->owner_id,
                        'verb' => 'created',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case Container::class . 'DidUpdate':
                    /**
                     * @var \Courseware\Block $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    if (!$resource->edit_blocker_id) {
                        $containerType = $resource->type;
                        $containerTitle = $containerType->getTitle() ?: _('unbekannt');
                        $content = _('Ein Abschnitt vom Typ "%1$s" wurde auf der Seite "%2$s" verändert.');
                        $content = sprintf($content, $containerTitle, $structuralElement->title);
                        $data = [
                            'provider' => self::class,
                            'context' => $structuralElement->range_type,
                            'context_id' => $structuralElement->range_id,
                            'content' => $content,
                            'actor_type' => 'user',
                            'actor_id' => $resource->editor_id,
                            'verb' => 'edited',
                            'object_id' => $structuralElement->id,
                            'object_type' => 'courseware',
                            'mkdate' => time(),
                        ];
                    }
                    break;

                case Container::class . 'DidDelete':
                    /**
                     * @var \Courseware\Container $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $containerType = $resource->type;
                    $containerTitle = $containerType->getTitle() ?: _('unbekannt');
                    $content = _('Ein Abschnitt vom Typ "%1$s" wurde auf der Seite "%2$s" gelöscht.');
                    $content = sprintf($content, $containerTitle, $structuralElement->title);
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $content,
                        'actor_type' => 'user',
                        'actor_id' => $resource->editor_id,
                        'verb' => 'voided',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case StructuralElement::class . 'DidCreate':
                    /**
                     * @var \Courseware\StructuralElement $resource
                     */
                    $content = _('Eine Seite mit dem Titel "%s" wurde angelegt.');
                    $content = sprintf($content, $structuralElement->title);
                    $data = [
                        'provider' => self::class,
                        'context' => $resource->range_type,
                        'context_id' => $resource->range_id,
                        'content' => $content,
                        'actor_type' => 'user',
                        'actor_id' => $resource->owner_id,
                        'verb' => 'created',
                        'object_id' => $resource->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;
                case StructuralElement::class . 'DidDelete':
                    /**
                     * @var \Courseware\StructuralElement $resource
                     */
                    $content = _('Eine Seite mit dem Titel "%s" wurde gelöscht.');
                    $content = sprintf($content, $structuralElement->title);
                    $data = [
                        'provider' => self::class,
                        'context' => $resource->range_type,
                        'context_id' => $resource->range_id,
                        'content' => null,
                        'actor_type' => 'user',
                        'actor_id' => $resource->owner_id,
                        'verb' => 'voided',
                        'object_id' => $resource->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case StructuralElementComment::class . 'DidCreate':
                    /**
                     * @var \Courseware\StructuralElementComment $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $resource->comment,
                        'actor_type' => 'user',
                        'actor_id' => $resource->user_id,
                        'verb' => 'interacted',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case StructuralElementFeedback::class . 'DidCreate':
                    /**
                     * @var \Courseware\StructuralElementFeedback $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $resource->feedback,
                        'actor_type' => 'user',
                        'actor_id' => $resource->user_id,
                        'verb' => 'answered',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case Task::class . 'DidCreate':
                    /**
                     * @var \Courseware\Task $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => null,
                        'actor_type' => 'user',
                        'actor_id' => $resource->task_group->lecturer_id,
                        'verb' => 'created',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;

                case TaskFeedback::class . 'DidCreate':
                    /**
                     * @var \Courseware\TaskFeedback $resource
                     * @var \Courseware\StructuralElement $structuralElement
                     */
                    $data = [
                        'provider' => self::class,
                        'context' => $structuralElement->range_type,
                        'context_id' => $structuralElement->range_id,
                        'content' => $resource->content,
                        'actor_type' => 'user',
                        'actor_id' => $resource->lecturer_id,
                        'verb' => 'answered',
                        'object_id' => $structuralElement->id,
                        'object_type' => 'courseware',
                        'mkdate' => time(),
                    ];
                    break;
            }
            if ($data) {
                Activity::create($data);
            }
        }
    }
}
