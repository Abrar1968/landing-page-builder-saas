# Drag-and-Drop Builder

## Overview

The drag-and-drop builder is the core feature of the Landing Page Builder SaaS. It provides a visual interface for users to create landing pages by dragging elements onto a canvas and customizing them.

## Technology Stack

- **React 18** - UI framework
- **SortableJS** - Drag-and-drop library
- **react-sortablejs** - React bindings for SortableJS
- **Zustand** - State management
- **TypeScript** - Type safety

## SortableJS Integration

### Installation

```bash
npm install sortablejs react-sortablejs @types/sortablejs
```

### Core Sortable Configuration

```typescript
// src/builder/components/Canvas.tsx
import { ReactSortable, SortableEvent } from 'react-sortablejs';
import { useBuilderStore } from '../store/builderStore';

interface CanvasProps {
  pageId: string;
}

export const Canvas: React.FC<CanvasProps> = ({ pageId }) => {
  const { elements, setElements, addToHistory } = useBuilderStore();

  const handleSort = (evt: SortableEvent) => {
    addToHistory();
  };

  const handleAdd = (evt: SortableEvent) => {
    addToHistory();
  };

  return (
    <ReactSortable
      list={elements}
      setList={setElements}
      group={{ name: 'builder', pull: true, put: true }}
      animation={200}
      ghostClass="element-ghost"
      chosenClass="element-chosen"
      dragClass="element-drag"
      handle=".drag-handle"
      onSort={handleSort}
      onAdd={handleAdd}
      className="canvas-container"
    >
      {elements.map((element) => (
        <BuilderElement key={element.id} element={element} />
      ))}
    </ReactSortable>
  );
};
```

### Element Palette (Sidebar)

```typescript
// src/builder/components/ElementPalette.tsx
import { ReactSortable } from 'react-sortablejs';
import { v4 as uuidv4 } from 'uuid';
import { ElementType, BuilderElement } from '../types';

const paletteElements: BuilderElement[] = [
  { id: 'palette-section', type: 'section', label: 'Section', props: {} },
  { id: 'palette-heading', type: 'heading', label: 'Heading', props: {} },
  { id: 'palette-paragraph', type: 'paragraph', label: 'Paragraph', props: {} },
  { id: 'palette-image', type: 'image', label: 'Image', props: {} },
  { id: 'palette-button', type: 'button', label: 'Button', props: {} },
  { id: 'palette-video', type: 'video', label: 'Video', props: {} },
  { id: 'palette-form', type: 'form', label: 'Form', props: {} },
  { id: 'palette-columns', type: 'columns', label: 'Columns', props: {} },
];

export const ElementPalette: React.FC = () => {
  return (
    <div className="element-palette">
      <h3>Elements</h3>
      <ReactSortable
        list={paletteElements}
        setList={() => {}}
        group={{ name: 'builder', pull: 'clone', put: false }}
        sort={false}
        clone={(item) => ({
          ...item,
          id: uuidv4(),
          props: getDefaultProps(item.type),
        })}
        className="palette-list"
      >
        {paletteElements.map((element) => (
          <div key={element.id} className="palette-item">
            <ElementIcon type={element.type} />
            <span>{element.label}</span>
          </div>
        ))}
      </ReactSortable>
    </div>
  );
};
```

---

## Builder Elements

### Type Definitions

```typescript
// src/builder/types/elements.ts
export type ElementType =
  | 'section'
  | 'heading'
  | 'paragraph'
  | 'image'
  | 'button'
  | 'video'
  | 'form'
  | 'columns';

export interface BaseElement {
  id: string;
  type: ElementType;
  label: string;
  props: Record<string, any>;
  children?: BuilderElement[];
  styles?: ElementStyles;
}

export interface ElementStyles {
  margin?: string;
  padding?: string;
  backgroundColor?: string;
  borderRadius?: string;
  border?: string;
  boxShadow?: string;
  width?: string;
  maxWidth?: string;
  textAlign?: 'left' | 'center' | 'right';
}

// Section Element
export interface SectionElement extends BaseElement {
  type: 'section';
  props: {
    backgroundColor?: string;
    backgroundImage?: string;
    backgroundSize?: 'cover' | 'contain' | 'auto';
    minHeight?: string;
    fullWidth?: boolean;
  };
  children: BuilderElement[];
}

// Heading Element
export interface HeadingElement extends BaseElement {
  type: 'heading';
  props: {
    text: string;
    level: 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6';
    color?: string;
    fontSize?: string;
    fontWeight?: string;
    fontFamily?: string;
  };
}

// Paragraph Element
export interface ParagraphElement extends BaseElement {
  type: 'paragraph';
  props: {
    text: string;
    color?: string;
    fontSize?: string;
    lineHeight?: string;
    fontFamily?: string;
  };
}

// Image Element
export interface ImageElement extends BaseElement {
  type: 'image';
  props: {
    src: string;
    alt: string;
    width?: string;
    height?: string;
    objectFit?: 'cover' | 'contain' | 'fill' | 'none';
    link?: string;
    linkTarget?: '_blank' | '_self';
  };
}

// Button Element
export interface ButtonElement extends BaseElement {
  type: 'button';
  props: {
    text: string;
    link?: string;
    linkTarget?: '_blank' | '_self';
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost';
    size?: 'sm' | 'md' | 'lg';
    backgroundColor?: string;
    textColor?: string;
    borderRadius?: string;
    fullWidth?: boolean;
  };
}

// Video Element
export interface VideoElement extends BaseElement {
  type: 'video';
  props: {
    src: string;
    provider?: 'youtube' | 'vimeo' | 'custom';
    autoplay?: boolean;
    muted?: boolean;
    loop?: boolean;
    controls?: boolean;
    aspectRatio?: '16:9' | '4:3' | '1:1';
  };
}

// Form Element
export interface FormElement extends BaseElement {
  type: 'form';
  props: {
    fields: FormField[];
    submitText: string;
    submitAction: string;
    successMessage: string;
    buttonColor?: string;
    buttonTextColor?: string;
  };
}

export interface FormField {
  id: string;
  type: 'text' | 'email' | 'phone' | 'textarea' | 'select' | 'checkbox';
  label: string;
  placeholder?: string;
  required?: boolean;
  options?: string[]; // For select fields
}

// Columns Element
export interface ColumnsElement extends BaseElement {
  type: 'columns';
  props: {
    columns: number;
    gap?: string;
    layout?: string; // e.g., '1:1', '1:2', '2:1', '1:1:1'
  };
  children: ColumnChild[];
}

export interface ColumnChild {
  id: string;
  elements: BuilderElement[];
}

export type BuilderElement =
  | SectionElement
  | HeadingElement
  | ParagraphElement
  | ImageElement
  | ButtonElement
  | VideoElement
  | FormElement
  | ColumnsElement;
```

### Default Props Factory

```typescript
// src/builder/utils/defaultProps.ts
import { ElementType } from '../types';

export const getDefaultProps = (type: ElementType): Record<string, any> => {
  switch (type) {
    case 'section':
      return {
        backgroundColor: '#ffffff',
        minHeight: '200px',
        fullWidth: false,
      };

    case 'heading':
      return {
        text: 'Heading Text',
        level: 'h2',
        color: '#000000',
        fontSize: '32px',
        fontWeight: '700',
      };

    case 'paragraph':
      return {
        text: 'Enter your text here. Click to edit this paragraph.',
        color: '#333333',
        fontSize: '16px',
        lineHeight: '1.6',
      };

    case 'image':
      return {
        src: '/placeholder-image.jpg',
        alt: 'Image description',
        width: '100%',
        objectFit: 'cover',
      };

    case 'button':
      return {
        text: 'Click Me',
        variant: 'primary',
        size: 'md',
        backgroundColor: '#3b82f6',
        textColor: '#ffffff',
        borderRadius: '6px',
      };

    case 'video':
      return {
        src: '',
        provider: 'youtube',
        autoplay: false,
        muted: false,
        loop: false,
        controls: true,
        aspectRatio: '16:9',
      };

    case 'form':
      return {
        fields: [
          { id: '1', type: 'text', label: 'Name', placeholder: 'Your name', required: true },
          { id: '2', type: 'email', label: 'Email', placeholder: 'your@email.com', required: true },
        ],
        submitText: 'Submit',
        submitAction: '/api/forms/submit',
        successMessage: 'Thank you for your submission!',
        buttonColor: '#3b82f6',
        buttonTextColor: '#ffffff',
      };

    case 'columns':
      return {
        columns: 2,
        gap: '24px',
        layout: '1:1',
      };

    default:
      return {};
  }
};
```

### Element Components

```typescript
// src/builder/components/elements/index.tsx
import React from 'react';
import { BuilderElement } from '../../types';
import { SectionElement } from './SectionElement';
import { HeadingElement } from './HeadingElement';
import { ParagraphElement } from './ParagraphElement';
import { ImageElement } from './ImageElement';
import { ButtonElement } from './ButtonElement';
import { VideoElement } from './VideoElement';
import { FormElement } from './FormElement';
import { ColumnsElement } from './ColumnsElement';

interface ElementRendererProps {
  element: BuilderElement;
  isEditing?: boolean;
  onSelect?: (id: string) => void;
  isSelected?: boolean;
}

export const ElementRenderer: React.FC<ElementRendererProps> = ({
  element,
  isEditing = false,
  onSelect,
  isSelected = false,
}) => {
  const components: Record<string, React.FC<any>> = {
    section: SectionElement,
    heading: HeadingElement,
    paragraph: ParagraphElement,
    image: ImageElement,
    button: ButtonElement,
    video: VideoElement,
    form: FormElement,
    columns: ColumnsElement,
  };

  const Component = components[element.type];

  if (!Component) {
    return <div>Unknown element type: {element.type}</div>;
  }

  return (
    <div
      className={`element-wrapper ${isSelected ? 'selected' : ''}`}
      onClick={(e) => {
        e.stopPropagation();
        onSelect?.(element.id);
      }}
    >
      {isEditing && (
        <div className="element-toolbar">
          <button className="drag-handle" title="Drag">
            <GripIcon />
          </button>
          <span className="element-type-label">{element.label}</span>
        </div>
      )}
      <Component element={element} isEditing={isEditing} />
    </div>
  );
};
```

#### Section Element

```typescript
// src/builder/components/elements/SectionElement.tsx
import React from 'react';
import { ReactSortable } from 'react-sortablejs';
import { SectionElement as SectionType } from '../../types';
import { useBuilderStore } from '../../store/builderStore';
import { ElementRenderer } from './index';

interface Props {
  element: SectionType;
  isEditing?: boolean;
}

export const SectionElement: React.FC<Props> = ({ element, isEditing }) => {
  const { updateElementChildren, selectedElement, setSelectedElement } = useBuilderStore();

  const style: React.CSSProperties = {
    backgroundColor: element.props.backgroundColor,
    backgroundImage: element.props.backgroundImage
      ? `url(${element.props.backgroundImage})`
      : undefined,
    backgroundSize: element.props.backgroundSize,
    minHeight: element.props.minHeight,
    width: element.props.fullWidth ? '100vw' : '100%',
    padding: element.styles?.padding || '40px 20px',
  };

  if (isEditing) {
    return (
      <section style={style} className="builder-section">
        <ReactSortable
          list={element.children || []}
          setList={(newChildren) => updateElementChildren(element.id, newChildren)}
          group={{ name: 'builder', pull: true, put: true }}
          animation={200}
          className="section-dropzone"
        >
          {(element.children || []).map((child) => (
            <ElementRenderer
              key={child.id}
              element={child}
              isEditing={isEditing}
              onSelect={setSelectedElement}
              isSelected={selectedElement === child.id}
            />
          ))}
        </ReactSortable>
        {(!element.children || element.children.length === 0) && (
          <div className="empty-section-placeholder">
            Drop elements here
          </div>
        )}
      </section>
    );
  }

  return (
    <section style={style} className="builder-section">
      {(element.children || []).map((child) => (
        <ElementRenderer key={child.id} element={child} />
      ))}
    </section>
  );
};
```

#### Heading Element

```typescript
// src/builder/components/elements/HeadingElement.tsx
import React from 'react';
import { HeadingElement as HeadingType } from '../../types';

interface Props {
  element: HeadingType;
  isEditing?: boolean;
}

export const HeadingElement: React.FC<Props> = ({ element, isEditing }) => {
  const Tag = element.props.level as keyof JSX.IntrinsicElements;

  const style: React.CSSProperties = {
    color: element.props.color,
    fontSize: element.props.fontSize,
    fontWeight: element.props.fontWeight,
    fontFamily: element.props.fontFamily,
    margin: element.styles?.margin || '0 0 16px 0',
    textAlign: element.styles?.textAlign,
  };

  return (
    <Tag style={style} className="builder-heading">
      {element.props.text}
    </Tag>
  );
};
```

#### Paragraph Element

```typescript
// src/builder/components/elements/ParagraphElement.tsx
import React from 'react';
import { ParagraphElement as ParagraphType } from '../../types';

interface Props {
  element: ParagraphType;
  isEditing?: boolean;
}

export const ParagraphElement: React.FC<Props> = ({ element }) => {
  const style: React.CSSProperties = {
    color: element.props.color,
    fontSize: element.props.fontSize,
    lineHeight: element.props.lineHeight,
    fontFamily: element.props.fontFamily,
    margin: element.styles?.margin || '0 0 16px 0',
    textAlign: element.styles?.textAlign,
  };

  return (
    <p style={style} className="builder-paragraph">
      {element.props.text}
    </p>
  );
};
```

#### Image Element

```typescript
// src/builder/components/elements/ImageElement.tsx
import React from 'react';
import { ImageElement as ImageType } from '../../types';

interface Props {
  element: ImageType;
  isEditing?: boolean;
}

export const ImageElement: React.FC<Props> = ({ element, isEditing }) => {
  const imgStyle: React.CSSProperties = {
    width: element.props.width,
    height: element.props.height,
    objectFit: element.props.objectFit,
    borderRadius: element.styles?.borderRadius,
  };

  const img = (
    <img
      src={element.props.src}
      alt={element.props.alt}
      style={imgStyle}
      className="builder-image"
      loading="lazy"
    />
  );

  if (element.props.link && !isEditing) {
    return (
      <a
        href={element.props.link}
        target={element.props.linkTarget}
        rel={element.props.linkTarget === '_blank' ? 'noopener noreferrer' : undefined}
      >
        {img}
      </a>
    );
  }

  return img;
};
```

#### Button Element

```typescript
// src/builder/components/elements/ButtonElement.tsx
import React from 'react';
import { ButtonElement as ButtonType } from '../../types';

interface Props {
  element: ButtonType;
  isEditing?: boolean;
}

export const ButtonElement: React.FC<Props> = ({ element, isEditing }) => {
  const sizeStyles = {
    sm: { padding: '8px 16px', fontSize: '14px' },
    md: { padding: '12px 24px', fontSize: '16px' },
    lg: { padding: '16px 32px', fontSize: '18px' },
  };

  const style: React.CSSProperties = {
    backgroundColor: element.props.backgroundColor,
    color: element.props.textColor,
    borderRadius: element.props.borderRadius,
    width: element.props.fullWidth ? '100%' : 'auto',
    border: element.props.variant === 'outline'
      ? `2px solid ${element.props.backgroundColor}`
      : 'none',
    ...sizeStyles[element.props.size || 'md'],
    cursor: isEditing ? 'default' : 'pointer',
    display: 'inline-block',
    textDecoration: 'none',
    textAlign: 'center',
    fontWeight: '600',
  };

  if (element.props.variant === 'outline') {
    style.backgroundColor = 'transparent';
    style.color = element.props.backgroundColor;
  }

  if (element.props.variant === 'ghost') {
    style.backgroundColor = 'transparent';
    style.color = element.props.backgroundColor;
    style.border = 'none';
  }

  const handleClick = (e: React.MouseEvent) => {
    if (isEditing) {
      e.preventDefault();
    }
  };

  if (element.props.link && !isEditing) {
    return (
      <a
        href={element.props.link}
        target={element.props.linkTarget}
        rel={element.props.linkTarget === '_blank' ? 'noopener noreferrer' : undefined}
        style={style}
        className="builder-button"
      >
        {element.props.text}
      </a>
    );
  }

  return (
    <button style={style} className="builder-button" onClick={handleClick}>
      {element.props.text}
    </button>
  );
};
```

#### Video Element

```typescript
// src/builder/components/elements/VideoElement.tsx
import React from 'react';
import { VideoElement as VideoType } from '../../types';

interface Props {
  element: VideoType;
  isEditing?: boolean;
}

export const VideoElement: React.FC<Props> = ({ element, isEditing }) => {
  const aspectRatios = {
    '16:9': '56.25%',
    '4:3': '75%',
    '1:1': '100%',
  };

  const containerStyle: React.CSSProperties = {
    position: 'relative',
    paddingBottom: aspectRatios[element.props.aspectRatio || '16:9'],
    height: 0,
    overflow: 'hidden',
    borderRadius: element.styles?.borderRadius,
  };

  const mediaStyle: React.CSSProperties = {
    position: 'absolute',
    top: 0,
    left: 0,
    width: '100%',
    height: '100%',
  };

  const getYouTubeEmbedUrl = (url: string): string => {
    const videoId = url.match(/(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([^&?]+)/)?.[1];
    if (!videoId) return url;

    const params = new URLSearchParams();
    if (element.props.autoplay) params.set('autoplay', '1');
    if (element.props.muted) params.set('mute', '1');
    if (element.props.loop) params.set('loop', '1');
    if (!element.props.controls) params.set('controls', '0');

    return `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
  };

  const getVimeoEmbedUrl = (url: string): string => {
    const videoId = url.match(/vimeo\.com\/(\d+)/)?.[1];
    if (!videoId) return url;

    const params = new URLSearchParams();
    if (element.props.autoplay) params.set('autoplay', '1');
    if (element.props.muted) params.set('muted', '1');
    if (element.props.loop) params.set('loop', '1');

    return `https://player.vimeo.com/video/${videoId}?${params.toString()}`;
  };

  if (element.props.provider === 'youtube' || element.props.provider === 'vimeo') {
    const embedUrl = element.props.provider === 'youtube'
      ? getYouTubeEmbedUrl(element.props.src)
      : getVimeoEmbedUrl(element.props.src);

    return (
      <div style={containerStyle} className="builder-video">
        <iframe
          src={embedUrl}
          style={mediaStyle}
          frameBorder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowFullScreen
          title="Video"
        />
      </div>
    );
  }

  return (
    <div style={containerStyle} className="builder-video">
      <video
        src={element.props.src}
        style={mediaStyle}
        autoPlay={element.props.autoplay && !isEditing}
        muted={element.props.muted}
        loop={element.props.loop}
        controls={element.props.controls}
      />
    </div>
  );
};
```

#### Form Element

```typescript
// src/builder/components/elements/FormElement.tsx
import React, { useState } from 'react';
import { FormElement as FormType, FormField } from '../../types';

interface Props {
  element: FormType;
  isEditing?: boolean;
}

export const FormElement: React.FC<Props> = ({ element, isEditing }) => {
  const [formData, setFormData] = useState<Record<string, string>>({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSuccess, setIsSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (isEditing) return;

    setIsSubmitting(true);
    try {
      const response = await fetch(element.props.submitAction, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      if (response.ok) {
        setIsSuccess(true);
        setFormData({});
      }
    } catch (error) {
      console.error('Form submission error:', error);
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleChange = (fieldId: string, value: string) => {
    setFormData((prev) => ({ ...prev, [fieldId]: value }));
  };

  const renderField = (field: FormField) => {
    const commonProps = {
      id: field.id,
      name: field.id,
      placeholder: field.placeholder,
      required: field.required,
      value: formData[field.id] || '',
      onChange: (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) =>
        handleChange(field.id, e.target.value),
      disabled: isEditing,
    };

    switch (field.type) {
      case 'textarea':
        return <textarea {...commonProps} rows={4} className="form-textarea" />;

      case 'select':
        return (
          <select {...commonProps} className="form-select">
            <option value="">{field.placeholder || 'Select...'}</option>
            {field.options?.map((option) => (
              <option key={option} value={option}>
                {option}
              </option>
            ))}
          </select>
        );

      case 'checkbox':
        return (
          <label className="form-checkbox-label">
            <input
              type="checkbox"
              {...commonProps}
              checked={formData[field.id] === 'true'}
              onChange={(e) => handleChange(field.id, String(e.target.checked))}
            />
            {field.label}
          </label>
        );

      default:
        return (
          <input
            type={field.type}
            {...commonProps}
            className="form-input"
          />
        );
    }
  };

  if (isSuccess) {
    return (
      <div className="form-success">
        {element.props.successMessage}
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="builder-form">
      {element.props.fields.map((field) => (
        <div key={field.id} className="form-field">
          {field.type !== 'checkbox' && (
            <label htmlFor={field.id} className="form-label">
              {field.label}
              {field.required && <span className="required">*</span>}
            </label>
          )}
          {renderField(field)}
        </div>
      ))}
      <button
        type="submit"
        disabled={isSubmitting || isEditing}
        style={{
          backgroundColor: element.props.buttonColor,
          color: element.props.buttonTextColor,
        }}
        className="form-submit"
      >
        {isSubmitting ? 'Submitting...' : element.props.submitText}
      </button>
    </form>
  );
};
```

#### Columns Element

```typescript
// src/builder/components/elements/ColumnsElement.tsx
import React from 'react';
import { ReactSortable } from 'react-sortablejs';
import { ColumnsElement as ColumnsType } from '../../types';
import { useBuilderStore } from '../../store/builderStore';
import { ElementRenderer } from './index';

interface Props {
  element: ColumnsType;
  isEditing?: boolean;
}

export const ColumnsElement: React.FC<Props> = ({ element, isEditing }) => {
  const { updateColumnChildren, selectedElement, setSelectedElement } = useBuilderStore();

  const parseLayout = (layout: string): number[] => {
    return layout.split(':').map(Number);
  };

  const layoutRatios = parseLayout(element.props.layout || '1:1');
  const totalRatio = layoutRatios.reduce((a, b) => a + b, 0);

  const containerStyle: React.CSSProperties = {
    display: 'grid',
    gridTemplateColumns: layoutRatios
      .map((ratio) => `${(ratio / totalRatio) * 100}%`)
      .join(' '),
    gap: element.props.gap,
  };

  return (
    <div style={containerStyle} className="builder-columns">
      {element.children.map((column, index) => (
        <div key={column.id} className="builder-column">
          {isEditing ? (
            <ReactSortable
              list={column.elements}
              setList={(newElements) =>
                updateColumnChildren(element.id, column.id, newElements)
              }
              group={{ name: 'builder', pull: true, put: true }}
              animation={200}
              className="column-dropzone"
            >
              {column.elements.map((child) => (
                <ElementRenderer
                  key={child.id}
                  element={child}
                  isEditing={isEditing}
                  onSelect={setSelectedElement}
                  isSelected={selectedElement === child.id}
                />
              ))}
            </ReactSortable>
          ) : (
            column.elements.map((child) => (
              <ElementRenderer key={child.id} element={child} />
            ))
          )}
          {isEditing && column.elements.length === 0 && (
            <div className="empty-column-placeholder">
              Column {index + 1}
            </div>
          )}
        </div>
      ))}
    </div>
  );
};
```

---

## Element Editing

### Properties Panel

```typescript
// src/builder/components/PropertiesPanel.tsx
import React from 'react';
import { useBuilderStore } from '../store/builderStore';
import { BuilderElement } from '../types';
import {
  HeadingPropsEditor,
  ParagraphPropsEditor,
  ImagePropsEditor,
  ButtonPropsEditor,
  VideoPropsEditor,
  FormPropsEditor,
  SectionPropsEditor,
  ColumnsPropsEditor,
  StylesEditor,
} from './editors';

export const PropertiesPanel: React.FC = () => {
  const { selectedElement, elements, updateElementProps, updateElementStyles, deleteElement, duplicateElement } = useBuilderStore();

  const findElement = (
    elements: BuilderElement[],
    id: string
  ): BuilderElement | null => {
    for (const el of elements) {
      if (el.id === id) return el;
      if ('children' in el && el.children) {
        const found = findElement(el.children as BuilderElement[], id);
        if (found) return found;
      }
    }
    return null;
  };

  const element = selectedElement ? findElement(elements, selectedElement) : null;

  if (!element) {
    return (
      <div className="properties-panel empty">
        <p>Select an element to edit its properties</p>
      </div>
    );
  }

  const editors: Record<string, React.FC<any>> = {
    heading: HeadingPropsEditor,
    paragraph: ParagraphPropsEditor,
    image: ImagePropsEditor,
    button: ButtonPropsEditor,
    video: VideoPropsEditor,
    form: FormPropsEditor,
    section: SectionPropsEditor,
    columns: ColumnsPropsEditor,
  };

  const PropsEditor = editors[element.type];

  return (
    <div className="properties-panel">
      <div className="panel-header">
        <h3>{element.label} Properties</h3>
        <div className="panel-actions">
          <button onClick={() => duplicateElement(element.id)} title="Duplicate">
            <CopyIcon />
          </button>
          <button onClick={() => deleteElement(element.id)} title="Delete">
            <TrashIcon />
          </button>
        </div>
      </div>

      <div className="panel-content">
        {PropsEditor && (
          <PropsEditor
            props={element.props}
            onChange={(newProps: Record<string, any>) =>
              updateElementProps(element.id, newProps)
            }
          />
        )}

        <StylesEditor
          styles={element.styles || {}}
          onChange={(newStyles) => updateElementStyles(element.id, newStyles)}
        />
      </div>
    </div>
  );
};
```

### Property Editors

```typescript
// src/builder/components/editors/HeadingPropsEditor.tsx
import React from 'react';
import { HeadingElement } from '../../types';
import { ColorPicker, Select, TextInput, FontPicker } from '../ui';

interface Props {
  props: HeadingElement['props'];
  onChange: (props: HeadingElement['props']) => void;
}

export const HeadingPropsEditor: React.FC<Props> = ({ props, onChange }) => {
  const update = (key: string, value: any) => {
    onChange({ ...props, [key]: value });
  };

  return (
    <div className="props-editor">
      <div className="editor-section">
        <h4>Content</h4>
        <TextInput
          label="Text"
          value={props.text}
          onChange={(value) => update('text', value)}
          multiline
        />
        <Select
          label="Level"
          value={props.level}
          onChange={(value) => update('level', value)}
          options={[
            { value: 'h1', label: 'H1' },
            { value: 'h2', label: 'H2' },
            { value: 'h3', label: 'H3' },
            { value: 'h4', label: 'H4' },
            { value: 'h5', label: 'H5' },
            { value: 'h6', label: 'H6' },
          ]}
        />
      </div>

      <div className="editor-section">
        <h4>Typography</h4>
        <ColorPicker
          label="Color"
          value={props.color || '#000000'}
          onChange={(value) => update('color', value)}
        />
        <TextInput
          label="Font Size"
          value={props.fontSize || '32px'}
          onChange={(value) => update('fontSize', value)}
        />
        <Select
          label="Font Weight"
          value={props.fontWeight || '700'}
          onChange={(value) => update('fontWeight', value)}
          options={[
            { value: '400', label: 'Normal' },
            { value: '500', label: 'Medium' },
            { value: '600', label: 'Semibold' },
            { value: '700', label: 'Bold' },
          ]}
        />
        <FontPicker
          label="Font Family"
          value={props.fontFamily}
          onChange={(value) => update('fontFamily', value)}
        />
      </div>
    </div>
  );
};
```

### Styles Editor

```typescript
// src/builder/components/editors/StylesEditor.tsx
import React from 'react';
import { ElementStyles } from '../../types';
import { ColorPicker, TextInput, Select } from '../ui';

interface Props {
  styles: ElementStyles;
  onChange: (styles: ElementStyles) => void;
}

export const StylesEditor: React.FC<Props> = ({ styles, onChange }) => {
  const update = (key: keyof ElementStyles, value: any) => {
    onChange({ ...styles, [key]: value });
  };

  return (
    <div className="styles-editor">
      <h4>Styles</h4>

      <div className="editor-section">
        <h5>Spacing</h5>
        <TextInput
          label="Margin"
          value={styles.margin || ''}
          onChange={(value) => update('margin', value)}
          placeholder="e.g., 10px 20px"
        />
        <TextInput
          label="Padding"
          value={styles.padding || ''}
          onChange={(value) => update('padding', value)}
          placeholder="e.g., 20px"
        />
      </div>

      <div className="editor-section">
        <h5>Background</h5>
        <ColorPicker
          label="Background Color"
          value={styles.backgroundColor || '#ffffff'}
          onChange={(value) => update('backgroundColor', value)}
        />
      </div>

      <div className="editor-section">
        <h5>Border</h5>
        <TextInput
          label="Border Radius"
          value={styles.borderRadius || ''}
          onChange={(value) => update('borderRadius', value)}
          placeholder="e.g., 8px"
        />
        <TextInput
          label="Border"
          value={styles.border || ''}
          onChange={(value) => update('border', value)}
          placeholder="e.g., 1px solid #ccc"
        />
        <TextInput
          label="Box Shadow"
          value={styles.boxShadow || ''}
          onChange={(value) => update('boxShadow', value)}
          placeholder="e.g., 0 2px 4px rgba(0,0,0,0.1)"
        />
      </div>

      <div className="editor-section">
        <h5>Layout</h5>
        <TextInput
          label="Width"
          value={styles.width || ''}
          onChange={(value) => update('width', value)}
          placeholder="e.g., 100% or 500px"
        />
        <TextInput
          label="Max Width"
          value={styles.maxWidth || ''}
          onChange={(value) => update('maxWidth', value)}
          placeholder="e.g., 1200px"
        />
        <Select
          label="Text Align"
          value={styles.textAlign || 'left'}
          onChange={(value) => update('textAlign', value as ElementStyles['textAlign'])}
          options={[
            { value: 'left', label: 'Left' },
            { value: 'center', label: 'Center' },
            { value: 'right', label: 'Right' },
          ]}
        />
      </div>
    </div>
  );
};
```

---

## Canvas Features

### Builder Store with Full Features

```typescript
// src/builder/store/builderStore.ts
import { create } from 'zustand';
import { subscribeWithSelector } from 'zustand/middleware';
import { BuilderElement, ColumnChild } from '../types';

interface HistoryState {
  elements: BuilderElement[];
  selectedElement: string | null;
}

interface Clipboard {
  element: BuilderElement | null;
}

interface BuilderState {
  // Core state
  elements: BuilderElement[];
  selectedElement: string | null;
  pageId: string | null;
  isDirty: boolean;

  // History for undo/redo
  history: HistoryState[];
  historyIndex: number;

  // Clipboard
  clipboard: Clipboard;

  // Responsive preview
  previewMode: 'desktop' | 'tablet' | 'mobile';

  // Actions
  setElements: (elements: BuilderElement[]) => void;
  setSelectedElement: (id: string | null) => void;
  updateElementProps: (id: string, props: Record<string, any>) => void;
  updateElementStyles: (id: string, styles: Record<string, any>) => void;
  updateElementChildren: (id: string, children: BuilderElement[]) => void;
  updateColumnChildren: (elementId: string, columnId: string, elements: BuilderElement[]) => void;
  deleteElement: (id: string) => void;
  duplicateElement: (id: string) => void;

  // History actions
  addToHistory: () => void;
  undo: () => void;
  redo: () => void;

  // Clipboard actions
  copy: () => void;
  paste: () => void;
  cut: () => void;

  // Preview
  setPreviewMode: (mode: 'desktop' | 'tablet' | 'mobile') => void;

  // Persistence
  loadPage: (pageId: string) => Promise<void>;
  savePage: () => Promise<void>;
  setDirty: (dirty: boolean) => void;
}

export const useBuilderStore = create<BuilderState>()(
  subscribeWithSelector((set, get) => ({
    elements: [],
    selectedElement: null,
    pageId: null,
    isDirty: false,
    history: [],
    historyIndex: -1,
    clipboard: { element: null },
    previewMode: 'desktop',

    setElements: (elements) => {
      set({ elements, isDirty: true });
    },

    setSelectedElement: (id) => {
      set({ selectedElement: id });
    },

    updateElementProps: (id, newProps) => {
      const { addToHistory } = get();
      addToHistory();

      set((state) => ({
        elements: updateElementInTree(state.elements, id, (el) => ({
          ...el,
          props: { ...el.props, ...newProps },
        })),
        isDirty: true,
      }));
    },

    updateElementStyles: (id, newStyles) => {
      const { addToHistory } = get();
      addToHistory();

      set((state) => ({
        elements: updateElementInTree(state.elements, id, (el) => ({
          ...el,
          styles: { ...el.styles, ...newStyles },
        })),
        isDirty: true,
      }));
    },

    updateElementChildren: (id, children) => {
      set((state) => ({
        elements: updateElementInTree(state.elements, id, (el) => ({
          ...el,
          children,
        })),
        isDirty: true,
      }));
    },

    updateColumnChildren: (elementId, columnId, newElements) => {
      set((state) => ({
        elements: updateElementInTree(state.elements, elementId, (el) => {
          if (el.type !== 'columns') return el;
          return {
            ...el,
            children: el.children.map((col: ColumnChild) =>
              col.id === columnId ? { ...col, elements: newElements } : col
            ),
          };
        }),
        isDirty: true,
      }));
    },

    deleteElement: (id) => {
      const { addToHistory } = get();
      addToHistory();

      set((state) => ({
        elements: removeElementFromTree(state.elements, id),
        selectedElement: state.selectedElement === id ? null : state.selectedElement,
        isDirty: true,
      }));
    },

    duplicateElement: (id) => {
      const { addToHistory, elements } = get();
      const element = findElementInTree(elements, id);

      if (!element) return;

      addToHistory();
      const duplicated = deepCloneWithNewIds(element);

      set((state) => ({
        elements: insertAfterElement(state.elements, id, duplicated),
        selectedElement: duplicated.id,
        isDirty: true,
      }));
    },

    // History Management
    addToHistory: () => {
      const { elements, selectedElement, history, historyIndex } = get();
      const newHistory = history.slice(0, historyIndex + 1);
      newHistory.push({
        elements: JSON.parse(JSON.stringify(elements)),
        selectedElement,
      });

      // Limit history to 50 states
      if (newHistory.length > 50) {
        newHistory.shift();
      }

      set({
        history: newHistory,
        historyIndex: newHistory.length - 1,
      });
    },

    undo: () => {
      const { history, historyIndex } = get();
      if (historyIndex <= 0) return;

      const newIndex = historyIndex - 1;
      const prevState = history[newIndex];

      set({
        elements: JSON.parse(JSON.stringify(prevState.elements)),
        selectedElement: prevState.selectedElement,
        historyIndex: newIndex,
        isDirty: true,
      });
    },

    redo: () => {
      const { history, historyIndex } = get();
      if (historyIndex >= history.length - 1) return;

      const newIndex = historyIndex + 1;
      const nextState = history[newIndex];

      set({
        elements: JSON.parse(JSON.stringify(nextState.elements)),
        selectedElement: nextState.selectedElement,
        historyIndex: newIndex,
        isDirty: true,
      });
    },

    // Clipboard
    copy: () => {
      const { selectedElement, elements } = get();
      if (!selectedElement) return;

      const element = findElementInTree(elements, selectedElement);
      if (element) {
        set({ clipboard: { element: JSON.parse(JSON.stringify(element)) } });
      }
    },

    paste: () => {
      const { clipboard, addToHistory, selectedElement, elements } = get();
      if (!clipboard.element) return;

      addToHistory();
      const pasted = deepCloneWithNewIds(clipboard.element);

      if (selectedElement) {
        set((state) => ({
          elements: insertAfterElement(state.elements, selectedElement, pasted),
          selectedElement: pasted.id,
          isDirty: true,
        }));
      } else {
        set((state) => ({
          elements: [...state.elements, pasted],
          selectedElement: pasted.id,
          isDirty: true,
        }));
      }
    },

    cut: () => {
      const { copy, deleteElement, selectedElement } = get();
      if (!selectedElement) return;

      copy();
      deleteElement(selectedElement);
    },

    setPreviewMode: (mode) => {
      set({ previewMode: mode });
    },

    // Persistence
    loadPage: async (pageId) => {
      try {
        const response = await fetch(`/api/pages/${pageId}`);
        const data = await response.json();

        set({
          pageId,
          elements: data.elements || [],
          isDirty: false,
          history: [{
            elements: data.elements || [],
            selectedElement: null,
          }],
          historyIndex: 0,
        });
      } catch (error) {
        console.error('Failed to load page:', error);
        throw error;
      }
    },

    savePage: async () => {
      const { pageId, elements } = get();
      if (!pageId) return;

      try {
        await fetch(`/api/pages/${pageId}`, {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ elements }),
        });

        set({ isDirty: false });
      } catch (error) {
        console.error('Failed to save page:', error);
        throw error;
      }
    },

    setDirty: (dirty) => {
      set({ isDirty: dirty });
    },
  }))
);

// Helper functions
function updateElementInTree(
  elements: BuilderElement[],
  id: string,
  updater: (el: BuilderElement) => BuilderElement
): BuilderElement[] {
  return elements.map((el) => {
    if (el.id === id) {
      return updater(el);
    }
    if ('children' in el && Array.isArray(el.children)) {
      if (el.type === 'columns') {
        return {
          ...el,
          children: el.children.map((col: ColumnChild) => ({
            ...col,
            elements: updateElementInTree(col.elements, id, updater),
          })),
        };
      }
      return {
        ...el,
        children: updateElementInTree(el.children as BuilderElement[], id, updater),
      };
    }
    return el;
  });
}

function removeElementFromTree(elements: BuilderElement[], id: string): BuilderElement[] {
  return elements
    .filter((el) => el.id !== id)
    .map((el) => {
      if ('children' in el && Array.isArray(el.children)) {
        if (el.type === 'columns') {
          return {
            ...el,
            children: el.children.map((col: ColumnChild) => ({
              ...col,
              elements: removeElementFromTree(col.elements, id),
            })),
          };
        }
        return {
          ...el,
          children: removeElementFromTree(el.children as BuilderElement[], id),
        };
      }
      return el;
    });
}

function findElementInTree(elements: BuilderElement[], id: string): BuilderElement | null {
  for (const el of elements) {
    if (el.id === id) return el;
    if ('children' in el && Array.isArray(el.children)) {
      if (el.type === 'columns') {
        for (const col of el.children as ColumnChild[]) {
          const found = findElementInTree(col.elements, id);
          if (found) return found;
        }
      } else {
        const found = findElementInTree(el.children as BuilderElement[], id);
        if (found) return found;
      }
    }
  }
  return null;
}

function insertAfterElement(
  elements: BuilderElement[],
  afterId: string,
  newElement: BuilderElement
): BuilderElement[] {
  const result: BuilderElement[] = [];
  for (const el of elements) {
    result.push(el);
    if (el.id === afterId) {
      result.push(newElement);
    }
  }
  return result;
}

function deepCloneWithNewIds(element: BuilderElement): BuilderElement {
  const cloned = JSON.parse(JSON.stringify(element));

  const assignNewIds = (el: any) => {
    el.id = crypto.randomUUID();
    if (el.children) {
      if (Array.isArray(el.children)) {
        el.children.forEach((child: any) => {
          if (child.elements) {
            child.id = crypto.randomUUID();
            child.elements.forEach(assignNewIds);
          } else {
            assignNewIds(child);
          }
        });
      }
    }
  };

  assignNewIds(cloned);
  return cloned;
}
```

### Keyboard Shortcuts

```typescript
// src/builder/hooks/useKeyboardShortcuts.ts
import { useEffect } from 'react';
import { useBuilderStore } from '../store/builderStore';

export const useKeyboardShortcuts = () => {
  const {
    undo,
    redo,
    copy,
    paste,
    cut,
    deleteElement,
    selectedElement,
    savePage,
  } = useBuilderStore();

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      const isCtrlOrCmd = e.ctrlKey || e.metaKey;

      // Undo: Ctrl/Cmd + Z
      if (isCtrlOrCmd && e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        undo();
      }

      // Redo: Ctrl/Cmd + Shift + Z or Ctrl/Cmd + Y
      if ((isCtrlOrCmd && e.shiftKey && e.key === 'z') || (isCtrlOrCmd && e.key === 'y')) {
        e.preventDefault();
        redo();
      }

      // Copy: Ctrl/Cmd + C
      if (isCtrlOrCmd && e.key === 'c') {
        e.preventDefault();
        copy();
      }

      // Paste: Ctrl/Cmd + V
      if (isCtrlOrCmd && e.key === 'v') {
        e.preventDefault();
        paste();
      }

      // Cut: Ctrl/Cmd + X
      if (isCtrlOrCmd && e.key === 'x') {
        e.preventDefault();
        cut();
      }

      // Delete: Delete or Backspace
      if ((e.key === 'Delete' || e.key === 'Backspace') && selectedElement) {
        const target = e.target as HTMLElement;
        if (target.tagName !== 'INPUT' && target.tagName !== 'TEXTAREA') {
          e.preventDefault();
          deleteElement(selectedElement);
        }
      }

      // Save: Ctrl/Cmd + S
      if (isCtrlOrCmd && e.key === 's') {
        e.preventDefault();
        savePage();
      }

      // Escape: Deselect
      if (e.key === 'Escape') {
        useBuilderStore.getState().setSelectedElement(null);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [undo, redo, copy, paste, cut, deleteElement, selectedElement, savePage]);
};
```

### Responsive Preview

```typescript
// src/builder/components/PreviewToolbar.tsx
import React from 'react';
import { useBuilderStore } from '../store/builderStore';
import { Monitor, Tablet, Smartphone } from 'lucide-react';

export const PreviewToolbar: React.FC = () => {
  const { previewMode, setPreviewMode, undo, redo, savePage, isDirty } = useBuilderStore();

  const previewSizes = {
    desktop: { width: '100%', icon: Monitor },
    tablet: { width: '768px', icon: Tablet },
    mobile: { width: '375px', icon: Smartphone },
  };

  return (
    <div className="preview-toolbar">
      <div className="preview-modes">
        {Object.entries(previewSizes).map(([mode, { icon: Icon }]) => (
          <button
            key={mode}
            className={`preview-btn ${previewMode === mode ? 'active' : ''}`}
            onClick={() => setPreviewMode(mode as any)}
            title={mode.charAt(0).toUpperCase() + mode.slice(1)}
          >
            <Icon size={18} />
          </button>
        ))}
      </div>

      <div className="toolbar-actions">
        <button onClick={undo} title="Undo (Ctrl+Z)">
          <UndoIcon />
        </button>
        <button onClick={redo} title="Redo (Ctrl+Y)">
          <RedoIcon />
        </button>
        <button
          onClick={savePage}
          className={isDirty ? 'unsaved' : ''}
          title="Save (Ctrl+S)"
        >
          <SaveIcon />
          {isDirty && <span className="unsaved-indicator" />}
        </button>
      </div>
    </div>
  );
};
```

### Canvas Wrapper with Responsive Preview

```typescript
// src/builder/components/CanvasWrapper.tsx
import React from 'react';
import { useBuilderStore } from '../store/builderStore';
import { Canvas } from './Canvas';

export const CanvasWrapper: React.FC = () => {
  const { previewMode } = useBuilderStore();

  const previewWidths = {
    desktop: '100%',
    tablet: '768px',
    mobile: '375px',
  };

  return (
    <div className="canvas-wrapper">
      <div
        className="canvas-viewport"
        style={{
          width: previewWidths[previewMode],
          margin: previewMode !== 'desktop' ? '0 auto' : undefined,
          transition: 'width 0.3s ease',
        }}
      >
        <Canvas pageId={useBuilderStore.getState().pageId!} />
      </div>
    </div>
  );
};
```

---

## Data Persistence

### Auto-Save Hook

```typescript
// src/builder/hooks/useAutoSave.ts
import { useEffect, useRef } from 'react';
import { useBuilderStore } from '../store/builderStore';

interface AutoSaveOptions {
  interval?: number; // milliseconds
  enabled?: boolean;
}

export const useAutoSave = (options: AutoSaveOptions = {}) => {
  const { interval = 30000, enabled = true } = options;
  const { isDirty, savePage, elements } = useBuilderStore();
  const timeoutRef = useRef<NodeJS.Timeout>();
  const lastSavedRef = useRef<string>('');

  useEffect(() => {
    if (!enabled) return;

    const currentState = JSON.stringify(elements);

    // Only set up auto-save if there are actual changes
    if (isDirty && currentState !== lastSavedRef.current) {
      // Debounce save
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }

      timeoutRef.current = setTimeout(async () => {
        try {
          await savePage();
          lastSavedRef.current = currentState;
          console.log('Auto-saved at', new Date().toLocaleTimeString());
        } catch (error) {
          console.error('Auto-save failed:', error);
        }
      }, interval);
    }

    return () => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
    };
  }, [elements, isDirty, enabled, interval, savePage]);

  // Save on beforeunload if dirty
  useEffect(() => {
    const handleBeforeUnload = (e: BeforeUnloadEvent) => {
      if (isDirty) {
        e.preventDefault();
        e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        return e.returnValue;
      }
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, [isDirty]);
};
```

### Page Data Schema

```typescript
// src/builder/types/page.ts
import { BuilderElement } from './elements';

export interface PageData {
  id: string;
  name: string;
  slug: string;
  elements: BuilderElement[];
  settings: PageSettings;
  metadata: PageMetadata;
  createdAt: string;
  updatedAt: string;
  publishedAt?: string;
  status: 'draft' | 'published' | 'archived';
}

export interface PageSettings {
  favicon?: string;
  customCss?: string;
  customJs?: string;
  bodyClass?: string;
  fonts?: string[];
}

export interface PageMetadata {
  title: string;
  description?: string;
  keywords?: string[];
  ogImage?: string;
  ogTitle?: string;
  ogDescription?: string;
  twitterCard?: 'summary' | 'summary_large_image';
  canonicalUrl?: string;
  noIndex?: boolean;
}
```

### API Service

```typescript
// src/builder/services/pageService.ts
import { PageData, BuilderElement } from '../types';

const API_BASE = '/api';

export const pageService = {
  async getPage(pageId: string): Promise<PageData> {
    const response = await fetch(`${API_BASE}/pages/${pageId}`);
    if (!response.ok) {
      throw new Error('Failed to fetch page');
    }
    return response.json();
  },

  async savePage(pageId: string, elements: BuilderElement[]): Promise<void> {
    const response = await fetch(`${API_BASE}/pages/${pageId}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ elements }),
    });
    if (!response.ok) {
      throw new Error('Failed to save page');
    }
  },

  async createPage(data: Partial<PageData>): Promise<PageData> {
    const response = await fetch(`${API_BASE}/pages`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });
    if (!response.ok) {
      throw new Error('Failed to create page');
    }
    return response.json();
  },

  async deletePage(pageId: string): Promise<void> {
    const response = await fetch(`${API_BASE}/pages/${pageId}`, {
      method: 'DELETE',
    });
    if (!response.ok) {
      throw new Error('Failed to delete page');
    }
  },

  async publishPage(pageId: string): Promise<PageData> {
    const response = await fetch(`${API_BASE}/pages/${pageId}/publish`, {
      method: 'POST',
    });
    if (!response.ok) {
      throw new Error('Failed to publish page');
    }
    return response.json();
  },

  async duplicatePage(pageId: string): Promise<PageData> {
    const response = await fetch(`${API_BASE}/pages/${pageId}/duplicate`, {
      method: 'POST',
    });
    if (!response.ok) {
      throw new Error('Failed to duplicate page');
    }
    return response.json();
  },

  async exportPage(pageId: string): Promise<string> {
    const response = await fetch(`${API_BASE}/pages/${pageId}/export`);
    if (!response.ok) {
      throw new Error('Failed to export page');
    }
    const data = await response.json();
    return JSON.stringify(data, null, 2);
  },

  async importPage(data: string): Promise<PageData> {
    const parsed = JSON.parse(data);
    const response = await fetch(`${API_BASE}/pages/import`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(parsed),
    });
    if (!response.ok) {
      throw new Error('Failed to import page');
    }
    return response.json();
  },
};
```

### Local Storage Backup

```typescript
// src/builder/hooks/useLocalStorageBackup.ts
import { useEffect } from 'react';
import { useBuilderStore } from '../store/builderStore';

export const useLocalStorageBackup = () => {
  const { elements, pageId } = useBuilderStore();

  // Save to localStorage on changes
  useEffect(() => {
    if (!pageId || elements.length === 0) return;

    const key = `builder_backup_${pageId}`;
    const backup = {
      elements,
      timestamp: Date.now(),
    };

    localStorage.setItem(key, JSON.stringify(backup));
  }, [elements, pageId]);

  // Restore from localStorage
  const restoreBackup = (pageId: string): BuilderElement[] | null => {
    const key = `builder_backup_${pageId}`;
    const data = localStorage.getItem(key);

    if (!data) return null;

    try {
      const backup = JSON.parse(data);
      // Only restore if backup is less than 24 hours old
      if (Date.now() - backup.timestamp < 24 * 60 * 60 * 1000) {
        return backup.elements;
      }
    } catch {
      return null;
    }

    return null;
  };

  // Clear backup after successful save
  const clearBackup = (pageId: string) => {
    const key = `builder_backup_${pageId}`;
    localStorage.removeItem(key);
  };

  return { restoreBackup, clearBackup };
};
```

---

## Render Engine

### Static HTML Renderer

```typescript
// src/builder/renderer/renderToHtml.ts
import { BuilderElement, PageData, FormField } from '../types';

export const renderPageToHtml = (page: PageData): string => {
  const elementsHtml = page.elements.map(renderElement).join('\n');

  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>${escapeHtml(page.metadata.title)}</title>
  ${page.metadata.description ? `<meta name="description" content="${escapeHtml(page.metadata.description)}">` : ''}
  ${page.metadata.keywords?.length ? `<meta name="keywords" content="${escapeHtml(page.metadata.keywords.join(', '))}">` : ''}
  ${page.metadata.ogTitle ? `<meta property="og:title" content="${escapeHtml(page.metadata.ogTitle)}">` : ''}
  ${page.metadata.ogDescription ? `<meta property="og:description" content="${escapeHtml(page.metadata.ogDescription)}">` : ''}
  ${page.metadata.ogImage ? `<meta property="og:image" content="${escapeHtml(page.metadata.ogImage)}">` : ''}
  ${page.metadata.canonicalUrl ? `<link rel="canonical" href="${escapeHtml(page.metadata.canonicalUrl)}">` : ''}
  ${page.metadata.noIndex ? '<meta name="robots" content="noindex">' : ''}
  ${page.settings.favicon ? `<link rel="icon" href="${escapeHtml(page.settings.favicon)}">` : ''}
  ${page.settings.fonts?.map(font => `<link href="${font}" rel="stylesheet">`).join('\n') || ''}
  <style>
    ${getBaseStyles()}
    ${page.settings.customCss || ''}
  </style>
</head>
<body class="${page.settings.bodyClass || ''}">
  ${elementsHtml}
  ${page.settings.customJs ? `<script>${page.settings.customJs}</script>` : ''}
</body>
</html>`;
};

const renderElement = (element: BuilderElement): string => {
  const styleAttr = element.styles ? `style="${styleObjectToString(element.styles)}"` : '';

  switch (element.type) {
    case 'section':
      return renderSection(element, styleAttr);
    case 'heading':
      return renderHeading(element, styleAttr);
    case 'paragraph':
      return renderParagraph(element, styleAttr);
    case 'image':
      return renderImage(element, styleAttr);
    case 'button':
      return renderButton(element, styleAttr);
    case 'video':
      return renderVideo(element, styleAttr);
    case 'form':
      return renderForm(element, styleAttr);
    case 'columns':
      return renderColumns(element, styleAttr);
    default:
      return '';
  }
};

const renderSection = (element: any, styleAttr: string): string => {
  const sectionStyle = `
    background-color: ${element.props.backgroundColor || '#ffffff'};
    ${element.props.backgroundImage ? `background-image: url(${element.props.backgroundImage});` : ''}
    ${element.props.backgroundSize ? `background-size: ${element.props.backgroundSize};` : ''}
    min-height: ${element.props.minHeight || 'auto'};
    ${element.props.fullWidth ? 'width: 100vw; margin-left: calc(-50vw + 50%);' : ''}
  `;

  const children = (element.children || []).map(renderElement).join('\n');

  return `<section style="${sectionStyle}" ${styleAttr}>${children}</section>`;
};

const renderHeading = (element: any, styleAttr: string): string => {
  const Tag = element.props.level;
  const style = `
    color: ${element.props.color || '#000000'};
    font-size: ${element.props.fontSize || 'inherit'};
    font-weight: ${element.props.fontWeight || 'inherit'};
    ${element.props.fontFamily ? `font-family: ${element.props.fontFamily};` : ''}
  `;

  return `<${Tag} style="${style}" ${styleAttr}>${escapeHtml(element.props.text)}</${Tag}>`;
};

const renderParagraph = (element: any, styleAttr: string): string => {
  const style = `
    color: ${element.props.color || '#333333'};
    font-size: ${element.props.fontSize || '16px'};
    line-height: ${element.props.lineHeight || '1.6'};
    ${element.props.fontFamily ? `font-family: ${element.props.fontFamily};` : ''}
  `;

  return `<p style="${style}" ${styleAttr}>${escapeHtml(element.props.text)}</p>`;
};

const renderImage = (element: any, styleAttr: string): string => {
  const style = `
    width: ${element.props.width || '100%'};
    ${element.props.height ? `height: ${element.props.height};` : ''}
    object-fit: ${element.props.objectFit || 'cover'};
  `;

  const img = `<img src="${escapeHtml(element.props.src)}" alt="${escapeHtml(element.props.alt)}" style="${style}" ${styleAttr} loading="lazy">`;

  if (element.props.link) {
    const rel = element.props.linkTarget === '_blank' ? 'rel="noopener noreferrer"' : '';
    return `<a href="${escapeHtml(element.props.link)}" target="${element.props.linkTarget || '_self'}" ${rel}>${img}</a>`;
  }

  return img;
};

const renderButton = (element: any, styleAttr: string): string => {
  const sizeStyles: Record<string, string> = {
    sm: 'padding: 8px 16px; font-size: 14px;',
    md: 'padding: 12px 24px; font-size: 16px;',
    lg: 'padding: 16px 32px; font-size: 18px;',
  };

  let style = `
    background-color: ${element.props.backgroundColor || '#3b82f6'};
    color: ${element.props.textColor || '#ffffff'};
    border-radius: ${element.props.borderRadius || '6px'};
    ${element.props.fullWidth ? 'width: 100%;' : ''}
    ${sizeStyles[element.props.size || 'md']}
    border: none;
    cursor: pointer;
    display: inline-block;
    text-decoration: none;
    text-align: center;
    font-weight: 600;
  `;

  if (element.props.variant === 'outline') {
    style = `
      ${style}
      background-color: transparent;
      color: ${element.props.backgroundColor || '#3b82f6'};
      border: 2px solid ${element.props.backgroundColor || '#3b82f6'};
    `;
  }

  if (element.props.variant === 'ghost') {
    style = `
      ${style}
      background-color: transparent;
      color: ${element.props.backgroundColor || '#3b82f6'};
    `;
  }

  if (element.props.link) {
    const rel = element.props.linkTarget === '_blank' ? 'rel="noopener noreferrer"' : '';
    return `<a href="${escapeHtml(element.props.link)}" target="${element.props.linkTarget || '_self'}" style="${style}" ${styleAttr} ${rel}>${escapeHtml(element.props.text)}</a>`;
  }

  return `<button style="${style}" ${styleAttr}>${escapeHtml(element.props.text)}</button>`;
};

const renderVideo = (element: any, styleAttr: string): string => {
  const aspectRatios: Record<string, string> = {
    '16:9': '56.25%',
    '4:3': '75%',
    '1:1': '100%',
  };

  const containerStyle = `
    position: relative;
    padding-bottom: ${aspectRatios[element.props.aspectRatio || '16:9']};
    height: 0;
    overflow: hidden;
  `;

  const mediaStyle = `
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  `;

  if (element.props.provider === 'youtube') {
    const videoId = element.props.src.match(/(?:youtu\.be\/|youtube\.com(?:\/embed\/|\/v\/|\/watch\?v=|\/watch\?.+&v=))([^&?]+)/)?.[1];
    if (videoId) {
      const params = new URLSearchParams();
      if (element.props.autoplay) params.set('autoplay', '1');
      if (element.props.muted) params.set('mute', '1');
      if (element.props.loop) params.set('loop', '1');
      if (!element.props.controls) params.set('controls', '0');

      return `<div style="${containerStyle}" ${styleAttr}>
        <iframe src="https://www.youtube.com/embed/${videoId}?${params.toString()}" style="${mediaStyle}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      </div>`;
    }
  }

  if (element.props.provider === 'vimeo') {
    const videoId = element.props.src.match(/vimeo\.com\/(\d+)/)?.[1];
    if (videoId) {
      const params = new URLSearchParams();
      if (element.props.autoplay) params.set('autoplay', '1');
      if (element.props.muted) params.set('muted', '1');
      if (element.props.loop) params.set('loop', '1');

      return `<div style="${containerStyle}" ${styleAttr}>
        <iframe src="https://player.vimeo.com/video/${videoId}?${params.toString()}" style="${mediaStyle}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>
      </div>`;
    }
  }

  return `<div style="${containerStyle}" ${styleAttr}>
    <video src="${escapeHtml(element.props.src)}" style="${mediaStyle}" ${element.props.autoplay ? 'autoplay' : ''} ${element.props.muted ? 'muted' : ''} ${element.props.loop ? 'loop' : ''} ${element.props.controls ? 'controls' : ''}></video>
  </div>`;
};

const renderForm = (element: any, styleAttr: string): string => {
  const fields = element.props.fields.map((field: FormField) => {
    const required = field.required ? 'required' : '';
    const fieldHtml = (() => {
      switch (field.type) {
        case 'textarea':
          return `<textarea name="${field.id}" placeholder="${escapeHtml(field.placeholder || '')}" ${required} rows="4"></textarea>`;
        case 'select':
          return `<select name="${field.id}" ${required}>
            <option value="">${escapeHtml(field.placeholder || 'Select...')}</option>
            ${field.options?.map(opt => `<option value="${escapeHtml(opt)}">${escapeHtml(opt)}</option>`).join('')}
          </select>`;
        case 'checkbox':
          return `<label><input type="checkbox" name="${field.id}" ${required}> ${escapeHtml(field.label)}</label>`;
        default:
          return `<input type="${field.type}" name="${field.id}" placeholder="${escapeHtml(field.placeholder || '')}" ${required}>`;
      }
    })();

    if (field.type === 'checkbox') {
      return `<div class="form-field">${fieldHtml}</div>`;
    }

    return `<div class="form-field">
      <label for="${field.id}">${escapeHtml(field.label)}${field.required ? '<span class="required">*</span>' : ''}</label>
      ${fieldHtml}
    </div>`;
  }).join('\n');

  return `<form action="${escapeHtml(element.props.submitAction)}" method="POST" ${styleAttr}>
    ${fields}
    <button type="submit" style="background-color: ${element.props.buttonColor || '#3b82f6'}; color: ${element.props.buttonTextColor || '#ffffff'};">
      ${escapeHtml(element.props.submitText)}
    </button>
  </form>`;
};

const renderColumns = (element: any, styleAttr: string): string => {
  const layout = element.props.layout || '1:1';
  const ratios = layout.split(':').map(Number);
  const total = ratios.reduce((a: number, b: number) => a + b, 0);
  const gridTemplate = ratios.map((r: number) => `${(r / total) * 100}%`).join(' ');

  const style = `
    display: grid;
    grid-template-columns: ${gridTemplate};
    gap: ${element.props.gap || '24px'};
  `;

  const columns = element.children.map((col: any) => {
    const colContent = col.elements.map(renderElement).join('\n');
    return `<div class="column">${colContent}</div>`;
  }).join('\n');

  return `<div style="${style}" ${styleAttr}>${columns}</div>`;
};

// Utility functions
const escapeHtml = (str: string): string => {
  const htmlEntities: Record<string, string> = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  };
  return str.replace(/[&<>"']/g, (char) => htmlEntities[char]);
};

const styleObjectToString = (styles: Record<string, any>): string => {
  return Object.entries(styles)
    .filter(([_, value]) => value !== undefined && value !== '')
    .map(([key, value]) => {
      const cssKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
      return `${cssKey}: ${value}`;
    })
    .join('; ');
};

const getBaseStyles = (): string => `
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
  }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    line-height: 1.5;
    color: #333;
  }

  img {
    max-width: 100%;
    height: auto;
  }

  .form-field {
    margin-bottom: 16px;
  }

  .form-field label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
  }

  .form-field input,
  .form-field textarea,
  .form-field select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
  }

  .form-field .required {
    color: #e53e3e;
    margin-left: 4px;
  }

  form button[type="submit"] {
    padding: 12px 24px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
  }
`;
```

### React Renderer for Preview

```typescript
// src/builder/renderer/ReactRenderer.tsx
import React from 'react';
import { BuilderElement } from '../types';
import { ElementRenderer } from '../components/elements';

interface ReactRendererProps {
  elements: BuilderElement[];
}

export const ReactRenderer: React.FC<ReactRendererProps> = ({ elements }) => {
  return (
    <div className="rendered-page">
      {elements.map((element) => (
        <ElementRenderer
          key={element.id}
          element={element}
          isEditing={false}
        />
      ))}
    </div>
  );
};
```

---

## Main Builder Component

```typescript
// src/builder/Builder.tsx
import React, { useEffect } from 'react';
import { useBuilderStore } from './store/builderStore';
import { ElementPalette } from './components/ElementPalette';
import { CanvasWrapper } from './components/CanvasWrapper';
import { PropertiesPanel } from './components/PropertiesPanel';
import { PreviewToolbar } from './components/PreviewToolbar';
import { useKeyboardShortcuts } from './hooks/useKeyboardShortcuts';
import { useAutoSave } from './hooks/useAutoSave';
import './styles/builder.css';

interface BuilderProps {
  pageId: string;
}

export const Builder: React.FC<BuilderProps> = ({ pageId }) => {
  const { loadPage, setSelectedElement } = useBuilderStore();

  useKeyboardShortcuts();
  useAutoSave({ interval: 30000 });

  useEffect(() => {
    loadPage(pageId);
  }, [pageId, loadPage]);

  const handleCanvasClick = () => {
    setSelectedElement(null);
  };

  return (
    <div className="builder-container">
      <PreviewToolbar />
      <div className="builder-main">
        <ElementPalette />
        <div className="builder-canvas" onClick={handleCanvasClick}>
          <CanvasWrapper />
        </div>
        <PropertiesPanel />
      </div>
    </div>
  );
};

export default Builder;
```

---

## CSS Styles

```css
/* src/builder/styles/builder.css */
.builder-container {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #f5f5f5;
}

.preview-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 24px;
  background: #fff;
  border-bottom: 1px solid #e0e0e0;
}

.preview-modes {
  display: flex;
  gap: 4px;
}

.preview-btn {
  padding: 8px 12px;
  border: 1px solid #e0e0e0;
  background: #fff;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.preview-btn.active {
  background: #3b82f6;
  color: #fff;
  border-color: #3b82f6;
}

.toolbar-actions {
  display: flex;
  gap: 8px;
}

.builder-main {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.element-palette {
  width: 280px;
  background: #fff;
  border-right: 1px solid #e0e0e0;
  padding: 16px;
  overflow-y: auto;
}

.palette-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.palette-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 12px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  cursor: grab;
  transition: all 0.2s;
}

.palette-item:hover {
  border-color: #3b82f6;
  background: #f0f7ff;
}

.builder-canvas {
  flex: 1;
  overflow: auto;
  padding: 24px;
}

.canvas-wrapper {
  min-height: 100%;
}

.canvas-viewport {
  background: #fff;
  min-height: 600px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
}

.canvas-container {
  min-height: 400px;
  padding: 20px;
}

.properties-panel {
  width: 320px;
  background: #fff;
  border-left: 1px solid #e0e0e0;
  overflow-y: auto;
}

.properties-panel.empty {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #666;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
}

.panel-content {
  padding: 16px;
}

.element-wrapper {
  position: relative;
  margin: 4px 0;
}

.element-wrapper.selected {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.element-toolbar {
  position: absolute;
  top: -32px;
  left: 0;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 4px 8px;
  background: #3b82f6;
  color: #fff;
  border-radius: 4px 4px 0 0;
  font-size: 12px;
}

.drag-handle {
  cursor: grab;
  padding: 2px;
}

.empty-section-placeholder,
.empty-column-placeholder {
  padding: 40px;
  border: 2px dashed #e0e0e0;
  border-radius: 8px;
  text-align: center;
  color: #999;
}

.element-ghost {
  opacity: 0.4;
}

.element-chosen {
  opacity: 0.8;
}

.element-drag {
  opacity: 1;
}

.editor-section {
  margin-bottom: 24px;
}

.editor-section h4,
.editor-section h5 {
  margin-bottom: 12px;
  font-size: 14px;
  font-weight: 600;
}

.unsaved-indicator {
  width: 8px;
  height: 8px;
  background: #ef4444;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  right: 2px;
}

/* Responsive */
@media (max-width: 1024px) {
  .element-palette {
    width: 200px;
  }

  .properties-panel {
    width: 280px;
  }
}
```

---

## Summary

This drag-and-drop builder implementation provides:

1. **SortableJS Integration**: Full drag-and-drop support with cloning from palette, nested sorting in sections and columns
2. **8 Builder Elements**: Section, Heading, Paragraph, Image, Button, Video, Form, Columns - all with customizable properties
3. **Element Editing**: Complete properties panel with dedicated editors for each element type and global styles editor
4. **Canvas Features**: Selection, copy/paste, undo/redo (50 states), responsive preview (desktop/tablet/mobile)
5. **Data Persistence**: JSON save/load, auto-save with debouncing, localStorage backup, API service layer
6. **Render Engine**: Full HTML renderer for publishing, React renderer for preview

The implementation uses TypeScript throughout, Zustand for state management, and follows React best practices for a production-ready drag-and-drop page builder.
