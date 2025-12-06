/**
 * Widget Components Index
 * Maps widget types to their Vue component implementations.
 */

// Basic Widgets
import HeadingWidget from './HeadingWidget.vue';
import TextEditorWidget from './TextEditorWidget.vue';
import ImageWidget from './ImageWidget.vue';
import ButtonWidget from './ButtonWidget.vue';
import VideoWidget from './VideoWidget.vue';
import DividerWidget from './DividerWidget.vue';
import SpacerWidget from './SpacerWidget.vue';
import IconWidget from './IconWidget.vue';

// Content Widgets
import IconBoxWidget from './IconBoxWidget.vue';
import ImageBoxWidget from './ImageBoxWidget.vue';
import CounterWidget from './CounterWidget.vue';
import ProgressBarWidget from './ProgressBarWidget.vue';
import TestimonialWidget from './TestimonialWidget.vue';
import StarRatingWidget from './StarRatingWidget.vue';
import AlertWidget from './AlertWidget.vue';
import SocialIconsWidget from './SocialIconsWidget.vue';
import IconListWidget from './IconListWidget.vue';

// Interactive Widgets
import TabsWidget from './TabsWidget.vue';
import AccordionWidget from './AccordionWidget.vue';
import ToggleWidget from './ToggleWidget.vue';
import CountdownWidget from './CountdownWidget.vue';
import FormWidget from './FormWidget.vue';

// Media Widgets
import SliderWidget from './SliderWidget.vue';
import ImageCarouselWidget from './ImageCarouselWidget.vue';
import BasicGalleryWidget from './BasicGalleryWidget.vue';
import SoundCloudWidget from './SoundCloudWidget.vue';

// Marketing Widgets
import CallToActionWidget from './CallToActionWidget.vue';
import FlipBoxWidget from './FlipBoxWidget.vue';
import PriceTableWidget from './PriceTableWidget.vue';

// Utility Widgets
import GoogleMapsWidget from './GoogleMapsWidget.vue';
import TextPathWidget from './TextPathWidget.vue';
import HtmlWidget from './HtmlWidget.vue';
import ShortcodeWidget from './ShortcodeWidget.vue';
import MenuAnchorWidget from './MenuAnchorWidget.vue';

// Layout Widgets
import ContainerWidget from './ContainerWidget.vue';
import InnerSectionWidget from './InnerSectionWidget.vue';
import SidebarWidget from './SidebarWidget.vue';

// Widget type to component mapping
export const widgetComponentMap = {
    // Basic
    'heading': HeadingWidget,
    'text-editor': TextEditorWidget,
    'image': ImageWidget,
    'button': ButtonWidget,
    'video': VideoWidget,
    'divider': DividerWidget,
    'spacer': SpacerWidget,
    'icon': IconWidget,

    // Content
    'icon-box': IconBoxWidget,
    'image-box': ImageBoxWidget,
    'counter': CounterWidget,
    'progress-bar': ProgressBarWidget,
    'testimonial': TestimonialWidget,
    'star-rating': StarRatingWidget,
    'alert': AlertWidget,
    'social-icons': SocialIconsWidget,
    'icon-list': IconListWidget,

    // Interactive
    'tabs': TabsWidget,
    'accordion': AccordionWidget,
    'toggle': ToggleWidget,
    'countdown': CountdownWidget,
    'form': FormWidget,

    // Media
    'slider': SliderWidget,
    'image-carousel': ImageCarouselWidget,
    'basic-gallery': BasicGalleryWidget,
    'soundcloud': SoundCloudWidget,

    // Marketing
    'call-to-action': CallToActionWidget,
    'flip-box': FlipBoxWidget,
    'price-table': PriceTableWidget,

    // Utility
    'google-maps': GoogleMapsWidget,
    'text-path': TextPathWidget,
    'html': HtmlWidget,
    'shortcode': ShortcodeWidget,
    'menu-anchor': MenuAnchorWidget,

    // Layout
    'container': ContainerWidget,
    'inner-section': InnerSectionWidget,
    'sidebar': SidebarWidget,
};

/**
 * Get widget component by type
 */
export function getWidgetComponent(widgetType) {
    return widgetComponentMap[widgetType] || null;
}

/**
 * Check if widget type has a component
 */
export function hasWidgetComponent(widgetType) {
    return widgetType in widgetComponentMap;
}

// Export all widgets
export {
    HeadingWidget,
    TextEditorWidget,
    ImageWidget,
    ButtonWidget,
    VideoWidget,
    DividerWidget,
    SpacerWidget,
    IconWidget,
    IconBoxWidget,
    ImageBoxWidget,
    CounterWidget,
    ProgressBarWidget,
    TestimonialWidget,
    StarRatingWidget,
    AlertWidget,
    SocialIconsWidget,
    IconListWidget,
    TabsWidget,
    AccordionWidget,
    ToggleWidget,
    CountdownWidget,
    FormWidget,
    SliderWidget,
    ImageCarouselWidget,
    BasicGalleryWidget,
    SoundCloudWidget,
    CallToActionWidget,
    FlipBoxWidget,
    PriceTableWidget,
    GoogleMapsWidget,
    TextPathWidget,
    HtmlWidget,
    ShortcodeWidget,
    MenuAnchorWidget,
    ContainerWidget,
    InnerSectionWidget,
    SidebarWidget,
};
