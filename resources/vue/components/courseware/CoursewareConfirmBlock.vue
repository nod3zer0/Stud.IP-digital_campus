<template>
    <div class="cw-block cw-block-confirm">
        <courseware-default-block
            :block="block"
            :canEdit="canEdit"
            :isTeacher="isTeacher"
            :preview="true"
            :defaultGrade="false"
            @showEdit="initCurrentData"
            @storeEdit="storeBlock"
            @closeEdit="initCurrentData"
        >
            <template #content>
                <div class="cw-block-title">
                    {{ $gettext('Bestätigung') }}
                </div>
                <form class="default cw-block-confirm-content" prevent.default="">
                    <label>
                        <input type="checkbox" :disabled="confirm" :checked="confirm" @click="setConfirm"/>
                        <span>{{ currentText }}</span>
                    </label>
                </form>
            </template>
            <template v-if="canEdit" #edit>
                <form class="default" @submit.prevent="">
                    <label>
                        {{ $gettext('Text') }}
                        <input type="text" v-model="currentText" />
                    </label>
                </form>
            </template>
            <template #info>{{ $gettext('Informationen zum Bestätigungs-Block') }}</template>
        </courseware-default-block>
    </div>
</template>

<script>
import CoursewareDefaultBlock from './CoursewareDefaultBlock.vue';
import { mapActions, mapGetters } from 'vuex';
import { blockMixin } from './block-mixin.js';

export default {
    name: 'courseware-confirm-block',
    mixins: [blockMixin],
    components: {
        CoursewareDefaultBlock
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            currentText: '',
            confirm: false,
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            getUserDataById: 'courseware-user-data-fields/byId',
        }),
        text() {
            return this.block?.attributes?.payload?.text;
        },
        userData() {
            return this.getUserDataById({ id: this.block.relationships['user-data-field'].data.id });
        },
    },
    mounted() {
        this.initCurrentData();
    },
    methods: {
        ...mapActions({
            updateBlock: 'updateBlockInContainer',
            updateUserDataFields: 'courseware-user-data-fields/update'
        }),
        initCurrentData() {
            this.currentText = this.text;
            if (this.userData?.attributes?.payload?.confirm) {
                this.confirm = this.userData.attributes.payload.confirm;
            }
        },
        async setConfirm() {
            if (this.confirm) {
                return;
            }
            let data = {};
            data.type = 'courseware-user-data-fields';
            data.id = this.block.relationships['user-data-field'].data.id;
            data.attributes = {};
            data.attributes.payload = {};
            data.attributes.payload.confirm = true;
            data.relationships = {};
            data.relationships.block = {};
            data.relationships.block.data = {};
            data.relationships.block.data.id = this.block.id;
            data.relationships.block.data.type = this.block.type;

            await this.updateUserDataFields(data);
            this.userProgress = 1;
            this.confirm = true;
        },
        storeBlock() {
            let attributes = {};
            attributes.payload = {};
            attributes.payload.text = this.currentText;

            this.updateBlock({
                attributes: attributes,
                blockId: this.block.id,
                containerId: this.block.relationships.container.data.id,
            });
        },
    },
};
</script>
