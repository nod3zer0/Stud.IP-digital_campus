export default {
    methods: {
        calcCreated(mkdate) {
            let created = {oneDay: false, oneWeek: false};
            const delta = (new Date() - new Date(mkdate)) / 1000 / 60 / 60 / 24;
            if (delta < 2) {
                created.oneDay = true;
            }
            if (delta < 8) {
                created.oneWeek = true;
            }

            return created;
        }
    }
}