# MCP stdout Protocol Fix

## Problem

The MCP server was outputting `console.log()` statements to **stdout**, which broke the JSON-RPC protocol. MCP servers communicate exclusively via JSON-RPC over stdio, so any non-JSON output to stdout causes parsing errors.

### Error Symptoms

```
Unexpected token '�', "🧠 Content"... is not valid JSON
Unexpected token 'c', "  contentTyp"... is not valid JSON
```

These errors occurred because:
1. `console.log()` writes to **stdout**
2. MCP client expects only JSON-RPC messages on stdout
3. Any text output breaks the protocol

## Solution

Redirected all `console.log()` statements to `console.error()` which writes to **stderr** instead of stdout.

### Files Fixed

1. **`src/prompt-generator.js`**
   - Content analysis logging
   - Cliché replacement logging
   - Prompt mutation logging
   - Meta-prompt generation logging

2. **`src/generator.js`**
   - Image generation progress
   - Success messages

3. **`src/image-processor.js`**
   - Image processing progress
   - File save confirmations

### Changes Made

**Before:**
```javascript
console.log('🧠 Content analysis:', {...});
console.log(`Generating ${count} image(s)...`);
```

**After:**
```javascript
// Log to stderr to avoid breaking MCP JSON-RPC protocol (stdout is for JSON only)
console.error('🧠 Content analysis:', {...});
console.error(`Generating ${count} image(s)...`);
```

## Why This Works

- **stdout**: Reserved for JSON-RPC protocol messages only
- **stderr**: Safe for logging/debugging output
- **console.error()**: Writes to stderr, doesn't interfere with protocol

## Files NOT Changed

- **`src/cli.js`**: Uses `console.log()` - this is fine because CLI doesn't use MCP protocol
- **`src/mcp-server.js`**: Uses `console.warn()` - warnings go to stderr by default

## Testing

After this fix, the MCP server should:
- ✅ Communicate properly via JSON-RPC
- ✅ No "Unexpected token" errors
- ✅ Logs still visible in stderr for debugging
- ✅ All functionality preserved

## Verification

```bash
# Check for remaining console.log in MCP-related files
grep -r "console\.log" src/prompt-generator.js src/generator.js src/image-processor.js

# Should return nothing (all changed to console.error)
```

---

**Status:** ✅ Fixed - All stdout logging redirected to stderr

