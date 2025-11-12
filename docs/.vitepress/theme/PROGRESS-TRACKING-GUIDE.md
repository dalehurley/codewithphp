# Progress Tracking System

This guide explains how to use the progress tracking system with localStorage persistence.

## Overview

The progress tracking system allows learners to:

- Mark chapters as complete/incomplete
- View progress per series (e.g., "5 / 25 chapters")
- See progress bars with percentage completion
- Reset progress for individual series or all series
- Persist progress in localStorage (survives browser refresh)
- Export/import progress data

## Components

### 1. ProgressTracker

Displays a progress bar for a series. Shows completion count, percentage, and a reset button.

**Usage in Series Overview Pages:**

```vue
<ProgressTracker
  seriesId="php-basics"
  :totalChapters="25"
  title="Your Progress"
/>
```

**Props:**

- `seriesId` (string, required): Unique identifier for the series
- `totalChapters` (number, required): Total number of chapters in the series
- `title` (string, optional): Header text (default: "Your Progress")

**Example:**

```vue
<ProgressTracker seriesId="ai-ml-php-developers" :totalChapters="25" />
```

### 2. ChapterCheckbox

A checkbox that allows users to mark a chapter as complete on individual chapter pages.

**Usage in Chapter Pages:**

```vue
<ChapterCheckbox
  seriesId="php-basics"
  chapterId="01-variables-data-types"
  label="Mark this chapter as complete"
/>
```

**Props:**

- `seriesId` (string, required): The series this chapter belongs to
- `chapterId` (string, required): Unique identifier for the chapter (e.g., "01-variables")
- `label` (string, optional): Text label for the checkbox (default: "Mark as complete")

**Example:**

```vue
<ChapterCheckbox seriesId="php-basics" chapterId="05-arrays" />
```

## Composable: useProgress()

For advanced use cases, you can use the `useProgress()` composable directly:

```typescript
import { useProgress } from "../composables/useProgress";

const {
  progress,
  toggleChapterCompletion,
  markChapterComplete,
  markChapterIncomplete,
  getSeriesProgress,
  isChapterComplete,
  setTotalChapters,
  resetSeriesProgress,
  resetAllProgress,
  exportProgress,
  importProgress,
} = useProgress();

// Example: Check if a chapter is complete
const completed = isChapterComplete("php-basics", "01-variables");

// Example: Mark a chapter complete
markChapterComplete("php-basics", "01-variables");

// Example: Get series progress
const phpBasicsProgress = getSeriesProgress("php-basics");
console.log(phpBasicsProgress);
// {
//   seriesId: 'php-basics',
//   totalChapters: 25,
//   completedChapters: 5,
//   chapters: {
//     '01-variables': true,
//     '02-control-structures': true,
//     ...
//   }
// }
```

## Storage Format

Progress is stored in localStorage under the key `codewithphp_progress` as JSON:

```json
{
  "php-basics": {
    "seriesId": "php-basics",
    "totalChapters": 25,
    "completedChapters": 5,
    "chapters": {
      "00-setting-up": true,
      "01-variables-data-types": true,
      "02-control-structures": true,
      "03-functions": true,
      "04-strings": true
    }
  },
  "ai-ml-php-developers": {
    "seriesId": "ai-ml-php-developers",
    "totalChapters": 25,
    "completedChapters": 2,
    "chapters": {
      "01-introduction": true,
      "02-setup": true
    }
  }
}
```

## Features

### Auto-save

Progress is automatically saved to localStorage whenever a chapter is marked complete/incomplete.

### Progress Percentage

Progress bars show completion percentage and change color when 100% complete:

- In progress: Teal gradient
- Complete: Green gradient with pulsing animation

### Reset Functionality

- **Per-series reset**: Click "Reset" button in ProgressTracker (requires confirmation)
- **Global reset**: Use `resetAllProgress()` from composable (requires confirmation)

### Export/Import

Export progress data to share across devices:

```typescript
// Export
const data = exportProgress();
console.log(data); // JSON string

// Import
importProgress(data); // Returns true if successful
```

## Adding to New Series

1. Add ProgressTracker to the series overview page:

```vue
<ProgressTracker seriesId="new-series-slug" :totalChapters="15" />
```

2. Add ChapterCheckbox to each chapter page:

```vue
<ChapterCheckbox seriesId="new-series-slug" chapterId="chapter-slug" />
```

## Styling

Both components use the unified color scheme from `custom.css`:

- Primary teal for progress bars
- Green for completed state
- Responsive design for mobile/desktop
- Dark mode support

## Browser Compatibility

- Uses localStorage (supported in all modern browsers)
- Graceful fallback if localStorage is unavailable (console warning)
- Data persists across browser sessions
- Data is domain-specific (only accessible on codewithphp.com)

## Future Enhancements

Potential improvements:

- Cloud sync with user accounts
- Progress sharing via URL
- Milestone badges/achievements
- Email progress reports
- Analytics dashboard
