<?php

use Courseware\StructuralElement;
use Courseware\Block;
use Courseware\Container;

/**
 * Global search module for files
 *
 * @author      Ron Lucke <lucke@elan-ev.de>
 * @category    Stud.IP
 * @since       5.2
 */
class GlobalSearchCourseware extends GlobalSearchModule implements GlobalSearchFulltext
{
    /**
     * Returns the displayname for this module
     *
     * @return string
     */
    public static function getName()
    {
        return _('Courseware');
    }

    /**
     * Returns the filters that are displayed in the sidebar of the global search.
     *
     * @return array Filters for this class.
     */
    public static function getFilters()
    {
        return [];
    }

    public static function getSQL($search, $filter, $limit)
    {
        if (!$search) {
            return null;
        }

        $query = DBManager::get()->quote("%{$search}%");
        if ($filter['rangeId']) {
            $range_id = $filter['rangeId'];
            $sql = "(SELECT `cw_structural_elements` . `id` AS id, CONCAT('', 'cw_structural_elements') AS type
            FROM `cw_structural_elements`
            WHERE (`title` LIKE {$query} OR `payload` LIKE {$query})
                AND `range_id` = '{$range_id}'
            ORDER BY `cw_structural_elements`.`mkdate` DESC)
            UNION (
                SELECT se . `id` AS id, CONCAT('', 'cw_containers') AS type
                FROM `cw_containers` c
                JOIN cw_structural_elements se
                ON se . `id` = c . `structural_element_id`
                WHERE c. `payload` LIKE {$query}
                    AND `container_type` != 'list'
                    AND se . `range_id` = '{$range_id}'
                ORDER BY c . `mkdate` DESC)
            UNION (
                SELECT se . `id` AS id, CONCAT('', 'cw_blocks') AS type
                FROM `cw_blocks` b
                JOIN cw_containers c
                ON c.id = b.container_id
                JOIN cw_structural_elements se
                ON se . `id` = c . `structural_element_id`
                WHERE b.payload LIKE {$query}
                    AND se . `range_id` = '{$range_id}'
                ORDER BY b . `mkdate` DESC
            ) LIMIT {$limit}";
        } else {
            $sql = "(SELECT `cw_structural_elements` . `id` AS id, CONCAT('', 'cw_structural_elements') AS type
            FROM `cw_structural_elements`
            WHERE (`title` LIKE {$query} OR `payload` LIKE {$query})
            ORDER BY `cw_structural_elements`.`mkdate` DESC)
            UNION (
                SELECT se . `id` AS id, CONCAT('', 'cw_containers') AS type
                FROM `cw_containers` c
                JOIN cw_structural_elements se
                ON se . `id` = c . `structural_element_id`
                WHERE c. `payload` LIKE {$query}
                    AND `container_type` != 'list'
                ORDER BY c . `mkdate` DESC)
            UNION (
                SELECT se . `id` AS id, CONCAT('', 'cw_blocks') AS type
                FROM `cw_blocks` b
                JOIN cw_containers c
                ON c.id = b.container_id
                JOIN cw_structural_elements se
                ON se . `id` = c . `structural_element_id`
                WHERE b.payload LIKE {$query}
                ORDER BY b . `mkdate` DESC
            ) LIMIT {$limit}";
        }

        return $sql;
    }

    public static function filter($data, $search)
    {
        $user = $GLOBALS['user'];
        $structural_element = StructuralElement::find($data['id']);
        if ($structural_element->canRead($user)) {
            if ($data['type'] === 'cw_structural_elements') {
                $description = self::mark($structural_element->payload['description'], $search, true);
            }
            if ($data['type'] === 'cw_containers') {
                $description = _('Suchbegriff wurde in einem Abschnitt gefunden');
            }
            if ($data['type'] === 'cw_blocks') {
                $description = _('Suchbegriff wurde in einem Block gefunden');
            }
            $pageData = self::getPageData($structural_element);
            $date = new DateTime();
            $date->setTimestamp($structural_element->chdate);

            return [
                'name' => self::mark($structural_element->title, $search, true),
                'description' => $description,
                'url' => $pageData['url'],
                'img' => $structural_element->image ? $structural_element->getImageUrl() : Icon::create('courseware')->asImagePath(),
                'additional' => '<a href="' . $pageData['originUrl'] . '" title="' . $pageData['originName'] . '">' . $pageData['originName'] . '</a>',
                'date' => $date->format('d.m.Y H:i'),
                'structural-element-id' => $structural_element->id
            ];
        }
        return [];
    }

    private static function getPageData(StructuralElement $structural_element): Array
    {
        $url = '';
        $originUrl = '';
        $originName = '';
        if ($structural_element->range_type === 'course') {
            $url = URLHelper::getURL(
                "dispatch.php/course/courseware?cid={$structural_element->range_id}#/structural_element/{$structural_element->id}",
                [],
                true);
            $originUrl = URLHelper::getURL(
                "dispatch.php/course/overview?cid={$structural_element->range_id}",
                [],
                true);
            $originName = Course::find($structural_element->range_id)->name;
        }
        if ($structural_element->range_type === 'user') {
            $url = URLHelper::getURL(
                "dispatch.php/contents/courseware/courseware#/structural_element/{$structural_element->id}",
                [],
                true);
            $originUrl = URLHelper::getURL(
                "dispatch.php/contents/courseware/index",
                [],
                true);
            $originName = _('Persönliche Lernmaterialien');
        }

        return array(
            'url' => $url,
            'originUrl' => $originUrl,
            'originName' => $originName
        );
    }

    public static function enable()
    {
    }

    public static function disable()
    {
    }

    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/globalsearch', [
            'q'        => $searchterm,
            'category' => self::class
        ]);
    }

}
