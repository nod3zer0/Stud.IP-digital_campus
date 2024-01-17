<template>
    <MountingPortal mountTo="#sidebar" append name="wiki_online_editing_users">
        <SidebarWidget :title="$gettext('Anwesende Personen')">
             <template #content>
                <ol class="clean">
                    <li v-for="user in users" :key="user.user_id">
                        <img class="avatar-small" :src="user.avatar">
                        {{ user.fullname }}

                        <span v-if="user.editing" :title="$gettext('Diese Person hat den Bearbeitungsmodus.')">
                            <studip-icon shape="comment" role="info" class="text-bottom"></studip-icon>
                        </span>
                        <span v-else-if="user.editing_request" :title="$gettext('Diese Person beantragt den Bearbeitungsmodus.')">
                            <studip-icon shape="hand" role="info" class="text-bottom"></studip-icon>
                        </span>
                    </li>
                </ol>
             </template>
        </SidebarWidget>
    </MountingPortal>
</template>

<script>
export default {
    name: 'WikiEditorOnlineUsers',
    props: {
        users: Array
    },
};
</script>
