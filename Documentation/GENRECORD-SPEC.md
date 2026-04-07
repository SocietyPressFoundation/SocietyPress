# GENRECORD 1.0 — Genealogical Record Exchange

**Version:** 1.0 Draft
**Date:** 2026-03-28
**Author:** Charles Stricklin / SocietyPress Project
**License:** This specification is released into the public domain (CC0 1.0).

---

## Purpose

GENRECORD is an open, plain-text file format for exchanging genealogical source records between societies, archives, and software systems. It solves a simple problem: genealogical societies create transcribed record sets (cemetery inscriptions, obituary indexes, census abstracts, courthouse records) but there is no standard way to package and share them.

GENRECORD is a self-describing format. A single file contains both the metadata about a record collection (what it is, where it came from, who created it, what license applies) and the record data itself. Any software that reads the format gets everything it needs in one file.

---

## Design Principles

1. **Human-readable.** A GENRECORD file is plain text. Open it in any text editor or spreadsheet and you can read it.
2. **Simple.** Flat tabular data with a metadata header. No nesting, no XML, no binary encoding.
3. **Self-describing.** The file carries its own context. No external schema file required.
4. **Tool-friendly.** Strip the header lines and you have a standard TSV or CSV that any tool can process.
5. **Non-proprietary.** This spec is public domain. No license fees, no permission needed, no gatekeepers.

---

## File Structure

A GENRECORD file has two sections:

1. **Header** — metadata lines, each beginning with `#`
2. **Data** — a column header row followed by data rows, tab-delimited or comma-delimited

### Example

```
#GENRECORD 1.0
#Collection: Hart Island Burial Records
#Type: Cemetery
#Description: Burial records from Hart Island, New York City's public cemetery, maintained by the NYC Department of Correction.
#Source: NYC Open Data (https://data.cityofnewyork.us)
#Location: Hart Island, Bronx, New York, NY
#Date-Range: 1977–2024
#License: Public Domain
#Creator: NYC Department of Correction
#Contact: opendata@cityofnewyork.us
#Created: 2026-03-28
#Record-Count: 4519
#Delimiter: tab
Last Name	First Name	Age	Death Date	Place of Death
ABAD	PEDRO	90	09/22/2019	BRONXCARE HEALTH SYSTEMS
ABARCA	LUIS	56	05/28/2020	ELMHURST HOSPITAL CENTER
ABBOTT	JAMES	72	01/15/2019	BELLEVUE HOSPITAL CENTER
```

---

## Header Specification

Header lines begin with `#` and use `Key: Value` format. The first line MUST be the format identifier. All other header fields are optional but recommended.

### Required

| Field | Description |
|-------|-------------|
| `#GENRECORD 1.0` | Format identifier and version. Must be the first line. No colon — this is a fixed string. |

### Recommended

| Field | Description | Example |
|-------|-------------|---------|
| `Collection` | Human-readable name for this record set | `Hart Island Burial Records` |
| `Type` | Record type from the standard vocabulary (see below) | `Cemetery` |
| `Description` | Brief description of what these records contain | `Burial records from Hart Island...` |
| `Source` | Where the original records came from — archive, office, publication | `NYC Open Data` |
| `Location` | Geographic area these records cover | `Bronx, New York, NY` |
| `Date-Range` | Time period covered, as a range or single year | `1977–2024` |
| `License` | Usage license for this data set | `Public Domain` |
| `Creator` | Person or organization that created/transcribed the records | `NYC Department of Correction` |
| `Created` | Date this file was created (ISO 8601: YYYY-MM-DD) | `2026-03-28` |
| `Record-Count` | Number of data rows (excluding column header) | `4519` |
| `Delimiter` | `tab` or `comma` — defaults to `tab` if omitted | `tab` |

### Optional

| Field | Description | Example |
|-------|-------------|---------|
| `Contact` | Email address or URL for questions about this data | `records@example-society.org` |
| `Citation` | How to cite this record set in publications | `Hart Island Project, NYC DOC, 2024` |
| `Language` | Language of the records (ISO 639-1 code) | `en` |
| `URL` | Web page with more information about this collection | `https://www.hartisland.net` |
| `Society` | Name of the genealogical society that compiled this data | `Example County Genealogical Society` |
| `Access` | Intended access level: `Public`, `Members`, `Restricted` | `Public` |
| `Notes` | Any additional information about the collection | `Names redacted for fetal remains` |
| `Field-Count` | Number of data columns | `5` |
| `Exported-From` | Software that generated the file | `SocietyPress 0.49d` |

### Rules

- Header lines MUST begin with `#` as the first character on the line.
- The format identifier `#GENRECORD 1.0` MUST be the first line of the file.
- Field names are case-insensitive (`Collection` = `collection` = `COLLECTION`).
- Values are trimmed of leading/trailing whitespace.
- Lines beginning with `##` are comments and MUST be ignored by parsers.
- The header ends at the first line that does not begin with `#`. That line is the column header row.
- Unknown header fields SHOULD be preserved on import/export and MUST NOT cause errors.

---

## Standard Record Type Vocabulary

The `Type` field SHOULD use one of these values. Software MAY support additional types.

| Type | Description |
|------|-------------|
| `Cemetery` | Headstone transcriptions, burial registers, plot records |
| `Census` | Federal, state, or local census indexes or transcriptions |
| `Church` | Baptism, confirmation, marriage, burial, and membership records |
| `Court` | Court proceedings, naturalizations, guardianships |
| `Immigration` | Passenger lists, naturalization records, border crossings |
| `Land` | Deeds, grants, surveys, plat records |
| `Marriage` | Marriage licenses, bonds, certificates, announcements |
| `Military` | Service records, pension files, draft registrations, burials |
| `Newspaper` | Clippings, announcements, legal notices |
| `Obituary` | Death notices and obituaries from any source |
| `Probate` | Wills, estate inventories, letters of administration |
| `Tax` | Tax rolls, assessments, property valuations |
| `Vital` | Birth, death, marriage certificates from civil authorities |
| `General` | Records that do not fit another category |

---

## Data Section

### Column Header Row

The first non-`#` line is the column header row. Each value is a human-readable field name.

- Column names SHOULD be descriptive: `Last Name`, `Death Date`, `Cemetery Name` — not `col1`, `field_a`.
- Column names MUST be unique within the file.

### Data Rows

Each subsequent line is one record. Fields are separated by the delimiter specified in the header (tab by default).

### Delimiter Rules

- **Tab-delimited (default):** Fields separated by `\t` (U+0009). Recommended because genealogical data frequently contains commas (addresses, names, notes).
- **Comma-delimited:** Fields separated by `,`. Fields containing commas, quotes, or newlines MUST be enclosed in double quotes per RFC 4180.
- If the `Delimiter` header is omitted, parsers SHOULD auto-detect by comparing tab count vs. comma count on the column header row. If ambiguous, assume tab.

### Encoding

- Files MUST be encoded as UTF-8.
- A UTF-8 BOM (byte order mark, `EF BB BF`) at the start of the file is permitted and MUST be stripped by parsers before processing.

### Dates

- Dates SHOULD use `MM/DD/YYYY`, `YYYY-MM-DD` (ISO 8601), or `DD Mon YYYY` format.
- Partial dates are permitted: `1923`, `03/1923`, `Mar 1923`.
- The format is not enforced — records transcribe dates as they appear in the source material.

### Empty Values

- Empty fields are represented by consecutive delimiters (`\t\t` or `,,`).
- Parsers MUST NOT treat empty fields as errors.

---

## File Extension

- `.gedrec` — canonical extension for GENRECORD files
- `.tsv` and `.csv` — files using these extensions MAY contain GENRECORD headers; parsers SHOULD detect the `#GENRECORD` first line regardless of extension

---

## MIME Type

`text/gedrec` (proposed)

Fallback: `text/tab-separated-values` or `text/csv` depending on delimiter.

---

## Generating GENRECORD Files

### From SocietyPress

When a SocietyPress install exports a record collection, it writes a GENRECORD file with all header fields populated from the collection metadata and all records as tab-delimited rows.

### From a Spreadsheet

1. Add header lines (starting with `#`) at the top of the sheet, one per row, content in column A.
2. Add the column header row.
3. Add data rows.
4. Save As → Tab-Delimited Text (`.txt`).
5. Rename the file extension to `.gedrec`.

### By Hand

Open any text editor. Type the header lines, the column headers, and the data. Save as UTF-8 with a `.gedrec` extension.

---

## Parsing GENRECORD Files

Pseudocode for reading a GENRECORD file:

```
open file as UTF-8
strip BOM if present

metadata = {}
for each line:
    if line starts with "##":
        skip (comment)
    else if line starts with "#":
        if line == "#GENRECORD 1.0":
            metadata["version"] = "1.0"
        else:
            key, value = split on first ":"
            key = strip "#" and whitespace, lowercase
            value = strip whitespace
            metadata[key] = value
    else:
        this line is the column header row
        break

detect delimiter from metadata or auto-detect
parse column header row using delimiter
parse remaining lines as data rows using delimiter
```

---

## Compatibility Notes

- A GENRECORD file with the `#` header lines stripped is a valid TSV or CSV file. This means any tool that cannot parse GENRECORD headers can still use the data — just skip lines starting with `#`.
- Spreadsheet software (Excel, Google Sheets, LibreOffice Calc) will typically display `#` lines as data rows in column A. This is acceptable — the metadata is still human-readable.
- GEDCOM is a format for family tree data (individuals, families, events, relationships). GENRECORD is a format for source record data (transcriptions, indexes, abstracts). They are complementary, not competing.

---

## Versioning

This specification uses semantic versioning. The version number appears in the `#GENRECORD` identifier line.

- **1.0** — flat tabular records with metadata header (this document)
- Future versions MAY add support for: record relationships, embedded images/references, multi-collection files, linked data URIs

Parsers SHOULD check the version number and warn (not error) if they encounter an unrecognized version.

---

## License

This specification is released under CC0 1.0 Universal (Public Domain Dedication). Anyone may use, implement, modify, or redistribute it without restriction or attribution.

The GENRECORD format is not owned by SocietyPress or any other organization. It belongs to everyone.
