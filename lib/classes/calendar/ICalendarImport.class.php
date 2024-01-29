<?php
class ICalendarImport
{
    private $range_id;

    private $count = 0;

    private $dates = [];

    private $import_time;

    private $convert_to_private = false;

    public function __construct($range_id)
    {
        $this->range_id = $range_id;
        $this->import_time = time();
    }

    public function import($ical_data)
    {
        $this->parse($ical_data);
    }

    public function countEvents($ical_data)
    {
        $matches = [];
        if (is_null($this->count)) {
            // Unfold any folded lines
            $data = preg_replace('/\x0D?\x0A[\x20\x09]/', '', $ical_data);
            preg_match_all('/(BEGIN:VEVENT(\r\n|\r|\n)[\W\w]*?END:VEVENT\r?\n?)/', $ical_data, $matches);
            $this->count = sizeof($matches[1]);
        }

        return $this->count;
    }

    public function getCountEvents() : int
    {
        return (int) $this->count;
    }

    public function convertPublicToPrivate(bool $to_private = true) : void
    {
        $this->convert_to_private = $to_private;
    }

    /**
     * Parse a string containing vCalendar data.
     *
     * @access private
     * @param string $data  The data to parse
     */
    public function parse(string $data)
    {
        // match categories
        $studip_categories = [];
        $i = 1;
        foreach ($GLOBALS['PERS_TERMIN_KAT'] as $cat) {
            $studip_categories[mb_strtolower($cat['name'])] = $i++;
        }

        // Unfold any folded lines
        // the CR is optional for files imported from Korganizer (non-standard)
        $data = $this->unfoldLine($data);

        if (!preg_match('/BEGIN:VCALENDAR(\r\n|\r|\n)([\W\w]*)END:VCALENDAR\r?\n?/', $data, $matches)) {
            throw new UnexpectedValueException();
        }

        // client identifier
        if (!$this->parseClientIdentifier($matches[2])) {
            throw new UnexpectedValueException();
        }

        // All sub components
        if (!preg_match_all('/BEGIN:VEVENT(\r\n|\r|\n)([\w\W]*?)END:VEVENT(\r\n|\r|\n)/', $matches[2], $v_events)) {
            // _("Die importierte Datei enthält keine Termine.")
            throw new UnexpectedValueException();
        }

        if ($this->count) {
            $this->count = 0;
        }
        foreach ($v_events[2] as $v_event) {

            if (preg_match_all('/(.*):(.*)(\r|\n)+/', $v_event, $matches)) {
                $properties = [];
                $check = [];
                foreach ($matches[0] as $property) {
                    preg_match('/([^;^:]*)((;[^:]*)?):(.*)/', $property, $parts);
                    $tag = $parts[1];
                    $value = $parts[4];
                    $params = [];

                    // skip seminar events
                    if ((!$this->import_sem) && $tag == 'UID') {
                        if (mb_strpos($value, 'Stud.IP-SEM') === 0) {
                            continue 2;
                        }
                    }

                    if (!empty($parts[2])) {
                        preg_match_all('/;(([^;=]*)(=([^;]*))?)/', $parts[2], $param_parts);
                        foreach ($param_parts[2] as $key => $param_name)
                            $params[mb_strtoupper($param_name)] = mb_strtoupper($param_parts[4][$key]);

                        if ($params['ENCODING']) {
                            switch ($params['ENCODING']) {
                                case 'QUOTED-PRINTABLE':
                                    $value = $this->qp_decode($value);
                                    break;

                                case 'BASE64':
                                    $value = base64_decode($value);
                                    break;
                            }
                        }
                    }

                    switch ($tag) {
                        // text fields
                        case 'DESCRIPTION':
                        case 'SUMMARY':
                        case 'LOCATION':
                            $value = preg_replace('/\\\\,/', ',', $value);
                            $value = preg_replace('/\\\\n/', "\n", $value);
                            $properties[$tag] = trim($value);
                            break;

                        case 'CATEGORIES':
                            $categories = [];
                            $properties['STUDIP_CATEGORY'] = null;
                            foreach (explode(',', $value) as $category) {
                                if (!$studip_categories[mb_strtolower($category)]) {
                                    $categories[] = $category;
                                } else if (!$properties['STUDIP_CATEGORY']) {
                                    $properties['STUDIP_CATEGORY']
                                        = $studip_categories[mb_strtolower($category)];
                                }
                            }
                            $properties[$tag] = implode(',', $categories);
                            break;

                        // Date fields
                        case 'DCREATED': // vCalendar property name for "CREATED"
                        case 'DTSTAMP':
                        case 'COMPLETED':
                        case 'CREATED':
                        case 'LAST-MODIFIED':
                            $properties[$tag] = $this->parseDateTime($value);
                            break;

                        case 'DTSTART':
                        case 'DTEND':
                            // checking for day events
                            if ($params['VALUE'] == 'DATE')
                                $check['DAY_EVENT'] = true;
                        case 'DUE':
                        case 'RECURRENCE-ID':
                            $properties[$tag] = $this->parseDateTime($value);
                            break;

                        case 'RDATE':
                            if (array_key_exists('VALUE', $params)) {
                                if ($params['VALUE'] == 'PERIOD') {
                                    $properties[$tag] = $this->parsePeriod($value);
                                } else {
                                    $properties[$tag] = $this->parseDateTime($value);
                                }
                            } else {
                                $properties[$tag] = $this->parseDateTime($value);
                            }
                            break;

                        case 'TRIGGER':
                            if (array_key_exists('VALUE', $params)) {
                                if ($params['VALUE'] == 'DATE-TIME') {
                                    $properties[$tag] = $this->parseDateTime($value);
                                } else {
                                    $properties[$tag] = $this->parseDuration($value);
                                }
                            } else {
                                $properties[$tag] = $this->parseDuration($value);
                            }
                            break;

                        case 'EXDATE':
                            $properties[$tag] = [];
                            // comma seperated dates
                            $values = [];
                            $dates = [];
                            preg_match_all('/,([^,]*)/', ',' . $value, $values);
                            foreach ($values[1] as $value) {
                                if (array_key_exists('VALUE', $params)) {
                                    if ($params['VALUE'] == 'DATE-TIME') {
                                        $dates[] = $this->parseDateTime($value);
                                    } else if ($params['VALUE'] == 'DATE') {
                                        $dates[] = $this->parseDate($value);
                                    }
                                } else {
                                    $dates[] = $this->parseDateTime($value);
                                }
                            }
                            // some iCalendar exports (e.g. KOrganizer) use an EXDATE-entry for every
                            // exception, so we have to merge them
                            array_merge($properties[$tag], $dates);
                            break;

                        // Duration fields
                        case 'DURATION':
                            $attibutes[$tag] = $this->parseDuration($value);
                            break;

                        // Period of time fields
                        case 'FREEBUSY':
                            $values = [];
                            $periods = [];
                            preg_match_all('/,([^,]*)/', ',' . $value, $values);
                            foreach ($values[1] as $value) {
                                $periods[] = $this->parsePeriod($value);
                            }

                            $properties[$tag] = $periods;
                            break;

                        // UTC offset fields
                        case 'TZOFFSETFROM':
                        case 'TZOFFSETTO':
                            $properties[$tag] = $this->parseUtcOffset($value);
                            break;

                        case 'PRIORITY':
                            $properties[$tag] = $this->parsePriority($value);
                            break;

                        case 'CLASS':
                            switch (trim($value)) {
                                case 'PUBLIC':
                                    $properties[$tag] = 'PUBLIC';
                                    break;
                                case 'CONFIDENTIAL':
                                    $properties[$tag] = 'CONFIDENTIAL';
                                    break;
                                default:
                                    $properties[$tag] = 'PRIVATE';
                            }
                            break;

                        // Integer fields
                        case 'PERCENT-COMPLETE':
                        case 'REPEAT':
                        case 'SEQUENCE':
                            $properties[$tag] = intval($value);
                            break;

                        // Geo fields
                        case 'GEO':
                            $floats = explode(';', $value);
                            $value['latitude'] = floatval($floats[0]);
                            $value['longitude'] = floatval($floats[1]);
                            $properties[$tag] = $value;
                            break;

                        // Recursion fields
                        case 'EXRULE':
                        case 'RRULE':
                            $properties[$tag] = $this->parseRecurrence($value);
                            break;

                        default:
                            // string fields
                            $properties[$tag] = trim($value);
                            break;
                    }
                }

                if (!$properties['RRULE']['rtype']) {
                    $properties['RRULE'] = ['rtype' => 'SINGLE'];
                }

                if (!$properties['LAST-MODIFIED']) {
                    $properties['LAST-MODIFIED'] = $properties['DTSTAMP'] ?: $properties['CREATED'] ?? time();
                }

                if (!$properties['DTSTART'] || ($properties['EXDATE'] && !$properties['RRULE'])) {
                    // _("Die Datei ist keine gültige iCalendar-Datei!")
                    throw new UnexpectedValueException();
                }

                if (!$properties['DTEND']) {
                    $properties['DTEND'] = $properties['DTSTART'];
                }

                // day events starts at 00:00:00 and ends at 23:59:59
                if ($check['DAY_EVENT'])
                    $properties['DTEND']--;

                // default: all imported events are set to private
                if (!$properties['CLASS']
                    || ($this->convert_to_private && $properties['CLASS'] == 'PUBLIC')) {
                    $properties['CLASS'] = 'PRIVATE';
                }

                /*
                if (isset($studip_categories[$properties['CATEGORIES']])) {
                    $properties['STUDIP_CATEGORY'] = $studip_categories[$properties['CATEGORIES']];
                    $properties['CATEGORIES'] = '';
                }
                 *
                 */

                $this->createDateFromProperties($properties);
            } else {
                // _("Die Datei ist keine gültige iCalendar-Datei!")
                throw new InvalidValuesException();
            }
            $this->count++;
        }

        return true;
    }

    private function createDateFromProperties($properties)
    {
        $date = CalendarDate::findOneBySQL(
            'LEFT JOIN `calendar_date_assignments`
              ON `calendar_dates`.`id` = `calendar_date_assignments`.`calendar_date_id`
            WHERE `calendar_dates`.`unique_id` = :uid
              AND `calendar_date_assignments`.`range_id` = :range_id',
            [
                ':uid'      => $properties['UID'],
                ':range_id' => $this->range_id
            ]
        );

        if (!$date) {
            $date = new CalendarDate();
            $date->id = $date->getNewId();
            $date->author_id = $this->range_id;
            $date->editor_id = $this->range_id;
            $range_date = new CalendarDateAssignment();
            $range_date->range_id = $this->range_id;
            $range_date->participation = '';
            $date->calendars[] = $range_date;
        }

        $date->begin = $properties['DTSTART']->getTimestamp();
        $date->end = $properties['DTEND']->getTimestamp();
        $date->title = $properties['SUMMARY'];
        $date->description = $properties['DESCRIPTION'];
        $date->access = $properties['CLASS'] ?? 'PRIVATE';
        $date->user_category = $properties['CATEGORIES'];
        $date->category = $properties['STUDIP_CATEGORY'] ?: 1;
        $date->priority = $properties['PRIORITY'] ?? '';
        $date->location = $properties['LOCATION'];
        if (is_array($properties['EXDATE'])) {
            foreach ($properties['EXDATE'] as $exdate) {
                $exception = new CalendarDateException();
                $exception->date = $exdate->format('Y-m-d');
                $date->exceptions[] = $exception;
            }
        }
        $date->mkdate = $properties['CREATED'] ? $properties['CREATED']->getTimestamp() : time();
        if (isset($properties['LAST-MODIFIED'])) {
            $date->chdate = $properties['LAST-MODIFIED']->getTimestamp();
        } else {
            $date->chdate = $date->mkdate;
        }
        $date->import_date = $this->import_time;
        $date->unique_id = $properties['UID'];

        $this->setRecurrenceRule($date, $properties['RRULE']);
        $date->store();
    }

    private function setRecurrenceRule(CalendarDate $date, $rrule)
    {
        $date->interval = $rrule['linterval'] ?? 1;
        if (strlen($rrule['wdays'] ?? '')) {
            $date->offset = $rrule['sinterval'] ?? 0;
            $date->days = $rrule['wdays'] ?? null;
        } else {
            $date->offset = $rrule['day'] ?? 0;
            $date->days = $rrule['sinterval'] ?? null;
        }
        $date->month = $rrule['month'] ?? null;
        $date->repetition_type = $rrule['rtype'] ?? 'SINGLE';
        $date->number_of_dates = $rrule['count'] ?? 1;
        $date->repetition_end = $rrule['expire'] ?? 0;
    }

    private function unfoldLine($data)
    {
        return preg_replace('/\x0D?\x0A[\x20\x09]/', '', $data);
    }

    /**
     * Parse a UTC Offset field
     */
    private function parseUtcOffset($offset_text)
    {
        $offset = 0;
        if (preg_match('/(\+|-)([0-9]{2})([0-9]{2})([0-9]{2})?/', $offset_text, $matches)) {
            $offset += 3600 * intval($matches[2]);
            $offset += 60 * intval($matches[3]);
            $offset *= ( $matches[1] == '+' ? 1 : -1);
            if (array_key_exists(4, $matches)) {
                $offset += intval($matches[4]);
            }
        }
        return $offset;
    }

    /**
     * Parse a Time Period field
     */
    private function parsePeriod($period_text): array
    {
        $matches = explode('/', $period_text);

        $start = $this->parseDateTime($matches[0]);

        if ($duration = $this->parseDuration($matches[1])) {
            return ['start' => $start, 'duration' => $duration];
        } else if ($end = $this->parseDateTime($matches[1])) {
            return ['start' => $start, 'end' => $end];
        }
        return [];
    }

    /**
     * Parse a DateTime field
     */
    private function parseDateTime(String $date_time)
    {
        $parts = explode('T', $date_time);
        if (count($parts) != 2) {
            // not a date time string but may be just a date string
            $date = $this->parseDate($date_time);
            return DateTimeImmutable::createFromFormat('YmdHis', implode('', $date) . '000000');
        }

        $date = $this->parseDate($parts[0]);
        $time = $this->parseTime($parts[1]);

        if ($time['zone'] == 'UTC') {
            $time_zone = new DateTimeZone('UTC');
        } else {
            $time_zone = new DateTimeZone('Europe/Berlin');
        }
        return DateTimeImmutable::createFromFormat(
            'YmdHis',
            implode('', $date) . $time['hour'] . $time['minute'] . $time['second'],
            $time_zone
        );
    }

    /**
     * Parse a Time field
     */
    private function parseTime($time_text): array
    {
        $matches = [];
        if (preg_match('/([0-9]{2})([0-9]{2})([0-9]{2})(Z)?/', $time_text, $matches)) {
            $time['hour'] = $matches[1];
            $time['minute'] = $matches[2];
            $time['second'] = $matches[3];
            if (array_key_exists(4, $matches)) {
                $time['zone'] = 'UTC';
            } else {
                $time['zone'] = 'LOCAL';
            }
            return $time;
        }
        throw new InvalidValuesException();
    }

    /**
     * Parse a Date field
     */
    private function parseDate($date_text): array
    {
        $matches = [];
        if (preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $date_text, $matches)) {
            $date['year'] = $matches[1];
            $date['month'] = $matches[2];
            $date['mday'] = $matches[3];
            return $date;
        }
        throw new InvalidValuesException();
    }

    /**
     * Parse a Duration Value field
     */
    private function parseDuration($interval_text): DateInterval
    {
        return new DateInterval($interval_text);
    }

    private function parsePriority($value)
    {
        $value = intval($value);
        if ($value > 0 && $value < 5) {
            return 'HIGH';
        }

        if ($value == 5) {
            return 'MEDIUM';
        }

        if ($value > 5 && $value < 10) {
            return 'LOW';
        }

        return '';
    }

    /**
     * Parse a recurrence rule.
     *
     * @param $text string The text of the recurrence rule.
     * @return array The translated recurrence rule as array.
     * @throws InvalidValuesException
     */
    private function parseRecurrence($text): array
    {
        global $_calendar_error;

        if (preg_match_all('/([A-Za-z]*?)=([^;]*);?/', $text, $matches, PREG_SET_ORDER)) {
            $r_rule = [];

            foreach ($matches as $match) {
                switch ($match[1]) {
                    case 'FREQ' :
                        switch (trim($match[2])) {
                            case 'DAILY' :
                            case 'WEEKLY' :
                            case 'MONTHLY' :
                            case 'YEARLY' :
                                $r_rule['rtype'] = trim($match[2]);
                                break;
                            default:
                                throw new InvalidValuesException(
                                    _("Der Import enthält Kalenderdaten, die Stud.IP nicht korrekt darstellen kann.")
                                );
                        }
                        break;

                    case 'UNTIL' :
                        $r_rule['expire'] = $this->parseDateTime($match[2]);
                        break;

                    case 'COUNT' :
                        $r_rule['count'] = intval($match[2]);
                        break;

                    case 'INTERVAL' :
                        $r_rule['linterval'] = intval($match[2]);
                        break;

                    case 'BYSECOND' :
                    case 'BYMINUTE' :
                    case 'BYHOUR' :
                    case 'BYWEEKNO' :
                    case 'BYYEARDAY' :
                        throw new InvalidValuesException(
                            _("Der Import enthält Kalenderdaten, die Stud.IP nicht korrekt darstellen kann.")
                        );
                    case 'BYDAY' :
                        $byday = $this->parseByDay($match[2]);
                        $r_rule['wdays'] = $byday['wdays'];
                        if ($byday['sinterval'])
                            $r_rule['sinterval'] = $byday['sinterval'];
                        break;

                    case 'BYMONTH' :
                        $r_rule['month'] = $this->parseByMonth($match[2]);
                        break;

                    case 'BYMONTHDAY' :
                        $r_rule['day'] = $this->parseByMonthDay($match[2]);
                        break;

                    case 'BYSETPOS':
                        $r_rule['sinterval'] = intval($match[2]);
                        break;

                    case 'WKST' :
                        break;
                }
            }
        }

        return $r_rule;
    }

    private function parseByDay($text)
    {
        global $_calendar_error;

        preg_match_all('/(-?\d{1,2})?(MO|TU|WE|TH|FR|SA|SU),?/', $text, $matches, PREG_SET_ORDER);
        $wdays_map = ['MO' => '1', 'TU' => '2', 'WE' => '3', 'TH' => '4', 'FR' => '5',
            'SA' => '6', 'SU' => '7'];
        $wdays = "";
        $sinterval = null;
        foreach ($matches as $match) {
            $wdays .= $wdays_map[$match[2]];
            if ($match[1]) {
                if (!$sinterval && ((int) $match[1]) > 0 || $match[1] == '-1') {
                    if ($match[1] == '-1') {
                        $sinterval = '5';
                    } else {
                        $sinterval = $match[1];
                    }
                } else {
                    throw new InvalidValuesException(
                        _("Der Import enthält Kalenderdaten, die Stud.IP nicht korrekt darstellen kann.")
                    );
                }
            }
        }

        return $wdays ? ['wdays' => $wdays, 'sinterval' => $sinterval] : false;
    }

    private function parseByMonthDay($text)
    {
        $days = explode(',', $text);
        if (count($days) > 1 || ((int) $days[0]) < 0) {
            return false;
        }

        return $days[0];
    }

    private function parseByMonth($text)
    {
        $months = explode(',', $text);
        if (count($months) > 1) {
            return false;
        }

        return $months[0];
    }

    private function qp_decode($value)
    {
        return preg_replace_callback("/=([0-9A-F]{2})/", function ($m) {return chr(hexdec($m[1]));}, $value);
    }

    private function parseClientIdentifier(&$data)
    {
        global $_calendar_error;

        if ($this->client_identifier == '') {
            if (!preg_match('/PRODID((;[\W\w]*)*):([\W\w]+?)(\r\n|\r|\n)/', $data, $matches)
                || !trim($matches[3])) {
                // _("Die Datei ist keine gültige iCalendar-Datei!")
                throw new InvalidValuesException();
            } else {
                $this->client_identifier = trim($matches[3]);
            }
        }
        return true;
    }

    public function getClientIdentifier($data = null)
    {
        if (!is_null($data)) {
            $this->parseClientIdentifier($data);
        }

        return $this->client_identifier;
    }

}
