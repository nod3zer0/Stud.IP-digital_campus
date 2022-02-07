<template>
    <select v-model="bookmarkFilter" class="sidebar-selectlist">
        <option value="all">
            <translate>alle</translate>
        </option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
            {{ course.attributes.title }}
        </option>
    </select>
    
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-content-bookmark-filter-widget',
    data() {
        return {
            bookmarkFilter: 'all'
        };
    },
    computed: {
        ...mapGetters({
            courses: 'courses/all',
        }),
    },
    methods: {
        ...mapActions({
            setBookmarkFilter: 'setBookmarkFilter',
        }),
        setFilter() {
            this.setBookmarkFilter(this.bookmarkFilter);
        }
    },
    watch: {
        bookmarkFilter() {
            this.setFilter();
        }
    }
}
</script>
