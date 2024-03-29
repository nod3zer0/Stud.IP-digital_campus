import { loadChunk, loadScript, } from './chunk-loader.js';
import Vue from './lib/studip-vue.js';

import ActionMenu from './lib/actionmenu.js';
import ActivityFeed from './lib/activityfeed.js';
import admin_sem_class from './lib/admin_sem_class.js';
import AdminCourses from './lib/admin-courses.js';
import Admission from './lib/admission.js';
import Arbeitsgruppen from './lib/arbeitsgruppen.js';
import Archive from './lib/archive.js';
import Avatar from './lib/avatar.js';
import BigImageHandler from './lib/big_image_handler.js';
import Blubber from './lib/blubber.js';
import Browse from './lib/browse.js';
import Cache from './lib/cache.js';
import Calendar from './lib/calendar.js';
import Clipboard from './lib/clipboard.js';
import Cookie from './lib/cookie.js';
import CourseWizard from './lib/course_wizard.js';
import { createURLHelper } from './lib/url_helper.ts';
import CSS from './lib/css.js';
import Dates from './lib/dates.js';
import DateTime from './lib/datetime.js';
import Dialog from './lib/dialog.js';
import DragAndDropUpload from './lib/drag_and_drop_upload.js';
import enrollment from './lib/enrollment.js';
import eventBus from './lib/event-bus.ts';
import extractCallback from './lib/extract_callback.js';
import Files from './lib/files.js';
import FilesDashboard from './lib/files_dashboard.js';
import Folders from './lib/folders.js';
import Forms from './lib/forms.js';
import Forum from './lib/forum.js';
import Fullcalendar from './lib/fullcalendar.js';
import Fullscreen from './lib/fullscreen.js';
import GlobalSearch from './lib/global_search.js';
import HeaderMagic from './lib/header_magic.js';
import i18n from './lib/i18n.js';
import Instschedule from './lib/instschedule.js';
import InlineEditing from './lib/inline-editing.js';
import JSONAPI, { jsonapi } from './lib/jsonapi.js';
import JSUpdater from './lib/jsupdater.js';
import Lightbox from './lib/lightbox.js';
import Markup from './lib/markup.js';
import Members from './lib/members.js';
import Messages from './lib/messages.js';
import MultiPersonSearch from './lib/multi_person_search.js';
import MultiSelect from './lib/multi_select.js';
import NavigationShrinker from './lib/navigation_shrinker.js';
import OER from './lib/oer.js';
import OldUpload from './lib/old_upload.js';
import Overlapping from './lib/overlapping.js';
import Overlay from './lib/overlay.js';
import PageLayout from './lib/page_layout.js';
import parseOptions from './lib/parse_options.js';
import PersonalNotifications from './lib/personal_notifications.js';
import QRCode from './lib/qr_code.js';
import Questionnaire from './lib/questionnaire.js';
import QuickSearch from './lib/quick_search.js';
import QuickSelection from './lib/quick_selection.js';
import Raumzeit from './lib/raumzeit.js';
import {ready, domReady, dialogReady} from './lib/ready.js';
import register from './lib/register.js';
import Report from './lib/report.js';
import Resources from './lib/resources.js';
import Responsive from './lib/responsive.js';
import RESTAPI, { api } from './lib/restapi.js';
import Schedule from './lib/schedule.js';
import Screenreader from './lib/screenreader.js';
import Scroll from './lib/scroll.js';
import Search from './lib/search.js';
import Sidebar from './lib/sidebar.js';
import SkipLinks from './lib/skip_links.js';
import startpage from './lib/startpage.js';
import Statusgroups from './lib/statusgroups.js';
import study_area_selection from './lib/study_area_selection.js';
import Table from './lib/table.js';
import TableOfContents from './lib/table-of-contents.js';
import Tooltip from './lib/tooltip.js';
import Tour from './lib/tour.js';
import * as Gettext from './lib/gettext';
import UserFilter from './lib/user_filter.js';
import wysiwyg from './lib/wysiwyg.js';
import ScrollToTop from './lib/scroll_to_top.js';
import Wiki from './lib/wiki.js';

const configURLHelper = _.get(window, 'STUDIP.URLHelper', {});
const URLHelper = createURLHelper(configURLHelper);

window.STUDIP = _.assign(window.STUDIP || {}, {
    ActionMenu,
    ActivityFeed,
    admin_sem_class,
    AdminCourses,
    Admission,
    api,
    Arbeitsgruppen,
    Archive,
    Avatar,
    BigImageHandler,
    Blubber,
    Browse,
    Cache,
    Calendar,
    Cookie,
    CourseWizard,
    CSS,
    Dates,
    DateTime,
    Dialog,
    DragAndDropUpload,
    enrollment,
    eventBus,
    extractCallback,
    Files,
    FilesDashboard,
    Folders,
    Forms,
    Forum,
    Fullcalendar,
    Fullscreen,
    Gettext,
    GlobalSearch,
    HeaderMagic,
    i18n,
    Instschedule,
    InlineEditing,
    jsonapi,
    JSONAPI,
    JSUpdater,
    Lightbox,
    loadChunk,
    loadScript,
    Markup,
    Members,
    Messages,
    MultiPersonSearch,
    MultiSelect,
    NavigationShrinker,
    OER,
    OldUpload,
    Overlapping,
    Overlay,
    PageLayout,
    parseOptions,
    PersonalNotifications,
    QRCode,
    Questionnaire,
    QuickSearch,
    QuickSelection,
    Raumzeit,
    register,
    Report,
    Responsive,
    RESTAPI,
    Schedule,
    Scroll,
    Screenreader,
    Search,
    Sidebar,
    SkipLinks,
    startpage,
    Statusgroups,
    study_area_selection,
    Table,
    TableOfContents,
    Tooltip,
    Tour,
    URLHelper,
    UserFilter,
    wysiwyg,
    Resources,
    Clipboard,
    ready,
    domReady,
    dialogReady,
    ScrollToTop,
    Vue,
    Wiki
});
