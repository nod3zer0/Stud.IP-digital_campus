<template>
    <div class="cw-block cw-block-biography cw-block-biography-goals">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            @closeEdit="initCurrentData"
            @showEdit="setShowEdit"
            @storeEdit="storeBlock"
        >
            <template #content>
                <div class="cw-block-biography-content" >
                    <div class="cw-block-biography-type" :class="'cw-block-biography-goals-type-' + currentData.type">
                        <h2>{{ goalTypeName }}</h2>
                    </div>
                    <div class="cw-block-biography-details formatted-content" v-html="currentData.description">
                    </div>
                </div>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default">
                    <label for="type">
                        <span><translate>Ziel</translate></span>
                        <select name="type" class="type" v-model="currentData.type">
                            <option value="personal"><translate>Persönliches Ziel</translate></option>
                            <option value="school"><translate>Schulisches Ziel</translate></option>
                            <option value="academic"><translate>Akademisches Ziel</translate></option>
                            <option value="professional"><translate>Berufliches Ziel</translate></option>
                        </select>
                    </label>
                    <label for="description">
                        <span><translate>Beschreibung</translate></span>
                        <studip-wysiwyg name="description" v-model="currentData.description"></studip-wysiwyg>
                    </label>
                </form>
            </template>
            <template #info><translate>Informationen zum Ziele-Block</translate></template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions } from 'vuex';
import { blockMixin } from './block-mixin.js';
import StudipWysiwyg from '../StudipWysiwyg.vue';

export default {
    name: 'courseware-biography-goals-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock,
        StudipWysiwyg,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            showEdit: false,
            currentData: {
                type: '',
                description: ''
            },
        }
    },
    computed: {
        type() {
            return this.block?.attributes?.payload?.type;
        },
        description() {
            return this.block?.attributes?.payload?.description;
        },
        goalTypeName() {
            switch (this.currentData.type) {
                case 'personal':
                    return this.$gettext('Persönliches Ziel');
                case 'school':
                    return this.$gettext('Schulisches Ziel');
                case 'academic':
                    return this.$gettext('Akademisches Ziel');
                case 'professional':
                    return this.$gettext('Berufliches Ziel');
            }

            throw new Error('Undefined data type ' + this.currentData.type);
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
        }),
        initCurrentData() {
            this.currentData = {
                type: this.type,
                description: this.description
            };
        },
        setShowEdit(state) {
            this.showEdit = state;
        },
        storeBlock() {
            let attributes = {
                payload: {
                    type: this.currentData.type,
                    description: this.currentData.description
                }
            };

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
    watch: {
        type() {
            if (!this.showEdit) {
                this.currentData.type = this.type;
            }
        },
        description() {
            if (!this.showEdit) {
                this.currentData.description = this.description;
            }
        },
    }
};
</script>
