import axios from 'axios';

export default {
    methods: {
        suggestViaAction(element, text) {
            let owner = element.relationships.owner.data.id;
            let cid = element.relationships.course.data.id;
            let elementid = element.id;

            axios({
                method: 'post',
                url: STUDIP.URLHelper.getURL('dispatch.php/messages/sendCwMessage/' + cid + '/' + elementid + '/' + owner),
                data: {
                    text: text,
                },
            }).then( () => {
                this.companionInfo({ info: this.$gettext('Der Vorschlag wurde verschickt.') });
            });

        }
    },

};
