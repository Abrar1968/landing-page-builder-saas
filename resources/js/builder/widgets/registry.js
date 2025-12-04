// Widget Registry - Central store for all widget definitions
export const widgetRegistry = {
    widgets: {},

    register(name, config) {
        this.widgets[name] = config;
    },

    get(name) {
        return this.widgets[name];
    },

    getAll() {
        return this.widgets;
    },
};

// Common advanced controls shared by all widgets
const commonAdvancedControls = [
    { name: "margin", type: "dimensions", label: "Margin" },
    { name: "padding", type: "dimensions", label: "Padding" },
    { name: "z_index", type: "number", label: "Z-Index", default: 0 },
    { name: "css_classes", type: "text", label: "CSS Classes" },
    { name: "css_id", type: "text", label: "CSS ID" },
    { name: "background", type: "background", label: "Background" },
    { name: "border", type: "border", label: "Border" },
    { name: "box_shadow", type: "box_shadow", label: "Box Shadow" },
    { name: "responsive_visibility", type: "responsive_visibility", label: "Responsive Visibility" },
    { name: "motion_effects", type: "motion_effects", label: "Motion Effects" },
];

// Helper to get standard advanced controls with optional extras
function getAdvancedControls(extras = []) {
    return [...extras, ...commonAdvancedControls];
}

// Register default widgets
widgetRegistry.register("heading", {
    title: "Heading",
    icon: "H",
    category: "basic",
    controls: {
        content: [
            {
                name: "title",
                type: "textarea",
                label: "Title",
                default: "Add Your Heading Text Here",
            },
            { name: "link", type: "url", label: "Link" },
            {
                name: "size",
                type: "select",
                label: "HTML Tag",
                default: "h2",
                options: {
                    h1: "H1",
                    h2: "H2",
                    h3: "H3",
                    h4: "H4",
                    h5: "H5",
                    h6: "H6",
                },
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "left",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
        ],
        style: [
            {
                name: "text_color",
                type: "color",
                label: "Text Color",
                default: "#1f2937",
            },
            {
                name: "typography",
                type: "typography",
                label: "Typography",
                options: {
                    line_height: true,
                    letter_spacing: true,
                    text_transform: true,
                    text_decoration: true,
                    font_style: true
                }
            },
        ],
        advanced: getAdvancedControls([
            { name: "custom_css", type: "code", label: "Custom CSS" },
        ]),
    },
});

widgetRegistry.register("text-editor", {
    title: "Text Editor",
    icon: "¶",
    category: "basic",
    controls: {
        content: [
            {
                name: "editor",
                type: "wysiwyg",
                label: "Text Editor",
                default:
                    "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>",
            },
        ],
        style: [
            {
                name: "text_color",
                type: "color",
                label: "Text Color",
                default: "#4b5563",
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "left",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("image", {
    title: "Image",
    icon: "🖼",
    category: "basic",
    controls: {
        content: [
            { name: "image_url", type: "media", label: "Image" },
            { name: "alt_text", type: "text", label: "Alt Text" },
            { name: "caption", type: "text", label: "Caption" },
            {
                name: "link",
                type: "text",
                label: "Link",
                placeholder: "https://",
            },
            { name: "link_target", type: "switcher", label: "Open in new window" },
        ],
        style: [
            {
                name: "width",
                type: "slider",
                label: "Width",
                min: 0,
                max: 100,
                default: 100,
                unit: "%",
            },
            {
                name: "max_width",
                type: "slider",
                label: "Max Width",
                min: 0,
                max: 1200,
                default: 0,
                unit: "px",
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            {
                name: "opacity",
                type: "slider",
                label: "Opacity",
                min: 0,
                max: 1,
                default: 1,
                step: 0.1,
                unit: "",
            },
            // CSS Filters
            {
                name: "filter_blur",
                type: "slider",
                label: "Blur",
                min: 0,
                max: 20,
                default: 0,
                unit: "px",
            },
            {
                name: "filter_brightness",
                type: "slider",
                label: "Brightness",
                min: 0,
                max: 200,
                default: 100,
                unit: "%",
            },
            {
                name: "filter_contrast",
                type: "slider",
                label: "Contrast",
                min: 0,
                max: 200,
                default: 100,
                unit: "%",
            },
            {
                name: "filter_saturation",
                type: "slider",
                label: "Saturation",
                min: 0,
                max: 200,
                default: 100,
                unit: "%",
            },
            {
                name: "filter_hue",
                type: "slider",
                label: "Hue Rotate",
                min: 0,
                max: 360,
                default: 0,
                unit: "deg",
            },
            {
                name: "hover_animation",
                type: "select",
                label: "Hover Animation",
                default: "none",
                options: {
                    none: "None",
                    zoom: "Zoom In",
                    zoom_out: "Zoom Out",
                    grayscale: "Grayscale",
                    blur: "Blur",
                    brightness: "Brighten",
                },
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("button", {
    title: "Button",
    icon: "▢",
    category: "basic",
    controls: {
        content: [
            { name: "text", type: "text", label: "Text", default: "Click Me" },
            {
                name: "link",
                type: "text",
                label: "Link",
                placeholder: "https://",
            },
            { name: "target", type: "switcher", label: "Open in new window" },
            // Icon Settings
            {
                name: "icon",
                type: "text",
                label: "Icon",
                placeholder: "e.g., ★ or →",
                default: "",
            },
            {
                name: "icon_position",
                type: "choose",
                label: "Icon Position",
                default: "left",
                options: {
                    left: { title: "Before", icon: "⬅" },
                    right: { title: "After", icon: "➡" },
                },
            },
            {
                name: "icon_spacing",
                type: "slider",
                label: "Icon Spacing",
                min: 0,
                max: 50,
                default: 8,
                unit: "px",
            },
        ],
        style: [
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "left",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            // Hover State Tabs
            { name: "_hover_state", type: "hover_tabs", label: "State" },
            // Normal State Colors
            {
                name: "background_color",
                type: "color",
                label: "Background",
                default: "#4f46e5",
            },
            {
                name: "text_color",
                type: "color",
                label: "Text Color",
                default: "#ffffff",
            },
            {
                name: "border_color",
                type: "color",
                label: "Border Color",
                default: "#4f46e5",
            },
            // Hover State Colors
            {
                name: "hover_background_color",
                type: "color",
                label: "Hover Background",
                default: "#4338ca",
            },
            {
                name: "hover_text_color",
                type: "color",
                label: "Hover Text Color",
                default: "#ffffff",
            },
            {
                name: "hover_border_color",
                type: "color",
                label: "Hover Border Color",
                default: "#4338ca",
            },
            // Common Style Controls
            {
                name: "border_width",
                type: "slider",
                label: "Border Width",
                min: 0,
                max: 10,
                default: 0,
                unit: "px",
            },
            {
                name: "border_radius",
                type: "slider",
                label: "Border Radius",
                min: 0,
                max: 50,
                default: 6,
                unit: "px",
            },
            {
                name: "padding_horizontal",
                type: "slider",
                label: "Horizontal Padding",
                min: 0,
                max: 100,
                default: 24,
                unit: "px",
            },
            {
                name: "padding_vertical",
                type: "slider",
                label: "Vertical Padding",
                min: 0,
                max: 50,
                default: 12,
                unit: "px",
            },
            { name: "typography", type: "typography", label: "Typography" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("video", {
    title: "Video",
    icon: "▶",
    category: "basic",
    controls: {
        content: [
            {
                name: "video_type",
                type: "select",
                label: "Source",
                default: "youtube",
                options: { youtube: "YouTube", vimeo: "Vimeo" },
            },
            { name: "youtube_url", type: "text", label: "YouTube URL" },
            // Playback Controls
            {
                name: "autoplay",
                type: "switcher",
                label: "Autoplay",
                default: false,
            },
            {
                name: "mute",
                type: "switcher",
                label: "Mute",
                default: false,
            },
            {
                name: "loop",
                type: "switcher",
                label: "Loop",
                default: false,
            },
            {
                name: "controls",
                type: "switcher",
                label: "Player Controls",
                default: true,
            },
            {
                name: "modest_branding",
                type: "switcher",
                label: "Modest Branding",
                default: false,
            },
            {
                name: "start_time",
                type: "number",
                label: "Start Time (seconds)",
                default: 0,
            },
            {
                name: "end_time",
                type: "number",
                label: "End Time (seconds)",
                placeholder: "Leave empty for full video",
            },
        ],
        style: [
            {
                name: "aspect_ratio",
                type: "select",
                label: "Aspect Ratio",
                default: "16:9",
                options: { "16:9": "16:9", "4:3": "4:3", "21:9": "21:9" },
            },
            {
                name: "width",
                type: "slider",
                label: "Width",
                min: 0,
                max: 100,
                default: 100,
                unit: "%",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("divider", {
    title: "Divider",
    icon: "—",
    category: "basic",
    controls: {
        content: [
            {
                name: "style",
                type: "select",
                label: "Style",
                default: "solid",
                options: {
                    solid: "Solid",
                    dashed: "Dashed",
                    dotted: "Dotted",
                    double: "Double",
                },
            },
            // Element in divider (optional)
            {
                name: "divider_element",
                type: "select",
                label: "Add Element",
                default: "none",
                options: {
                    none: "None",
                    text: "Text",
                    icon: "Icon",
                },
            },
            {
                name: "element_text",
                type: "text",
                label: "Text",
                default: "OR",
                placeholder: "Enter text",
            },
            {
                name: "element_icon",
                type: "text",
                label: "Icon",
                default: "★",
                placeholder: "e.g., ★ or ●",
            },
        ],
        style: [
            {
                name: "color",
                type: "color",
                label: "Color",
                default: "#e5e7eb",
            },
            {
                name: "weight",
                type: "slider",
                label: "Weight",
                min: 1,
                max: 10,
                default: 1,
                unit: "px",
            },
            {
                name: "width",
                type: "slider",
                label: "Width",
                min: 0,
                max: 100,
                default: 100,
                unit: "%",
            },
            {
                name: "gap",
                type: "slider",
                label: "Gap (Spacing)",
                min: 0,
                max: 100,
                default: 20,
                unit: "px",
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "center",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            // Element styling
            {
                name: "element_color",
                type: "color",
                label: "Element Color",
                default: "#6b7280",
            },
            {
                name: "element_size",
                type: "slider",
                label: "Element Size",
                min: 10,
                max: 50,
                default: 16,
                unit: "px",
            },
            {
                name: "element_spacing",
                type: "slider",
                label: "Element Spacing",
                min: 0,
                max: 50,
                default: 16,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("spacer", {
    title: "Spacer",
    icon: "↕",
    category: "basic",
    controls: {
        content: [
            {
                name: "space",
                type: "slider",
                label: "Space",
                min: 0,
                max: 500,
                default: 50,
                unit: "px",
            },
            {
                name: "space_unit",
                type: "select",
                label: "Unit",
                default: "px",
                options: {
                    px: "Pixels (px)",
                    vh: "Viewport Height (vh)",
                },
            },
        ],
        style: [],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("icon", {
    title: "Icon",
    icon: "★",
    category: "basic",
    controls: {
        content: [
            { name: "icon", type: "icon", label: "Choose Icon", default: "⭐" },
            { name: "link", type: "text", label: "Link" },
        ],
        style: [
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "center",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            {
                name: "primary_color",
                type: "color",
                label: "Color",
                default: "#4f46e5",
            },
            {
                name: "size",
                type: "slider",
                label: "Size",
                min: 10,
                max: 200,
                default: 50,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("icon-box", {
    title: "Icon Box",
    icon: "◈",
    category: "basic",
    controls: {
        content: [
            { name: "icon", type: "icon", label: "Choose Icon", default: "⚡" },
            {
                name: "title",
                type: "text",
                label: "Title",
                default: "This is the heading",
            },
            {
                name: "description",
                type: "textarea",
                label: "Description",
                default:
                    "Click here to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.",
            },
            {
                name: "link",
                type: "url",
                label: "Link",
            },
        ],
        style: [
            {
                name: "icon_position",
                type: "select",
                label: "Icon Position",
                default: "top",
                options: {
                    top: "Top",
                    left: "Left",
                    right: "Right",
                },
            },
            {
                name: "icon_color",
                type: "color",
                label: "Icon Color",
                default: "#4f46e5",
            },
            {
                name: "hover_icon_color",
                type: "color",
                label: "Icon Hover Color",
            },
            {
                name: "icon_size",
                type: "slider",
                label: "Icon Size",
                min: 20,
                max: 100,
                default: 50,
                unit: "px",
            },
            {
                name: "icon_spacing",
                type: "slider",
                label: "Icon Spacing",
                min: 0,
                max: 50,
                default: 15,
                unit: "px",
            },
            {
                name: "title_color",
                type: "color",
                label: "Title Color",
                default: "#1f2937",
            },
            {
                name: "hover_title_color",
                type: "color",
                label: "Title Hover Color",
            },
            {
                name: "description_color",
                type: "color",
                label: "Description Color",
                default: "#6b7280",
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "center",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            {
                name: "content_vertical_alignment",
                type: "select",
                label: "Vertical Alignment",
                default: "top",
                options: {
                    top: "Top",
                    center: "Center",
                    bottom: "Bottom",
                },
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("counter", {
    title: "Counter",
    icon: "123",
    category: "basic",
    controls: {
        content: [
            {
                name: "ending_number",
                type: "number",
                label: "Number",
                default: 100,
            },
            { name: "prefix", type: "text", label: "Prefix" },
            { name: "suffix", type: "text", label: "Suffix" },
            {
                name: "title",
                type: "text",
                label: "Title",
                default: "Cool Number",
            },
        ],
        style: [
            {
                name: "number_color",
                type: "color",
                label: "Number Color",
                default: "#4f46e5",
            },
            {
                name: "title_color",
                type: "color",
                label: "Title Color",
                default: "#6b7280",
            },
            {
                name: "number_size",
                type: "slider",
                label: "Number Size",
                min: 20,
                max: 100,
                default: 48,
                unit: "px",
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "center",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("progress-bar", {
    title: "Progress Bar",
    icon: "█▒",
    category: "basic",
    controls: {
        content: [
            {
                name: "title",
                type: "text",
                label: "Title",
                default: "Progress",
            },
            {
                name: "percent",
                type: "slider",
                label: "Percentage",
                min: 0,
                max: 100,
                default: 75,
                unit: "%",
            },
            {
                name: "display_percent",
                type: "switcher",
                label: "Display Percentage",
                default: true,
            },
        ],
        style: [
            {
                name: "bar_color",
                type: "color",
                label: "Bar Color",
                default: "#4f46e5",
            },
            {
                name: "bg_color",
                type: "color",
                label: "Background",
                default: "#e5e7eb",
            },
            {
                name: "height",
                type: "slider",
                label: "Height",
                min: 4,
                max: 50,
                default: 12,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("testimonial", {
    title: "Testimonial",
    icon: "💬",
    category: "basic",
    controls: {
        content: [
            {
                name: "content",
                type: "textarea",
                label: "Content",
                default:
                    "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
            },
            { name: "image_url", type: "media", label: "Image" },
            { name: "name", type: "text", label: "Name", default: "John Doe" },
            {
                name: "title",
                type: "text",
                label: "Title",
                default: "Designer",
            },
        ],
        style: [
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "center",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            {
                name: "content_color",
                type: "color",
                label: "Content Color",
                default: "#4b5563",
            },
            {
                name: "name_color",
                type: "color",
                label: "Name Color",
                default: "#1f2937",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("social-icons", {
    title: "Social Icons",
    icon: "📱",
    category: "basic",
    controls: {
        content: [
            { name: "facebook", type: "text", label: "Facebook URL" },
            { name: "twitter", type: "text", label: "Twitter URL" },
            { name: "instagram", type: "text", label: "Instagram URL" },
            { name: "linkedin", type: "text", label: "LinkedIn URL" },
        ],
        style: [
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "center",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            {
                name: "icon_color",
                type: "color",
                label: "Icon Color",
                default: "#4b5563",
            },
            {
                name: "icon_size",
                type: "slider",
                label: "Size",
                min: 16,
                max: 50,
                default: 24,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("alert", {
    title: "Alert",
    icon: "⚠",
    category: "basic",
    controls: {
        content: [
            {
                name: "title",
                type: "text",
                label: "Title",
                default: "This is an Alert",
            },
            {
                name: "content",
                type: "textarea",
                label: "Content",
                default: "Click to edit this text.",
            },
            {
                name: "alert_type",
                type: "select",
                label: "Type",
                default: "info",
                options: {
                    info: "Info",
                    success: "Success",
                    warning: "Warning",
                    danger: "Danger",
                },
            },
            {
                name: "show_icon",
                type: "switcher",
                label: "Show Icon",
                default: true,
            },
        ],
        style: [],
        advanced: getAdvancedControls(),
    },
});

// Additional Pro-style widgets

widgetRegistry.register("image-box", {
    title: "Image Box",
    icon: "🖼️",
    category: "general",
    controls: {
        content: [
            { name: "image_url", type: "media", label: "Image" },
            {
                name: "title",
                type: "text",
                label: "Title",
                default: "Image Box",
            },
            {
                name: "description",
                type: "textarea",
                label: "Description",
                default: "Click here to add your own text.",
            },
            { name: "link", type: "url", label: "Link" },
        ],
        style: [
            {
                name: "image_position",
                type: "select",
                label: "Image Position",
                default: "top",
                options: {
                    top: "Top",
                    left: "Left",
                    right: "Right",
                },
            },
            {
                name: "image_spacing",
                type: "slider",
                label: "Image Spacing",
                min: 0,
                max: 50,
                default: 15,
                unit: "px",
            },
            {
                name: "image_width",
                type: "slider",
                label: "Image Width",
                min: 50,
                max: 400,
                default: 100,
                unit: "%",
            },
            {
                name: "image_height",
                type: "slider",
                label: "Image Height",
                min: 100,
                max: 500,
                default: 160,
                unit: "px",
            },
            {
                name: "hover_animation",
                type: "select",
                label: "Image Hover Animation",
                default: "none",
                options: {
                    none: "None",
                    zoom: "Zoom In",
                    zoom_out: "Zoom Out",
                    grayscale: "Grayscale",
                    blur: "Blur",
                },
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "center",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
            {
                name: "content_vertical_alignment",
                type: "select",
                label: "Content Vertical Alignment",
                default: "top",
                options: {
                    top: "Top",
                    center: "Center",
                    bottom: "Bottom",
                },
            },
            {
                name: "title_color",
                type: "color",
                label: "Title Color",
                default: "#1f2937",
            },
            {
                name: "hover_title_color",
                type: "color",
                label: "Title Hover Color",
            },
            {
                name: "description_color",
                type: "color",
                label: "Description Color",
                default: "#6b7280",
            },
            { name: "background", type: "background", label: "Background" },
            { name: "border", type: "border", label: "Border" },
            { name: "box_shadow", type: "box_shadow", label: "Box Shadow" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("star-rating", {
    title: "Star Rating",
    icon: "⭐",
    category: "general",
    controls: {
        content: [
            {
                name: "rating",
                type: "slider",
                label: "Rating",
                min: 0,
                max: 5,
                default: 4,
                unit: "",
            },
            {
                name: "scale",
                type: "select",
                label: "Scale",
                default: "5",
                options: { 5: "1-5", 10: "1-10" },
            },
            { name: "title", type: "text", label: "Title" },
        ],
        style: [
            {
                name: "size",
                type: "slider",
                label: "Size",
                min: 10,
                max: 100,
                default: 24,
                unit: "px",
            },
            {
                name: "color",
                type: "color",
                label: "Color",
                default: "#fbbf24",
            },
            {
                name: "unmarked_color",
                type: "color",
                label: "Unmarked Color",
                default: "#d1d5db",
            },
            {
                name: "alignment",
                type: "choose",
                label: "Alignment",
                default: "left",
                options: {
                    left: { title: "Left", icon: "⬅" },
                    center: { title: "Center", icon: "⬌" },
                    right: { title: "Right", icon: "➡" },
                },
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("tabs", {
    title: "Tabs",
    icon: "📑",
    category: "general",
    controls: {
        content: [
            {
                name: "tab1_title",
                type: "text",
                label: "Tab 1 Title",
                default: "Tab 1",
            },
            {
                name: "tab1_content",
                type: "wysiwyg",
                label: "Tab 1 Content",
                default: "<p>Tab 1 content goes here.</p>",
            },
            {
                name: "tab2_title",
                type: "text",
                label: "Tab 2 Title",
                default: "Tab 2",
            },
            {
                name: "tab2_content",
                type: "wysiwyg",
                label: "Tab 2 Content",
                default: "<p>Tab 2 content goes here.</p>",
            },
            {
                name: "tab3_title",
                type: "text",
                label: "Tab 3 Title",
                default: "Tab 3",
            },
            {
                name: "tab3_content",
                type: "wysiwyg",
                label: "Tab 3 Content",
                default: "<p>Tab 3 content goes here.</p>",
            },
        ],
        style: [
            {
                name: "tab_color",
                type: "color",
                label: "Tab Color",
                default: "#4f46e5",
            },
            {
                name: "content_color",
                type: "color",
                label: "Content Color",
                default: "#1f2937",
            },
            { name: "border", type: "border", label: "Border" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("accordion", {
    title: "Accordion",
    icon: "📋",
    category: "general",
    controls: {
        content: [
            {
                name: "item1_title",
                type: "text",
                label: "Item 1 Title",
                default: "Accordion Item 1",
            },
            {
                name: "item1_content",
                type: "wysiwyg",
                label: "Item 1 Content",
                default: "<p>Content for accordion item 1.</p>",
            },
            {
                name: "item2_title",
                type: "text",
                label: "Item 2 Title",
                default: "Accordion Item 2",
            },
            {
                name: "item2_content",
                type: "wysiwyg",
                label: "Item 2 Content",
                default: "<p>Content for accordion item 2.</p>",
            },
            {
                name: "item3_title",
                type: "text",
                label: "Item 3 Title",
                default: "Accordion Item 3",
            },
            {
                name: "item3_content",
                type: "wysiwyg",
                label: "Item 3 Content",
                default: "<p>Content for accordion item 3.</p>",
            },
            {
                name: "first_open",
                type: "switcher",
                label: "First Item Open",
                default: true,
            },
        ],
        style: [
            {
                name: "title_color",
                type: "color",
                label: "Title Color",
                default: "#1f2937",
            },
            {
                name: "title_background",
                type: "color",
                label: "Title Background",
                default: "#f3f4f6",
            },
            {
                name: "content_color",
                type: "color",
                label: "Content Color",
                default: "#4b5563",
            },
            { name: "border", type: "border", label: "Border" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("countdown", {
    title: "Countdown",
    icon: "⏱️",
    category: "general",
    controls: {
        content: [
            {
                name: "due_date",
                type: "text",
                label: "Due Date",
                default: "2025-12-31",
                placeholder: "YYYY-MM-DD",
            },
            {
                name: "due_time",
                type: "text",
                label: "Due Time",
                default: "23:59",
                placeholder: "HH:MM",
            },
            {
                name: "show_days",
                type: "switcher",
                label: "Show Days",
                default: true,
            },
            {
                name: "show_hours",
                type: "switcher",
                label: "Show Hours",
                default: true,
            },
            {
                name: "show_minutes",
                type: "switcher",
                label: "Show Minutes",
                default: true,
            },
            {
                name: "show_seconds",
                type: "switcher",
                label: "Show Seconds",
                default: true,
            },
            {
                name: "show_labels",
                type: "switcher",
                label: "Show Labels",
                default: true,
            },
        ],
        style: [
            {
                name: "number_color",
                type: "color",
                label: "Number Color",
                default: "#1f2937",
            },
            {
                name: "label_color",
                type: "color",
                label: "Label Color",
                default: "#6b7280",
            },
            {
                name: "number_size",
                type: "slider",
                label: "Number Size",
                min: 20,
                max: 100,
                default: 48,
                unit: "px",
            },
            { name: "background", type: "background", label: "Box Background" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("google-maps", {
    title: "Google Maps",
    icon: "🗺️",
    category: "general",
    controls: {
        content: [
            {
                name: "address",
                type: "text",
                label: "Address",
                default: "New York, USA",
            },
            {
                name: "zoom",
                type: "slider",
                label: "Zoom",
                min: 1,
                max: 20,
                default: 14,
                unit: "",
            },
        ],
        style: [
            {
                name: "height",
                type: "slider",
                label: "Height",
                min: 100,
                max: 800,
                default: 400,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("call-to-action", {
    title: "Call to Action",
    icon: "📢",
    category: "marketing",
    controls: {
        content: [
            {
                name: "title",
                type: "text",
                label: "Title",
                default: "This is the heading",
            },
            {
                name: "description",
                type: "textarea",
                label: "Description",
                default: "Click here to add your own text and edit me.",
            },
            {
                name: "button_text",
                type: "text",
                label: "Button Text",
                default: "Click Here",
            },
            { name: "button_link", type: "url", label: "Button Link" },
            { name: "ribbon_text", type: "text", label: "Ribbon Text" },
        ],
        style: [
            { name: "background", type: "background", label: "Background" },
            {
                name: "title_color",
                type: "color",
                label: "Title Color",
                default: "#1f2937",
            },
            {
                name: "description_color",
                type: "color",
                label: "Description Color",
                default: "#4b5563",
            },
            {
                name: "button_background",
                type: "color",
                label: "Button Background",
                default: "#4f46e5",
            },
            {
                name: "button_color",
                type: "color",
                label: "Button Text Color",
                default: "#ffffff",
            },
            {
                name: "ribbon_color",
                type: "color",
                label: "Ribbon Color",
                default: "#ef4444",
            },
            { name: "box_shadow", type: "box_shadow", label: "Box Shadow" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("flip-box", {
    title: "Flip Box",
    icon: "🔄",
    category: "marketing",
    controls: {
        content: [
            {
                name: "front_icon",
                type: "text",
                label: "Front Icon",
                default: "⚡",
            },
            {
                name: "front_title",
                type: "text",
                label: "Front Title",
                default: "Front Title",
            },
            {
                name: "front_description",
                type: "textarea",
                label: "Front Description",
                default: "This is the front content.",
            },
            {
                name: "back_icon",
                type: "text",
                label: "Back Icon",
                default: "✨",
            },
            {
                name: "back_title",
                type: "text",
                label: "Back Title",
                default: "Back Title",
            },
            {
                name: "back_description",
                type: "textarea",
                label: "Back Description",
                default: "This is the back content.",
            },
            {
                name: "button_text",
                type: "text",
                label: "Button Text",
                default: "Click Here",
            },
            { name: "button_link", type: "url", label: "Button Link" },
            {
                name: "flip_direction",
                type: "select",
                label: "Flip Direction",
                default: "horizontal",
                options: { horizontal: "Horizontal", vertical: "Vertical" },
            },
        ],
        style: [
            {
                name: "front_background",
                type: "color",
                label: "Front Background",
                default: "#ffffff",
            },
            {
                name: "front_color",
                type: "color",
                label: "Front Color",
                default: "#1f2937",
            },
            {
                name: "back_background",
                type: "color",
                label: "Back Background",
                default: "#4f46e5",
            },
            {
                name: "back_color",
                type: "color",
                label: "Back Color",
                default: "#ffffff",
            },
            {
                name: "height",
                type: "slider",
                label: "Height",
                min: 200,
                max: 600,
                default: 300,
                unit: "px",
            },
            { name: "border", type: "border", label: "Border" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("price-table", {
    title: "Price Table",
    icon: "💰",
    category: "marketing",
    controls: {
        content: [
            { name: "title", type: "text", label: "Plan Name", default: "Pro" },
            { name: "price", type: "text", label: "Price", default: "$49" },
            {
                name: "period",
                type: "text",
                label: "Period",
                default: "/month",
            },
            {
                name: "features",
                type: "textarea",
                label: "Features (one per line)",
                default:
                    "10 Projects\n50GB Storage\nPriority Support\nCustom Domain",
            },
            {
                name: "button_text",
                type: "text",
                label: "Button Text",
                default: "Get Started",
            },
            { name: "button_link", type: "url", label: "Button Link" },
            {
                name: "featured",
                type: "switcher",
                label: "Featured",
                default: false,
            },
            {
                name: "ribbon_text",
                type: "text",
                label: "Ribbon Text",
                default: "Popular",
            },
        ],
        style: [
            {
                name: "header_background",
                type: "color",
                label: "Header Background",
                default: "#4f46e5",
            },
            {
                name: "header_color",
                type: "color",
                label: "Header Color",
                default: "#ffffff",
            },
            {
                name: "price_color",
                type: "color",
                label: "Price Color",
                default: "#1f2937",
            },
            {
                name: "features_color",
                type: "color",
                label: "Features Color",
                default: "#4b5563",
            },
            {
                name: "button_background",
                type: "color",
                label: "Button Background",
                default: "#4f46e5",
            },
            {
                name: "button_color",
                type: "color",
                label: "Button Color",
                default: "#ffffff",
            },
            { name: "border", type: "border", label: "Border" },
            { name: "box_shadow", type: "box_shadow", label: "Box Shadow" },
        ],
        advanced: getAdvancedControls(),
    },
});

// Form Builder Widget
widgetRegistry.register("form", {
    title: "Form",
    icon: "📝",
    category: "pro",
    controls: {
        content: [
            {
                name: "form_name",
                type: "text",
                label: "Form Name",
                default: "Contact Form",
            },
            {
                name: "show_labels",
                type: "switcher",
                label: "Show Labels",
                default: true,
            },
            {
                name: "name_field",
                type: "switcher",
                label: "Name Field",
                default: true,
            },
            {
                name: "email_field",
                type: "switcher",
                label: "Email Field",
                default: true,
            },
            {
                name: "message_field",
                type: "switcher",
                label: "Message Field",
                default: true,
            },
            {
                name: "button_text",
                type: "text",
                label: "Button Text",
                default: "Send Message",
            },
            {
                name: "success_message",
                type: "textarea",
                label: "Success Message",
                default: "Thank you! Your message has been sent.",
            },
        ],
        style: [
            {
                name: "field_background",
                type: "color",
                label: "Field Background",
                default: "#ffffff",
            },
            {
                name: "field_border",
                type: "color",
                label: "Field Border",
                default: "#d1d5db",
            },
            {
                name: "field_text",
                type: "color",
                label: "Field Text",
                default: "#1f2937",
            },
            {
                name: "button_background",
                type: "color",
                label: "Button Background",
                default: "#4f46e5",
            },
            {
                name: "button_text",
                type: "color",
                label: "Button Text",
                default: "#ffffff",
            },
            {
                name: "spacing",
                type: "slider",
                label: "Field Spacing",
                min: 0,
                max: 50,
                default: 16,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

// Slider/Carousel Widget
widgetRegistry.register("slider", {
    title: "Slider",
    icon: "🎠",
    category: "pro",
    controls: {
        content: [
            { name: "slide1_image", type: "media", label: "Slide 1 Image" },
            {
                name: "slide1_title",
                type: "text",
                label: "Slide 1 Title",
                default: "First Slide",
            },
            {
                name: "slide1_description",
                type: "textarea",
                label: "Slide 1 Description",
                default: "This is the first slide content.",
            },
            {
                name: "slide1_button",
                type: "text",
                label: "Slide 1 Button Text",
                default: "Learn More",
            },
            { name: "slide1_link", type: "url", label: "Slide 1 Button Link" },
            { name: "slide2_image", type: "media", label: "Slide 2 Image" },
            {
                name: "slide2_title",
                type: "text",
                label: "Slide 2 Title",
                default: "Second Slide",
            },
            {
                name: "slide2_description",
                type: "textarea",
                label: "Slide 2 Description",
                default: "This is the second slide content.",
            },
            {
                name: "slide2_button",
                type: "text",
                label: "Slide 2 Button Text",
                default: "Learn More",
            },
            { name: "slide2_link", type: "url", label: "Slide 2 Button Link" },
            { name: "slide3_image", type: "media", label: "Slide 3 Image" },
            {
                name: "slide3_title",
                type: "text",
                label: "Slide 3 Title",
                default: "Third Slide",
            },
            {
                name: "slide3_description",
                type: "textarea",
                label: "Slide 3 Description",
                default: "This is the third slide content.",
            },
            {
                name: "slide3_button",
                type: "text",
                label: "Slide 3 Button Text",
                default: "Learn More",
            },
            { name: "slide3_link", type: "url", label: "Slide 3 Button Link" },
            {
                name: "autoplay",
                type: "switcher",
                label: "Autoplay",
                default: true,
            },
            {
                name: "autoplay_speed",
                type: "slider",
                label: "Autoplay Speed (ms)",
                min: 1000,
                max: 10000,
                default: 3000,
                unit: "ms",
            },
            {
                name: "show_arrows",
                type: "switcher",
                label: "Show Arrows",
                default: true,
            },
            {
                name: "show_dots",
                type: "switcher",
                label: "Show Dots",
                default: true,
            },
        ],
        style: [
            {
                name: "height",
                type: "slider",
                label: "Height",
                min: 200,
                max: 800,
                default: 500,
                unit: "px",
            },
            {
                name: "overlay_color",
                type: "color",
                label: "Overlay Color",
                default: "rgba(0,0,0,0.3)",
            },
            {
                name: "title_color",
                type: "color",
                label: "Title Color",
                default: "#ffffff",
            },
            {
                name: "description_color",
                type: "color",
                label: "Description Color",
                default: "#f3f4f6",
            },
            {
                name: "button_background",
                type: "color",
                label: "Button Background",
                default: "#4f46e5",
            },
            {
                name: "button_color",
                type: "color",
                label: "Button Color",
                default: "#ffffff",
            },
            {
                name: "arrows_color",
                type: "color",
                label: "Arrows Color",
                default: "#ffffff",
            },
            {
                name: "dots_color",
                type: "color",
                label: "Dots Color",
                default: "#ffffff",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

// Missing Elementor 28 Basic Widgets

widgetRegistry.register("toggle", {
    title: "Toggle",
    icon: "⊞",
    category: "basic",
    controls: {
        content: [
            {
                name: "item1_title",
                type: "text",
                label: "Item 1 Title",
                default: "Toggle Item 1",
            },
            {
                name: "item1_content",
                type: "wysiwyg",
                label: "Item 1 Content",
                default: "<p>Content for toggle item 1.</p>",
            },
            {
                name: "item2_title",
                type: "text",
                label: "Item 2 Title",
                default: "Toggle Item 2",
            },
            {
                name: "item2_content",
                type: "wysiwyg",
                label: "Item 2 Content",
                default: "<p>Content for toggle item 2.</p>",
            },
            {
                name: "item3_title",
                type: "text",
                label: "Item 3 Title",
                default: "Toggle Item 3",
            },
            {
                name: "item3_content",
                type: "wysiwyg",
                label: "Item 3 Content",
                default: "<p>Content for toggle item 3.</p>",
            },
        ],
        style: [
            {
                name: "title_color",
                type: "color",
                label: "Title Color",
                default: "#1f2937",
            },
            {
                name: "title_background",
                type: "color",
                label: "Title Background",
                default: "#f3f4f6",
            },
            {
                name: "content_color",
                type: "color",
                label: "Content Color",
                default: "#4b5563",
            },
            { name: "border", type: "border", label: "Border" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("icon-list", {
    title: "Icon List",
    icon: "☰",
    category: "basic",
    controls: {
        content: [
            {
                name: "item1_text",
                type: "text",
                label: "Item 1 Text",
                default: "List Item 1",
            },
            {
                name: "item1_icon",
                type: "icon",
                label: "Item 1 Icon",
                default: "✓",
            },
            { name: "item1_link", type: "url", label: "Item 1 Link" },
            {
                name: "item2_text",
                type: "text",
                label: "Item 2 Text",
                default: "List Item 2",
            },
            {
                name: "item2_icon",
                type: "icon",
                label: "Item 2 Icon",
                default: "✓",
            },
            { name: "item2_link", type: "url", label: "Item 2 Link" },
            {
                name: "item3_text",
                type: "text",
                label: "Item 3 Text",
                default: "List Item 3",
            },
            {
                name: "item3_icon",
                type: "icon",
                label: "Item 3 Icon",
                default: "✓",
            },
            { name: "item3_link", type: "url", label: "Item 3 Link" },
        ],
        style: [
            {
                name: "icon_color",
                type: "color",
                label: "Icon Color",
                default: "#4f46e5",
            },
            {
                name: "text_color",
                type: "color",
                label: "Text Color",
                default: "#1f2937",
            },
            {
                name: "icon_size",
                type: "slider",
                label: "Icon Size",
                min: 10,
                max: 50,
                default: 20,
                unit: "px",
            },
            {
                name: "spacing",
                type: "slider",
                label: "Spacing",
                min: 0,
                max: 50,
                default: 12,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("text-path", {
    title: "Text Path",
    icon: "⌇",
    category: "basic",
    controls: {
        content: [
            {
                name: "text",
                type: "text",
                label: "Text",
                default: "Curved Text",
            },
            {
                name: "path_type",
                type: "select",
                label: "Path Type",
                default: "wave",
                options: { wave: "Wave", circle: "Circle", arch: "Arch" },
            },
            { name: "link", type: "url", label: "Link" },
        ],
        style: [
            {
                name: "text_color",
                type: "color",
                label: "Text Color",
                default: "#1f2937",
            },
            {
                name: "font_size",
                type: "slider",
                label: "Font Size",
                min: 12,
                max: 72,
                default: 24,
                unit: "px",
            },
            {
                name: "font_weight",
                type: "select",
                label: "Font Weight",
                default: "400",
                options: {
                    300: "Light",
                    400: "Normal",
                    600: "Semi Bold",
                    700: "Bold",
                },
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("image-carousel", {
    title: "Image Carousel",
    icon: "🎠",
    category: "basic",
    controls: {
        content: [
            { name: "image1", type: "media", label: "Image 1" },
            { name: "image2", type: "media", label: "Image 2" },
            { name: "image3", type: "media", label: "Image 3" },
            { name: "image4", type: "media", label: "Image 4" },
            {
                name: "slides_to_show",
                type: "slider",
                label: "Slides to Show",
                min: 1,
                max: 6,
                default: 3,
                unit: "",
            },
            {
                name: "autoplay",
                type: "switcher",
                label: "Autoplay",
                default: true,
            },
            {
                name: "autoplay_speed",
                type: "slider",
                label: "Autoplay Speed (ms)",
                min: 1000,
                max: 10000,
                default: 3000,
                unit: "ms",
            },
            {
                name: "infinite_loop",
                type: "switcher",
                label: "Infinite Loop",
                default: true,
            },
            {
                name: "show_arrows",
                type: "switcher",
                label: "Show Arrows",
                default: true,
            },
            {
                name: "show_dots",
                type: "switcher",
                label: "Show Dots",
                default: true,
            },
        ],
        style: [
            {
                name: "image_spacing",
                type: "slider",
                label: "Image Spacing",
                min: 0,
                max: 50,
                default: 10,
                unit: "px",
            },
            {
                name: "arrow_color",
                type: "color",
                label: "Arrow Color",
                default: "#ffffff",
            },
            {
                name: "dot_color",
                type: "color",
                label: "Dot Color",
                default: "#4f46e5",
            },
            {
                name: "border_radius",
                type: "slider",
                label: "Border Radius",
                min: 0,
                max: 50,
                default: 8,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("basic-gallery", {
    title: "Basic Gallery",
    icon: "🖼️",
    category: "basic",
    controls: {
        content: [
            { name: "image1", type: "media", label: "Image 1" },
            { name: "image2", type: "media", label: "Image 2" },
            { name: "image3", type: "media", label: "Image 3" },
            { name: "image4", type: "media", label: "Image 4" },
            { name: "image5", type: "media", label: "Image 5" },
            { name: "image6", type: "media", label: "Image 6" },
            {
                name: "columns",
                type: "select",
                label: "Columns",
                default: "3",
                options: { 2: "2", 3: "3", 4: "4", 5: "5", 6: "6" },
            },
            {
                name: "lightbox",
                type: "switcher",
                label: "Lightbox",
                default: true,
            },
            {
                name: "random_order",
                type: "switcher",
                label: "Random Order",
                default: false,
            },
        ],
        style: [
            {
                name: "gap",
                type: "slider",
                label: "Gap",
                min: 0,
                max: 50,
                default: 10,
                unit: "px",
            },
            {
                name: "border_radius",
                type: "slider",
                label: "Border Radius",
                min: 0,
                max: 50,
                default: 8,
                unit: "px",
            },
            {
                name: "hover_effect",
                type: "select",
                label: "Hover Effect",
                default: "zoom",
                options: { none: "None", zoom: "Zoom", grayscale: "Grayscale" },
            },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("soundcloud", {
    title: "SoundCloud",
    icon: "🎵",
    category: "basic",
    controls: {
        content: [
            {
                name: "url",
                type: "text",
                label: "SoundCloud URL",
                placeholder: "https://soundcloud.com/...",
            },
            {
                name: "visual",
                type: "switcher",
                label: "Visual Player",
                default: false,
            },
            {
                name: "auto_play",
                type: "switcher",
                label: "Auto Play",
                default: false,
            },
            {
                name: "buying",
                type: "switcher",
                label: "Show Buy Button",
                default: true,
            },
            {
                name: "sharing",
                type: "switcher",
                label: "Show Share Button",
                default: true,
            },
            {
                name: "download",
                type: "switcher",
                label: "Show Download Button",
                default: true,
            },
        ],
        style: [
            {
                name: "height",
                type: "slider",
                label: "Height",
                min: 100,
                max: 600,
                default: 166,
                unit: "px",
            },
        ],
        advanced: getAdvancedControls(),
    },
});

// Layout & Advanced Widgets

widgetRegistry.register("container", {
    title: "Container",
    icon: "▭",
    category: "layout",
    controls: {
        content: [
            {
                name: "container_layout",
                type: "select",
                label: "Container Layout",
                default: "flexbox",
                options: { flexbox: "Flexbox", grid: "Grid" },
            },
            {
                name: "content_width",
                type: "select",
                label: "Content Width",
                default: "boxed",
                options: { boxed: "Boxed", full: "Full Width" },
            },
            {
                name: "width",
                type: "slider",
                label: "Width",
                min: 0,
                max: 2000,
                default: 1140,
                unit: "px",
            },
            {
                name: "min_height",
                type: "slider",
                label: "Min Height",
                min: 0,
                max: 1000,
                default: 0,
                unit: "px",
            },
            // Items Section
            {
                name: "flex_direction",
                type: "flexbox_direction",
                label: "Direction",
                default: "row",
            },
            {
                name: "justify_content",
                type: "flexbox_justify",
                label: "Justify Content",
                default: "flex-start",
            },
            {
                name: "align_items",
                type: "flexbox_align",
                label: "Align Items",
                default: "flex-start",
            },
            {
                name: "gaps",
                type: "gaps",
                label: "Gaps",
                default: { column: 20, row: 20, linked: true },
                unit: "px",
            },
            {
                name: "flex_wrap",
                type: "flexbox_wrap",
                label: "Wrap",
                default: "nowrap",
            },
            {
                name: "html_tag",
                type: "select",
                label: "HTML Tag",
                default: "div",
                options: {
                    div: "div",
                    section: "section",
                    article: "article",
                    main: "main",
                    aside: "aside",
                },
            },
        ],
        style: [
            { name: "background", type: "background", label: "Background" },
            { name: "border", type: "border", label: "Border" },
            { name: "box_shadow", type: "box_shadow", label: "Box Shadow" },
        ],
        advanced: getAdvancedControls([
            {
                name: "align_self",
                type: "align_self",
                label: "Align Self",
                default: "auto",
            },
            { name: "order", type: "order", label: "Order", default: 0 },
            {
                name: "size",
                type: "size_control",
                label: "Size",
                default: { type: "default" },
            },
            {
                name: "position",
                type: "position",
                label: "Position",
                default: { type: "default" },
            },
        ]),
    },
});

widgetRegistry.register("inner-section", {
    title: "Inner Section",
    icon: "▦",
    category: "layout",
    controls: {
        content: [
            {
                name: "columns",
                type: "select",
                label: "Columns",
                default: "2",
                options: {
                    1: "1 Column",
                    2: "2 Columns",
                    3: "3 Columns",
                    4: "4 Columns",
                },
            },
            {
                name: "column_gap",
                type: "slider",
                label: "Column Gap",
                min: 0,
                max: 100,
                default: 20,
                unit: "px",
            },
        ],
        style: [
            { name: "background", type: "background", label: "Background" },
            { name: "border", type: "border", label: "Border" },
        ],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("menu-anchor", {
    title: "Menu Anchor",
    icon: "⚓",
    category: "layout",
    controls: {
        content: [
            {
                name: "anchor_id",
                type: "text",
                label: "Anchor ID",
                placeholder: "my-anchor",
                default: "anchor",
            },
        ],
        style: [],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("sidebar", {
    title: "Sidebar",
    icon: "▐",
    category: "layout",
    controls: {
        content: [
            {
                name: "sidebar_id",
                type: "select",
                label: "Sidebar",
                default: "primary",
                options: {
                    primary: "Primary Sidebar",
                    secondary: "Secondary Sidebar",
                    footer: "Footer Sidebar",
                },
            },
        ],
        style: [],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("html", {
    title: "HTML",
    icon: "\u003c/\u003e",
    category: "advanced",
    controls: {
        content: [
            {
                name: "html_code",
                type: "code",
                label: "HTML Code",
                default:
                    '<div class="custom-html">\n  <p>Add your custom HTML here</p>\n</div>',
            },
        ],
        style: [],
        advanced: getAdvancedControls(),
    },
});

widgetRegistry.register("shortcode", {
    title: "Shortcode",
    icon: "[ ]",
    category: "advanced",
    controls: {
        content: [
            {
                name: "shortcode",
                type: "text",
                label: "Shortcode",
                placeholder: "[your_shortcode]",
                default: "[shortcode]",
            },
        ],
        style: [],
        advanced: getAdvancedControls(),
    },
});
