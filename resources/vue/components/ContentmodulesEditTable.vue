<template>
    <table class="admin_contentmodules table default">
        <colgroup>
            <col style="width: 20px" v-if="filterCategory === null">
            <col style="width: 20px">
            <col>
            <col style="width: 24px">
        </colgroup>
        <thead>
        <tr>
            <th v-if="filterCategory === null"></th>
            <th></th>
            <th>{{ $gettext('Name') }}</th>
            <th class="actions">{{ $gettext('Aktionen') }}</th>
        </tr>
        </thead>

        <draggable v-model="sortedModules" handle=".dragarea" tag="tbody">
            <tr v-for="module in sortedModules"
                :key="module.id"
                :class="getModuleCSSClasses(module)"
                v-cloak>
                <td v-if="filterCategory === null">
                    <a class="dragarea"
                       tabindex="0"
                       :title="$gettextInterpolate('Sortierelement für Module %{module}. Drücken Sie die Tasten Pfeil-nach-oben oder Pfeil-nach-unten, um dieses Element in der Liste zu verschieben.', {module: module.displayname})"
                       @keydown="keyboardHandler($event, module)"
                       v-if="module.active"
                       :ref="`draghandle-${module.id}`"
                    >
                        <span class="drag-handle"></span>
                    </a>
                </td>
                <td>
                    <input type="checkbox"
                           v-model="module.active"
                           @click="toggleModuleActivation(module)"
                           v-if="!module.mandatory"
                           :ref="'checkbox_' + module.id">
                </td>
                <td>
                    <a class="upper_part"
                       :class="{ dragrea: module.active }"
                       :href="getDescriptionURL(module)"
                       data-dialog
                    >
                        <img :src="module.icon" width="20" height="20" v-if="module.icon" class="text-bottom">
                        {{ module.displayname }}
                    </a>
                </td>
                <td class="actions">
                    <a href="#"
                       v-if="module.active && !module.mandatory"
                       role="checkbox"
                       :aria-checked="module.visibility !== 'tutor' ? 'true' : 'false'"
                       @click.prevent="toggleModuleVisibility(module)">
                        <studip-icon :shape="module.visibility !== 'tutor' ? 'visibility-visible' : 'visibility-invisible'"
                                     class="text-bottom"
                                     :title="$gettextInterpolate($gettext('Inhaltsmoduls %{ name } für Teilnehmende unsichtbar bzw. sichtbar schalten'), { name: module.displayname })"></studip-icon>
                    </a>
                    <a :href="getRenameURL(module)" data-dialog="size=auto" v-if="module.active">
                        <studip-icon shape="edit" class="text-bottom" :title="$gettextInterpolate($gettext('Umbenennen des Inhaltsmoduls %{ name }'), { name: module.displayname })"></studip-icon>
                    </a>
                </td>
            </tr>
        </draggable>
    </table>
</template>

<script>
import ContentModulesMixin from '../mixins/ContentModulesMixin.js';

export default {
    name: 'contentmodules-edit-table',

    mixins: [ContentModulesMixin],
}
</script>
<style lang="scss">
table.admin_contentmodules > tbody > tr  {
    > td:first-child {
        background-image: linear-gradient(var(--dark-gray-color-60), var(--dark-gray-color-60));
        background-repeat: no-repeat;
        background-position: left;
        background-size: 10px auto;
        padding-left: 15px;
    }
    &.visibility-visible > td:first-child {
        background-image: linear-gradient(var(--green), var(--green));
    }
    &.visibility-invisible > td:first-child {
        background-image: linear-gradient(var(--yellow), var(--yellow));
    }
    > td {
        height: 31px; //to make all rows equally high
    }
}
</style>
