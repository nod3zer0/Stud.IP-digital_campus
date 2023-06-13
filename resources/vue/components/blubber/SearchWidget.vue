<template>
    <SidebarWidget :title="$gettext('Suche')">
        <template #content>
            <form action="?#" method="get" class="sidebar-search">
                <ul class="needles">
                    <li>
                        <div class="input-group files-search">
                            <label :for="inputId" class="sr-only">{{ $gettext('Suche nach …') }}</label>
                            <input
                                type="text"
                                :id="inputId"
                                name="search"
                                :value="search"
                                :placeholder="$gettext('Suche nach …')"
                            />

                            <a
                                class="reset-search"
                                :href="urlReset"
                                tabindex="0"
                                role="button"
                                :title="$gettext('Suche zurücksetzen')"
                            >
                                <studip-icon shape="decline" :size="20" alt="" />
                            </a>

                            <button type="submit" class="submit-search" :title="$gettext('Suche ausführen')">
                                <studip-icon shape="search" :size="20" alt="" />
                            </button>
                        </div>
                    </li>
                </ul>
            </form>
        </template>
    </SidebarWidget>
</template>

<script>
import SidebarWidget from '../SidebarWidget.vue';

let nextId = 0;

export default {
    props: {
        search: {
            type: String,
            default: '',
        },
    },
    components: {
        SidebarWidget,
    },
    data: () => ({
        inputId: ++nextId,
    }),
    computed: {
        urlReset() {
            return STUDIP.URLHelper.getURL('dispatch.php/blubber');
        },
    },
};
</script>
