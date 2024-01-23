import { $gettext } from '../../../../assets/javascripts/lib/gettext';

export function getStatus(taskGroup) {
    const now = new Date();
    const startDate = new Date(taskGroup.attributes['start-date']);
    const endDate = new Date(taskGroup.attributes['end-date']);

    if (startDate <= now && now <= endDate) {
        return {
            shape: 'span-3quarter',
            role: 'status-green',
            description: $gettext('Die Bearbeitungszeit hat begonnen.'),
        };
    }

    if (now < startDate) {
        return {
            shape: 'span-empty',
            role: 'status-yellow',
            description: $gettext('Die Bearbeitungszeit hat noch nicht begonnen.'),
        };
    }

    if (endDate < now) {
        return {
            shape: 'span-full',
            role: 'status-red',
            description: $gettext('Die Bearbeitungszeit ist beendet.'),
        };
    }
}
