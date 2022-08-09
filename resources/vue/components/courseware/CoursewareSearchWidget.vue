<template>
  <form class="sidebar-search" @submit.prevent="">
      <ul class="needles">
          <li>
              <form @submit.prevent="">
                <input
                    type="text"
                    v-model="searchTerm"
                    :aria-label="$gettext('Geben Sie einen Suchbegriff mit mindestens 3 Zeichen ein.')"
                />
                <input
                    type="submit"
                    :value="$gettext('Suchen')"
                    aria-controls="search"
                    @click="loadResults"
                />
              </form>
          </li>
      </ul>
  </form>
</template>

<script>
import axios from 'axios';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-search-widget',
    data() {
        return {
            searchTerm: '',
        }
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            context: 'context',
        }),
    },
    methods: {
        ...mapActions({
            setShowSearchResults: 'setShowSearchResults',
            setSearchResults: 'setSearchResults',
            companionWarning: 'companionWarning'
        }),
        loadResults() {
            if (this.searchTerm.length < 3) {
                this.companionWarning({ info: this.$gettext('Leider ist Ihr Suchbegriff zu kurz. Der Suchbegriff muss mindestens 3 Zeichen lang sein.')});
                return;
            }
            const limit = 100;
            let params = {
                search: this.searchTerm,
                filters: { category: 'GlobalSearchCourseware', contextType: this.context.type, rangeId: this.context.id}
            };

            axios({
                method: 'get',
                url: STUDIP.URLHelper.getURL('dispatch.php/globalsearch/find/' + limit),
                params: params,
            }).then( result => {
                this.setShowSearchResults(true);
                if (result.data.GlobalSearchCourseware) {
                    this.setSearchResults((result.data.GlobalSearchCourseware.content));
                } else {
                    this.setSearchResults([]);
                }
            }).catch(error => {
                console.debug(error);
            });
        }
    }
    

}
</script>
