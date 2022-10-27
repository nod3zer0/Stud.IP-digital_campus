<template>
  <form class="sidebar-search" @submit.prevent="">
      <ul class="needles">
          <li>
              <div class="input-group files-search">
                  <input
                      type="text"
                      v-model="searchTerm"
                      :aria-label="$gettext('Geben Sie einen Suchbegriff mit mindestens 3 Zeichen ein.')"
                  />
                  <a v-if="searched" @click.prevent="setShowSearchResults(false)" tabindex="0" role="button"
                     class="reset-search" :title="$gettext('Suche zurücksetzen')">
                      <studip-icon shape="decline" size="20"></studip-icon>
                  </a>
                  <button
                      type="submit"
                      :value="$gettext('Suchen')"
                      aria-controls="search"
                      class="submit-search"
                      @click="loadResults"
                  >
                      <studip-icon shape="search" size="20"></studip-icon>
                  </button>
              </div>
          </li>
      </ul>
  </form>
</template>

<script>
import axios from 'axios';
import { mapActions, mapGetters } from 'vuex';
import StudipIcon from '../StudipIcon.vue';

export default {
    name: 'courseware-search-widget',
    components: { StudipIcon },
    data() {
        return {
            searchTerm: ''
        }
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware',
            context: 'context',
        }),
        searched() {
            return this.$store.state.courseware.showSearchResults
        }
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
