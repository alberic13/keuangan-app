import re
import html
from pathlib import Path

# Paths
WORKSPACE_DIR = Path(r"c:\xampp\php\www\keuangan-app")
MD_PATH = WORKSPACE_DIR / "docs" / "google-drive-setup.md"
HTML_PATH = WORKSPACE_DIR / "docs" / "google-drive-setup.html"

def escape_html(text):
    return html.escape(text)

def inline_formatting(text):
    # Escape HTML characters first to avoid breaking formatting
    # but do not escape already processed HTML tags
    # Let's do regex replacements for markdown inline styles
    
    # Code tags: `code`
    text = re.sub(r'`([^`]+)`', r'<code>\1</code>', text)
    
    # Bold tags: **bold**
    text = re.sub(r'\*\*([^*]+)\*\*', r'<strong>\1</strong>', text)
    
    # Links: [text](url)
    text = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', r'<a href="\2" target="_blank">\1</a>', text)
    
    # Plain URL autolinks: https://...
    # Avoid replacing URLs inside href="..."
    # A simple way is to match https:// outside of quotes
    text = re.sub(r'(?<!href=")(https?://[^\s<]+)', r'<a href="\1" target="_blank">\1</a>', text)
    
    return text

def parse_markdown(md_text):
    lines = md_text.splitlines()
    html_lines = []
    
    in_code_block = False
    code_lang = ""
    code_content = []
    
    in_list = None # 'ul', 'ol', or None
    list_level = 0
    
    in_table = False
    table_headers = []
    table_rows = []
    
    in_paragraph = False
    paragraph_text = []

    def close_paragraph():
        nonlocal in_paragraph, paragraph_text
        if in_paragraph:
            text = " ".join(paragraph_text)
            text_stripped = text.strip()
            
            # Check for Callouts / Alerts starting with "Catatan:" or "Poin penting:"
            if text_stripped.startswith("Catatan:"):
                html_lines.append(f'<div class="callout callout-note"><span class="callout-icon">💡</span><div class="callout-content"><strong>Catatan:</strong> {inline_formatting(text_stripped[8:])}</div></div>')
            elif text_stripped.startswith("Poin penting untuk PPT:"):
                html_lines.append(f'<div class="callout callout-info"><span class="callout-icon">📌</span><div class="callout-content"><strong>Poin penting untuk PPT:</strong> {inline_formatting(text_stripped[23:])}</div></div>')
            elif text_stripped.startswith("Contoh penjelasan saat presentasi:"):
                html_lines.append(f'<div class="callout callout-presentation"><span class="callout-icon">🗣️</span><div class="callout-content"><strong>Penjelasan Presentasi:</strong> {inline_formatting(text_stripped[34:])}</div></div>')
            elif text_stripped:
                html_lines.append(f'<p>{inline_formatting(text)}</p>')
            paragraph_text = []
            in_paragraph = False

    def close_list():
        nonlocal in_list
        if in_list == 'ul':
            html_lines.append('</ul>')
        elif in_list == 'ol':
            html_lines.append('</ol>')
        in_list = None

    def close_table():
        nonlocal in_table, table_headers, table_rows
        if in_table:
            html_lines.append('<table>')
            if table_headers:
                html_lines.append('<thead><tr>')
                for th in table_headers:
                    html_lines.append(f'<th>{inline_formatting(th)}</th>')
                html_lines.append('</tr></thead>')
            if table_rows:
                html_lines.append('<tbody>')
                for row in table_rows:
                    html_lines.append('<tr>')
                    for cell in row:
                        html_lines.append(f'<td>{inline_formatting(cell)}</td>')
                    html_lines.append('</tr>')
                html_lines.append('</tbody>')
            html_lines.append('</table>')
            table_headers = []
            table_rows = []
            in_table = False

    for line in lines:
        stripped = line.strip()
        
        # Handle Code Blocks
        if stripped.startswith("```"):
            if in_code_block:
                # End of code block
                code_str = "\n".join(code_content)
                # Escape code block content
                code_escaped = escape_html(code_str)
                lang_label = code_lang.upper() if code_lang else "CODE"
                html_lines.append(f'<div class="code-wrapper"><div class="code-header"><span class="code-lang">{lang_label}</span></div><pre><code class="language-{code_lang}">{code_escaped}</code></pre></div>')
                in_code_block = False
                code_content = []
            else:
                # Start of code block
                close_paragraph()
                close_list()
                close_table()
                in_code_block = True
                code_lang = stripped[3:].strip()
            continue
            
        if in_code_block:
            code_content.append(line)
            continue
            
        # Handle Headers
        if stripped.startswith("#"):
            close_paragraph()
            close_list()
            close_table()
            
            level = 0
            while level < len(stripped) and stripped[level] == '#':
                level += 1
            
            header_text = stripped[level:].strip()
            header_text_formatted = inline_formatting(header_text)
            
            html_lines.append(f'<h{level}>{header_text_formatted}</h{level}>')
            continue
            
        # Handle Tables
        if stripped.startswith("|"):
            close_paragraph()
            close_list()
            
            # Split cells
            cells = [c.strip() for c in stripped.split("|")[1:-1]]
            
            # Skip separator line like | --- | --- |
            if all(re.match(r'^:?-+:?$', c) for c in cells):
                in_table = True
                continue
                
            if not in_table:
                table_headers = cells
                in_table = True
            else:
                table_rows.append(cells)
            continue
        elif in_table:
            close_table()
            
        # Handle Lists
        # Unordered: - or *
        # Ordered: 1. or 2. etc.
        ul_match = re.match(r'^[\-\*]\s+(.*)$', stripped)
        ol_match = re.match(r'^(\d+)\.\s+(.*)$', stripped)
        
        if ul_match:
            close_paragraph()
            close_table()
            if in_list != 'ul':
                close_list()
                html_lines.append('<ul>')
                in_list = 'ul'
            item_text = ul_match.group(1)
            html_lines.append(f'<li>{inline_formatting(item_text)}</li>')
            continue
        elif ol_match:
            close_paragraph()
            close_table()
            if in_list != 'ol':
                close_list()
                html_lines.append('<ol>')
                in_list = 'ol'
            item_text = ol_match.group(2)
            html_lines.append(f'<li>{inline_formatting(item_text)}</li>')
            continue
        
        # Empty Line
        if not stripped:
            close_paragraph()
            close_list()
            close_table()
            continue
            
        # Paragraph Text
        if not in_code_block and not in_table and in_list is None:
            if not in_paragraph:
                in_paragraph = True
            paragraph_text.append(stripped)

    # Clean up any open tags at the end
    close_paragraph()
    close_list()
    close_table()
    
    return "\n".join(html_lines)

# Beautiful, high-end, premium template
HTML_TEMPLATE = """<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Google Drive via Google Apps Script</title>
    <!-- Modern Premium Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-heading: #0f172a;
            --text-muted: #64748b;
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --accent: #06b6d4;
            --border: #e2e8f0;
            --code-bg: #0f172a;
            --code-border: #1e293b;
            --code-text: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-page);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--text-main);
            line-height: 1.625;
            font-size: 15px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .container {
            max-width: 840px;
            margin: 2rem auto;
            background: var(--bg-card);
            padding: 3rem 4rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        /* Cover-like Top Section */
        .doc-header {
            margin-bottom: 3rem;
            border-bottom: 2px solid var(--border);
            padding-bottom: 1.5rem;
            position: relative;
        }

        .doc-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .doc-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 1rem;
            font-weight: 500;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Headings */
        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
            color: var(--text-heading);
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        h1 {
            font-size: 2.2rem;
            letter-spacing: -0.03em;
            margin-top: 0;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--text-heading) 40%, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h2 {
            font-size: 1.45rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
            margin-top: 2.5rem;
            letter-spacing: -0.02em;
        }

        h3 {
            font-size: 1.15rem;
            margin-top: 1.8rem;
            color: var(--primary);
        }

        p {
            margin-top: 0;
            margin-bottom: 1.25rem;
        }

        /* Links */
        a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
            border-bottom: 1px solid transparent;
        }
        
        a:hover {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }

        /* Lists */
        ul, ol {
            margin-top: 0;
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        li {
            margin-bottom: 0.5rem;
        }

        li::marker {
            color: var(--primary);
            font-weight: bold;
        }

        /* Code Blocks */
        .code-wrapper {
            margin: 1.5rem 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--code-border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .code-header {
            background-color: #1e293b;
            padding: 0.5rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #334155;
        }

        .code-lang {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.05em;
        }

        pre {
            margin: 0;
            background-color: var(--code-bg);
            padding: 1.2rem;
            overflow-x: auto;
        }

        code {
            font-family: 'Fira Code', 'Courier New', Courier, monospace;
            font-size: 0.85rem;
            color: var(--code-text);
        }

        /* Inline Code */
        p > code, li > code, td > code {
            background-color: #f1f5f9;
            color: #334155;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.88em;
            border: 1px solid #e2e8f0;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
            font-size: 0.9rem;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
        }

        th {
            background-color: var(--text-heading);
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tr:border-bottom {
            border-bottom: 1px solid var(--border);
        }

        td {
            border-bottom: 1px solid var(--border);
        }

        /* Callouts / Alerts */
        .callout {
            display: flex;
            gap: 1rem;
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 1rem 1.25rem;
            border-radius: 0 8px 8px 0;
            margin: 1.5rem 0;
        }

        .callout-icon {
            font-size: 1.25rem;
            line-height: 1;
            user-select: none;
        }

        .callout-content {
            font-size: 0.92rem;
            color: #14532d;
        }

        .callout-note {
            background-color: #e0f2fe;
            border-left-color: #0284c7;
        }
        .callout-note .callout-content {
            color: #0369a1;
        }

        .callout-info {
            background-color: #e0f7fa;
            border-left-color: #00acc1;
        }
        .callout-info .callout-content {
            color: #006064;
        }

        .callout-presentation {
            background-color: #f5f3ff;
            border-left-color: #7c3aed;
        }
        .callout-presentation .callout-content {
            color: #5b21b6;
        }

        /* Print styling rules */
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
                font-size: 12pt;
            }

            .container {
                margin: 0;
                padding: 0;
                box-shadow: none;
                max-width: 100%;
            }

            a {
                color: #000000;
                text-decoration: underline;
            }

            pre, blockquote, table, img, .callout {
                page-break-inside: avoid;
            }

            h1, h2, h3 {
                page-break-after: avoid;
            }

            @page {
                size: A4;
                margin: 20mm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="doc-header">
            <h1>Setup Google Drive via Google Apps Script</h1>
            <div class="doc-meta">
                <div class="meta-item">
                    <span>Dokumen Setup</span>
                </div>
                <div class="meta-item">
                    <span>Aplikasi E-Keuangan</span>
                </div>
                <div class="meta-item">
                    <span>MAN 2 Surakarta</span>
                </div>
            </div>
        </div>
        <div class="doc-content">
            {{CONTENT}}
        </div>
    </div>
</body>
</html>
"""

def main():
    if not MD_PATH.exists():
        print(f"Error: {MD_PATH} not found.")
        return
        
    md_content = MD_PATH.read_text(encoding="utf-8")
    parsed_html = parse_markdown(md_content)
    
    html_output = HTML_TEMPLATE.replace("{{CONTENT}}", parsed_html)
    HTML_PATH.write_text(html_output, encoding="utf-8")
    print(f"Success: Generated beautiful HTML at {HTML_PATH}")

if __name__ == "__main__":
    main()
