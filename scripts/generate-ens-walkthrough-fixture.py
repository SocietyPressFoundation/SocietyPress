#!/usr/bin/env python3
"""
Generate the ENS export used by docs/ENS-MIGRATION-WALKTHROUGH.md.

Writes "Sample Data/ENS Migration Demo/members-export.csv": 22 invented
members of an invented society, in the 87 columns of an EasyNetSites member
export with the dues and payment block.

WHY a generator rather than the CSV itself: Sample Data/ is gitignored, so
the fixture never reaches a fresh clone. Keeping the recipe in the repo means
the walkthrough can be re-run on any later version and its numbers checked,
instead of trusting a file that only ever existed on one machine.

WHY every person is invented and every address is example.net: SocietyPress
docs never carry a real society's members. The reserved example.net domain
also guarantees no import test can mail a stranger.

Usage: python3 scripts/generate-ens-walkthrough-fixture.py [output.csv]
"""

import csv
import os
import sys

# Column order of an ENS member export. Every name here is one the importer's
# auto-detection recognises (sp_process_import_batch's default map), which is
# the point: the walkthrough accepts the suggested mapping without edits.
HEADERS = [
    'ENS Member Record ID', 'Membership Number', 'Name Prefix', 'First Name',
    'Middle Name', 'Last Name', 'Name Suffix', 'PreferredName', 'Maiden Name',
    'UseMaiden', 'Address Label Name', 'File Name', 'Membership Plan',
    'Membership Type', 'Member Join Date', 'Expiration Date', 'Member Active',
    'Lifetime', 'Deceased', 'Max Members', 'Membership Tie ID', 'Acct. Primary',
    'Joint Member', 'Email of Joint', 'Phone of Joint', 'Email', 'Alt. Email',
    'Telephone', 'Cell Phone', 'Business Phone', 'Fax', 'Toll Free Phone',
    'International Phone', 'Preferred Phone', 'Address 1', 'Address 2', 'City',
    'State / Province', 'Postal Code', 'Country', 'Website',
    'Alternate Address 1', 'Alt. Address 2', 'Alt. City',
    'Alt. State / Province', 'Alt. Postal Code', 'Al. Country',
    'Use Seasonal Address', 'Use Alt. Info Month Begin',
    'Alt. Contact Day Begin', 'Use Alt. Info Month End', 'Alt. Contact Day End',
    'Alt. Phone', 'Alt. International Phone', 'Alt. Preferred Phone',
    'General Email', 'Event Email', 'Newsletter Email', 'Quarterly',
    'Mail Publication', 'Surname Inquiry', 'Mbr. List - Show Name',
    'Mbr. List - Show Address', 'Mbr. List - Show Phone',
    'Mbr. List - Show Email', 'Mbr. List - Show Photo', 'Birth Year',
    'Birth Month', 'Birth Day', 'Gender', 'Your Skills', 'Your Interests',
    'Your Education', 'Volunteering?', 'Contact', 'Image Filename',
    'Amount Paid', 'Date Paid', 'Payment Type', 'Donation', 'Comment', 'Login',
    'Login Count', 'Last Login Date', 'Administrative Notes', 'Last Updated By',
    'Last Updated Date',
]

assert len(HEADERS) == 87, len(HEADERS)

CITY = ('Cedar Springs', 'IA', '52240')

# Defaults every row starts from; each member below overrides what differs.
BASE = {
    'Country': 'USA', 'Member Active': 'Yes', 'Lifetime': 'No',
    'Deceased': 'No', 'Membership Plan': 'Individual',
    'Membership Type': 'Individual', 'Expiration Date': '12/31/2026',
    'General Email': 'Yes', 'Event Email': 'Yes', 'Newsletter Email': 'Yes',
    'Quarterly': 'Yes', 'Mail Publication': 'Yes', 'Surname Inquiry': 'Yes',
    'Mbr. List - Show Name': 'Yes', 'Mbr. List - Show Address': 'No',
    'Mbr. List - Show Phone': 'No', 'Mbr. List - Show Email': 'Yes',
    'Mbr. List - Show Photo': 'No', 'Use Seasonal Address': 'No',
    'UseMaiden': 'No', 'Acct. Primary': 'Yes', 'Payment Type': 'Check',
    'Amount Paid': '30.00', 'Date Paid': '01/12/2026',
    'Last Updated By': 'membership', 'Last Updated Date': '01/12/2026',
}

# (first, last, street, join date, gender, interests)
INDIVIDUALS = [
    ('Eleanor', 'Pruitt', '114 Orchard Ln', '03/14/2008', 'F', 'Scots-Irish settlers'),
    ('Walter', 'Hasbrouck', '22 Mill Rd', '09/02/2015', 'M', 'Civil War pensions'),
    ('Bernice', 'Castellano', '871 Prairie Ave', '05/20/2019', 'F', 'Italian immigration'),
    ('Ogden', 'Fairweather', '9 Depot St', '11/11/2011', 'M', 'Railroad employment records'),
    ('Louisa', 'Okonkwo', '303 Elm Ct', '02/01/2022', 'F', 'Freedmen\'s Bureau records'),
    ('Clement', 'Rasmussen', '58 Quarry Rd', '07/07/2003', 'M', 'Danish Lutheran parishes'),
    ('Ida', 'Vanderhoef', '1201 County Rd 8', '04/18/2017', 'F', 'Dutch Reformed church books'),
    ('Percival', 'Mbeki-Hart', '46 Juniper Way', '10/30/2023', 'M', 'DNA matching'),
    ('Agnes', 'Trowbridge', '700 Main St Apt 3', '06/05/1999', 'F', 'Cemetery transcription'),
    ('Rufus', 'Delacroix', '15 Bluff St', '01/22/2014', 'M', 'French Canadian voyageurs'),
]

# Couples: (tie id, (first, last, email), (first, last, email), street, join)
COUPLES = [
    (2001, ('Harold', 'Whitmore', 'hwhitmore@example.net'),
           ('Greta', 'Whitmore', 'gwhitmore@example.net'), '18 Linden St', '08/15/2005'),
    (2002, ('Silas', 'Duplantier', 'duplantiers@example.net'),
           ('Ramona', 'Duplantier', 'duplantiers@example.net'), '402 River Rd', '03/03/2012'),
    (2003, ('Theodore', 'Ashby', 'tashby@example.net'),
           ('Winifred', 'Ashby', 'washby@example.net'), '67 Hollow Creek Dr', '12/01/2018'),
]


def row(**over):
    r = {h: '' for h in HEADERS}
    r.update(BASE)
    r.update(over)
    r['City'], r['State / Province'], r['Postal Code'] = (
        over.get('City', CITY[0]), over.get('State / Province', CITY[1]),
        over.get('Postal Code', CITY[2]))
    r['Address Label Name'] = r['Address Label Name'] or f"{r['First Name']} {r['Last Name']}"
    r['Login'] = r['Login'] or (r['First Name'][0] + r['Last Name']).lower().replace('-', '')
    return r


def build():
    rows = []
    rec = 50001
    num = 101

    def nxt():
        nonlocal rec, num
        rec += 1
        num += 1
        return str(rec - 1), str(num - 1)

    for first, last, street, joined, gender, interests in INDIVIDUALS:
        rid, mno = nxt()
        rows.append(row(**{
            'ENS Member Record ID': rid, 'Membership Number': mno,
            'First Name': first, 'Last Name': last, 'Address 1': street,
            'Email': f'{first[0].lower()}{last.lower().replace("-", "")}@example.net',
            'Telephone': f'319-555-01{mno[-2:]}', 'Member Join Date': joined,
            'Gender': gender, 'Your Interests': interests,
        }))

    # A couple is two rows sharing a Membership Tie ID. The first is the
    # account holder; the second names the first as their joint member.
    for tie, a, b, street, joined in COUPLES:
        for person, other, primary in ((a, b, 'Yes'), (b, a, 'No')):
            rid, mno = nxt()
            rows.append(row(**{
                'ENS Member Record ID': rid, 'Membership Number': mno,
                'First Name': person[0], 'Last Name': person[1],
                'Email': person[2], 'Address 1': street,
                'Membership Plan': 'Joint', 'Membership Type': 'Joint',
                'Membership Tie ID': str(tie), 'Acct. Primary': primary,
                'Joint Member': f'{other[0]} {other[1]}', 'Email of Joint': other[2],
                'Address Label Name': f'{a[0]} & {b[0]} {a[1]}',
                'Member Join Date': joined,
                'Amount Paid': '40.00' if primary == 'Yes' else '',
                'Date Paid': '01/12/2026' if primary == 'Yes' else '',
                'Payment Type': 'Check' if primary == 'Yes' else '',
            }))

    # The snowbird: home in Iowa, Naples from November to mid-April.
    rid, mno = nxt()
    rows.append(row(**{
        'ENS Member Record ID': rid, 'Membership Number': mno,
        'First Name': 'Gustav', 'Last Name': 'Lindholm', 'Address 1': '5 Birch Knoll',
        'Email': 'glindholm@example.net', 'Member Join Date': '05/09/2010',
        'Use Seasonal Address': 'Yes', 'Alternate Address 1': '2280 Gulf Shore Blvd N',
        'Alt. Address 2': 'Unit 4B', 'Alt. City': 'Naples',
        'Alt. State / Province': 'FL', 'Alt. Postal Code': '34102', 'Al. Country': 'USA',
        'Use Alt. Info Month Begin': 'November', 'Alt. Contact Day Begin': '1',
        'Use Alt. Info Month End': 'April', 'Alt. Contact Day End': '15',
        'Alt. Phone': '239-555-0148',
    }))

    # The life member: joined 1994, paid once in 2004, never expires.
    rid, mno = nxt()
    rows.append(row(**{
        'ENS Member Record ID': rid, 'Membership Number': mno,
        'First Name': 'Mildred', 'Last Name': 'Sorensen', 'Address 1': '31 Chapel Hill Rd',
        'Email': 'msorensen@example.net', 'Member Join Date': '04/02/1994',
        'Membership Plan': 'Life', 'Membership Type': 'Life', 'Lifetime': 'Yes',
        'Expiration Date': '', 'Amount Paid': '300.00', 'Date Paid': '06/01/2004',
    }))

    # The lapsed member: last renewed for 2025.
    rid, mno = nxt()
    rows.append(row(**{
        'ENS Member Record ID': rid, 'Membership Number': mno,
        'First Name': 'Desmond', 'Last Name': 'Kittredge', 'Address 1': '88 Ferry St',
        'Email': 'dkittredge@example.net', 'Member Join Date': '09/09/2020',
        'Member Active': 'No', 'Expiration Date': '12/31/2025',
        'Amount Paid': '30.00', 'Date Paid': '01/20/2025',
        'Last Updated Date': '01/20/2025',
    }))

    # The deceased member, whose family asked that the surname file stay open.
    rid, mno = nxt()
    rows.append(row(**{
        'ENS Member Record ID': rid, 'Membership Number': mno,
        'First Name': 'Augustina', 'Last Name': 'Belcourt', 'Address 1': '12 Willow Bend',
        'Email': 'abelcourt@example.net', 'Member Join Date': '02/14/2001',
        'Member Active': 'No', 'Deceased': 'Yes', 'Expiration Date': '12/31/2025',
        'Administrative Notes': 'Died March 2026. Family asks that her Belcourt/Beaulieu surname file stay open for researchers.',
        'Last Updated Date': '03/30/2026',
    }))

    # Two institutions, each with a named contact person. They differ in one
    # cell — the Membership Type — which is what the walkthrough tests.
    for org, mtype, first, last, street in (
        ('Fairhaven Public Library', 'Organization', 'Corinne', 'Ashdown', '200 Library Plaza'),
        ('Marengo Township Historical Museum', 'Institutional', 'Lyle', 'Pettibone', '1 Museum Sq'),
    ):
        rid, mno = nxt()
        rows.append(row(**{
            'ENS Member Record ID': rid, 'Membership Number': mno,
            'First Name': first, 'Last Name': last, 'File Name': org,
            'Address 1': street, 'Address Label Name': org,
            'Email': f'{first[0].lower()}{last.lower()}@example.net',
            'Membership Plan': 'Institutional', 'Membership Type': mtype,
            'Max Members': '3', 'Amount Paid': '75.00', 'Payment Type': 'Invoice',
        }))

    assert len(rows) == 22, len(rows)
    return rows


def main():
    repo = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    out = sys.argv[1] if len(sys.argv) > 1 else os.path.join(
        repo, 'Sample Data', 'ENS Migration Demo', 'members-export.csv')
    os.makedirs(os.path.dirname(out), exist_ok=True)
    with open(out, 'w', newline='', encoding='utf-8') as f:
        w = csv.DictWriter(f, fieldnames=HEADERS)
        w.writeheader()
        w.writerows(build())
    print(f'Wrote 22 members x {len(HEADERS)} columns to {out}')


if __name__ == '__main__':
    main()
