# Implementation Flow - 14 Day Plan

## Day 1: Project Setup & Scaffolding

### Goals
- Initialize project structure with Next.js, NestJS, and database
- Set up development environment and tooling

### Tasks
1. Initialize Next.js frontend with TypeScript and Tailwind
2. Initialize NestJS backend with TypeScript
3. Set up PostgreSQL with Prisma ORM
4. Configure ESLint, Prettier, Husky
5. Set up Docker for local development

### Files to Create
```
frontend/
├── src/
│   ├── app/layout.tsx
│   ├── app/page.tsx
│   ├── lib/api.ts
│   └── components/ui/
backend/
├── src/
│   ├── main.ts
│   ├── app.module.ts
│   └── prisma/schema.prisma
docker-compose.yml
```

### Key Code Snippets

**Prisma Schema Base**
```prisma
// backend/prisma/schema.prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "postgresql"
  url      = env("DATABASE_URL")
}

model User {
  id        String   @id @default(cuid())
  email     String   @unique
  password  String
  name      String?
  createdAt DateTime @default(now())
  updatedAt DateTime @updatedAt
  pages     Page[]
  subscription Subscription?
}

model Page {
  id          String   @id @default(cuid())
  title       String
  slug        String
  content     Json
  published   Boolean  @default(false)
  userId      String
  user        User     @relation(fields: [userId], references: [id])
  domain      Domain?
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
}
```

**Docker Compose**
```yaml
# docker-compose.yml
version: '3.8'
services:
  postgres:
    image: postgres:15
    environment:
      POSTGRES_USER: dev
      POSTGRES_PASSWORD: dev
      POSTGRES_DB: landing_builder
    ports:
      - "5432:5432"
    volumes:
      - postgres_data:/var/lib/postgresql/data

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

volumes:
  postgres_data:
```

### Deliverables
- [ ] Running frontend on localhost:3000
- [ ] Running backend on localhost:3001
- [ ] Database connected and migrations working

---

## Day 2: Authentication System

### Goals
- Implement complete JWT-based authentication
- Create login/register flows with protected routes

### Tasks
1. Create auth module in NestJS with JWT strategy
2. Implement register, login, logout endpoints
3. Build frontend auth pages and context
4. Set up HTTP-only cookie handling
5. Implement password hashing with bcrypt

### Files to Create
```
backend/src/auth/
├── auth.module.ts
├── auth.service.ts
├── auth.controller.ts
├── jwt.strategy.ts
├── guards/jwt-auth.guard.ts
└── dto/
frontend/src/
├── app/(auth)/login/page.tsx
├── app/(auth)/register/page.tsx
├── context/auth-context.tsx
└── lib/auth.ts
```

### Key Code Snippets

**JWT Strategy**
```typescript
// backend/src/auth/jwt.strategy.ts
import { Injectable } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { ExtractJwt, Strategy } from 'passport-jwt';
import { Request } from 'express';

@Injectable()
export class JwtStrategy extends PassportStrategy(Strategy) {
  constructor() {
    super({
      jwtFromRequest: ExtractJwt.fromExtractors([
        (req: Request) => req?.cookies?.access_token,
      ]),
      secretOrKey: process.env.JWT_SECRET,
    });
  }

  async validate(payload: { sub: string; email: string }) {
    return { userId: payload.sub, email: payload.email };
  }
}
```

**Auth Service**
```typescript
// backend/src/auth/auth.service.ts
import { Injectable, UnauthorizedException } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { PrismaService } from '../prisma/prisma.service';
import * as bcrypt from 'bcrypt';

@Injectable()
export class AuthService {
  constructor(
    private prisma: PrismaService,
    private jwtService: JwtService,
  ) {}

  async register(email: string, password: string, name?: string) {
    const hashedPassword = await bcrypt.hash(password, 10);
    const user = await this.prisma.user.create({
      data: { email, password: hashedPassword, name },
    });
    return this.generateTokens(user.id, user.email);
  }

  async login(email: string, password: string) {
    const user = await this.prisma.user.findUnique({ where: { email } });
    if (!user || !(await bcrypt.compare(password, user.password))) {
      throw new UnauthorizedException('Invalid credentials');
    }
    return this.generateTokens(user.id, user.email);
  }

  private generateTokens(userId: string, email: string) {
    const payload = { sub: userId, email };
    return {
      access_token: this.jwtService.sign(payload, { expiresIn: '15m' }),
      refresh_token: this.jwtService.sign(payload, { expiresIn: '7d' }),
    };
  }
}
```

**Auth Context (Frontend)**
```typescript
// frontend/src/context/auth-context.tsx
'use client';
import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { api } from '@/lib/api';

interface User {
  id: string;
  email: string;
  name?: string;
}

interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  isLoading: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    api.get('/auth/me').then(setUser).catch(() => null).finally(() => setIsLoading(false));
  }, []);

  const login = async (email: string, password: string) => {
    const { user } = await api.post('/auth/login', { email, password });
    setUser(user);
  };

  const logout = async () => {
    await api.post('/auth/logout');
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, login, logout, isLoading }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used within AuthProvider');
  return context;
};
```

### Deliverables
- [ ] User registration with email/password
- [ ] User login with JWT tokens
- [ ] Protected route middleware
- [ ] Auth state persisted across refreshes

---

## Day 3: Dashboard & Page Management

### Goals
- Build user dashboard with page CRUD operations
- Implement page listing, creation, and deletion

### Tasks
1. Create pages module in backend
2. Build dashboard layout with sidebar navigation
3. Implement page list with search/filter
4. Add create page modal
5. Implement delete with confirmation

### Files to Create
```
backend/src/pages/
├── pages.module.ts
├── pages.service.ts
├── pages.controller.ts
└── dto/
frontend/src/app/dashboard/
├── layout.tsx
├── page.tsx
├── pages/page.tsx
└── settings/page.tsx
frontend/src/components/dashboard/
├── sidebar.tsx
├── page-card.tsx
└── create-page-modal.tsx
```

### Key Code Snippets

**Pages Controller**
```typescript
// backend/src/pages/pages.controller.ts
import { Controller, Get, Post, Put, Delete, Body, Param, UseGuards, Req } from '@nestjs/common';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { PagesService } from './pages.service';
import { CreatePageDto, UpdatePageDto } from './dto';

@Controller('pages')
@UseGuards(JwtAuthGuard)
export class PagesController {
  constructor(private pagesService: PagesService) {}

  @Get()
  findAll(@Req() req) {
    return this.pagesService.findAllByUser(req.user.userId);
  }

  @Post()
  create(@Req() req, @Body() dto: CreatePageDto) {
    return this.pagesService.create(req.user.userId, dto);
  }

  @Get(':id')
  findOne(@Req() req, @Param('id') id: string) {
    return this.pagesService.findOne(id, req.user.userId);
  }

  @Put(':id')
  update(@Req() req, @Param('id') id: string, @Body() dto: UpdatePageDto) {
    return this.pagesService.update(id, req.user.userId, dto);
  }

  @Delete(':id')
  remove(@Req() req, @Param('id') id: string) {
    return this.pagesService.remove(id, req.user.userId);
  }
}
```

**Dashboard Layout**
```typescript
// frontend/src/app/dashboard/layout.tsx
import { Sidebar } from '@/components/dashboard/sidebar';
import { redirect } from 'next/navigation';
import { getServerSession } from '@/lib/auth';

export default async function DashboardLayout({ children }: { children: React.ReactNode }) {
  const session = await getServerSession();
  if (!session) redirect('/login');

  return (
    <div className="flex h-screen bg-gray-100">
      <Sidebar />
      <main className="flex-1 overflow-auto p-6">{children}</main>
    </div>
  );
}
```

**Page Card Component**
```typescript
// frontend/src/components/dashboard/page-card.tsx
'use client';
import { Page } from '@/types';
import { MoreVertical, Edit, Trash, Eye } from 'lucide-react';
import Link from 'next/link';

interface PageCardProps {
  page: Page;
  onDelete: (id: string) => void;
}

export function PageCard({ page, onDelete }: PageCardProps) {
  return (
    <div className="bg-white rounded-lg shadow p-4 hover:shadow-md transition">
      <div className="flex justify-between items-start">
        <div>
          <h3 className="font-semibold text-lg">{page.title}</h3>
          <p className="text-sm text-gray-500">/{page.slug}</p>
        </div>
        <div className="flex gap-2">
          <Link href={`/builder/${page.id}`} className="p-2 hover:bg-gray-100 rounded">
            <Edit size={16} />
          </Link>
          <button onClick={() => onDelete(page.id)} className="p-2 hover:bg-red-100 rounded text-red-600">
            <Trash size={16} />
          </button>
        </div>
      </div>
      <div className="mt-4 flex items-center gap-2">
        <span className={`px-2 py-1 text-xs rounded ${page.published ? 'bg-green-100 text-green-800' : 'bg-gray-100'}`}>
          {page.published ? 'Published' : 'Draft'}
        </span>
        <span className="text-xs text-gray-400">
          Updated {new Date(page.updatedAt).toLocaleDateString()}
        </span>
      </div>
    </div>
  );
}
```

### Deliverables
- [ ] Dashboard with navigation sidebar
- [ ] Page listing with grid/list view
- [ ] Create new page functionality
- [ ] Delete page with confirmation
- [ ] Page status indicators

---

## Day 4-5: Builder Foundation & Drag-Drop System

### Goals
- Implement core builder interface with canvas
- Build drag-and-drop system with element positioning

### Tasks
1. Set up builder layout with sidebar, canvas, properties panel
2. Implement drag-and-drop with @dnd-kit
3. Create element tree state management with Zustand
4. Build canvas rendering with element positioning
5. Implement selection and multi-select
6. Add undo/redo functionality

### Files to Create
```
frontend/src/app/builder/[id]/
├── page.tsx
└── layout.tsx
frontend/src/components/builder/
├── builder-canvas.tsx
├── element-sidebar.tsx
├── properties-panel.tsx
├── canvas-element.tsx
└── toolbar.tsx
frontend/src/stores/
├── builder-store.ts
└── history-store.ts
frontend/src/types/
└── builder.ts
```

### Key Code Snippets

**Builder Store (Zustand)**
```typescript
// frontend/src/stores/builder-store.ts
import { create } from 'zustand';
import { nanoid } from 'nanoid';

export interface Element {
  id: string;
  type: string;
  props: Record<string, any>;
  children: Element[];
  parentId: string | null;
}

interface BuilderState {
  elements: Element[];
  selectedIds: string[];
  hoveredId: string | null;

  // Actions
  addElement: (type: string, parentId?: string) => void;
  updateElement: (id: string, props: Partial<Element['props']>) => void;
  deleteElement: (id: string) => void;
  moveElement: (id: string, targetId: string, position: 'before' | 'after' | 'inside') => void;
  selectElement: (id: string, multi?: boolean) => void;
  clearSelection: () => void;
}

export const useBuilderStore = create<BuilderState>((set, get) => ({
  elements: [],
  selectedIds: [],
  hoveredId: null,

  addElement: (type, parentId = null) => {
    const newElement: Element = {
      id: nanoid(),
      type,
      props: getDefaultProps(type),
      children: [],
      parentId,
    };

    set((state) => {
      if (parentId) {
        return {
          elements: updateElementInTree(state.elements, parentId, (el) => ({
            ...el,
            children: [...el.children, newElement],
          })),
        };
      }
      return { elements: [...state.elements, newElement] };
    });
  },

  updateElement: (id, props) => {
    set((state) => ({
      elements: updateElementInTree(state.elements, id, (el) => ({
        ...el,
        props: { ...el.props, ...props },
      })),
    }));
  },

  deleteElement: (id) => {
    set((state) => ({
      elements: removeElementFromTree(state.elements, id),
      selectedIds: state.selectedIds.filter((sid) => sid !== id),
    }));
  },

  moveElement: (id, targetId, position) => {
    set((state) => {
      const element = findElementInTree(state.elements, id);
      if (!element) return state;

      let newElements = removeElementFromTree(state.elements, id);
      newElements = insertElementInTree(newElements, element, targetId, position);

      return { elements: newElements };
    });
  },

  selectElement: (id, multi = false) => {
    set((state) => ({
      selectedIds: multi
        ? state.selectedIds.includes(id)
          ? state.selectedIds.filter((sid) => sid !== id)
          : [...state.selectedIds, id]
        : [id],
    }));
  },

  clearSelection: () => set({ selectedIds: [] }),
}));

// Helper functions
function getDefaultProps(type: string): Record<string, any> {
  const defaults: Record<string, any> = {
    text: { content: 'New text', fontSize: 16, color: '#000000' },
    heading: { content: 'Heading', level: 'h2', fontSize: 32 },
    button: { text: 'Click me', variant: 'primary', size: 'md' },
    image: { src: '', alt: '', width: '100%' },
    container: { padding: 16, background: 'transparent', direction: 'column' },
  };
  return defaults[type] || {};
}
```

**Builder Canvas**
```typescript
// frontend/src/components/builder/builder-canvas.tsx
'use client';
import { useBuilderStore } from '@/stores/builder-store';
import { DndContext, DragEndEvent, DragOverlay, useSensor, useSensors, PointerSensor } from '@dnd-kit/core';
import { CanvasElement } from './canvas-element';
import { useState } from 'react';

export function BuilderCanvas() {
  const { elements, addElement, moveElement, clearSelection } = useBuilderStore();
  const [activeId, setActiveId] = useState<string | null>(null);

  const sensors = useSensors(
    useSensor(PointerSensor, { activationConstraint: { distance: 8 } })
  );

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    setActiveId(null);

    if (!over) return;

    // New element from sidebar
    if (active.data.current?.isNew) {
      addElement(active.data.current.type, over.id as string);
      return;
    }

    // Move existing element
    if (active.id !== over.id) {
      moveElement(
        active.id as string,
        over.id as string,
        over.data.current?.position || 'inside'
      );
    }
  };

  return (
    <DndContext
      sensors={sensors}
      onDragStart={(e) => setActiveId(e.active.id as string)}
      onDragEnd={handleDragEnd}
    >
      <div
        className="flex-1 bg-gray-50 p-8 overflow-auto"
        onClick={(e) => {
          if (e.target === e.currentTarget) clearSelection();
        }}
      >
        <div className="max-w-4xl mx-auto bg-white min-h-[600px] shadow-lg rounded-lg p-4">
          {elements.length === 0 ? (
            <div className="h-full flex items-center justify-center text-gray-400">
              Drag elements here to start building
            </div>
          ) : (
            elements.map((element) => (
              <CanvasElement key={element.id} element={element} />
            ))
          )}
        </div>
      </div>

      <DragOverlay>
        {activeId && <div className="bg-blue-100 p-2 rounded opacity-80">Moving...</div>}
      </DragOverlay>
    </DndContext>
  );
}
```

**Canvas Element Renderer**
```typescript
// frontend/src/components/builder/canvas-element.tsx
'use client';
import { Element, useBuilderStore } from '@/stores/builder-store';
import { useDraggable, useDroppable } from '@dnd-kit/core';
import { cn } from '@/lib/utils';

interface CanvasElementProps {
  element: Element;
}

export function CanvasElement({ element }: CanvasElementProps) {
  const { selectedIds, selectElement, hoveredId } = useBuilderStore();
  const isSelected = selectedIds.includes(element.id);

  const { attributes, listeners, setNodeRef: setDragRef, isDragging } = useDraggable({
    id: element.id,
  });

  const { setNodeRef: setDropRef, isOver } = useDroppable({
    id: element.id,
    data: { accepts: element.type === 'container' },
  });

  const renderElement = () => {
    switch (element.type) {
      case 'text':
        return <p style={{ fontSize: element.props.fontSize, color: element.props.color }}>{element.props.content}</p>;
      case 'heading':
        const Tag = element.props.level as keyof JSX.IntrinsicElements;
        return <Tag style={{ fontSize: element.props.fontSize }}>{element.props.content}</Tag>;
      case 'button':
        return (
          <button className={cn('px-4 py-2 rounded', element.props.variant === 'primary' ? 'bg-blue-600 text-white' : 'border')}>
            {element.props.text}
          </button>
        );
      case 'image':
        return element.props.src ? (
          <img src={element.props.src} alt={element.props.alt} style={{ width: element.props.width }} />
        ) : (
          <div className="bg-gray-200 h-32 flex items-center justify-center">Image placeholder</div>
        );
      case 'container':
        return (
          <div style={{ padding: element.props.padding, background: element.props.background, display: 'flex', flexDirection: element.props.direction }}>
            {element.children.map((child) => (
              <CanvasElement key={child.id} element={child} />
            ))}
          </div>
        );
      default:
        return <div>Unknown element</div>;
    }
  };

  return (
    <div
      ref={(node) => { setDragRef(node); setDropRef(node); }}
      {...attributes}
      {...listeners}
      onClick={(e) => { e.stopPropagation(); selectElement(element.id, e.shiftKey); }}
      className={cn(
        'relative cursor-move',
        isSelected && 'ring-2 ring-blue-500',
        isOver && 'ring-2 ring-green-500',
        isDragging && 'opacity-50'
      )}
    >
      {renderElement()}
    </div>
  );
}
```

**History Store (Undo/Redo)**
```typescript
// frontend/src/stores/history-store.ts
import { create } from 'zustand';
import { Element } from './builder-store';

interface HistoryState {
  past: Element[][];
  future: Element[][];

  pushState: (elements: Element[]) => void;
  undo: () => Element[] | null;
  redo: () => Element[] | null;
  canUndo: () => boolean;
  canRedo: () => boolean;
}

export const useHistoryStore = create<HistoryState>((set, get) => ({
  past: [],
  future: [],

  pushState: (elements) => {
    set((state) => ({
      past: [...state.past.slice(-49), elements],
      future: [],
    }));
  },

  undo: () => {
    const { past } = get();
    if (past.length === 0) return null;

    const previous = past[past.length - 1];
    set((state) => ({
      past: state.past.slice(0, -1),
      future: [previous, ...state.future],
    }));
    return past[past.length - 2] || [];
  },

  redo: () => {
    const { future } = get();
    if (future.length === 0) return null;

    const next = future[0];
    set((state) => ({
      past: [...state.past, next],
      future: state.future.slice(1),
    }));
    return next;
  },

  canUndo: () => get().past.length > 0,
  canRedo: () => get().future.length > 0,
}));
```

### Deliverables
- [ ] Builder interface with three-panel layout
- [ ] Drag elements from sidebar to canvas
- [ ] Drag to reorder elements
- [ ] Element selection with visual feedback
- [ ] Undo/redo functionality
- [ ] Auto-save to backend

---

## Day 6: Builder Elements Library

### Goals
- Create comprehensive set of builder elements
- Implement properties panel for each element type

### Tasks
1. Create all element types (text, heading, image, button, form, video, etc.)
2. Build properties panel with type-specific controls
3. Implement style controls (spacing, colors, typography)
4. Add responsive preview modes

### Files to Create
```
frontend/src/components/builder/elements/
├── text-element.tsx
├── heading-element.tsx
├── image-element.tsx
├── button-element.tsx
├── form-element.tsx
├── video-element.tsx
├── divider-element.tsx
└── spacer-element.tsx
frontend/src/components/builder/properties/
├── text-properties.tsx
├── style-properties.tsx
├── spacing-properties.tsx
└── color-picker.tsx
```

### Key Code Snippets

**Properties Panel**
```typescript
// frontend/src/components/builder/properties-panel.tsx
'use client';
import { useBuilderStore } from '@/stores/builder-store';
import { TextProperties } from './properties/text-properties';
import { StyleProperties } from './properties/style-properties';
import { SpacingProperties } from './properties/spacing-properties';

export function PropertiesPanel() {
  const { elements, selectedIds, updateElement } = useBuilderStore();

  if (selectedIds.length === 0) {
    return (
      <div className="w-72 bg-white border-l p-4">
        <p className="text-gray-500 text-sm">Select an element to edit properties</p>
      </div>
    );
  }

  const selectedElement = findElementById(elements, selectedIds[0]);
  if (!selectedElement) return null;

  const handleUpdate = (props: Record<string, any>) => {
    updateElement(selectedElement.id, props);
  };

  return (
    <div className="w-72 bg-white border-l overflow-y-auto">
      <div className="p-4 border-b">
        <h3 className="font-semibold capitalize">{selectedElement.type}</h3>
      </div>

      <div className="p-4 space-y-4">
        {/* Type-specific properties */}
        {selectedElement.type === 'text' && (
          <TextProperties props={selectedElement.props} onChange={handleUpdate} />
        )}

        {/* Common style properties */}
        <StyleProperties props={selectedElement.props} onChange={handleUpdate} />
        <SpacingProperties props={selectedElement.props} onChange={handleUpdate} />
      </div>
    </div>
  );
}
```

**Text Properties**
```typescript
// frontend/src/components/builder/properties/text-properties.tsx
interface TextPropertiesProps {
  props: Record<string, any>;
  onChange: (props: Record<string, any>) => void;
}

export function TextProperties({ props, onChange }: TextPropertiesProps) {
  return (
    <div className="space-y-3">
      <div>
        <label className="block text-sm font-medium mb-1">Content</label>
        <textarea
          value={props.content || ''}
          onChange={(e) => onChange({ content: e.target.value })}
          className="w-full border rounded p-2 text-sm"
          rows={3}
        />
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">Font Size</label>
        <input
          type="number"
          value={props.fontSize || 16}
          onChange={(e) => onChange({ fontSize: Number(e.target.value) })}
          className="w-full border rounded p-2 text-sm"
        />
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">Color</label>
        <input
          type="color"
          value={props.color || '#000000'}
          onChange={(e) => onChange({ color: e.target.value })}
          className="w-full h-10 border rounded"
        />
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">Font Weight</label>
        <select
          value={props.fontWeight || 'normal'}
          onChange={(e) => onChange({ fontWeight: e.target.value })}
          className="w-full border rounded p-2 text-sm"
        >
          <option value="normal">Normal</option>
          <option value="medium">Medium</option>
          <option value="semibold">Semibold</option>
          <option value="bold">Bold</option>
        </select>
      </div>
    </div>
  );
}
```

**Element Sidebar**
```typescript
// frontend/src/components/builder/element-sidebar.tsx
'use client';
import { useDraggable } from '@dnd-kit/core';
import { Type, Heading, Image, Square, Video, FormInput, Minus, ArrowUpDown } from 'lucide-react';

const elements = [
  { type: 'text', label: 'Text', icon: Type },
  { type: 'heading', label: 'Heading', icon: Heading },
  { type: 'image', label: 'Image', icon: Image },
  { type: 'button', label: 'Button', icon: Square },
  { type: 'container', label: 'Container', icon: Square },
  { type: 'video', label: 'Video', icon: Video },
  { type: 'form', label: 'Form', icon: FormInput },
  { type: 'divider', label: 'Divider', icon: Minus },
  { type: 'spacer', label: 'Spacer', icon: ArrowUpDown },
];

export function ElementSidebar() {
  return (
    <div className="w-64 bg-white border-r overflow-y-auto">
      <div className="p-4 border-b">
        <h3 className="font-semibold">Elements</h3>
      </div>
      <div className="p-4 grid grid-cols-2 gap-2">
        {elements.map((el) => (
          <DraggableElement key={el.type} {...el} />
        ))}
      </div>
    </div>
  );
}

function DraggableElement({ type, label, icon: Icon }: { type: string; label: string; icon: any }) {
  const { attributes, listeners, setNodeRef, isDragging } = useDraggable({
    id: `new-${type}`,
    data: { isNew: true, type },
  });

  return (
    <div
      ref={setNodeRef}
      {...attributes}
      {...listeners}
      className={`p-3 border rounded-lg cursor-grab flex flex-col items-center gap-1 hover:bg-gray-50 ${isDragging ? 'opacity-50' : ''}`}
    >
      <Icon size={20} />
      <span className="text-xs">{label}</span>
    </div>
  );
}
```

### Deliverables
- [ ] All core element types implemented
- [ ] Properties panel with type-specific controls
- [ ] Color picker and style controls
- [ ] Responsive preview (desktop/tablet/mobile)

---

## Day 7: Templates System

### Goals
- Implement template library and management
- Enable starting from templates and saving as template

### Tasks
1. Create template schema and backend module
2. Build template gallery with categories
3. Implement "Use Template" functionality
4. Add "Save as Template" feature
5. Create starter templates

### Files to Create
```
backend/src/templates/
├── templates.module.ts
├── templates.service.ts
├── templates.controller.ts
└── dto/
frontend/src/app/dashboard/templates/
├── page.tsx
└── [id]/page.tsx
frontend/src/components/templates/
├── template-gallery.tsx
├── template-card.tsx
└── template-preview.tsx
```

### Key Code Snippets

**Template Schema**
```prisma
// Add to schema.prisma
model Template {
  id          String   @id @default(cuid())
  name        String
  description String?
  thumbnail   String?
  content     Json
  category    String
  isPublic    Boolean  @default(false)
  userId      String?
  user        User?    @relation(fields: [userId], references: [id])
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
}
```

**Templates Service**
```typescript
// backend/src/templates/templates.service.ts
import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class TemplatesService {
  constructor(private prisma: PrismaService) {}

  async findAll(category?: string) {
    return this.prisma.template.findMany({
      where: {
        isPublic: true,
        ...(category && { category }),
      },
      orderBy: { createdAt: 'desc' },
    });
  }

  async findUserTemplates(userId: string) {
    return this.prisma.template.findMany({
      where: { userId },
      orderBy: { createdAt: 'desc' },
    });
  }

  async createFromPage(userId: string, pageId: string, name: string, category: string) {
    const page = await this.prisma.page.findFirst({
      where: { id: pageId, userId },
    });

    if (!page) throw new Error('Page not found');

    return this.prisma.template.create({
      data: {
        name,
        category,
        content: page.content,
        userId,
        isPublic: false,
      },
    });
  }

  async useTemplate(userId: string, templateId: string, pageTitle: string) {
    const template = await this.prisma.template.findUnique({
      where: { id: templateId },
    });

    if (!template) throw new Error('Template not found');

    return this.prisma.page.create({
      data: {
        title: pageTitle,
        slug: generateSlug(pageTitle),
        content: template.content,
        userId,
      },
    });
  }
}
```

**Template Gallery**
```typescript
// frontend/src/components/templates/template-gallery.tsx
'use client';
import { useState, useEffect } from 'react';
import { api } from '@/lib/api';
import { TemplateCard } from './template-card';

const categories = ['All', 'Landing Page', 'Portfolio', 'Business', 'E-commerce', 'Event'];

export function TemplateGallery() {
  const [templates, setTemplates] = useState([]);
  const [category, setCategory] = useState('All');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const params = category === 'All' ? '' : `?category=${category}`;
    api.get(`/templates${params}`)
      .then(setTemplates)
      .finally(() => setLoading(false));
  }, [category]);

  return (
    <div>
      <div className="flex gap-2 mb-6">
        {categories.map((cat) => (
          <button
            key={cat}
            onClick={() => setCategory(cat)}
            className={`px-4 py-2 rounded-full text-sm ${
              category === cat ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200'
            }`}
          >
            {cat}
          </button>
        ))}
      </div>

      {loading ? (
        <div className="grid grid-cols-3 gap-4">
          {[...Array(6)].map((_, i) => (
            <div key={i} className="h-48 bg-gray-100 rounded-lg animate-pulse" />
          ))}
        </div>
      ) : (
        <div className="grid grid-cols-3 gap-4">
          {templates.map((template) => (
            <TemplateCard key={template.id} template={template} />
          ))}
        </div>
      )}
    </div>
  );
}
```

### Deliverables
- [ ] Template gallery with categories
- [ ] Use template to create new page
- [ ] Save page as template
- [ ] Template preview modal
- [ ] 5+ starter templates created

---

## Day 8: Media Library

### Goals
- Build media upload and management system
- Integrate with cloud storage (S3/Cloudinary)

### Tasks
1. Set up cloud storage integration
2. Create upload endpoints with validation
3. Build media library UI with grid view
4. Implement image optimization
5. Add media picker in builder

### Files to Create
```
backend/src/media/
├── media.module.ts
├── media.service.ts
├── media.controller.ts
└── storage.service.ts
frontend/src/components/media/
├── media-library.tsx
├── media-upload.tsx
├── media-grid.tsx
└── media-picker-modal.tsx
```

### Key Code Snippets

**Media Schema**
```prisma
// Add to schema.prisma
model Media {
  id        String   @id @default(cuid())
  filename  String
  url       String
  type      String
  size      Int
  width     Int?
  height    Int?
  userId    String
  user      User     @relation(fields: [userId], references: [id])
  createdAt DateTime @default(now())
}
```

**Storage Service (S3)**
```typescript
// backend/src/media/storage.service.ts
import { Injectable } from '@nestjs/common';
import { S3Client, PutObjectCommand, DeleteObjectCommand } from '@aws-sdk/client-s3';
import { v4 as uuid } from 'uuid';
import * as sharp from 'sharp';

@Injectable()
export class StorageService {
  private s3: S3Client;
  private bucket: string;

  constructor() {
    this.s3 = new S3Client({
      region: process.env.AWS_REGION,
      credentials: {
        accessKeyId: process.env.AWS_ACCESS_KEY_ID,
        secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY,
      },
    });
    this.bucket = process.env.AWS_S3_BUCKET;
  }

  async upload(file: Express.Multer.File, userId: string): Promise<{ url: string; key: string; metadata: any }> {
    const key = `${userId}/${uuid()}-${file.originalname}`;

    // Optimize image
    let buffer = file.buffer;
    let metadata = { width: 0, height: 0 };

    if (file.mimetype.startsWith('image/')) {
      const image = sharp(file.buffer);
      metadata = await image.metadata();

      buffer = await image
        .resize({ width: 1920, withoutEnlargement: true })
        .webp({ quality: 85 })
        .toBuffer();
    }

    await this.s3.send(new PutObjectCommand({
      Bucket: this.bucket,
      Key: key,
      Body: buffer,
      ContentType: file.mimetype,
    }));

    return {
      url: `https://${this.bucket}.s3.amazonaws.com/${key}`,
      key,
      metadata: { width: metadata.width, height: metadata.height },
    };
  }

  async delete(key: string): Promise<void> {
    await this.s3.send(new DeleteObjectCommand({
      Bucket: this.bucket,
      Key: key,
    }));
  }
}
```

**Media Controller**
```typescript
// backend/src/media/media.controller.ts
import { Controller, Get, Post, Delete, Param, UseGuards, Req, UseInterceptors, UploadedFile } from '@nestjs/common';
import { FileInterceptor } from '@nestjs/platform-express';
import { JwtAuthGuard } from '../auth/guards/jwt-auth.guard';
import { MediaService } from './media.service';

@Controller('media')
@UseGuards(JwtAuthGuard)
export class MediaController {
  constructor(private mediaService: MediaService) {}

  @Get()
  findAll(@Req() req) {
    return this.mediaService.findAllByUser(req.user.userId);
  }

  @Post('upload')
  @UseInterceptors(FileInterceptor('file', {
    limits: { fileSize: 10 * 1024 * 1024 }, // 10MB
    fileFilter: (req, file, cb) => {
      if (file.mimetype.match(/^(image|video)\//)) {
        cb(null, true);
      } else {
        cb(new Error('Invalid file type'), false);
      }
    },
  }))
  upload(@Req() req, @UploadedFile() file: Express.Multer.File) {
    return this.mediaService.upload(req.user.userId, file);
  }

  @Delete(':id')
  remove(@Req() req, @Param('id') id: string) {
    return this.mediaService.remove(id, req.user.userId);
  }
}
```

**Media Library Component**
```typescript
// frontend/src/components/media/media-library.tsx
'use client';
import { useState, useEffect, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';
import { api } from '@/lib/api';
import { Upload, Trash, Check } from 'lucide-react';

interface MediaLibraryProps {
  onSelect?: (media: Media) => void;
  selectable?: boolean;
}

export function MediaLibrary({ onSelect, selectable }: MediaLibraryProps) {
  const [media, setMedia] = useState<Media[]>([]);
  const [uploading, setUploading] = useState(false);
  const [selected, setSelected] = useState<string | null>(null);

  useEffect(() => {
    api.get('/media').then(setMedia);
  }, []);

  const onDrop = useCallback(async (files: File[]) => {
    setUploading(true);

    for (const file of files) {
      const formData = new FormData();
      formData.append('file', file);

      const uploaded = await api.upload('/media/upload', formData);
      setMedia((prev) => [uploaded, ...prev]);
    }

    setUploading(false);
  }, []);

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop,
    accept: { 'image/*': [], 'video/*': [] },
  });

  const handleDelete = async (id: string) => {
    await api.delete(`/media/${id}`);
    setMedia((prev) => prev.filter((m) => m.id !== id));
  };

  const handleSelect = (item: Media) => {
    if (selectable) {
      setSelected(item.id);
      onSelect?.(item);
    }
  };

  return (
    <div className="space-y-4">
      <div
        {...getRootProps()}
        className={`border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition ${
          isDragActive ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'
        }`}
      >
        <input {...getInputProps()} />
        <Upload className="mx-auto mb-2" />
        <p>{uploading ? 'Uploading...' : 'Drop files here or click to upload'}</p>
      </div>

      <div className="grid grid-cols-4 gap-4">
        {media.map((item) => (
          <div
            key={item.id}
            onClick={() => handleSelect(item)}
            className={`relative group rounded-lg overflow-hidden cursor-pointer ${
              selected === item.id ? 'ring-2 ring-blue-500' : ''
            }`}
          >
            <img src={item.url} alt={item.filename} className="w-full h-32 object-cover" />
            {selected === item.id && (
              <div className="absolute top-2 right-2 bg-blue-500 text-white rounded-full p-1">
                <Check size={12} />
              </div>
            )}
            <button
              onClick={(e) => { e.stopPropagation(); handleDelete(item.id); }}
              className="absolute bottom-2 right-2 bg-red-500 text-white p-1 rounded opacity-0 group-hover:opacity-100"
            >
              <Trash size={12} />
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}
```

### Deliverables
- [ ] Drag-and-drop file upload
- [ ] Image optimization and resizing
- [ ] Media grid with delete
- [ ] Media picker modal for builder
- [ ] Storage quota tracking

---

## Day 9: Publishing System

### Goals
- Implement page publishing with preview
- Generate optimized static pages

### Tasks
1. Create publishing endpoints
2. Build preview system
3. Generate SEO-optimized HTML
4. Implement publish/unpublish flow
5. Add analytics snippet injection

### Files to Create
```
backend/src/publishing/
├── publishing.module.ts
├── publishing.service.ts
├── publishing.controller.ts
└── renderer.service.ts
frontend/src/app/(published)/
├── [slug]/page.tsx
└── preview/[id]/page.tsx
frontend/src/components/builder/
└── publish-modal.tsx
```

### Key Code Snippets

**Renderer Service**
```typescript
// backend/src/publishing/renderer.service.ts
import { Injectable } from '@nestjs/common';
import { Element } from '../types';

@Injectable()
export class RendererService {
  renderToHtml(elements: Element[], meta: { title: string; description?: string }): string {
    const body = elements.map((el) => this.renderElement(el)).join('');

    return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>${meta.title}</title>
  ${meta.description ? `<meta name="description" content="${meta.description}">` : ''}
  <meta property="og:title" content="${meta.title}">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
  </style>
</head>
<body>
  ${body}
</body>
</html>`;
  }

  private renderElement(element: Element): string {
    const style = this.propsToStyle(element.props);

    switch (element.type) {
      case 'text':
        return `<p style="${style}">${element.props.content}</p>`;
      case 'heading':
        return `<${element.props.level} style="${style}">${element.props.content}</${element.props.level}>`;
      case 'button':
        return `<button style="${style}" class="px-4 py-2 bg-blue-600 text-white rounded">${element.props.text}</button>`;
      case 'image':
        return `<img src="${element.props.src}" alt="${element.props.alt || ''}" style="${style}" loading="lazy">`;
      case 'container':
        const children = element.children.map((c) => this.renderElement(c)).join('');
        return `<div style="${style}">${children}</div>`;
      default:
        return '';
    }
  }

  private propsToStyle(props: Record<string, any>): string {
    const styles: string[] = [];
    if (props.fontSize) styles.push(`font-size: ${props.fontSize}px`);
    if (props.color) styles.push(`color: ${props.color}`);
    if (props.padding) styles.push(`padding: ${props.padding}px`);
    if (props.margin) styles.push(`margin: ${props.margin}px`);
    if (props.background) styles.push(`background: ${props.background}`);
    if (props.width) styles.push(`width: ${props.width}`);
    return styles.join('; ');
  }
}
```

**Publishing Service**
```typescript
// backend/src/publishing/publishing.service.ts
import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { RendererService } from './renderer.service';

@Injectable()
export class PublishingService {
  constructor(
    private prisma: PrismaService,
    private renderer: RendererService,
  ) {}

  async publish(pageId: string, userId: string) {
    const page = await this.prisma.page.findFirst({
      where: { id: pageId, userId },
    });

    if (!page) throw new Error('Page not found');

    // Generate static HTML
    const html = this.renderer.renderToHtml(page.content as any[], {
      title: page.title,
      description: page.meta?.description,
    });

    // Update page status
    await this.prisma.page.update({
      where: { id: pageId },
      data: {
        published: true,
        publishedHtml: html,
        publishedAt: new Date(),
      },
    });

    return {
      success: true,
      url: `${process.env.APP_URL}/p/${page.slug}`
    };
  }

  async unpublish(pageId: string, userId: string) {
    await this.prisma.page.update({
      where: { id: pageId, userId },
      data: { published: false },
    });
    return { success: true };
  }

  async getPublishedPage(slug: string) {
    const page = await this.prisma.page.findFirst({
      where: { slug, published: true },
      include: { domain: true },
    });

    if (!page) return null;
    return page.publishedHtml;
  }
}
```

**Publish Modal**
```typescript
// frontend/src/components/builder/publish-modal.tsx
'use client';
import { useState } from 'react';
import { api } from '@/lib/api';
import { Check, Copy, ExternalLink } from 'lucide-react';

interface PublishModalProps {
  pageId: string;
  isPublished: boolean;
  onClose: () => void;
  onPublish: () => void;
}

export function PublishModal({ pageId, isPublished, onClose, onPublish }: PublishModalProps) {
  const [loading, setLoading] = useState(false);
  const [publishedUrl, setPublishedUrl] = useState('');
  const [copied, setCopied] = useState(false);

  const handlePublish = async () => {
    setLoading(true);
    const result = await api.post(`/pages/${pageId}/publish`);
    setPublishedUrl(result.url);
    setLoading(false);
    onPublish();
  };

  const handleUnpublish = async () => {
    setLoading(true);
    await api.post(`/pages/${pageId}/unpublish`);
    setLoading(false);
    onPublish();
    onClose();
  };

  const copyUrl = () => {
    navigator.clipboard.writeText(publishedUrl);
    setCopied(true);
    setTimeout(() => setCopied(false), 2000);
  };

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 w-[400px]">
        <h2 className="text-xl font-semibold mb-4">
          {isPublished ? 'Page Published' : 'Publish Page'}
        </h2>

        {publishedUrl ? (
          <div className="space-y-4">
            <div className="flex items-center gap-2 p-3 bg-green-50 text-green-800 rounded">
              <Check size={20} />
              <span>Published successfully!</span>
            </div>

            <div className="flex items-center gap-2">
              <input
                value={publishedUrl}
                readOnly
                className="flex-1 p-2 border rounded text-sm"
              />
              <button onClick={copyUrl} className="p-2 border rounded hover:bg-gray-50">
                {copied ? <Check size={16} /> : <Copy size={16} />}
              </button>
              <a href={publishedUrl} target="_blank" className="p-2 border rounded hover:bg-gray-50">
                <ExternalLink size={16} />
              </a>
            </div>
          </div>
        ) : (
          <div className="space-y-4">
            <p className="text-gray-600">
              {isPublished
                ? 'Your page is currently live. You can unpublish it to take it offline.'
                : 'Publishing will make your page live and accessible to anyone with the link.'}
            </p>

            <div className="flex gap-2 justify-end">
              <button onClick={onClose} className="px-4 py-2 border rounded">
                Cancel
              </button>
              {isPublished ? (
                <button
                  onClick={handleUnpublish}
                  disabled={loading}
                  className="px-4 py-2 bg-red-600 text-white rounded"
                >
                  {loading ? 'Unpublishing...' : 'Unpublish'}
                </button>
              ) : (
                <button
                  onClick={handlePublish}
                  disabled={loading}
                  className="px-4 py-2 bg-blue-600 text-white rounded"
                >
                  {loading ? 'Publishing...' : 'Publish'}
                </button>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
```

### Deliverables
- [ ] Publish/unpublish functionality
- [ ] SEO-optimized HTML generation
- [ ] Preview before publishing
- [ ] Shareable published URLs
- [ ] Analytics snippet integration

---

## Day 10: Custom Domains

### Goals
- Implement custom domain connection
- Set up SSL and DNS verification

### Tasks
1. Create domain management module
2. Implement DNS verification
3. Set up SSL certificate provisioning
4. Build domain settings UI
5. Configure routing for custom domains

### Files to Create
```
backend/src/domains/
├── domains.module.ts
├── domains.service.ts
├── domains.controller.ts
└── ssl.service.ts
frontend/src/app/dashboard/pages/[id]/domains/
└── page.tsx
frontend/src/components/domains/
├── domain-setup.tsx
└── dns-instructions.tsx
```

### Key Code Snippets

**Domain Schema**
```prisma
// Add to schema.prisma
model Domain {
  id         String   @id @default(cuid())
  domain     String   @unique
  pageId     String   @unique
  page       Page     @relation(fields: [pageId], references: [id])
  verified   Boolean  @default(false)
  sslStatus  String   @default("pending")
  createdAt  DateTime @default(now())
  verifiedAt DateTime?
}
```

**Domains Service**
```typescript
// backend/src/domains/domains.service.ts
import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import * as dns from 'dns';
import { promisify } from 'util';

const resolveTxt = promisify(dns.resolveTxt);
const resolveCname = promisify(dns.resolveCname);

@Injectable()
export class DomainsService {
  constructor(private prisma: PrismaService) {}

  async addDomain(pageId: string, userId: string, domain: string) {
    // Verify page ownership
    const page = await this.prisma.page.findFirst({
      where: { id: pageId, userId },
    });
    if (!page) throw new Error('Page not found');

    // Check domain availability
    const existing = await this.prisma.domain.findUnique({ where: { domain } });
    if (existing) throw new Error('Domain already in use');

    return this.prisma.domain.create({
      data: {
        domain,
        pageId,
        verificationToken: this.generateToken(),
      },
    });
  }

  async verifyDomain(domainId: string, userId: string) {
    const domainRecord = await this.prisma.domain.findUnique({
      where: { id: domainId },
      include: { page: true },
    });

    if (!domainRecord || domainRecord.page.userId !== userId) {
      throw new Error('Domain not found');
    }

    // Check TXT record
    try {
      const records = await resolveTxt(`_verify.${domainRecord.domain}`);
      const verified = records.flat().includes(domainRecord.verificationToken);

      if (verified) {
        // Also check CNAME
        const cname = await resolveCname(domainRecord.domain);
        const cnameValid = cname.includes(process.env.APP_DOMAIN);

        if (cnameValid) {
          await this.prisma.domain.update({
            where: { id: domainId },
            data: { verified: true, verifiedAt: new Date() },
          });

          // Trigger SSL provisioning
          await this.provisionSsl(domainRecord.domain);

          return { verified: true };
        }
      }
    } catch (e) {
      // DNS records not found
    }

    return { verified: false };
  }

  async provisionSsl(domain: string) {
    // Integration with Let's Encrypt or Cloudflare
    // This would typically use certbot or Cloudflare API
    await this.prisma.domain.update({
      where: { domain },
      data: { sslStatus: 'provisioning' },
    });

    // Actual SSL provisioning logic here
    // ...

    await this.prisma.domain.update({
      where: { domain },
      data: { sslStatus: 'active' },
    });
  }

  private generateToken(): string {
    return `lp-verify-${Math.random().toString(36).substring(2, 15)}`;
  }
}
```

**Domain Setup Component**
```typescript
// frontend/src/components/domains/domain-setup.tsx
'use client';
import { useState } from 'react';
import { api } from '@/lib/api';
import { Check, AlertCircle, RefreshCw } from 'lucide-react';

interface DomainSetupProps {
  pageId: string;
  existingDomain?: Domain;
}

export function DomainSetup({ pageId, existingDomain }: DomainSetupProps) {
  const [domain, setDomain] = useState(existingDomain?.domain || '');
  const [domainData, setDomainData] = useState(existingDomain);
  const [loading, setLoading] = useState(false);
  const [verifying, setVerifying] = useState(false);

  const handleAdd = async () => {
    setLoading(true);
    const result = await api.post(`/domains`, { pageId, domain });
    setDomainData(result);
    setLoading(false);
  };

  const handleVerify = async () => {
    setVerifying(true);
    const result = await api.post(`/domains/${domainData.id}/verify`);
    if (result.verified) {
      setDomainData({ ...domainData, verified: true });
    }
    setVerifying(false);
  };

  if (!domainData) {
    return (
      <div className="space-y-4">
        <div>
          <label className="block text-sm font-medium mb-1">Custom Domain</label>
          <input
            type="text"
            value={domain}
            onChange={(e) => setDomain(e.target.value)}
            placeholder="www.example.com"
            className="w-full border rounded p-2"
          />
        </div>
        <button
          onClick={handleAdd}
          disabled={loading || !domain}
          className="px-4 py-2 bg-blue-600 text-white rounded"
        >
          {loading ? 'Adding...' : 'Add Domain'}
        </button>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between p-4 bg-gray-50 rounded">
        <span className="font-medium">{domainData.domain}</span>
        {domainData.verified ? (
          <span className="flex items-center gap-1 text-green-600">
            <Check size={16} /> Verified
          </span>
        ) : (
          <span className="flex items-center gap-1 text-yellow-600">
            <AlertCircle size={16} /> Pending
          </span>
        )}
      </div>

      {!domainData.verified && (
        <>
          <div className="space-y-4">
            <h3 className="font-medium">DNS Configuration</h3>
            <div className="text-sm space-y-2">
              <p>Add these DNS records to your domain:</p>

              <div className="bg-gray-100 p-3 rounded font-mono text-xs">
                <p>TXT Record:</p>
                <p>Host: _verify</p>
                <p>Value: {domainData.verificationToken}</p>
              </div>

              <div className="bg-gray-100 p-3 rounded font-mono text-xs">
                <p>CNAME Record:</p>
                <p>Host: {domainData.domain.split('.')[0]}</p>
                <p>Value: {process.env.NEXT_PUBLIC_APP_DOMAIN}</p>
              </div>
            </div>
          </div>

          <button
            onClick={handleVerify}
            disabled={verifying}
            className="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded"
          >
            <RefreshCw size={16} className={verifying ? 'animate-spin' : ''} />
            {verifying ? 'Verifying...' : 'Verify DNS'}
          </button>
        </>
      )}
    </div>
  );
}
```

### Deliverables
- [ ] Add custom domain to page
- [ ] DNS verification system
- [ ] SSL certificate provisioning
- [ ] Domain status dashboard
- [ ] Routing for custom domains

---

## Day 11: Payment Integration

### Goals
- Integrate Stripe for payment processing
- Implement checkout and payment flows

### Tasks
1. Set up Stripe integration
2. Create checkout session endpoints
3. Implement webhook handlers
4. Build pricing page
5. Add payment method management

### Files to Create
```
backend/src/payments/
├── payments.module.ts
├── payments.service.ts
├── payments.controller.ts
├── stripe.service.ts
└── webhooks.controller.ts
frontend/src/app/pricing/
└── page.tsx
frontend/src/app/dashboard/billing/
└── page.tsx
```

### Key Code Snippets

**Stripe Service**
```typescript
// backend/src/payments/stripe.service.ts
import { Injectable } from '@nestjs/common';
import Stripe from 'stripe';
import { PrismaService } from '../prisma/prisma.service';

@Injectable()
export class StripeService {
  private stripe: Stripe;

  constructor(private prisma: PrismaService) {
    this.stripe = new Stripe(process.env.STRIPE_SECRET_KEY, {
      apiVersion: '2023-10-16',
    });
  }

  async createCustomer(userId: string, email: string) {
    const customer = await this.stripe.customers.create({ email });

    await this.prisma.user.update({
      where: { id: userId },
      data: { stripeCustomerId: customer.id },
    });

    return customer;
  }

  async createCheckoutSession(userId: string, priceId: string) {
    const user = await this.prisma.user.findUnique({ where: { id: userId } });

    let customerId = user.stripeCustomerId;
    if (!customerId) {
      const customer = await this.createCustomer(userId, user.email);
      customerId = customer.id;
    }

    const session = await this.stripe.checkout.sessions.create({
      customer: customerId,
      mode: 'subscription',
      payment_method_types: ['card'],
      line_items: [{ price: priceId, quantity: 1 }],
      success_url: `${process.env.APP_URL}/dashboard/billing?success=true`,
      cancel_url: `${process.env.APP_URL}/pricing?canceled=true`,
    });

    return { sessionId: session.id, url: session.url };
  }

  async createPortalSession(userId: string) {
    const user = await this.prisma.user.findUnique({ where: { id: userId } });

    const session = await this.stripe.billingPortal.sessions.create({
      customer: user.stripeCustomerId,
      return_url: `${process.env.APP_URL}/dashboard/billing`,
    });

    return { url: session.url };
  }

  async handleWebhook(signature: string, payload: Buffer) {
    const event = this.stripe.webhooks.constructEvent(
      payload,
      signature,
      process.env.STRIPE_WEBHOOK_SECRET,
    );

    switch (event.type) {
      case 'checkout.session.completed':
        await this.handleCheckoutComplete(event.data.object);
        break;
      case 'customer.subscription.updated':
        await this.handleSubscriptionUpdate(event.data.object);
        break;
      case 'customer.subscription.deleted':
        await this.handleSubscriptionCancel(event.data.object);
        break;
    }
  }

  private async handleCheckoutComplete(session: Stripe.Checkout.Session) {
    const user = await this.prisma.user.findFirst({
      where: { stripeCustomerId: session.customer as string },
    });

    if (!user) return;

    await this.prisma.subscription.upsert({
      where: { userId: user.id },
      create: {
        userId: user.id,
        stripeSubscriptionId: session.subscription as string,
        status: 'active',
        plan: 'pro', // Determine from price
        currentPeriodEnd: new Date(),
      },
      update: {
        stripeSubscriptionId: session.subscription as string,
        status: 'active',
      },
    });
  }
}
```

**Webhook Controller**
```typescript
// backend/src/payments/webhooks.controller.ts
import { Controller, Post, Req, Res, Headers } from '@nestjs/common';
import { Request, Response } from 'express';
import { StripeService } from './stripe.service';

@Controller('webhooks')
export class WebhooksController {
  constructor(private stripeService: StripeService) {}

  @Post('stripe')
  async handleStripeWebhook(
    @Req() req: Request,
    @Res() res: Response,
    @Headers('stripe-signature') signature: string,
  ) {
    try {
      await this.stripeService.handleWebhook(signature, req.body);
      res.status(200).send('OK');
    } catch (err) {
      console.error('Webhook error:', err.message);
      res.status(400).send(`Webhook Error: ${err.message}`);
    }
  }
}
```

**Pricing Page**
```typescript
// frontend/src/app/pricing/page.tsx
'use client';
import { useState } from 'react';
import { useAuth } from '@/context/auth-context';
import { api } from '@/lib/api';
import { Check } from 'lucide-react';

const plans = [
  {
    name: 'Free',
    price: 0,
    priceId: null,
    features: ['1 Landing Page', 'Basic Elements', 'Subdomain Only', 'Community Support'],
  },
  {
    name: 'Pro',
    price: 19,
    priceId: 'price_pro_monthly',
    features: ['10 Landing Pages', 'All Elements', 'Custom Domain', 'Priority Support', 'Analytics'],
    popular: true,
  },
  {
    name: 'Business',
    price: 49,
    priceId: 'price_business_monthly',
    features: ['Unlimited Pages', 'All Elements', 'Multiple Domains', 'Team Collaboration', 'White Label', 'API Access'],
  },
];

export default function PricingPage() {
  const { user } = useAuth();
  const [loading, setLoading] = useState<string | null>(null);

  const handleSubscribe = async (priceId: string) => {
    if (!user) {
      window.location.href = '/login?redirect=/pricing';
      return;
    }

    setLoading(priceId);
    const { url } = await api.post('/payments/checkout', { priceId });
    window.location.href = url;
  };

  return (
    <div className="py-20 px-4">
      <div className="text-center mb-12">
        <h1 className="text-4xl font-bold mb-4">Simple, Transparent Pricing</h1>
        <p className="text-gray-600">Choose the plan that works for you</p>
      </div>

      <div className="max-w-5xl mx-auto grid md:grid-cols-3 gap-8">
        {plans.map((plan) => (
          <div
            key={plan.name}
            className={`border rounded-lg p-6 ${plan.popular ? 'border-blue-500 ring-2 ring-blue-500' : ''}`}
          >
            {plan.popular && (
              <span className="bg-blue-500 text-white text-xs px-2 py-1 rounded">Most Popular</span>
            )}
            <h3 className="text-xl font-semibold mt-4">{plan.name}</h3>
            <div className="mt-4">
              <span className="text-4xl font-bold">${plan.price}</span>
              <span className="text-gray-500">/month</span>
            </div>
            <ul className="mt-6 space-y-3">
              {plan.features.map((feature) => (
                <li key={feature} className="flex items-center gap-2">
                  <Check size={16} className="text-green-500" />
                  {feature}
                </li>
              ))}
            </ul>
            <button
              onClick={() => plan.priceId && handleSubscribe(plan.priceId)}
              disabled={loading === plan.priceId || !plan.priceId}
              className={`w-full mt-6 py-2 rounded ${
                plan.popular
                  ? 'bg-blue-600 text-white'
                  : 'border hover:bg-gray-50'
              }`}
            >
              {loading === plan.priceId ? 'Loading...' : plan.priceId ? 'Subscribe' : 'Current Plan'}
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}
```

### Deliverables
- [ ] Stripe checkout integration
- [ ] Webhook handlers for subscription events
- [ ] Pricing page with plan comparison
- [ ] Billing management portal
- [ ] Invoice history

---

## Day 12: Subscription Management

### Goals
- Implement subscription features and limits
- Build usage tracking and enforcement

### Tasks
1. Create subscription schema and service
2. Implement feature gating middleware
3. Build usage tracking
4. Add upgrade/downgrade flows
5. Implement cancellation with grace period

### Files to Create
```
backend/src/subscriptions/
├── subscriptions.module.ts
├── subscriptions.service.ts
├── subscriptions.controller.ts
└── guards/subscription.guard.ts
frontend/src/components/subscription/
├── usage-meter.tsx
├── upgrade-modal.tsx
└── plan-comparison.tsx
```

### Key Code Snippets

**Subscription Schema**
```prisma
// Add to schema.prisma
model Subscription {
  id                   String   @id @default(cuid())
  userId               String   @unique
  user                 User     @relation(fields: [userId], references: [id])
  stripeSubscriptionId String?  @unique
  status               String   @default("inactive")
  plan                 String   @default("free")
  currentPeriodEnd     DateTime?
  cancelAtPeriodEnd    Boolean  @default(false)
  createdAt            DateTime @default(now())
  updatedAt            DateTime @updatedAt
}

model Usage {
  id        String   @id @default(cuid())
  userId    String
  user      User     @relation(fields: [userId], references: [id])
  metric    String
  count     Int      @default(0)
  period    String   // e.g., "2024-01"
  createdAt DateTime @default(now())

  @@unique([userId, metric, period])
}
```

**Subscription Guard**
```typescript
// backend/src/subscriptions/guards/subscription.guard.ts
import { Injectable, CanActivate, ExecutionContext, ForbiddenException } from '@nestjs/common';
import { Reflector } from '@nestjs/core';
import { SubscriptionsService } from '../subscriptions.service';

@Injectable()
export class SubscriptionGuard implements CanActivate {
  constructor(
    private reflector: Reflector,
    private subscriptionsService: SubscriptionsService,
  ) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const requiredPlan = this.reflector.get<string>('plan', context.getHandler());
    if (!requiredPlan) return true;

    const request = context.switchToHttp().getRequest();
    const userId = request.user.userId;

    const subscription = await this.subscriptionsService.getUserSubscription(userId);
    const userPlan = subscription?.plan || 'free';

    const planHierarchy = ['free', 'pro', 'business'];
    const userPlanIndex = planHierarchy.indexOf(userPlan);
    const requiredPlanIndex = planHierarchy.indexOf(requiredPlan);

    if (userPlanIndex < requiredPlanIndex) {
      throw new ForbiddenException(`This feature requires ${requiredPlan} plan or higher`);
    }

    return true;
  }
}
```

**Subscriptions Service**
```typescript
// backend/src/subscriptions/subscriptions.service.ts
import { Injectable } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';

const PLAN_LIMITS = {
  free: { pages: 1, storage: 100, domains: 0 },
  pro: { pages: 10, storage: 1000, domains: 1 },
  business: { pages: -1, storage: 10000, domains: -1 }, // -1 = unlimited
};

@Injectable()
export class SubscriptionsService {
  constructor(private prisma: PrismaService) {}

  async getUserSubscription(userId: string) {
    return this.prisma.subscription.findUnique({ where: { userId } });
  }

  async checkLimit(userId: string, metric: string): Promise<{ allowed: boolean; current: number; limit: number }> {
    const subscription = await this.getUserSubscription(userId);
    const plan = subscription?.plan || 'free';
    const limit = PLAN_LIMITS[plan][metric];

    if (limit === -1) return { allowed: true, current: 0, limit: -1 };

    const current = await this.getCurrentUsage(userId, metric);
    return { allowed: current < limit, current, limit };
  }

  async getCurrentUsage(userId: string, metric: string): Promise<number> {
    switch (metric) {
      case 'pages':
        return this.prisma.page.count({ where: { userId } });
      case 'storage':
        const media = await this.prisma.media.aggregate({
          where: { userId },
          _sum: { size: true },
        });
        return Math.round((media._sum.size || 0) / 1024 / 1024); // MB
      case 'domains':
        return this.prisma.domain.count({
          where: { page: { userId } },
        });
      default:
        return 0;
    }
  }

  async getUsageSummary(userId: string) {
    const subscription = await this.getUserSubscription(userId);
    const plan = subscription?.plan || 'free';
    const limits = PLAN_LIMITS[plan];

    const usage = await Promise.all([
      this.getCurrentUsage(userId, 'pages'),
      this.getCurrentUsage(userId, 'storage'),
      this.getCurrentUsage(userId, 'domains'),
    ]);

    return {
      plan,
      pages: { current: usage[0], limit: limits.pages },
      storage: { current: usage[1], limit: limits.storage },
      domains: { current: usage[2], limit: limits.domains },
      subscription: subscription ? {
        status: subscription.status,
        currentPeriodEnd: subscription.currentPeriodEnd,
        cancelAtPeriodEnd: subscription.cancelAtPeriodEnd,
      } : null,
    };
  }
}
```

**Usage Meter Component**
```typescript
// frontend/src/components/subscription/usage-meter.tsx
interface UsageMeterProps {
  label: string;
  current: number;
  limit: number;
  unit?: string;
}

export function UsageMeter({ label, current, limit, unit = '' }: UsageMeterProps) {
  const percentage = limit === -1 ? 0 : (current / limit) * 100;
  const isUnlimited = limit === -1;
  const isNearLimit = percentage >= 80;

  return (
    <div className="space-y-2">
      <div className="flex justify-between text-sm">
        <span>{label}</span>
        <span className={isNearLimit && !isUnlimited ? 'text-red-600' : ''}>
          {current}{unit} / {isUnlimited ? 'Unlimited' : `${limit}${unit}`}
        </span>
      </div>
      {!isUnlimited && (
        <div className="h-2 bg-gray-200 rounded-full overflow-hidden">
          <div
            className={`h-full transition-all ${
              isNearLimit ? 'bg-red-500' : 'bg-blue-500'
            }`}
            style={{ width: `${Math.min(percentage, 100)}%` }}
          />
        </div>
      )}
    </div>
  );
}
```

### Deliverables
- [ ] Subscription status tracking
- [ ] Feature gating by plan
- [ ] Usage meters in dashboard
- [ ] Upgrade prompts when hitting limits
- [ ] Cancellation flow with confirmation

---

## Day 13: Testing & Quality Assurance

### Goals
- Implement comprehensive test coverage
- Set up CI/CD pipeline

### Tasks
1. Write unit tests for services
2. Write integration tests for API
3. Write E2E tests for critical flows
4. Set up CI pipeline with GitHub Actions
5. Add code quality checks

### Files to Create
```
backend/test/
├── auth.service.spec.ts
├── pages.service.spec.ts
├── app.e2e-spec.ts
└── setup.ts
frontend/__tests__/
├── components/
├── hooks/
└── e2e/
.github/workflows/
├── ci.yml
└── deploy.yml
```

### Key Code Snippets

**Auth Service Tests**
```typescript
// backend/test/auth.service.spec.ts
import { Test, TestingModule } from '@nestjs/testing';
import { AuthService } from '../src/auth/auth.service';
import { PrismaService } from '../src/prisma/prisma.service';
import { JwtService } from '@nestjs/jwt';
import { UnauthorizedException } from '@nestjs/common';
import * as bcrypt from 'bcrypt';

describe('AuthService', () => {
  let service: AuthService;
  let prisma: PrismaService;

  const mockPrisma = {
    user: {
      create: jest.fn(),
      findUnique: jest.fn(),
    },
  };

  const mockJwt = {
    sign: jest.fn().mockReturnValue('mock-token'),
  };

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      providers: [
        AuthService,
        { provide: PrismaService, useValue: mockPrisma },
        { provide: JwtService, useValue: mockJwt },
      ],
    }).compile();

    service = module.get<AuthService>(AuthService);
    prisma = module.get<PrismaService>(PrismaService);
  });

  describe('register', () => {
    it('should create user and return tokens', async () => {
      mockPrisma.user.create.mockResolvedValue({
        id: '1',
        email: 'test@test.com',
      });

      const result = await service.register('test@test.com', 'password123');

      expect(result).toHaveProperty('access_token');
      expect(result).toHaveProperty('refresh_token');
      expect(mockPrisma.user.create).toHaveBeenCalled();
    });
  });

  describe('login', () => {
    it('should return tokens for valid credentials', async () => {
      const hashedPassword = await bcrypt.hash('password123', 10);
      mockPrisma.user.findUnique.mockResolvedValue({
        id: '1',
        email: 'test@test.com',
        password: hashedPassword,
      });

      const result = await service.login('test@test.com', 'password123');

      expect(result).toHaveProperty('access_token');
    });

    it('should throw for invalid credentials', async () => {
      mockPrisma.user.findUnique.mockResolvedValue(null);

      await expect(service.login('test@test.com', 'wrong')).rejects.toThrow(
        UnauthorizedException,
      );
    });
  });
});
```

**E2E Test**
```typescript
// backend/test/app.e2e-spec.ts
import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication } from '@nestjs/common';
import * as request from 'supertest';
import { AppModule } from '../src/app.module';

describe('AppController (e2e)', () => {
  let app: INestApplication;

  beforeEach(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [AppModule],
    }).compile();

    app = moduleFixture.createNestApplication();
    await app.init();
  });

  afterEach(async () => {
    await app.close();
  });

  describe('Auth', () => {
    it('/auth/register (POST)', async () => {
      const response = await request(app.getHttpServer())
        .post('/auth/register')
        .send({ email: 'test@test.com', password: 'password123' })
        .expect(201);

      expect(response.body).toHaveProperty('access_token');
    });

    it('/auth/login (POST)', async () => {
      // First register
      await request(app.getHttpServer())
        .post('/auth/register')
        .send({ email: 'login@test.com', password: 'password123' });

      // Then login
      const response = await request(app.getHttpServer())
        .post('/auth/login')
        .send({ email: 'login@test.com', password: 'password123' })
        .expect(200);

      expect(response.body).toHaveProperty('access_token');
    });
  });

  describe('Pages', () => {
    let accessToken: string;

    beforeEach(async () => {
      const authResponse = await request(app.getHttpServer())
        .post('/auth/register')
        .send({ email: `user-${Date.now()}@test.com`, password: 'password123' });
      accessToken = authResponse.body.access_token;
    });

    it('/pages (POST) - create page', async () => {
      const response = await request(app.getHttpServer())
        .post('/pages')
        .set('Authorization', `Bearer ${accessToken}`)
        .send({ title: 'Test Page', slug: 'test-page' })
        .expect(201);

      expect(response.body).toHaveProperty('id');
      expect(response.body.title).toBe('Test Page');
    });

    it('/pages (GET) - list pages', async () => {
      const response = await request(app.getHttpServer())
        .get('/pages')
        .set('Authorization', `Bearer ${accessToken}`)
        .expect(200);

      expect(Array.isArray(response.body)).toBe(true);
    });
  });
});
```

**GitHub Actions CI**
```yaml
# .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  test-backend:
    runs-on: ubuntu-latest

    services:
      postgres:
        image: postgres:15
        env:
          POSTGRES_USER: test
          POSTGRES_PASSWORD: test
          POSTGRES_DB: test
        ports:
          - 5432:5432
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
          cache-dependency-path: backend/package-lock.json

      - name: Install dependencies
        working-directory: backend
        run: npm ci

      - name: Run Prisma migrations
        working-directory: backend
        run: npx prisma migrate deploy
        env:
          DATABASE_URL: postgresql://test:test@localhost:5432/test

      - name: Run tests
        working-directory: backend
        run: npm test -- --coverage
        env:
          DATABASE_URL: postgresql://test:test@localhost:5432/test
          JWT_SECRET: test-secret

      - name: Run E2E tests
        working-directory: backend
        run: npm run test:e2e
        env:
          DATABASE_URL: postgresql://test:test@localhost:5432/test
          JWT_SECRET: test-secret

  test-frontend:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
          cache-dependency-path: frontend/package-lock.json

      - name: Install dependencies
        working-directory: frontend
        run: npm ci

      - name: Run linter
        working-directory: frontend
        run: npm run lint

      - name: Run type check
        working-directory: frontend
        run: npm run type-check

      - name: Run tests
        working-directory: frontend
        run: npm test -- --coverage

      - name: Build
        working-directory: frontend
        run: npm run build

  e2e:
    runs-on: ubuntu-latest
    needs: [test-backend, test-frontend]

    steps:
      - uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'

      - name: Install Playwright
        working-directory: frontend
        run: npx playwright install --with-deps

      - name: Run E2E tests
        working-directory: frontend
        run: npm run test:e2e
```

### Deliverables
- [ ] 80%+ test coverage for services
- [ ] Integration tests for all API endpoints
- [ ] E2E tests for auth, builder, publishing
- [ ] CI pipeline running on PRs
- [ ] Automated linting and type checking

---

## Day 14: Deployment & Launch

### Goals
- Deploy to production environment
- Set up monitoring and logging

### Tasks
1. Configure production environment
2. Set up database and Redis
3. Deploy backend to cloud (Railway/Render/AWS)
4. Deploy frontend to Vercel
5. Configure DNS and SSL
6. Set up monitoring (Sentry, LogRocket)
7. Final testing and launch checklist

### Files to Create
```
backend/
├── Dockerfile
└── .env.production
frontend/
├── vercel.json
└── .env.production
infrastructure/
├── docker-compose.prod.yml
└── nginx.conf
docs/
└── DEPLOYMENT.md
```

### Key Code Snippets

**Backend Dockerfile**
```dockerfile
# backend/Dockerfile
FROM node:20-alpine AS builder

WORKDIR /app
COPY package*.json ./
COPY prisma ./prisma/

RUN npm ci
COPY . .
RUN npm run build
RUN npx prisma generate

FROM node:20-alpine AS runner

WORKDIR /app

ENV NODE_ENV=production

COPY --from=builder /app/node_modules ./node_modules
COPY --from=builder /app/dist ./dist
COPY --from=builder /app/prisma ./prisma
COPY --from=builder /app/package*.json ./

EXPOSE 3001

CMD ["sh", "-c", "npx prisma migrate deploy && node dist/main.js"]
```

**Production Docker Compose**
```yaml
# infrastructure/docker-compose.prod.yml
version: '3.8'

services:
  backend:
    build: ../backend
    environment:
      - DATABASE_URL=${DATABASE_URL}
      - REDIS_URL=${REDIS_URL}
      - JWT_SECRET=${JWT_SECRET}
      - STRIPE_SECRET_KEY=${STRIPE_SECRET_KEY}
      - AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
      - AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
    ports:
      - "3001:3001"
    depends_on:
      - redis
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data
    restart: unless-stopped

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
      - ./certs:/etc/nginx/certs
    depends_on:
      - backend
    restart: unless-stopped

volumes:
  redis_data:
```

**Nginx Configuration**
```nginx
# infrastructure/nginx.conf
events {
    worker_connections 1024;
}

http {
    upstream backend {
        server backend:3001;
    }

    server {
        listen 80;
        server_name api.yourapp.com;
        return 301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl;
        server_name api.yourapp.com;

        ssl_certificate /etc/nginx/certs/fullchain.pem;
        ssl_certificate_key /etc/nginx/certs/privkey.pem;

        location / {
            proxy_pass http://backend;
            proxy_http_version 1.1;
            proxy_set_header Upgrade $http_upgrade;
            proxy_set_header Connection 'upgrade';
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_cache_bypass $http_upgrade;
        }
    }
}
```

**Vercel Configuration**
```json
{
  "buildCommand": "npm run build",
  "outputDirectory": ".next",
  "framework": "nextjs",
  "regions": ["iad1"],
  "env": {
    "NEXT_PUBLIC_API_URL": "@api_url",
    "NEXT_PUBLIC_STRIPE_KEY": "@stripe_public_key"
  },
  "headers": [
    {
      "source": "/(.*)",
      "headers": [
        {
          "key": "X-Frame-Options",
          "value": "DENY"
        },
        {
          "key": "X-Content-Type-Options",
          "value": "nosniff"
        }
      ]
    }
  ]
}
```

**GitHub Actions Deploy**
```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy-backend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Deploy to Railway
        uses: bervProject/railway-deploy@main
        with:
          railway_token: ${{ secrets.RAILWAY_TOKEN }}
          service: backend

  deploy-frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Deploy to Vercel
        uses: amondnet/vercel-action@v25
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
          vercel-args: '--prod'
```

**Launch Checklist**
```markdown
## Pre-Launch Checklist

### Infrastructure
- [ ] Production database provisioned and secured
- [ ] Redis cache configured
- [ ] SSL certificates installed
- [ ] DNS records configured
- [ ] Environment variables set

### Security
- [ ] All secrets in secure vault
- [ ] CORS configured correctly
- [ ] Rate limiting enabled
- [ ] Input validation on all endpoints
- [ ] SQL injection protection verified

### Monitoring
- [ ] Sentry error tracking configured
- [ ] Application logs aggregated
- [ ] Uptime monitoring (Pingdom/UptimeRobot)
- [ ] Database monitoring
- [ ] Alert thresholds set

### Performance
- [ ] CDN configured for static assets
- [ ] Image optimization enabled
- [ ] Database indexes verified
- [ ] Cache headers configured
- [ ] Lighthouse score > 90

### Business
- [ ] Stripe webhooks configured
- [ ] Email service connected
- [ ] Analytics tracking
- [ ] Terms of service and privacy policy
- [ ] Support contact configured

### Final Testing
- [ ] Full user flow tested (register → create → publish)
- [ ] Payment flow tested with test cards
- [ ] Custom domain verified
- [ ] Mobile responsive tested
- [ ] Cross-browser testing
```

### Deliverables
- [ ] Backend deployed and running
- [ ] Frontend deployed to Vercel
- [ ] SSL configured on all domains
- [ ] Monitoring and alerting active
- [ ] Successful end-to-end test in production
- [ ] Launch announcement ready

---

## Summary

This 14-day implementation plan provides a structured approach to building a complete Landing Page Builder SaaS. Each day builds upon the previous, with clear goals, specific tasks, and code snippets for critical components.

**Total Estimated Lines of Code**: ~15,000-20,000
**Key Technologies**: Next.js, NestJS, PostgreSQL, Prisma, Stripe, AWS S3, @dnd-kit

**Post-Launch Priorities**:
1. User feedback collection
2. Performance optimization
3. Feature requests evaluation
4. Scale infrastructure as needed
