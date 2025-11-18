#!/usr/bin/env python3
"""
Generate agent chapters (42-54) from Claude PHP SDK tutorials.
"""

import subprocess
import re
import json

# Map tutorial numbers to directory names
TUTORIAL_DIRS = {
    2: "02-react-basics",
    3: "03-multi-tool-agent",
    4: "04-production-ready",
    5: "05-advanced-react",
    6: "06-agentic-framework",
    7: "07-chain-of-thought",
    8: "08-tree-of-thoughts",
    9: "09-plan-and-execute",
    10: "10-reflection",
    11: "11-hierarchical-agents",
    12: "12-multi-agent-debate",
    13: "13-rag-pattern",
    14: "14-autonomous-agents",
}

# Tutorial mapping: (tutorial_num, chapter_num, title, difficulty, slug)
TUTORIALS = [
    (2, 42, "ReAct Loop Basics", "Intermediate", "react-loop-basics"),
    (3, 43, "Multi-Tool Agent", "Intermediate", "multi-tool-agent"),
    (4, 44, "Production-Ready Agent", "Intermediate", "production-ready-agent"),
    (5, 45, "Advanced ReAct Patterns", "Advanced", "advanced-react-patterns"),
    (6, 46, "Complete Agentic Framework", "Advanced", "complete-agentic-framework"),
    (7, 47, "Chain of Thought (CoT)", "Intermediate", "chain-of-thought"),
    (8, 48, "Tree of Thoughts (ToT)", "Advanced", "tree-of-thoughts"),
    (9, 49, "Plan-and-Execute", "Intermediate", "plan-and-execute"),
    (10, 50, "Reflection & Self-Critique", "Intermediate", "reflection-self-critique"),
    (11, 51, "Hierarchical Agents", "Advanced", "hierarchical-agents"),
    (12, 52, "Multi-Agent Debate", "Advanced", "multi-agent-debate"),
    (13, 53, "RAG Pattern", "Advanced", "rag-pattern"),
    (14, 54, "Autonomous Agents", "Advanced", "autonomous-agents"),
]

BASE_URL = "https://raw.githubusercontent.com/claude-php/Claude-PHP-SDK/main/tutorials"
OUTPUT_DIR = "/workspace/docs/series/claude-php-developers/chapters"

def fetch_tutorial_readme(tutorial_num: int) -> str:
    """Fetch tutorial README from GitHub."""
    dir_name = TUTORIAL_DIRS.get(tutorial_num, f"{tutorial_num:02d}-tutorial")
    url = f"{BASE_URL}/{dir_name}/README.md"
    cmd = f"curl -s '{url}' 2>/dev/null"
    result = subprocess.run(cmd, shell=True, capture_output=True, text=True)
    return result.stdout

def extract_learning_objectives(content: str) -> list:
    """Extract learning objectives from tutorial."""
    objectives = []
    in_objectives = False
    for line in content.split('\n'):
        if 'Learning Objectives' in line or '🎯' in line:
            in_objectives = True
            continue
        if in_objectives and line.strip().startswith('-'):
            obj = line.strip().lstrip('- ').strip()
            if obj:
                objectives.append(obj)
        elif in_objectives and line.strip() and not line.startswith(' '):
            if objectives:
                break
    return objectives[:10]  # Limit to 10

def generate_chapter(tutorial_num: int, chapter_num: int, title: str, 
                     difficulty: str, slug: str) -> str:
    """Generate chapter markdown from tutorial content."""
    
    # Fetch tutorial content
    tutorial_content = fetch_tutorial_readme(tutorial_num)
    
    if not tutorial_content or len(tutorial_content) < 100:
        print(f"Warning: Could not fetch tutorial {tutorial_num}")
        return None
    
    # Extract time estimate
    time_match = re.search(r'\*\*Time:\s*(\d+\s*min(?:utes)?)\*\*', tutorial_content)
    time_estimate = time_match.group(1) if time_match else "45-60 min"
    
    # Extract learning objectives
    objectives = extract_learning_objectives(tutorial_content)
    
    # Determine prerequisites
    prev_chapter = chapter_num - 1
    prerequisites = [
        f"/series/claude-php-developers/chapters/{prev_chapter:02d}-*",
        "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
    ]
    
    # Generate frontmatter
    frontmatter = f"""---
title: "{chapter_num}: {title}"
description: "Build {title.lower()} with Claude PHP SDK. Learn agentic patterns, tool orchestration, and production-ready agent development."
series: "claude-php-developers"
chapter: {chapter_num}
order: {chapter_num}
difficulty: "{difficulty}"
prerequisites:
  - "/series/claude-php-developers/chapters/{prev_chapter:02d}-*"
  - "/series/claude-php-developers/chapters/11-tool-use-fundamentals"
---

![{chapter_num}: {title}](/images/claude-php/chapter-{chapter_num:02d}-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter {chapter_num}</span>
</div>

# Chapter {chapter_num}: {title}

## Overview

This chapter is based on Tutorial {tutorial_num} from the [Claude PHP SDK Tutorial Series](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials). 

**Estimated Time**: {time_estimate}

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter {prev_chapter}** - Previous agent concepts
- ✓ **Completed Chapter 11: Tool Use Fundamentals** - Tool definitions and execution
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude PHP SDK** installed: `composer require claude-php/claude-php-sdk`

## Learning Objectives

By the end of this chapter, you'll be able to:

"""
    
    # Add objectives
    for obj in objectives:
        frontmatter += f"- {obj}\n"
    
    if not objectives:
        frontmatter += "- Implement advanced agentic patterns\n"
        frontmatter += "- Build production-ready agents\n"
        frontmatter += "- Handle complex multi-step workflows\n"
    
    # Add tutorial content (cleaned)
    frontmatter += "\n## Tutorial Content\n\n"
    frontmatter += "> **Note**: This chapter is based on the [Claude PHP SDK Tutorial {tutorial_num}](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*).\n"
    frontmatter += "> For the complete tutorial with working code examples, visit the SDK repository.\n\n"
    
    # Clean and adapt tutorial content
    lines = tutorial_content.split('\n')
    in_code_block = False
    content_lines = []
    
    for i, line in enumerate(lines):
        # Skip frontmatter-like content
        if line.startswith('# Tutorial'):
            continue
        if '**Time:' in line or '**Difficulty:' in line:
            continue
        
        # Convert markdown headers (reduce by 1 level)
        if line.startswith('## '):
            content_lines.append('### ' + line[3:])
        elif line.startswith('### '):
            content_lines.append('#### ' + line[4:])
        elif line.startswith('#### '):
            content_lines.append('##### ' + line[5:])
        else:
            content_lines.append(line)
        
        # Limit content length
        if len(content_lines) > 200:
            break
    
    frontmatter += '\n'.join(content_lines[:200])
    
    # Add footer
    frontmatter += f"""

## Next Steps

Continue to the next chapter in the agent series, or explore related topics:

- **[Chapter {chapter_num + 1}](/series/claude-php-developers/chapters/{chapter_num + 1:02d}-*)** - Next agent chapter
- **[Chapter 33: Multi-Agent Systems](/series/claude-php-developers/chapters/33-multi-agent-systems)** - Advanced coordination
- **[Claude PHP SDK Tutorials](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials)** - Complete tutorial series

## Further Reading

- [Claude PHP SDK Repository](https://github.com/claude-php/Claude-PHP-SDK) - Source code and examples
- [Tutorial {tutorial_num} Source](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*) - Original tutorial

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="{chapter_num}"
  label="I've completed this chapter!"
/>

---

Continue to [Chapter {chapter_num + 1}](/series/claude-php-developers/chapters/{chapter_num + 1:02d}-*) or explore [all chapters](/series/claude-php-developers).

## 💻 Code Samples

Code examples for this chapter are available in the Claude PHP SDK repository:

**[View Tutorial {tutorial_num} Code](https://github.com/claude-php/Claude-PHP-SDK/tree/main/tutorials/{tutorial_num:02d}-*)**

Clone and run locally:
```bash
git clone https://github.com/claude-php/Claude-PHP-SDK.git
cd Claude-PHP-SDK/tutorials/{tutorial_num:02d}-*
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php *.php
```
"""
    
    return frontmatter

def main():
    """Generate all agent chapters."""
    import os
    
    for tut_num, ch_num, title, difficulty, slug in TUTORIALS:
        print(f"Generating Chapter {ch_num}: {title}...")
        
        content = generate_chapter(tut_num, ch_num, title, difficulty, slug)
        
        if content:
            filename = f"{OUTPUT_DIR}/{ch_num:02d}-{slug}.md"
            with open(filename, 'w') as f:
                f.write(content)
            print(f"  ✓ Created {filename}")
        else:
            print(f"  ✗ Failed to generate Chapter {ch_num}")

if __name__ == '__main__':
    main()
