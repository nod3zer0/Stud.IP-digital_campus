// contentbar
import CoursewareRibbon from './CoursewareRibbon.vue';
import CoursewareTabs from '../layouts/CoursewareTabs.vue';
import CoursewareTab from '../layouts/CoursewareTab.vue';
import { FocusTrap } from 'focus-trap-vue';
//layout
import CoursewareCompanionBox from '../layouts/CoursewareCompanionBox.vue';
import CoursewareEmptyElementBox from './CoursewareEmptyElementBox.vue';
import IsoDate from '../layouts/IsoDate.vue';
// containers
import CoursewareAccordionContainer from '../containers/CoursewareAccordionContainer.vue';
import CoursewareListContainer from '../containers/CoursewareListContainer.vue';
import CoursewareTabsContainer from '../containers/CoursewareTabsContainer.vue';

const StructuralElementComponents = {
    //contentbar
    CoursewareRibbon,
    CoursewareTabs,
    CoursewareTab,
    FocusTrap,
    //layout
    CoursewareCompanionBox,
    CoursewareEmptyElementBox,
    IsoDate,
    // containers
    CoursewareAccordionContainer,
    CoursewareListContainer,
    CoursewareTabsContainer
}

export default StructuralElementComponents;