<template>
    <div role="region" id="search" aria-live="polite">
        <courseware-ribbon
            :showToolbarButton="false"
            :showModeSwitchButton="false"
            buttonsClass="single-icon"
        >
            <template #buttons>
                <studip-icon shape="search" :size="24" />
            </template>
            <template #breadcrumbList>
                <translate>Suchergebnisse</translate>
            </template>
            <template #menu>
                <button :title="$gettext('Suchergebnisse schließen')" @click="closeResults">
                    <studip-icon shape="decline" :size="24"/>
                </button>
            </template>
        </courseware-ribbon>
        <div id="search-results">
            <article v-if="searchResults.length > 0">
                <section v-for="result in searchResults" :key="result['structural-element-id']">
                    <router-link
                        :to="'/structural_element/' + result['structural-element-id']"
                        @click.native="closeResults"
                    >
                        <div v-show="result.img !== null" class="search-result-img hidden-tiny-down">
                            <img :src="result.img" />
                        </div>
                        <div class="search-result-data">
                            <div class="search-result-title" v-html="result.name"></div>
                            <div class="search-result-details">
                                <div class="search-result-description" v-html="result.description"></div>
                            </div>
                        </div>
                        <div class="search-result-information">
                            <div class="search-result-time" v-html="result.date"></div>
                        </div>
                    </router-link>
                </section>
            </article>
            <courseware-companion-box
                v-else
                :msgCompanion="$gettext('Es wurden keine Suchergebnisse gefunden.')"
                mood="sad"
            />
        </div>
    </div>
</template>

<script>
import CoursewareRibbon from './CoursewareRibbon.vue';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';
import StudipIcon from '../StudipIcon.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-search-results',
    components: {
        CoursewareRibbon,
        CoursewareCompanionBox,
        StudipIcon
    },
    computed: {
        ...mapGetters({
            searchResults: 'searchResults'
        }),
    },
    methods: {
        ...mapActions({
            setShowSearchResults: 'setShowSearchResults',
            setSearchResults: 'setSearchResults',
        }),
        closeResults() {
            this.setShowSearchResults(false);
            this.setSearchResults([]);
        },
    }
}
</script>
