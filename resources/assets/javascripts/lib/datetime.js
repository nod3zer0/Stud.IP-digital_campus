import { $gettext, $gettextInterpolate } from "./gettext.ts";


const DateTime = {
    /**
     * A helper method for padding strings with leading zeros.
     * @param what The date to pad.
     * @param length The length of the string to output.
     * @returns {string} A padded version of $what.
     */
    pad(what, length = 2) {
        return `00000000${what}`.substr(-length);
    },

    /**
     * Returns an ISO representation of the specified Date object.
     * in the format YYYY-MM-DD.
     *
     * @param date The Date object to format as ISO date.
     * @returns {string} The ISO date string of the Date object.
     */
    getISODate(date) {
        return date.getFullYear() + '-' + this.pad(date.getMonth() + 1) + '-' + date.getDate();
    },

    /**
     * Returns a formatted version of the specified Date object
     * in the Stud.IP date formatting.
     *
     * @param date The Date object to be formatted.
     * @param relative_value Whether to return a relative time value (true)
     *     or an absolute one (false). Defaults to false.
     * @param date_only Whether to return the date only (true) or date and time (false).
     *     Defaults to false. Only regarded when $relative_value is false.
     * @returns {*|string} The date, formatted according to the Stud.IP format for dates.
     */
    getStudipDate(date, relative_value = false, date_only = false) {
        if (relative_value) {
            let now = Date.now();
            if (now - date < 1 * 60 * 1000) {
                return $gettext('Jetzt');
            }
            if (now - date < 2 * 60 * 60 * 1000) {
                return $gettextInterpolate(
                    $gettext('Vor %{ minutes } Minuten'),
                    {minutes: Math.floor((now - date) / (1000 * 60))}
                );
            }
            return this.pad(date.getHours()) + ':' + this.pad(date.getMinutes());
        }

        if (date_only) {
            return this.pad(date.getDate()) + '.' + this.pad(date.getMonth() + 1) + '.' + date.getFullYear();
        }

        return this.pad(date.getDate()) + '.' + this.pad(date.getMonth() + 1) + '.' + date.getFullYear() + ' ' + this.pad(date.getHours()) + ':' + this.pad(date.getMinutes());
    }
};


export default DateTime;
