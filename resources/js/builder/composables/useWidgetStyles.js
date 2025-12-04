/**
 * Composable for shared widget styling utilities
 * Extracted from WidgetRenderer.vue for reusability
 */
import { computed, ref } from 'vue';

export function useWidgetStyles(settings) {
    /**
     * Format dimensions object (margin/padding) to CSS string
     */
    const formatDimensions = (dims) => {
        if (!dims) return '';
        if (dims.linked) {
            const val = dims.top ?? 0;
            return `${val}px`;
        }
        return `${dims.top ?? 0}px ${dims.right ?? 0}px ${dims.bottom ?? 0}px ${dims.left ?? 0}px`;
    };

    /**
     * Get common wrapper styles (background, border, shadow, position)
     */
    const getCommonWrapperStyles = () => {
        const settingsVal = settings.value || settings;
        const bg = settingsVal.background;
        const border = settingsVal.border;
        const shadow = settingsVal.box_shadow;
        const position = settingsVal.position;

        const styles = {
            margin: formatDimensions(settingsVal.margin),
            padding: formatDimensions(settingsVal.padding),
        };

        // Z-Index
        if (settingsVal.z_index) {
            styles.zIndex = settingsVal.z_index;
            styles.position = 'relative';
        }

        // Position Control
        if (position?.type && position.type !== 'default') {
            styles.position = position.type;
            if (position.top) styles.top = position.top + 'px';
            if (position.right) styles.right = position.right + 'px';
            if (position.bottom) styles.bottom = position.bottom + 'px';
            if (position.left) styles.left = position.left + 'px';
        }

        // Background
        if (bg?.type === 'gradient') {
            styles.background = `linear-gradient(${bg.gradientAngle ?? 180}deg, ${bg.gradientColor1 ?? '#6366f1'}, ${bg.gradientColor2 ?? '#8b5cf6'})`;
        } else if (bg?.color) {
            styles.backgroundColor = bg.color;
        }

        if (bg?.image) {
            styles.backgroundImage = `url(${bg.image})`;
            styles.backgroundPosition = bg.position ?? 'center center';
            styles.backgroundSize = bg.size ?? 'cover';
            styles.backgroundRepeat = bg.repeat ?? 'no-repeat';
        }

        // Border
        if (border?.style && border.style !== 'none') {
            styles.borderStyle = border.style;
            styles.borderColor = border.color ?? '#e5e7eb';
            styles.borderWidth = '1px';
        }
        if (border?.radius) {
            styles.borderRadius = `${border.radius.topLeft ?? 0}px ${border.radius.topRight ?? 0}px ${border.radius.bottomRight ?? 0}px ${border.radius.bottomLeft ?? 0}px`;
        }

        // Box Shadow
        if (shadow && (shadow.horizontal || shadow.vertical || shadow.blur)) {
            const inset = shadow.position === 'inset' ? 'inset ' : '';
            styles.boxShadow = `${inset}${shadow.horizontal ?? 0}px ${shadow.vertical ?? 0}px ${shadow.blur ?? 0}px ${shadow.spread ?? 0}px ${shadow.color ?? 'rgba(0,0,0,0.1)'}`;
        }

        return styles;
    };

    /**
     * Responsive visibility classes based on settings
     */
    const responsiveVisibilityClasses = computed(() => {
        const settingsVal = settings.value || settings;
        const visibility = settingsVal.responsive_visibility;
        if (!visibility) return '';

        const classes = [];
        if (visibility.hide_desktop) classes.push('hidden-desktop');
        if (visibility.hide_tablet) classes.push('hidden-tablet');
        if (visibility.hide_mobile) classes.push('hidden-mobile');

        return classes.join(' ');
    });

    return {
        formatDimensions,
        getCommonWrapperStyles,
        responsiveVisibilityClasses,
    };
}

/**
 * Composable for entrance animation handling
 */
export function useWidgetAnimation(settings, widgetRef) {
    const animationTriggered = ref(false);
    let animationObserver = null;

    /**
     * Animation classes based on motion effects settings
     */
    const animationClasses = computed(() => {
        const settingsVal = settings.value || settings;
        const motion = settingsVal.motion_effects;
        if (!motion || !motion.entrance_animation || motion.entrance_animation === 'none') return '';

        // Only apply animation if triggered
        if (!animationTriggered.value) return 'animation-hidden';

        const classes = [`animate-${motion.entrance_animation}`];

        // Animation duration
        if (motion.animation_duration) {
            classes.push(`animation-duration-${motion.animation_duration}`);
        }

        return classes.join(' ');
    });

    /**
     * Setup Intersection Observer for animations
     */
    const setupAnimationObserver = () => {
        const settingsVal = settings.value || settings;
        const motion = settingsVal.motion_effects;
        if (!motion || !motion.entrance_animation || motion.entrance_animation === 'none') {
            animationTriggered.value = true;
            return;
        }

        animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !animationTriggered.value) {
                    animationTriggered.value = true;
                    animationObserver?.disconnect();
                }
            });
        }, { threshold: 0.1 });

        if (widgetRef.value) {
            animationObserver.observe(widgetRef.value);
        }
    };

    /**
     * Cleanup observer
     */
    const cleanupAnimationObserver = () => {
        animationObserver?.disconnect();
    };

    return {
        animationTriggered,
        animationClasses,
        setupAnimationObserver,
        cleanupAnimationObserver,
    };
}
