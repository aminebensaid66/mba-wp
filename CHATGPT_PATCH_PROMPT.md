# ChatGPT.com Prompt — Implement GitHub Issues as Reviewable Patches

Use this prompt in a ChatGPT.com coding session that can read the GitHub repository. It implements the first coordinated batch—issues **#1 through #5**—with tests and browser screenshots, then returns one patch for review here.

---

## Prompt to copy

You are the senior WordPress engineer implementing the MBA Menuiseries website.

Repository: `https://github.com/aminebensaid66/mba-wp`  
Exact base commit: `bf6683007c42f8efa0b2133fdaf2465f299c0eb9`  
Current target issues: `#1, #2, #3, #4, and #5`  
Issue tracker: `https://github.com/aminebensaid66/mba-wp/issues`

Your primary deliverable is a complete, tested patch file for all five target issues. Another coding agent will inspect and apply it locally. Do not push commits, open pull requests, close issues, modify GitHub metadata, or change the remote repository.

### Required context

Before implementing anything:

1. Read issues #1, #2, #3, #4, and #5 completely, including their scope, dependencies, acceptance criteria, and verification notes.
2. Read these repository files completely:
   - `PROJECT.md`
   - `MBA_MENUISERIES_WEBSITE_SPEC.md`
   - `README.md`
   - `docs/architecture.md`
   - `docs/client-input-required.md`
   - all existing code relevant to the issue
3. Inspect the repository at the exact base commit above. Do not generate a patch against another revision.
4. Resolve dependencies within the batch in this order: #1 → #2 → #3 and #4 → #5. If an external dependency is genuinely unavailable, finish every non-blocked part and report the exact blocker.

### Approved temporary catalogue and photography source

The client states that MBA has permission from **ALUMED** to use ALUMED photographs temporarily for development and testing:

- Source website: `https://alumed.tn/`
- Permission stated by the MBA project owner in the development request
- Purpose: local/staging sample catalogue and visual testing until MBA supplies its own photographs

You may use ALUMED's public product-category structure as the initial sample catalogue:

- Menuiserie battante
- Menuiserie coulissante
- Façades continues
- Volets roulants
- Brise-soleil
- Moustiquaires en aluminium
- Cloisons et agencement
- Garde-corps
- Rampes d'escalier
- Pergolas en aluminium
- Stores à bras invisibles

Rules for using this permission:

- Use only photographs hosted on `alumed.tn`; do not take media from unrelated aluminium websites.
- Download temporary assets into a clearly named development/sample-media location. Never hotlink production pages to ALUMED.
- Optimize sample images to reasonable WebP dimensions and file sizes before including them.
- Add `docs/sample-media-sources.md` listing each local filename, exact source page/image URL, retrieval date, permission note, purpose, and a `replace before final launch` status.
- Mark the sample catalogue and media as temporary in seed/import tooling and documentation—not as MBA's completed work.
- Do not copy ALUMED's company story, statistics, contact data, partners, certifications, testimonials, legal text, SEO copy, or technical/performance claims.
- Write original neutral sample descriptions. The product categories may match; the wording must not impersonate ALUMED or assert unverified MBA capabilities.
- Never present an ALUMED project photo as an MBA realization or customer project.
- Make every temporary photograph replaceable from WordPress Media Library and the product editing screen.
- Add a launch check that fails or clearly warns while temporary ALUMED media remains configured.
- If the source blocks downloading or an image's provenance cannot be recorded precisely, use a neutral local placeholder instead of bypassing controls or guessing.

### Product requirement that applies everywhere

The MBA owner must be able to update photographs and business content from WordPress without editing code or page templates. Preserve this architecture in every implementation:

- products, projects, FAQs, testimonials, partners, articles, and business settings remain CMS-managed;
- product cover images and galleries are replaceable and reorderable;
- project cover, before, during, and after photographs are replaceable and reorderable;
- templates work when optional images or fields are empty;
- templates accept different owner-uploaded image orientations without breaking;
- admin fields include useful image ratio, dimension, format, file-size, caption, alt-text, and consent guidance where relevant;
- business data belongs in the site plugin or global settings, not hard-coded into the theme;
- no competitor media or copy is used;
- no certification, partner, warranty, review, price, location, experience, or performance claim is invented.

### Engineering requirements

- Implement all acceptance criteria for issues #1–#5, not a partial demonstration.
- Preserve the separation between the `mba-site-core` functionality plugin and `mba-menuiseries` block theme.
- Follow current WordPress APIs, PHP 8.1 compatibility, WordPress coding standards, secure input handling, output escaping, capability checks, nonces, REST permissions, and translation readiness.
- Prefer native Gutenberg/block-theme behavior and structured CMS fields.
- Do not add Elementor, WPBakery, Divi, or another page builder.
- Keep dependencies minimal and justify every new dependency in repository documentation.
- Do not commit generated dependencies, secrets, uploads, database files, build output, IDE files, or premium plugin archives.
- Add meaningful automated tests for behavior introduced by the issue.
- Update README/docs when setup, commands, architecture, or editor behavior changes.
- Preserve existing user code unless the issue requires a change.
- Avoid unrelated refactors and formatting churn.

### Verification requirements

Use the repository's documented environment and run all checks relevant to all five target issues. At minimum:

1. Validate changed PHP syntax.
2. Validate JSON, YAML, and Compose files that changed.
3. Run applicable automated tests and linters.
4. Confirm a clean WordPress installation can activate the theme and plugin without warnings.
5. Test every acceptance criterion from issues #1–#5 and map evidence to each issue separately.
6. Start the development site and use real browser automation—not HTML inspection alone—to verify the public theme and WordPress editing experience.
7. For UI/admin work, test keyboard behavior and required responsive widths.
8. Create browser screenshots only after the corresponding page has rendered successfully and console/runtime checks are clean.
9. Capture at minimum:
   - homepage at 390 × 844;
   - homepage at 1440 × 1000;
   - Products admin list;
   - Add/Edit Product screen showing structured editable fields and photo guidance;
   - one public sample product view or preview if implemented in this batch;
   - one screenshot demonstrating a validation or empty state relevant to these issues.
10. Inspect each screenshot for clipping, overlap, broken media, inconsistent spacing, unreadable text, incorrect crop behavior, and accidental ALUMED branding/claims.
11. Confirm browser console has no errors on the captured public pages.
12. Review the final diff for secrets, personal data, unsafe uploads, copied text/claims, and unrelated changes.

If a test cannot run in your environment, explain why and provide the exact command the receiving agent must run. Never claim that an unexecuted test passed.

### Patch format

Create one downloadable patch file named:

```text
mba-issues-1-5.patch
```

The file must be a standard UTF-8 unified Git patch generated from the exact base commit, suitable for:

```bash
git apply --check mba-issues-1-5.patch
git apply mba-issues-1-5.patch
```

Preferred generation method after implementation:

```bash
git diff --binary --full-index bf6683007c42f8efa0b2133fdaf2465f299c0eb9 -- . > mba-issues-1-5.patch
```

Rules for the patch:

- Include every source, test, configuration, and documentation change required by the issue.
- Include new text files in the diff.
- Do not include the `.patch` file inside itself.
- Do not include commits that were already present at the base revision.
- Do not include secrets, `.env`, uploads, databases, dependencies, caches, or unrelated files.
- Binary files must be limited to optimized, temporary ALUMED sample photos whose exact provenance is documented. The binary diff must apply successfully. Do not include screenshots in the source patch.
- Ensure paths are relative to the repository root.
- Ensure the patch passes `git apply --check` against a fresh checkout of the exact base commit.

### Screenshot deliverable

Also create a separate downloadable archive named:

```text
mba-issues-1-5-screenshots.zip
```

Use descriptive filenames such as `homepage-mobile-390.png` and `product-editor-fields.png`. Include `SCREENSHOTS.md` in the archive with viewport, URL/screen, test data used, what was verified, and any observed limitation for every image. Screenshots are verification artifacts and must not be added to the website source patch.

### Final response format

Return:

1. A downloadable attachment named `mba-issues-1-5.patch` containing the full patch. Do not truncate it and do not replace it with selected snippets.
2. A downloadable attachment named `mba-issues-1-5-screenshots.zip` containing the required screenshots and manifest.
3. A concise implementation report containing:
   - target issues and base commit;
   - files added, changed, or deleted;
   - acceptance criteria completed;
   - commands run and their real results;
   - tests not run and the reason;
   - assumptions, risks, or blockers;
   - exact local verification commands for the receiving agent.

Do not paste thousands of lines of patch text into the explanation when you can provide the `.patch` attachment. Do not say the batch is complete unless every acceptance criterion in issues #1–#5 is implemented and verified. Begin by inspecting the exact repository revision and all five issues, then implement them in dependency order, test the running site, inspect the screenshots, and produce both artifacts.

---

## Using the prompt for the next batch

After issues `#1–#5` are reviewed and applied, update these values before the next ChatGPT session:

```text
Exact base commit: <the new commit SHA after the previous patch is committed>
Current target issues: #<next five compatible issues>
Patch filename: mba-issues-<first>-<last>.patch
```

Never generate the next batch against the old `bf66830` base after this patch has changed the codebase. Every batch must use the latest committed revision to prevent conflicts and duplicated changes. Keep dependency-sensitive or very large issues in smaller batches when necessary.

## Applying a returned patch here

Attach the `.patch` file in this conversation. It will be checked before application using a workflow equivalent to:

```bash
git status --short
git apply --check /path/to/mba-issues-1-5.patch
git apply /path/to/mba-issues-1-5.patch
```

After application, the implementation and tests should be reviewed before committing or closing the GitHub issue.
