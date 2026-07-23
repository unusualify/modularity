<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:sm="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  exclude-result-prefixes="sm xhtml">

  <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes" doctype-system="about:legacy-compat"/>

  <xsl:template match="/">
    <html lang="en">
      <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>XML Sitemap</title>
        <style type="text/css">
          :root {
            --bg: #f6f7f9;
            --surface: #ffffff;
            --text: #1a1d23;
            --muted: #5c6570;
            --border: #e2e5ea;
            --accent: #0b5fff;
            --row-alt: #fafbfc;
          }
          * { box-sizing: border-box; }
          body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.45;
          }
          main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.25rem 3rem;
          }
          header {
            margin-bottom: 1.5rem;
          }
          h1 {
            margin: 0 0 0.35rem;
            font-size: 1.5rem;
            font-weight: 650;
            letter-spacing: -0.02em;
          }
          .meta {
            color: var(--muted);
            font-size: 0.95rem;
          }
          .note {
            margin: 1rem 0 1.25rem;
            padding: 0.75rem 1rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--muted);
            font-size: 0.875rem;
          }
          .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: auto;
          }
          table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
          }
          th, td {
            padding: 0.7rem 0.9rem;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid var(--border);
          }
          th {
            background: #eef1f5;
            color: var(--muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            position: sticky;
            top: 0;
          }
          tr:last-child td { border-bottom: none; }
          tbody tr:nth-child(even) { background: var(--row-alt); }
          a {
            color: var(--accent);
            text-decoration: none;
            word-break: break-all;
          }
          a:hover { text-decoration: underline; }
          .alts {
            margin-top: 0.35rem;
            color: var(--muted);
            font-size: 0.8rem;
          }
          .alts span { white-space: nowrap; }
          .alts a { color: var(--muted); }
          .num { white-space: nowrap; font-variant-numeric: tabular-nums; }
          @media (max-width: 720px) {
            th.col-freq, td.col-freq,
            th.col-prio, td.col-prio { display: none; }
          }
        </style>
      </head>
      <body>
        <main>
          <header>
            <h1>XML Sitemap</h1>
            <p class="meta">
              <xsl:value-of select="count(sm:urlset/sm:url)"/>
              <xsl:text> URLs</xsl:text>
            </p>
          </header>
          <p class="note">
            This page is a human-readable view of the sitemap. Search engines read the raw XML at
            <code>/sitemap.xml</code>.
          </p>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>#</th>
                  <th>URL</th>
                  <th>Last modified</th>
                  <th class="col-freq">Change frequency</th>
                  <th class="col-prio">Priority</th>
                </tr>
              </thead>
              <tbody>
                <xsl:for-each select="sm:urlset/sm:url">
                  <tr>
                    <td class="num"><xsl:value-of select="position()"/></td>
                    <td>
                      <a href="{sm:loc}"><xsl:value-of select="sm:loc"/></a>
                      <xsl:if test="xhtml:link">
                        <div class="alts">
                          <xsl:for-each select="xhtml:link">
                            <span>
                              <xsl:if test="position() &gt; 1"><xsl:text> · </xsl:text></xsl:if>
                              <a href="{@href}"><xsl:value-of select="@hreflang"/></a>
                            </span>
                          </xsl:for-each>
                        </div>
                      </xsl:if>
                    </td>
                    <td class="num"><xsl:value-of select="sm:lastmod"/></td>
                    <td class="col-freq"><xsl:value-of select="sm:changefreq"/></td>
                    <td class="col-prio num"><xsl:value-of select="sm:priority"/></td>
                  </tr>
                </xsl:for-each>
              </tbody>
            </table>
          </div>
        </main>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
