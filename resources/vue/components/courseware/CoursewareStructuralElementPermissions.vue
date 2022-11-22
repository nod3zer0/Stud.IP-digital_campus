<template>
    <div class="cw-element-permissions">
        <label>
            <input type="checkbox" class="default" v-model="userPermsReadAll" />
            <translate>Alle Teilnehmenden haben Leserechte</translate>
        </label>
        <label>
            <input type="checkbox" class="default" v-model="userPermsWriteAll" />
            <translate>Alle Teilnehmenden haben Schreibrechte</translate>
        </label>

        <table class="default" v-if="autor_members.length">
            <caption>
                <translate>Studierende</translate>
            </caption>
            <colgroup>
                <col style="width:1%" />
                <col style="width:19%" />
                <col style="width:1%" />
                <col style="width:29%" />
                <col style="width:50%" />
            </colgroup>
            <thead>
                <tr>
                    <th><input type="checkbox" v-model="bulkSelectAutorRead" @click="handleBulkSelectRead($event, 'autor')"/></th>
                    <th><translate>Lesen</translate></th>
                    <th><input type="checkbox" v-model="bulkSelectAutorWrite" @click="handleBulkSelectWrite($event)"/></th>
                    <th><translate>Lesen und Schreiben</translate></th>
                    <th><translate>Name</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in autor_members_filtered" :key="user.user_id">
                    <td class="perm" colspan="2">
                        <input
                            type="checkbox"
                            :id="user.user_id + `_read`"
                            :value="user.user_id"
                            v-model="userPermsReadUsers"
                        />
                    </td>
                    <td class="perm" colspan="2">
                        <input
                            type="checkbox"
                            :id="user.user_id + `_write`"
                            :value="user.user_id"
                            v-model="userPermsWriteUsers"
                        />
                    </td>

                    <td>
                        <label :for="user.user_id + `_read`">
                            {{ user.formattedname }}
                            <i>{{ user.username }}</i>
                        </label>
                    </td>
                </tr>
            </tbody>
            <tfoot v-if="can_paginate && autor_members.length > entries_per_page">
                <tr>
                    <td colspan="5">
                        <studip-pagination
                            :currentOffset="autorOffset"
                            :totalItems="autor_members.length"
                            :itemsPerPage="entries_per_page"
                            @updateOffset="updateAutorOffset" />
                    </td>
                </tr>
            </tfoot>
        </table>

        <table class="default" v-if="user_members.length">
            <caption>
                <translate>Leser/-innen</translate>
            </caption>
            <colgroup>
                <col style="width:1%" />
                <col style="width:39%" />
                <col style="width:50%" />
            </colgroup>
            <thead>
                <tr>
                    <th><input type="checkbox" v-model="bulkSelectUserRead" @click="handleBulkSelectRead($event, 'user')"/></th>
                    <th><translate>Lesen</translate></th>
                    <th><translate>Name</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in user_members_filtered" :key="user.user_id">
                    <td colspan="2">
                        <input
                            type="checkbox"
                            :id="user.user_id + `_read`"
                            :value="user.user_id"
                            v-model="userPermsReadUsers"
                        />
                    </td>
                    <td>
                        <label :for="user.user_id + `_read`">
                            {{ user.firstname }}
                            {{ user.lastname }}
                            <i>{{ user.username }}</i>
                        </label>
                    </td>
                </tr>
            </tbody>
            <tfoot v-if="can_paginate && user_members.length > entries_per_page">
                <tr>
                    <td colspan="3">
                        <studip-pagination
                            :currentOffset="userOffset"
                            :totalItems="user_members.length"
                            :itemsPerPage="entries_per_page"
                            @updateOffset="updateUserOffset"/>
                    </td>
                </tr>
            </tfoot>
        </table>

        <table class="default" v-if="groups.length">
            <caption>
                <translate>Gruppen</translate>
            </caption>
            <colgroup>
                <col style="width:20%" />
                <col style="width:35%" />
                <col style="width:45%" />
            </colgroup>
            <thead>
                <tr>
                    <th><translate>Lesen</translate></th>
                    <th><translate>Lesen und Schreiben</translate></th>
                    <th><translate>Name</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="group in groups" :key="group.id">
                    <td class="perm">
                        <input
                            type="checkbox"
                            :id="group.id + `_read`"
                            :value="group.id"
                            v-model="userPermsReadGroups"
                        />
                    </td>
                    <td class="perm">
                        <input
                            type="checkbox"
                            :id="group.id + `_write`"
                            :value="group.id"
                            v-model="userPermsWriteGroups"
                        />
                    </td>

                    <td>
                        <label :for="group.id + `_read`">
                            {{ group.name }}
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import StudipPagination from './../StudipPagination.vue';

import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-permissions',
    props: {
        element: Object,
    },
    components: {
        StudipPagination
    },
    data() {
        return {
            user_perms: {},
            userPermsReadUsers: [],
            userPermsReadGroups: [],
            userPermsReadAll: Boolean,
            userPermsWriteUsers: [],
            userPermsWriteGroups: [],
            userPermsWriteAll: Boolean,
            bulkSelectAutorRead: false,
            bulkSelectUserRead: false,
            bulkSelectAutorWrite: false,
            userOffset: 0,
            autorOffset: 0,
        };
    },

    mounted() {
        if (this.element.attributes['read-approval'].users !== undefined) {
            this.userPermsReadUsers = this.element.attributes['read-approval'].users;
        }
        if (this.element.attributes['read-approval'].groups !== undefined) {
            this.userPermsReadGroups = this.element.attributes['read-approval'].groups;
        }
        if (this.element.attributes['read-approval'].all !== undefined) {
            this.userPermsReadAll = this.element.attributes['read-approval'].all;
        } else {
            this.userPermsReadAll = true;
        }
        if (this.element.attributes['write-approval'].users !== undefined) {
            this.userPermsWriteUsers = this.element.attributes['write-approval'].users;
        }
        if (this.element.attributes['write-approval'].groups !== undefined) {
            this.userPermsWriteGroups = this.element.attributes['write-approval'].groups;
        }
        if (this.element.attributes['write-approval'].all !== undefined) {
            this.userPermsWriteAll = this.element.attributes['write-approval'].all;
        } else {
            this.userPermsWriteAll = false;
        }

        // load memberships for coursewares in a course context
        if (this.context.type === 'courses') {
            const parent = { type: 'courses', id: this.context.id };
            let options = {
                include: 'user',
                'page[limit]': 10000,
            }
            this.loadCourseMemberships({ parent, relationship: 'memberships', options: options });
            this.loadCourseStatusGroups({ parent, relationship: 'status-groups' });
        }
    },

    updated () {
        this.handleBulkSelectReadPassive('autor');
        this.handleBulkSelectReadPassive('user');
        this.handleBulkSelectWritePassive();
    },

    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware',
            course: 'courses/related',
            relatedCourseMemberships: 'course-memberships/related',
            relatedCourseStatusGroups: 'status-groups/related',
            relatedUser: 'users/related',
        }),
        users() {
            const parent = { type: 'courses', id: this.context.id };
            const relationship = 'memberships';
            const memberships = this.relatedCourseMemberships({ parent, relationship });

            return (
                memberships?.map((membership) => {
                    const parent = { type: membership.type, id: membership.id };
                    const member = this.relatedUser({ parent, relationship: 'user' });

                    return {
                        user_id: member.id,
                        formattedname: member.attributes['formatted-name'],
                        username: member.attributes['username'],
                        perm: membership.attributes['permission'],
                    };
                }) ?? []
            );
        },
        groups() {
            const parent = { type: 'courses', id: this.context.id };
            const relationship = 'status-groups';
            const statusGroups = this.relatedCourseStatusGroups({ parent, relationship });

            return (
                statusGroups?.map((statusGroup) => {
                    return {
                        id: statusGroup.id,
                        name: statusGroup.attributes['name'],
                    };
                }) ?? []
            );
        },
        autor_members() {
            if (Object.keys(this.users).length === 0 && this.users.constructor === Object) {
                return [];
            }

            let members = this.users.filter(function (user) {
                return user.perm === 'autor';
            });

            return members;
        },

        autor_members_filtered() {
            if (this.autor_members.length === 0) {
                return [];
            }
            let start = this.autorOffset * this.entries_per_page;
            let end = ((this.autorOffset + 1) * this.entries_per_page);
            return this.autor_members.slice(start, end);
        },

        user_members() {
            if (Object.keys(this.users).length === 0 && this.users.constructor === Object) {
                return [];
            }

            let members = this.users.filter(function (user) {
                return user.perm === 'user';
            });

            return members;
        },

        user_members_filtered() {
            if (this.user_members.length === 0) {
                return [];
            }
            let start = this.userOffset * this.entries_per_page;
            let end = ((this.userOffset + 1) * this.entries_per_page);
            return this.user_members.slice(start, end);
        },

        entries_per_page() {
            return STUDIP?.config?.ENTRIES_PER_PAGE ?? 0;
        },

        can_paginate() {
            return this.entries_per_page > 0;
        },

        readApproval() {
            return {
                all: this.userPermsReadAll,
                users: this.userPermsReadUsers,
                groups: this.userPermsReadGroups,
            };
        },

        writeApproval() {
            return {
                all: this.userPermsWriteAll,
                users: this.userPermsWriteUsers,
                groups: this.userPermsWriteGroups,
            };
        },
    },

    methods: {
        ...mapActions({
            loadCourseMemberships: 'course-memberships/loadRelated',
            loadCourseStatusGroups: 'status-groups/loadRelated',
        }),

        updateAutorOffset(offset) {
            this.autorOffset = parseInt(offset);
        },

        updateUserOffset(offset) {
            this.userOffset = parseInt(offset);
        },

        handleBulkSelectRead(event, type) {
            let state = event.target.checked;
            let list = type === 'autor' ? this.autor_members_filtered : this.user_members_filtered;
            if (list.length == 0) {
                return;
            }
            if (type === 'autor') {
                this.bulkSelectAutorRead = state;
            } else {
                this.bulkSelectUserRead = state;
            }
            for (let user of list) {
                if (state) { // Add
                    if (this.userPermsReadUsers.includes(user.user_id) === false) {
                        this.userPermsReadUsers.push(user.user_id);
                    }
                } else { // Remove
                    if (this.userPermsReadUsers.includes(user.user_id) === true) {
                        let index = this.userPermsReadUsers.findIndex((perm) => perm == user.user_id);
                        this.userPermsReadUsers.splice(index, 1);
                    }
                }
            }
        },

        handleBulkSelectWrite(event) {
            let state = event.target.checked;
            let list = this.autor_members_filtered;
            if (list.length == 0) {
                return;
            }
            this.bulkSelectAutorWrite = state;
            for (let user of list) {
                if (state) { // Add
                    if (this.userPermsWriteUsers.includes(user.user_id) === false) {
                        this.userPermsWriteUsers.push(user.user_id);
                    }
                } else { // Remove
                    if (this.userPermsWriteUsers.includes(user.user_id) === true) {
                        let index = this.userPermsWriteUsers.findIndex((perm) => perm == user.user_id);
                        this.userPermsWriteUsers.splice(index, 1);
                    }
                }
            }
        },

        handleBulkSelectReadPassive(type) {
            let bulkState = false;
            if (type === 'autor' && this.autor_members_filtered?.length > 0) {
                let currentAutorsIds = this.autor_members_filtered.map((autor) => autor.user_id);
                if (currentAutorsIds.every((id) => this.userPermsReadUsers.includes(id))) {
                    bulkState = true;
                }
                this.bulkSelectAutorRead = bulkState;
            }
            if (type === 'user' && this.user_members_filtered?.length > 0) {
                let currentUsersIds = this.user_members_filtered.map((user) => user.user_id);
                if (currentUsersIds.every((id) => this.userPermsReadUsers.includes(id))) {
                    bulkState = true;
                }
                this.bulkSelectUserRead = bulkState;
            }
        },

        handleBulkSelectWritePassive() {
            let bulkState = false;
            let currentAutorsIds = this.autor_members_filtered.map((autor) => autor.user_id);
            if (currentAutorsIds.every((id) => this.userPermsWriteUsers.includes(id))) {
                bulkState = true;
            }
            this.bulkSelectAutorWrite = bulkState;
        }
    },

    watch: {
        userPermsReadUsers(newVal, oldVal) {
            this.handleBulkSelectReadPassive('autor');
            this.handleBulkSelectReadPassive('user');
            this.$emit('updateReadApproval', this.readApproval);
        },
        userPermsReadGroups(newVal, oldVal) {
            this.$emit('updateReadApproval', this.readApproval);
        },
        userPermsReadAll(newVal, oldVal) {
            this.$emit('updateReadApproval', this.readApproval);
            if (newVal === true) {
                this.userPermsWriteAll = false;
            }
        },
        userPermsWriteUsers(newVal, oldVal) {
            this.handleBulkSelectWritePassive();
            this.$emit('updateWriteApproval', this.writeApproval);
        },
        userPermsWriteGroups(newVal, oldVal) {
            this.$emit('updateWriteApproval', this.writeApproval);
        },
        userPermsWriteAll(newVal, oldVal) {
            this.$emit('updateWriteApproval', this.writeApproval);
            if (newVal === true) {
                this.userPermsReadAll = false;
            }
        },
        autorOffset(newVal, oldVal) {
            this.handleBulkSelectReadPassive('autor');
            this.handleBulkSelectWritePassive();
        },
        userOffset(newVal, oldVal) {
            this.handleBulkSelectReadPassive('user');
        }
    },
};
</script>
