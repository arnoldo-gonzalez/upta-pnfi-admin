## Plan: Preservar formato en placeholders partidos

### Objective
When replacing placeholder text in .docx documents, preserve formatting (bold, font size, typeface, italics) even when the placeholder `{{KEY}}` is split across multiple `<w:r>` runs in the XML.

### Current State
- `getVariablesFromFile()` already detects placeholders split across runs
- `replaceSplitPlaceholders()` replaces the text but loses formatting (puts new text in plain `<w:t>`)
- `normalizeBrInXml()` fixes `<w:br/>` positioning
- The `replacer` callback only returns plain text, discarding `<w:rPr>`

### What Needs to Change
The `replaceSplitPlaceholders` method needs to:
1. Identify which `<w:r>` runs contain the `{{` and `}}` markers vs the variable name
2. Replace ONLY the `<w:t>` content in the run that has the variable name, preserving its `<w:rPr>` formatting
3. Empty (make `<w:t></w:t>`) the runs that contained `{{` and `}}` markers
4. Handle various splitting scenarios:
   - `{{KEY}}` in single run (already works)
   - `{{` in one run, `KEY}}` in another
   - `{{`, `KEY`, `}}` in three separate runs
   - `{{`, `KEY` split across multiple runs

### Design Decisions

**Approach A: Replace in the run with the variable name**
- Pros: Preserves formatting from that run, simplest
- Cons: What if variable name is also split? What if user wants formatting from a different run?

**Approach B: Consolidate to first run, empty others**
- Find the sequence of runs forming `{{KEY}}`
- The first run that contains non-brace text gets the replacement
- All other runs in the sequence get `<w:t></w:t>`
- Pros: Predictable, handles any splitting
- Cons: Loses formatting from non-first runs (but those were just markers anyway)

**Recommended: Approach B** (consolidate to first content run)

### Implementation Steps

1. **In `replaceSplitPlaceholders`, change the paragraph processing block (lines 142-168)**
   - Instead of: replacing `<w:t>` content with plain text in a new `<w:r>`
   - Do: identify runs forming `{{KEY}}`, consolidate to first content run, empty others

2. **New helper method: `consolidatePlaceholderRuns($innerXml, $replacer, $normKeys)`**
   - Input: the paragraph inner XML string
   - Find all `<w:r>` elements
   - For each `<w:r>`, check if its `<w:t>` contains part of `{{KEY}}` pattern
   - Build a list of run indices that form the placeholder
   - The first run with actual text (not just `{{` or `}}`) gets the replacement value
   - All other runs get `<w:t></w:t>`
   - Return modified inner XML

3. **Modify the `replacer` callback or create new one**
   - The callback now receives the full paragraph inner XML
   - Returns modified inner XML with formatting preserved

### Edge Cases to Handle
- Single run `{{KEY}}` (should still work as before)
- `{{` alone without closing `}}` in the paragraph
- Multiple placeholders in same paragraph
- Placeholder at start/middle/end of paragraph text
- `<w:br/>` already present in some runs

### Risks
- If the variable name is also split across multiple runs, we need to concatenate them or pick one
- If no run contains recognizable text (all are just `{{`/`}}`), fall back to current behavior
- XML validity must be maintained after modifications

### Validation
- Generated .docx files must open in Word without errors
- No `{{KEY}}` placeholders should remain in the output
- `<w:br/>` must be outside `<w:t>` (already handled by `normalizeBrInXml`)
- Formatting from the preserved run should remain visible in Word

### Files to Modify
- `C:\Users\Sebas\Documents\University things\Proyecto 2do año\Proyecto_Código\app\Http\Controllers\ProtocolPreviewController.php`
  - Modify `replaceSplitPlaceholders()` method
  - Possibly add new helper method `consolidatePlaceholderRuns()`

### Testing Checklist
- [ ] Single run `{{PROCESOS}}` → preserves formatting
- [ ] Two runs `{{` + `PROCESOS}}` → formatting preserved from run with PROCESOS
- [ ] Three runs `{{` + `PROCESOS` + `}}` → same
- [ ] Multiple placeholders in same paragraph → each handled independently
- [ ] Placeholder with `\n` (textarea) → `<w:br/>` outside `<w:t>`
- [ ] Regular text fields (no placeholders) → unchanged
- [ ] XML validity → DOMDocument loads without errors

---