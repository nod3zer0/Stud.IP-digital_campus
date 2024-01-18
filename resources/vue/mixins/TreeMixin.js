import axios from 'axios';

export const TreeMixin = {
    data() {
        return {
            showProgressIndicatorTimeout: 500,
            totalCourseCount: 0,
            offset: 0,
            limit: 50
        };
    },
    methods: {
        async getNode(id) {
            return axios.get(STUDIP.URLHelper.getURL('jsonapi.php/v1/tree-node/' + id));
        },
        async getNodeChildren(node, visibleOnly = true) {
            let parameters = {};

            if (visibleOnly) {
                parameters['filter[visible]'] = true;
            }

            return axios.get(
                STUDIP.URLHelper.getURL('jsonapi.php/v1/tree-node/' + node.id + '/children'),
                { params: parameters }
            );
        },
        async getNodeCourses(node, offset, semesterId = 'all', semClass = 0, searchterm = '', recursive = false, ids = []) {
            let parameters = {};

            parameters['page[offset]'] = offset * this.limit;
            parameters['page[limit]'] = this.limit;

            if (semesterId !== 'all' && semesterId !== '0') {
                parameters['filter[semester]'] = semesterId;
            }

            if (searchterm !== '') {
                parameters['filter[q]'] = searchterm;
            }

            if (semClass !== 0) {
                parameters['filter[semclass]'] = semClass;
            }

            if (node.attributes['has-children'] && recursive) {
                parameters['filter[recursive]'] = true;
            }

            if (ids.length > 0) {
                parameters['filter[ids]'] = ids;
            }

            return axios.get(
                STUDIP.URLHelper.getURL('jsonapi.php/v1/tree-node/' + node.id + '/courses'),
                {params: parameters}
            );
        },
        async getNodeCourseInfo(node, semesterId, semClass = 0) {
            let parameters = {};

            if (semesterId !== 'all' && semesterId !== '0') {
                parameters['filter[semester]'] = semesterId;
            }

            if (semClass !== 0) {
                parameters['filter[semclass]'] = semClass;
            }

            return axios.get(
                STUDIP.URLHelper.getURL('jsonapi.php/v1/tree-node/' + node.id + '/courseinfo'),
                { params: parameters }
            );
        },
        nodeUrl(node_id, semester = null ) {
            return STUDIP.URLHelper.getURL('', { node_id, semester })
        },
        courseUrl(courseId) {
            return STUDIP.URLHelper.getURL('dispatch.php/course/details/index/' + courseId)
        },
        profileUrl(username) {
            return STUDIP.URLHelper.getURL('dispatch.php/profile', { username })
        },
        exportUrl() {
            return STUDIP.URLHelper.getURL('dispatch.php/tree/export_csv');
        },
        editNode(editUrl, id) {
            STUDIP.Dialog.fromURL(
                editUrl + '/' + id,
                {
                    size: 'medium'
                }
            );
        },
        updateSorting(parentId, children) {
            let data = {};

            let position = 0;
            for (const child of children) {
                data[child.attributes.id] = position;
                position++;
            }

            const fd = new FormData();
            fd.append('sorting', JSON.stringify(data));
            axios.post(
                STUDIP.URLHelper.getURL('dispatch.php/admin/tree/sort/' + parentId),
                fd,
                { headers: { 'Content-Type': 'multipart/form-data' }}
            );
            STUDIP.Vue.emit('sort-tree-children', { parent: parentId, children: children });
        },
        updateOffset(newOffset) {
            this.getNodeCourses(this.currentNode, newOffset, this.semester, this.semClass, '', this.showingAllCourses)
                .then(courses => {
                    this.courseCount = courses.data.meta.page.total;
                    this.currentOffset = courses.data.meta.page.offset;
                    this.offset = newOffset;
                    this.courses = courses.data.data;
                });
        }
    }
}
