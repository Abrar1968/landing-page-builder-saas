# Dashboard View Documentation

## Overview

The dashboard serves as the main control center for users to manage their landing pages, view analytics, and access all platform features.

---

## 1. Dashboard Layout

### Main Structure

```tsx
// components/dashboard/DashboardLayout.tsx
import { ReactNode, useState } from 'react';
import Sidebar from './Sidebar';
import Header from './Header';

interface DashboardLayoutProps {
  children: ReactNode;
}

export default function DashboardLayout({ children }: DashboardLayoutProps) {
  const [sidebarOpen, setSidebarOpen] = useState(false);

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      {/* Main content area */}
      <div className="lg:pl-64">
        <Header onMenuClick={() => setSidebarOpen(true)} />

        <main className="py-6">
          <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}
```

---

## 2. Sidebar Navigation

### Sidebar Component

```tsx
// components/dashboard/Sidebar.tsx
import { Fragment } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import {
  HomeIcon,
  DocumentDuplicateIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  QuestionMarkCircleIcon,
  XMarkIcon,
} from '@heroicons/react/24/outline';

interface SidebarProps {
  isOpen: boolean;
  onClose: () => void;
}

const navigation = [
  { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
  { name: 'Pages', href: '/dashboard/pages', icon: DocumentDuplicateIcon },
  { name: 'Analytics', href: '/dashboard/analytics', icon: ChartBarIcon },
  { name: 'Settings', href: '/dashboard/settings', icon: Cog6ToothIcon },
];

const secondaryNavigation = [
  { name: 'Help & Support', href: '/support', icon: QuestionMarkCircleIcon },
];

export default function Sidebar({ isOpen, onClose }: SidebarProps) {
  const router = useRouter();

  const NavContent = () => (
    <div className="flex h-full flex-col">
      {/* Logo */}
      <div className="flex h-16 shrink-0 items-center px-6">
        <img
          className="h-8 w-auto"
          src="/logo.svg"
          alt="Landing Page Builder"
        />
        <span className="ml-2 text-xl font-bold text-gray-900">PageBuilder</span>
      </div>

      {/* Navigation */}
      <nav className="flex flex-1 flex-col px-4 pb-4">
        <ul role="list" className="flex flex-1 flex-col gap-y-7">
          {/* Primary navigation */}
          <li>
            <ul role="list" className="-mx-2 space-y-1">
              {navigation.map((item) => {
                const isActive = router.pathname === item.href;
                return (
                  <li key={item.name}>
                    <Link
                      href={item.href}
                      className={`
                        group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6
                        ${isActive
                          ? 'bg-indigo-50 text-indigo-600'
                          : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600'
                        }
                      `}
                    >
                      <item.icon
                        className={`h-6 w-6 shrink-0 ${
                          isActive ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600'
                        }`}
                      />
                      {item.name}
                    </Link>
                  </li>
                );
              })}
            </ul>
          </li>

          {/* Secondary navigation */}
          <li className="mt-auto">
            <ul role="list" className="-mx-2 space-y-1">
              {secondaryNavigation.map((item) => (
                <li key={item.name}>
                  <Link
                    href={item.href}
                    className="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-indigo-600"
                  >
                    <item.icon className="h-6 w-6 shrink-0 text-gray-400 group-hover:text-indigo-600" />
                    {item.name}
                  </Link>
                </li>
              ))}
            </ul>
          </li>

          {/* Plan info */}
          <li className="-mx-2">
            <div className="rounded-lg bg-gray-50 p-4">
              <p className="text-sm font-medium text-gray-900">Free Plan</p>
              <p className="mt-1 text-xs text-gray-500">3 of 5 pages used</p>
              <div className="mt-2 h-2 w-full rounded-full bg-gray-200">
                <div className="h-2 w-3/5 rounded-full bg-indigo-600" />
              </div>
              <Link
                href="/dashboard/billing"
                className="mt-3 block text-center text-sm font-semibold text-indigo-600 hover:text-indigo-500"
              >
                Upgrade Plan
              </Link>
            </div>
          </li>
        </ul>
      </nav>
    </div>
  );

  return (
    <>
      {/* Mobile sidebar */}
      <div
        className={`
          fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transition-transform duration-300 ease-in-out lg:hidden
          ${isOpen ? 'translate-x-0' : '-translate-x-full'}
        `}
      >
        <div className="absolute right-0 top-0 -mr-12 pt-2">
          <button
            type="button"
            className="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
            onClick={onClose}
          >
            <XMarkIcon className="h-6 w-6 text-white" />
          </button>
        </div>
        <NavContent />
      </div>

      {/* Desktop sidebar */}
      <div className="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
        <div className="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white">
          <NavContent />
        </div>
      </div>
    </>
  );
}
```

---

## 3. Header with User Dropdown

### Header Component

```tsx
// components/dashboard/Header.tsx
import { Fragment } from 'react';
import { Menu, Transition } from '@headlessui/react';
import {
  Bars3Icon,
  BellIcon,
  UserCircleIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon,
} from '@heroicons/react/24/outline';
import { useAuth } from '@/hooks/useAuth';

interface HeaderProps {
  onMenuClick: () => void;
}

export default function Header({ onMenuClick }: HeaderProps) {
  const { user, logout } = useAuth();

  return (
    <header className="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
      {/* Mobile menu button */}
      <button
        type="button"
        className="-m-2.5 p-2.5 text-gray-700 lg:hidden"
        onClick={onMenuClick}
      >
        <Bars3Icon className="h-6 w-6" />
      </button>

      {/* Separator */}
      <div className="h-6 w-px bg-gray-200 lg:hidden" />

      <div className="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        {/* Search */}
        <form className="relative flex flex-1" action="#" method="GET">
          <label htmlFor="search-field" className="sr-only">Search</label>
          <input
            id="search-field"
            className="block h-full w-full border-0 py-0 pl-8 pr-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
            placeholder="Search pages..."
            type="search"
            name="search"
          />
        </form>

        <div className="flex items-center gap-x-4 lg:gap-x-6">
          {/* Notifications */}
          <button
            type="button"
            className="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500"
          >
            <span className="sr-only">View notifications</span>
            <BellIcon className="h-6 w-6" />
          </button>

          {/* Separator */}
          <div className="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" />

          {/* User dropdown */}
          <Menu as="div" className="relative">
            <Menu.Button className="-m-1.5 flex items-center p-1.5">
              <span className="sr-only">Open user menu</span>
              {user?.avatar ? (
                <img
                  className="h-8 w-8 rounded-full bg-gray-50"
                  src={user.avatar}
                  alt=""
                />
              ) : (
                <div className="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                  <span className="text-sm font-medium text-white">
                    {user?.name?.charAt(0) || 'U'}
                  </span>
                </div>
              )}
              <span className="hidden lg:flex lg:items-center">
                <span className="ml-4 text-sm font-semibold leading-6 text-gray-900">
                  {user?.name || 'User'}
                </span>
              </span>
            </Menu.Button>

            <Transition
              as={Fragment}
              enter="transition ease-out duration-100"
              enterFrom="transform opacity-0 scale-95"
              enterTo="transform opacity-100 scale-100"
              leave="transition ease-in duration-75"
              leaveFrom="transform opacity-100 scale-100"
              leaveTo="transform opacity-0 scale-95"
            >
              <Menu.Items className="absolute right-0 z-10 mt-2.5 w-56 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none">
                <div className="px-4 py-3 border-b border-gray-100">
                  <p className="text-sm font-medium text-gray-900">{user?.name}</p>
                  <p className="text-sm text-gray-500 truncate">{user?.email}</p>
                </div>

                <Menu.Item>
                  {({ active }) => (
                    <a
                      href="/dashboard/profile"
                      className={`${
                        active ? 'bg-gray-50' : ''
                      } flex items-center px-4 py-2 text-sm text-gray-700`}
                    >
                      <UserCircleIcon className="mr-3 h-5 w-5 text-gray-400" />
                      Your Profile
                    </a>
                  )}
                </Menu.Item>

                <Menu.Item>
                  {({ active }) => (
                    <a
                      href="/dashboard/settings"
                      className={`${
                        active ? 'bg-gray-50' : ''
                      } flex items-center px-4 py-2 text-sm text-gray-700`}
                    >
                      <Cog6ToothIcon className="mr-3 h-5 w-5 text-gray-400" />
                      Settings
                    </a>
                  )}
                </Menu.Item>

                <div className="border-t border-gray-100 mt-2 pt-2">
                  <Menu.Item>
                    {({ active }) => (
                      <button
                        onClick={logout}
                        className={`${
                          active ? 'bg-gray-50' : ''
                        } flex w-full items-center px-4 py-2 text-sm text-gray-700`}
                      >
                        <ArrowRightOnRectangleIcon className="mr-3 h-5 w-5 text-gray-400" />
                        Sign out
                      </button>
                    )}
                  </Menu.Item>
                </div>
              </Menu.Items>
            </Transition>
          </Menu>
        </div>
      </div>
    </header>
  );
}
```

---

## 4. Dashboard Home

### Stats and Recent Pages

```tsx
// pages/dashboard/index.tsx
import { useState } from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
import {
  DocumentDuplicateIcon,
  EyeIcon,
  CursorArrowRaysIcon,
  ArrowTrendingUpIcon,
} from '@heroicons/react/24/outline';

const stats = [
  { name: 'Total Pages', value: '12', icon: DocumentDuplicateIcon, change: '+2', changeType: 'increase' },
  { name: 'Total Views', value: '4,567', icon: EyeIcon, change: '+12%', changeType: 'increase' },
  { name: 'Conversions', value: '234', icon: CursorArrowRaysIcon, change: '+8%', changeType: 'increase' },
  { name: 'Conversion Rate', value: '5.1%', icon: ArrowTrendingUpIcon, change: '+0.3%', changeType: 'increase' },
];

const recentPages = [
  {
    id: 1,
    name: 'Product Launch',
    status: 'published',
    views: 1234,
    conversions: 89,
    lastModified: '2 hours ago',
    thumbnail: '/thumbnails/page-1.png',
  },
  {
    id: 2,
    name: 'Newsletter Signup',
    status: 'draft',
    views: 0,
    conversions: 0,
    lastModified: '1 day ago',
    thumbnail: '/thumbnails/page-2.png',
  },
  {
    id: 3,
    name: 'Webinar Registration',
    status: 'published',
    views: 567,
    conversions: 45,
    lastModified: '3 days ago',
    thumbnail: '/thumbnails/page-3.png',
  },
];

export default function DashboardHome() {
  return (
    <DashboardLayout>
      {/* Page header */}
      <div className="md:flex md:items-center md:justify-between">
        <div className="min-w-0 flex-1">
          <h2 className="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Dashboard
          </h2>
          <p className="mt-1 text-sm text-gray-500">
            Welcome back! Here's what's happening with your pages.
          </p>
        </div>
        <div className="mt-4 flex md:ml-4 md:mt-0">
          <button
            type="button"
            className="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          >
            Create New Page
          </button>
        </div>
      </div>

      {/* Stats */}
      <div className="mt-8">
        <dl className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {stats.map((stat) => (
            <div
              key={stat.name}
              className="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:px-6 sm:py-6"
            >
              <dt>
                <div className="absolute rounded-md bg-indigo-500 p-3">
                  <stat.icon className="h-6 w-6 text-white" />
                </div>
                <p className="ml-16 truncate text-sm font-medium text-gray-500">
                  {stat.name}
                </p>
              </dt>
              <dd className="ml-16 flex items-baseline">
                <p className="text-2xl font-semibold text-gray-900">{stat.value}</p>
                <p
                  className={`ml-2 flex items-baseline text-sm font-semibold ${
                    stat.changeType === 'increase' ? 'text-green-600' : 'text-red-600'
                  }`}
                >
                  {stat.change}
                </p>
              </dd>
            </div>
          ))}
        </dl>
      </div>

      {/* Recent pages */}
      <div className="mt-8">
        <div className="sm:flex sm:items-center">
          <div className="sm:flex-auto">
            <h3 className="text-lg font-semibold leading-6 text-gray-900">
              Recent Pages
            </h3>
            <p className="mt-1 text-sm text-gray-500">
              Your most recently modified landing pages.
            </p>
          </div>
          <div className="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
            <a
              href="/dashboard/pages"
              className="text-sm font-semibold text-indigo-600 hover:text-indigo-500"
            >
              View all pages &rarr;
            </a>
          </div>
        </div>

        <div className="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {recentPages.map((page) => (
            <div
              key={page.id}
              className="group relative overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow"
            >
              {/* Thumbnail */}
              <div className="aspect-[16/9] bg-gray-100 overflow-hidden">
                <img
                  src={page.thumbnail}
                  alt={page.name}
                  className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200"
                />
              </div>

              {/* Content */}
              <div className="p-4">
                <div className="flex items-center justify-between">
                  <h4 className="text-sm font-semibold text-gray-900 truncate">
                    {page.name}
                  </h4>
                  <span
                    className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
                      page.status === 'published'
                        ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20'
                        : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'
                    }`}
                  >
                    {page.status}
                  </span>
                </div>

                <div className="mt-2 flex items-center text-sm text-gray-500">
                  <EyeIcon className="mr-1 h-4 w-4" />
                  {page.views} views
                  <span className="mx-2">|</span>
                  {page.conversions} conversions
                </div>

                <p className="mt-2 text-xs text-gray-400">
                  Modified {page.lastModified}
                </p>
              </div>

              {/* Hover actions */}
              <div className="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all opacity-0 group-hover:opacity-100">
                <div className="flex gap-2">
                  <button className="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50">
                    Edit
                  </button>
                  <button className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    View
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </DashboardLayout>
  );
}
```

---

## 5. Pages List View

### Full Pages List with Grid, Search, and Pagination

```tsx
// pages/dashboard/pages/index.tsx
import { useState } from 'react';
import DashboardLayout from '@/components/dashboard/DashboardLayout';
import CreatePageModal from '@/components/dashboard/CreatePageModal';
import {
  MagnifyingGlassIcon,
  PlusIcon,
  EllipsisVerticalIcon,
  EyeIcon,
  PencilIcon,
  DocumentDuplicateIcon,
  TrashIcon,
  Squares2X2Icon,
  ListBulletIcon,
} from '@heroicons/react/24/outline';
import { Menu, Transition } from '@headlessui/react';
import { Fragment } from 'react';

interface Page {
  id: number;
  name: string;
  status: 'published' | 'draft';
  views: number;
  conversions: number;
  lastModified: string;
  thumbnail: string;
  url: string;
}

const pages: Page[] = [
  {
    id: 1,
    name: 'Product Launch Campaign',
    status: 'published',
    views: 1234,
    conversions: 89,
    lastModified: '2024-01-15T10:30:00',
    thumbnail: '/thumbnails/page-1.png',
    url: 'product-launch',
  },
  // ... more pages
];

export default function PagesListView() {
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [searchQuery, setSearchQuery] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [statusFilter, setStatusFilter] = useState<'all' | 'published' | 'draft'>('all');

  const itemsPerPage = 9;

  // Filter pages
  const filteredPages = pages.filter((page) => {
    const matchesSearch = page.name.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesStatus = statusFilter === 'all' || page.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  // Paginate
  const totalPages = Math.ceil(filteredPages.length / itemsPerPage);
  const paginatedPages = filteredPages.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  return (
    <DashboardLayout>
      {/* Page header */}
      <div className="sm:flex sm:items-center sm:justify-between">
        <div>
          <h1 className="text-2xl font-bold text-gray-900">Pages</h1>
          <p className="mt-1 text-sm text-gray-500">
            Manage all your landing pages in one place.
          </p>
        </div>
        <div className="mt-4 sm:mt-0">
          <button
            onClick={() => setIsCreateModalOpen(true)}
            className="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          >
            <PlusIcon className="-ml-0.5 mr-1.5 h-5 w-5" />
            Create Page
          </button>
        </div>
      </div>

      {/* Filters and search */}
      <div className="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {/* Search */}
        <div className="relative flex-1 max-w-md">
          <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon className="h-5 w-5 text-gray-400" />
          </div>
          <input
            type="text"
            placeholder="Search pages..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
          />
        </div>

        <div className="flex items-center gap-4">
          {/* Status filter */}
          <select
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value as any)}
            className="rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"
          >
            <option value="all">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
          </select>

          {/* View toggle */}
          <div className="flex rounded-md shadow-sm">
            <button
              onClick={() => setViewMode('grid')}
              className={`relative inline-flex items-center rounded-l-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10 ${
                viewMode === 'grid'
                  ? 'bg-indigo-600 text-white ring-indigo-600'
                  : 'bg-white text-gray-900 hover:bg-gray-50'
              }`}
            >
              <Squares2X2Icon className="h-5 w-5" />
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={`relative -ml-px inline-flex items-center rounded-r-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10 ${
                viewMode === 'list'
                  ? 'bg-indigo-600 text-white ring-indigo-600'
                  : 'bg-white text-gray-900 hover:bg-gray-50'
              }`}
            >
              <ListBulletIcon className="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      {/* Pages grid/list */}
      {viewMode === 'grid' ? (
        <div className="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {paginatedPages.map((page) => (
            <PageGridCard key={page.id} page={page} />
          ))}
        </div>
      ) : (
        <div className="mt-6 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
          <table className="min-w-full divide-y divide-gray-300">
            <thead className="bg-gray-50">
              <tr>
                <th className="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                  Page
                </th>
                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                  Status
                </th>
                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                  Views
                </th>
                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                  Conversions
                </th>
                <th className="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                  Last Modified
                </th>
                <th className="relative py-3.5 pl-3 pr-4 sm:pr-6">
                  <span className="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200 bg-white">
              {paginatedPages.map((page) => (
                <PageListRow key={page.id} page={page} />
              ))}
            </tbody>
          </table>
        </div>
      )}

      {/* Pagination */}
      <div className="mt-6 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg shadow">
        <div className="flex flex-1 justify-between sm:hidden">
          <button
            onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
            disabled={currentPage === 1}
            className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <button
            onClick={() => setCurrentPage(Math.min(totalPages, currentPage + 1))}
            disabled={currentPage === totalPages}
            className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </div>
        <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p className="text-sm text-gray-700">
              Showing{' '}
              <span className="font-medium">{(currentPage - 1) * itemsPerPage + 1}</span>
              {' '}to{' '}
              <span className="font-medium">
                {Math.min(currentPage * itemsPerPage, filteredPages.length)}
              </span>
              {' '}of{' '}
              <span className="font-medium">{filteredPages.length}</span> results
            </p>
          </div>
          <div>
            <nav className="isolate inline-flex -space-x-px rounded-md shadow-sm">
              <button
                onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
                disabled={currentPage === 1}
                className="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
              >
                Previous
              </button>
              {Array.from({ length: totalPages }, (_, i) => i + 1).map((pageNum) => (
                <button
                  key={pageNum}
                  onClick={() => setCurrentPage(pageNum)}
                  className={`relative inline-flex items-center px-4 py-2 text-sm font-semibold ${
                    pageNum === currentPage
                      ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                      : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'
                  }`}
                >
                  {pageNum}
                </button>
              ))}
              <button
                onClick={() => setCurrentPage(Math.min(totalPages, currentPage + 1))}
                disabled={currentPage === totalPages}
                className="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
              >
                Next
              </button>
            </nav>
          </div>
        </div>
      </div>

      {/* Create Page Modal */}
      <CreatePageModal
        isOpen={isCreateModalOpen}
        onClose={() => setIsCreateModalOpen(false)}
      />
    </DashboardLayout>
  );
}

// Page Grid Card Component
function PageGridCard({ page }: { page: Page }) {
  return (
    <div className="group relative overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
      <div className="aspect-[16/9] bg-gray-100 overflow-hidden">
        <img
          src={page.thumbnail}
          alt={page.name}
          className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200"
        />
      </div>

      <div className="p-4">
        <div className="flex items-start justify-between">
          <div className="flex-1 min-w-0">
            <h3 className="text-sm font-semibold text-gray-900 truncate">{page.name}</h3>
            <p className="mt-1 text-xs text-gray-500 truncate">/{page.url}</p>
          </div>
          <PageActionsMenu page={page} />
        </div>

        <div className="mt-3 flex items-center justify-between">
          <span
            className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
              page.status === 'published'
                ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20'
                : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'
            }`}
          >
            {page.status}
          </span>
          <span className="text-xs text-gray-500">{page.views} views</span>
        </div>
      </div>
    </div>
  );
}

// Page List Row Component
function PageListRow({ page }: { page: Page }) {
  return (
    <tr className="hover:bg-gray-50">
      <td className="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
        <div className="flex items-center">
          <div className="h-10 w-16 flex-shrink-0 overflow-hidden rounded bg-gray-100">
            <img src={page.thumbnail} alt="" className="h-full w-full object-cover" />
          </div>
          <div className="ml-4">
            <div className="font-medium text-gray-900">{page.name}</div>
            <div className="text-gray-500">/{page.url}</div>
          </div>
        </div>
      </td>
      <td className="whitespace-nowrap px-3 py-4 text-sm">
        <span
          className={`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${
            page.status === 'published'
              ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20'
              : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'
          }`}
        >
          {page.status}
        </span>
      </td>
      <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{page.views}</td>
      <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{page.conversions}</td>
      <td className="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
        {new Date(page.lastModified).toLocaleDateString()}
      </td>
      <td className="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
        <PageActionsMenu page={page} />
      </td>
    </tr>
  );
}

// Page Actions Menu Component
function PageActionsMenu({ page }: { page: Page }) {
  return (
    <Menu as="div" className="relative inline-block text-left">
      <Menu.Button className="-m-2 flex items-center rounded-full p-2 text-gray-400 hover:text-gray-600">
        <EllipsisVerticalIcon className="h-5 w-5" />
      </Menu.Button>

      <Transition
        as={Fragment}
        enter="transition ease-out duration-100"
        enterFrom="transform opacity-0 scale-95"
        enterTo="transform opacity-100 scale-100"
        leave="transition ease-in duration-75"
        leaveFrom="transform opacity-100 scale-100"
        leaveTo="transform opacity-0 scale-95"
      >
        <Menu.Items className="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
          <div className="py-1">
            <Menu.Item>
              {({ active }) => (
                <a
                  href={`/dashboard/pages/${page.id}/edit`}
                  className={`${active ? 'bg-gray-100' : ''} flex items-center px-4 py-2 text-sm text-gray-700`}
                >
                  <PencilIcon className="mr-3 h-5 w-5 text-gray-400" />
                  Edit
                </a>
              )}
            </Menu.Item>
            <Menu.Item>
              {({ active }) => (
                <a
                  href={`/p/${page.url}`}
                  target="_blank"
                  className={`${active ? 'bg-gray-100' : ''} flex items-center px-4 py-2 text-sm text-gray-700`}
                >
                  <EyeIcon className="mr-3 h-5 w-5 text-gray-400" />
                  View Live
                </a>
              )}
            </Menu.Item>
            <Menu.Item>
              {({ active }) => (
                <button
                  className={`${active ? 'bg-gray-100' : ''} flex w-full items-center px-4 py-2 text-sm text-gray-700`}
                >
                  <DocumentDuplicateIcon className="mr-3 h-5 w-5 text-gray-400" />
                  Duplicate
                </button>
              )}
            </Menu.Item>
            <div className="border-t border-gray-100" />
            <Menu.Item>
              {({ active }) => (
                <button
                  className={`${active ? 'bg-red-50' : ''} flex w-full items-center px-4 py-2 text-sm text-red-600`}
                >
                  <TrashIcon className="mr-3 h-5 w-5 text-red-400" />
                  Delete
                </button>
              )}
            </Menu.Item>
          </div>
        </Menu.Items>
      </Transition>
    </Menu>
  );
}
```

---

## 6. Create Page Modal

```tsx
// components/dashboard/CreatePageModal.tsx
import { Fragment, useState } from 'react';
import { Dialog, Transition, RadioGroup } from '@headlessui/react';
import { XMarkIcon, CheckIcon } from '@heroicons/react/24/outline';

interface CreatePageModalProps {
  isOpen: boolean;
  onClose: () => void;
}

const templates = [
  {
    id: 'blank',
    name: 'Blank Page',
    description: 'Start from scratch with a blank canvas',
    image: '/templates/blank.png',
  },
  {
    id: 'product',
    name: 'Product Launch',
    description: 'Perfect for launching new products',
    image: '/templates/product.png',
  },
  {
    id: 'signup',
    name: 'Newsletter Signup',
    description: 'Grow your email list',
    image: '/templates/signup.png',
  },
  {
    id: 'webinar',
    name: 'Webinar Registration',
    description: 'Capture registrations for events',
    image: '/templates/webinar.png',
  },
];

export default function CreatePageModal({ isOpen, onClose }: CreatePageModalProps) {
  const [pageName, setPageName] = useState('');
  const [selectedTemplate, setSelectedTemplate] = useState(templates[0]);
  const [isLoading, setIsLoading] = useState(false);

  const handleCreate = async () => {
    if (!pageName.trim()) return;

    setIsLoading(true);
    try {
      // API call to create page
      await new Promise(resolve => setTimeout(resolve, 1000));
      onClose();
      // Navigate to editor
    } catch (error) {
      console.error('Failed to create page:', error);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <Transition appear show={isOpen} as={Fragment}>
      <Dialog as="div" className="relative z-50" onClose={onClose}>
        <Transition.Child
          as={Fragment}
          enter="ease-out duration-300"
          enterFrom="opacity-0"
          enterTo="opacity-100"
          leave="ease-in duration-200"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <div className="fixed inset-0 bg-black bg-opacity-25" />
        </Transition.Child>

        <div className="fixed inset-0 overflow-y-auto">
          <div className="flex min-h-full items-center justify-center p-4 text-center">
            <Transition.Child
              as={Fragment}
              enter="ease-out duration-300"
              enterFrom="opacity-0 scale-95"
              enterTo="opacity-100 scale-100"
              leave="ease-in duration-200"
              leaveFrom="opacity-100 scale-100"
              leaveTo="opacity-0 scale-95"
            >
              <Dialog.Panel className="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <div className="flex items-center justify-between">
                  <Dialog.Title as="h3" className="text-lg font-semibold leading-6 text-gray-900">
                    Create New Page
                  </Dialog.Title>
                  <button
                    type="button"
                    className="rounded-md bg-white text-gray-400 hover:text-gray-500"
                    onClick={onClose}
                  >
                    <XMarkIcon className="h-6 w-6" />
                  </button>
                </div>

                <div className="mt-6">
                  {/* Page name input */}
                  <div>
                    <label htmlFor="page-name" className="block text-sm font-medium text-gray-700">
                      Page Name
                    </label>
                    <input
                      type="text"
                      id="page-name"
                      value={pageName}
                      onChange={(e) => setPageName(e.target.value)}
                      placeholder="My Landing Page"
                      className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>

                  {/* Template selection */}
                  <div className="mt-6">
                    <label className="block text-sm font-medium text-gray-700 mb-3">
                      Choose a Template
                    </label>
                    <RadioGroup value={selectedTemplate} onChange={setSelectedTemplate}>
                      <div className="grid grid-cols-2 gap-4">
                        {templates.map((template) => (
                          <RadioGroup.Option
                            key={template.id}
                            value={template}
                            className={({ active, checked }) =>
                              `${active ? 'ring-2 ring-indigo-600 ring-offset-2' : ''}
                               ${checked ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'}
                               relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none`
                            }
                          >
                            {({ checked }) => (
                              <>
                                <div className="flex w-full items-center justify-between">
                                  <div className="flex items-center">
                                    <div className="text-sm">
                                      <RadioGroup.Label
                                        as="p"
                                        className={`font-medium ${
                                          checked ? 'text-indigo-900' : 'text-gray-900'
                                        }`}
                                      >
                                        {template.name}
                                      </RadioGroup.Label>
                                      <RadioGroup.Description
                                        as="span"
                                        className={`inline ${
                                          checked ? 'text-indigo-700' : 'text-gray-500'
                                        }`}
                                      >
                                        {template.description}
                                      </RadioGroup.Description>
                                    </div>
                                  </div>
                                  {checked && (
                                    <div className="shrink-0 text-indigo-600">
                                      <CheckIcon className="h-6 w-6" />
                                    </div>
                                  )}
                                </div>
                              </>
                            )}
                          </RadioGroup.Option>
                        ))}
                      </div>
                    </RadioGroup>
                  </div>
                </div>

                <div className="mt-6 flex justify-end gap-3">
                  <button
                    type="button"
                    className="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    onClick={onClose}
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    disabled={!pageName.trim() || isLoading}
                    onClick={handleCreate}
                    className="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {isLoading ? (
                      <>
                        <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        Creating...
                      </>
                    ) : (
                      'Create Page'
                    )}
                  </button>
                </div>
              </Dialog.Panel>
            </Transition.Child>
          </div>
        </div>
      </Dialog>
    </Transition>
  );
}
```

---

## 7. Responsive Design

### Breakpoint Strategy

| Breakpoint | Width | Target Device |
|------------|-------|---------------|
| sm | 640px | Mobile landscape |
| md | 768px | Tablet |
| lg | 1024px | Desktop |
| xl | 1280px | Large desktop |
| 2xl | 1536px | Extra large |

### Key Responsive Patterns

```css
/* Sidebar: Hidden on mobile, fixed on desktop */
.sidebar {
  @apply fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full transition-transform lg:translate-x-0;
}

/* Main content: Full width on mobile, offset on desktop */
.main-content {
  @apply lg:pl-64;
}

/* Grid: 1 column mobile, 2 tablet, 3 desktop */
.page-grid {
  @apply grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3;
}

/* Header elements: Stack on mobile, inline on desktop */
.header-content {
  @apply flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4;
}

/* Hide on mobile, show on desktop */
.desktop-only {
  @apply hidden lg:block;
}

/* Show on mobile, hide on desktop */
.mobile-only {
  @apply lg:hidden;
}
```

### Mobile Navigation Pattern

- Hamburger menu triggers sidebar overlay
- Sidebar slides in from left with backdrop
- Touch-friendly tap targets (minimum 44px)
- Simplified navigation for small screens

---

## Dependencies

```json
{
  "dependencies": {
    "@headlessui/react": "^1.7.17",
    "@heroicons/react": "^2.0.18",
    "next": "^14.0.0",
    "react": "^18.2.0",
    "tailwindcss": "^3.3.0"
  }
}
```

---

## File Structure

```
components/
  dashboard/
    DashboardLayout.tsx
    Sidebar.tsx
    Header.tsx
    CreatePageModal.tsx
pages/
  dashboard/
    index.tsx          # Dashboard home
    pages/
      index.tsx        # Pages list view
      [id]/
        edit.tsx       # Page editor
```
