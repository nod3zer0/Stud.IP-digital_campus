<template>
    <div class="cw-shared-items">
        <h2 v-if="sharedElements.length > 0">{{ $gettext('Geteilte Lernmaterialien') }}</h2>
        <ul class="cw-tiles">
            <li
                v-for="element in sharedElements"
                :key="element.id"
                class="tile"
                :class="[element.attributes.payload.color, sharedElements.length > 3 ? '':  'cw-tile-margin']"
            >
                <a :href="getSharedElementUrl(element.id)" :title="element.attributes.title">
                    <div
                        class="preview-image"
                        :class="[hasImage(element) ? '' : 'default-image']"
                        :style="getChildStyle(element)"
                    >
                        <div class="overlay-text">{{ getOwnerName(element) }}</div>
                    </div>
                    <div class="description">
                        <header
                            :class="[element.attributes.purpose !== '' ? 'description-icon-' + element.attributes.purpose : '']"
                        >
                            {{ element.attributes.title }}
                        </header>
                        <div class="description-text-wrapper">
                            <p>{{ element.attributes.payload.description }}</p>
                        </div>
                        <footer>
                            {{ countChildren(element) + 1 }}
                            <translate
                                :translate-n="countChildren(element) + 1"
                                translate-plural="Seiten"
                            >
                                Seite
                            </translate>
                        </footer>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</template>
<script>
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-shared-items',
    computed: {
        ...mapGetters({
            sharedElements: 'courseware-structural-elements-shared/all',
            userById: 'users/byId',
        }),
    },
    methods: {
        getChildStyle(child) {
            let url = child.relationships?.image?.meta?.['download-url'];

            if(url) {
                return {'background-image': 'url(' + url + ')'};
            } else {
                return {};
            }
        },
        hasImage(child) {
            return child.relationships?.image?.data !== null;
        },
        getElementUrl(elementId) {
            return STUDIP.URLHelper.base_url + 'dispatch.php/contents/courseware/courseware#/structural_element/' + elementId;
        },
        getSharedElementUrl(elementId) {
            return STUDIP.URLHelper.base_url + 'dispatch.php/contents/courseware/shared_content_courseware/' + elementId;
        },
        getOwnerName(element) {
            const ownerId = element.relationships.owner.data.id;
            const owner = this.userById({ id: ownerId });

            return owner.attributes['formatted-name']; 
        },
        countChildren(element) {
            let data = element.relationships.children.data;
            if (data) {
                return data.length;
            }
            return 0;
        },
    },
}
</script>