export default {
    methods: {
        getStatus(task) {
            let status = {};
            const now = new Date(Date.now());
            const submissionDate = new Date(task.attributes['submission-date']);
            let limit = new Date();
            limit.setDate(now.getDate() + 3);
            status.canSubmit = true;

            if (now < submissionDate) {
                status.shape = 'span-empty';
                status.role = 'status-green';
                status.description = this.$gettext('Aufgabe bereit');
            }
            if (task.attributes.renewal !== 'granted') {
                if (limit > submissionDate) {
                    status.shape = 'span-3quarter';
                    status.role = 'status-yellow';
                    status.description = this.$gettext('Aufgabe muss bald abgegeben werden');
                }

                if (now >= submissionDate) {
                    status.canSubmit = false;
                    status.shape = 'span-full';
                    status.role = 'status-red';
                    status.description = this.$gettext('Abgabe ist nicht bis zur Abgabefrist erfolgt');
                }
            } else {
                const renewalDate = new Date(task.attributes['renewal-date']);
                if (limit > renewalDate) {
                    status.shape = 'span-3quarter';
                    status.role = 'status-yellow';
                    status.description = this.$gettext('Aufgabe muss bald abgegeben werden');
                }

                if (now >= renewalDate) {
                    status.canSubmit = false;
                    status.shape = 'span-full';
                    status.role = 'status-red';
                    status.description = this.$gettext('Abgabe ist nicht bis zur verlängerten Abgabefrist erfolgt');
                }
            }

            if (task.attributes.submitted) {
                status.shape = 'span-full';
                status.role = 'status-green';
                status.description = this.$gettext('Aufgabe abgegeben');
            }

            return status;
        },
        getLinkToElement(elementId) {
            return (
                STUDIP.URLHelper.base_url +
                'dispatch.php/course/courseware/?cid=' +
                STUDIP.URLHelper.parameters.cid +
                '#/structural_element/' +
                elementId
            );
        },
        getReadableDate(date) {
            return new Date(date).toLocaleDateString();
        },
    },
};
