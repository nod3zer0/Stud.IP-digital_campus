import { mapActions, mapGetters } from 'vuex';

const blockMixin = {
    computed: {
        ...mapGetters({
            getUserProgress: 'courseware-user-progresses/related',
            context: 'context',
            userId: 'userId',
        }),
        userProgress: {
            get: function () {
                return this.getUserProgress({ parent: this.block, relationship: 'user-progress' });
            },
            set: function (grade) {
                this.userProgress.attributes.grade = grade;

                return this.updateUserProgress(this.userProgress);
            },
        },
        excludedCourseFolderTypes() {
            return ['HomeworkFolder'];
        }
    },
    methods: {
        ...mapActions({
            updateUserProgress: 'courseware-user-progresses/update',
        }),
        getReadableDate(date) {
            let locale = navigator.language ? navigator.language : 'de-DE';
            return new Date(date).toLocaleDateString(locale, {
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
            });
        },
        setShowEdit(state) {
            this.showEdit = state;
        },
    },
};

export default blockMixin;