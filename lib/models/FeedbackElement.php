<?php

/**
 *
 * @author Nils Gehrke <nils.gehrke@uni-goettingen.de>
 *
 * The column "range_type" represents the name of a class that implements
 * FeedbackRange.
 *
 * @property int $id database column
 * @property string $user_id database column
 * @property string $range_id database column
 * @property string $range_type database column
 * @property string $course_id database column
 * @property string $question database column
 * @property string $description database column
 * @property int $mode database column
 * @property int $results_visible database column
 * @property int $commentable database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property SimpleORMapCollection|FeedbackEntry[] $entries has_many FeedbackEntry
 * @property Course $course belongs_to Course
 * @property User $user belongs_to User
 */

class FeedbackElement extends SimpleORMap
{
    public const MODE_NO_RATING = 0;
    public const MODE_5STAR_RATING = 1;
    public const MODE_10STAR_RATING = 2;

    public static function configure($config = [])
    {
        $config['db_table'] = 'feedback';

        $config['has_many']['entries'] = [
            'class_name'        => FeedbackEntry::class,
            'assoc_foreign_key' => 'feedback_id',
            'order_by'          => 'ORDER BY mkdate DESC',
            'on_delete'         => 'delete'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'course_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }

    /**
     *
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return bool
     */
    public function isFeedbackable(string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        $feedbackable = false;
        if (Feedback::hasRangeAccess($this->range_id, $this->range_type, $user_id) && !$this->isOwner($user_id)) {
            $already_feedbacked = $this->getOwnEntry($user_id);
            if ($already_feedbacked === null) {
                $feedbackable = true;
            }
        }

        return $feedbackable;
    }

    /**
     *
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return bool
     */
    public function isOwner(string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        $ownership = false;
        if ($this->user_id == $user_id) {
            $ownership = true;
        }
        return $ownership;
    }

    /**
     *
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return FeedbackEntry|null
     */
    public function getOwnEntry(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;

        return FeedbackEntry::findOneBySQL("feedback_id = ? AND user_id = ?", [$this->id, $user_id]);
    }

    public function getRatings()
    {
        $ratings = $this->entries->pluck('rating');
        return $ratings;
    }

    public function getCountOfRating($rating)
    {
        $ratings = $this->entries->filter(function ($entry) use ($rating) {
            return $entry->rating == $rating;
        })->toArray();

        return count($ratings);
    }

    public function getPercentageOfRating($rating)
    {
        $ratings    = $this->getCountOfRating($rating);
        $total      = count($this->entries);
        $percentage = ($ratings * 100) / $total;

        return round($percentage);
    }

    public function getPercentageOfMeanRating($total)
    {
        $rating    = round($this->getMeanOfRating(), 2);
        $percentage = ($rating * 100) / $total;

        return $percentage;
    }

    public function getMeanOfRating()
    {
        $ratings = $this->getRatings();
        $count = count($ratings);
        $mean    = $count > 0 ? array_sum($ratings) / $count : 0;

        return number_format($mean, 2, _(','), ' ');
    }

    public function getMaxRating()
    {
        switch ($this->mode) {
            case self::MODE_5STAR_RATING:
                return 5;
                break;
            case self::MODE_10STAR_RATING:
                return 10;
                break;
            default:
                return 0;
        }
    }

    public function getRange()
    {
        return $this->range_type::find($this->range_id);
    }
}
