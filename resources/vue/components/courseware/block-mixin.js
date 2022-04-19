import { mapActions, mapGetters } from 'vuex';

export const blockMixin = {
    computed: {
        ...mapGetters({
            getUserProgress: 'courseware-user-progresses/related',
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
    },
    methods: {
        ...mapActions({
            updateUserProgress: 'courseware-user-progresses/update',
        }),
        contentsEqual(o1, o2) {
            return  typeof o1 === 'object' && Object.keys(o1).length > 0 
                    ? Object.keys(o1).length === Object.keys(o2).length 
                    && Object.keys(o1).every(p => this.contentsEqual(o1[p], o2[p]))
                    : o1 === o2;
        }
    },
};
