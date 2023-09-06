<template>
    <div class="cw-bookmarks">
        <ul class="cw-tiles">
            <li
            v-for="bookmark in sortedBookmarks"
            :key="bookmark.id"
            class="tile"
            :class="[bookmark.attributes.payload.color, sortedBookmarks.length > 3 ? '':  'cw-tile-margin']"
            >
                <a :href="getElementUrl(bookmark)" :title="bookmark.attributes.title">
                    <div
                        class="preview-image"
                        :class="[hasImage(bookmark) ? '' : 'default-image']"
                        :style="getChildStyle(bookmark)"
                    ></div>
                    <div class="description">
                        <header>
                            {{ bookmark.attributes.title }}
                        </header>
                        <div class="description-text-wrapper">
                            <p>{{ bookmark.attributes.payload.description }}</p>
                        </div>
                        <footer>
                            <span v-if="bookmark.relationships.course">
                                <studip-icon shape="seminar" role="info_alt"/> {{ getCourseName(bookmark.relationships.course.data.id) }}
                            </span>
                            <span v-if="bookmark.relationships.user">
                                <studip-icon shape="content2" role="info_alt"/> {{ $gettext('Arbeitsplatz') }}
                            </span>
                        </footer>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import StudipIcon from '../StudipIcon.vue'
export default {
    name: 'courseware-content-bookmarks',
    components: {
        StudipIcon
    },
    computed: {
        ...mapGetters({
            courseById: 'courses/byId',
            userById: 'users/byId',
            userId: 'userId',
            bookmarks: 'courseware-structural-elements/all',
            bookmarkFilter: 'bookmarkFilter'
        }),
        sortedBookmarks() {
            if (this.bookmarks) {
                if (this.bookmarkFilter === 'all') {
                    return this.bookmarks;
                }
                if (this.bookmarkFilter === 'contents') {
                    return this.bookmarks.filter(bookmark => {
                        return bookmark.relationships.user?.data;
                    });
                }
                return this.bookmarks.filter(bookmark => {
                    return bookmark.relationships.course?.data?.id === this.bookmarkFilter;
                });
            }
            return [];
        }
    },
    methods: {
        ...mapActions({
            loadUser: 'users/loadById',
        }),
        getCourseName(cid) {
            const course = this.courseById({id: cid});

            return course.attributes.title;
        },
        async getUserName(userId) {
            await this.loadUser({id: userId});
            const user = this.userById({id: userId});

            return user.attributes['formatted-name'];
        },
        getElementUrl(element) {
            const unitId = element.relationships.unit.data.id;

            if (element.relationships?.course?.data) {
                const cid = element.relationships.course.data.id;
                return STUDIP.URLHelper.base_url + 'dispatch.php/course/courseware/courseware/' + unitId + '?cid='+ cid +'#/structural_element/' + element.id;
            }

            return STUDIP.URLHelper.base_url + 'dispatch.php/contents/courseware/courseware/' + unitId + '#/structural_element/' + element.id;
        },
        getChildStyle(element) {
            let url = element.relationships?.image?.meta?.['download-url'];

            if (url) {
                return {'background-image': 'url(' + url + ')'};
            } else {
                return {};
            }
        },
        hasImage(bookmark) {
            return bookmark.relationships?.image?.data !== null;
        },
    },



}
</script>
