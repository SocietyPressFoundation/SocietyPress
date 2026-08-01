#!/usr/bin/env python3
"""
Build the sample newsletters for the SocietyPress demo site.

Produces one PDF plus a cover JPG per issue of the "Kindred Chronicle", the
newsletter of the fictional Kindred Genealogical Society that seed-demo.php
creates. Filenames match the map in attach-newsletters.php exactly:

    kc-<year>-<season>.pdf
    kc-<year>-<season>-cover.jpg

WHY the contents block is laid out in two columns with wide gaps: the plugin
parses a printed "Inside this Issue" table back out of the extracted text by
looking for a title, a run of whitespace, then a page number, several times per
line. Rendering it the way a real society would is what exercises that parser.

Everything here is invented. No real society, member, or address appears —
the demo is a public marketing property and must stay clear of anyone's
actual records.

Usage:  python3 generate-newsletters.py <output-dir>
"""

import subprocess
import sys
from pathlib import Path

from reportlab.lib.pagesizes import letter
from reportlab.lib.units import inch
from reportlab.pdfgen import canvas

PAGE_W, PAGE_H = letter
MARGIN = 0.9 * inch
BODY_W = PAGE_W - (2 * MARGIN)

SOCIETY = "Kindred Genealogical Society"
MASTHEAD = "The Kindred Chronicle"
ADDRESS = "148 Harrow Street, Fairhaven, IN 46815"
CONTACT = "(260) 555-0148  ·  hello@kindredgenealogy.example  ·  kindredgenealogy.example"


# Each issue names its own people and places so that searching the demo
# archive turns up different issues for different terms.
ISSUES = [
    {
        "year": 2023, "season": "spring", "label": "Spring 2023", "volume": 41, "issue": 1,
        "lead": "Ashbury",
        "articles": [
            ("President's Message", [
                "Our society begins its forty-first year with the widest slate of programs we have offered since the move to Harrow Street. Membership stands at 571, up from 498 a year ago, and the Tuesday research nights have outgrown the reading room twice over.",
                "The Board has approved the purchase of a second microfilm reader, which should arrive before the summer recess. My thanks to Eleanor Ashbury, who chaired the equipment committee and negotiated a price well under our estimate.",
                "We remain, as always, a society of volunteers. Nothing in these pages happened because someone was paid to make it happen.",
            ]),
            ("Member Spotlight: Eleanor Ashbury", [
                "Eleanor Ashbury joined the society in 1998 while tracing a great-grandmother who appeared in three counties under two spellings. She has since indexed more than eleven thousand entries from the Fairhaven Poor Farm register, work that had defeated two previous volunteers.",
                "Ask her about the Ashbury line and she will tell you the family arrived from Northumberland in 1847, settled first near Cranberry Marsh, and scattered within a generation. Ask her about the Poor Farm register and she will talk for an hour.",
                "\"The handwriting is not the hard part,\" she says. \"The hard part is that the clerk changed every four years and none of them agreed on how to spell anything.\"",
            ]),
            ("Research Tips: Reading a County Boundary", [
                "A record that cannot be found is often a record filed in a county that no longer exists. Before concluding that a courthouse fire destroyed your evidence, check whether the county lines moved.",
                "Fairhaven County was carved from Marchetti County in 1851 and lost its northern townships to Kilbride County in 1867. A deed recorded in 1860 for land at the north edge of the county sits in the Fairhaven books; the same farm, sold in 1870, is recorded in Kilbride.",
                "The society keeps a boundary atlas at the reference desk. It is the single most consulted volume we own, and it is not a book anyone thinks to ask for.",
            ]),
            ("Library Corner: New Acquisitions", [
                "Thirty-one volumes joined the collection this quarter, including the complete run of the Fairhaven Weekly Register on microfilm, 1856 to 1899, purchased with the Pendergast memorial fund.",
                "Also received: four boxes of Whitmore family papers, donated by the estate of Margaret Whitmore, comprising correspondence, two account books, and an unlabelled photograph album that the accessions committee would dearly like help identifying.",
            ]),
            ("Cemetery Preservation Project", [
                "Volunteers completed the survey of Cranberry Marsh Burying Ground in April, recording 384 stones, of which 71 were previously undocumented. Twenty-two are illegible and have been photographed under raking light for a second attempt.",
                "The oldest legible stone belongs to Thomas Kilbride, died 1839. The most puzzling is a small marker reading only the initials R.V. and the year 1871.",
                "Work resumes at Pendergast Chapel in September. No experience is needed and the society provides the tools.",
            ]),
            ("Upcoming Programs", [
                "May 14 — Reading the Unreadable: Approaches to Damaged Documents, with Dr. Helena Vasquez.",
                "June 11 — Beginning Irish Research, a workshop for members new to records outside the United States.",
                "July 9 — Annual picnic and surname exchange at Cranberry Marsh Park. Bring a chart and a folding chair.",
            ]),
            ("Surnames Under Study", [
                "Members are researching the following families this quarter. If a name matches your own line, the society will forward your message to the researcher.",
                "Ashbury · Calloway · Kilbride · Marchetti · Pendergast · Ravensworth · Thorne · Whitmore · Abernathy · Okonkwo · Lindqvist · Vasquez",
            ]),
        ],
    },
    {
        "year": 2023, "season": "summer", "label": "Summer 2023", "volume": 41, "issue": 2,
        "lead": "Calloway",
        "articles": [
            ("President's Message", [
                "The summer recess is shorter than usual this year. With the second microfilm reader installed and the Register run catalogued, the reading room stays open through July for the first time in a decade.",
                "I want to record my thanks to the Tuesday volunteers, who have quietly absorbed a doubling of visitors without once suggesting we should ask for help.",
            ]),
            ("Member Spotlight: Desmond Calloway", [
                "Desmond Calloway came to genealogy through a shoebox of photographs and a grandmother who would not explain any of them. Twelve years later he has identified all but nine.",
                "His method is unglamorous and effective: he dates the photographs by the studio imprint, cross-references the studio against city directories to establish when it operated, and only then attempts the faces.",
                "\"People want to start with who,\" he says. \"You get further starting with when.\"",
            ]),
            ("The Whitmore Album Identified", [
                "The unlabelled album donated with the Whitmore papers has been substantially identified, thanks to a reader who recognised a storefront in the background of the fourth plate.",
                "The building is the Marchetti dry goods store on Harrow Street, demolished in 1912. That single fact dated the album to a fourteen-year window and allowed eleven of the nineteen subjects to be named from the 1900 census and two obituaries.",
            ]),
            ("Research Tips: The Value of a Negative Result", [
                "A search that finds nothing is still evidence, provided you record what you searched. A note reading \"not in Fairhaven County marriage index, 1851-1880, groom surname\" is worth as much as a hit, because it stops you repeating the work in three years.",
                "The society's research log template is available at the reference desk and on the members' area of the website.",
            ]),
            ("DNA Interest Group", [
                "The DNA group now meets the first Thursday of each month. Current focus is on interpreting shared centimorgan ranges for relationships beyond second cousin, where the ranges overlap badly and confident conclusions are rarely warranted.",
                "New members are welcome. No test results are required to attend, and no one will ask you to share yours.",
            ]),
            ("Volunteer Spotlight: The Tuesday Crew", [
                "Six people keep the reading room open on Tuesdays: Priya Raman, Thomas Okonkwo, Beatrice Lindqvist, Sam Thorne, Joan Abernathy, and Walter Pendergast.",
                "Between them they answered 214 reference questions last quarter, reshelved a collection that had drifted badly out of order, and repaired the photocopier twice.",
            ]),
            ("New Members", [
                "The society welcomes: Adaeze Okonkwo, Martin Ravensworth, Cecily Thorne, Grace Abernathy, Rosa Vasquez, Henrik Lindqvist, and Delia Marchetti.",
            ]),
        ],
    },
    {
        "year": 2023, "season": "fall", "label": "Fall 2023", "volume": 41, "issue": 3,
        "lead": "Pendergast",
        "articles": [
            ("President's Message", [
                "Autumn brings the annual meeting and, this year, an election with more candidates than seats — a happy problem the society has not had since 2011.",
                "The Pendergast Chapel survey resumed in September and is already ahead of schedule.",
            ]),
            ("Pendergast Chapel: First Findings", [
                "The chapel yard proved larger than the plat suggested. Probing located nineteen unmarked graves in the southwest corner, in two rows consistent with a family plot.",
                "County records show the parcel was owned by the Pendergast family from 1844 until 1901. No burial register survives, and the society is not proposing any excavation.",
                "The findings have been reported to the county historical commission and the landowner, both of whom have been generous with access.",
            ]),
            ("Member Spotlight: Walter Pendergast", [
                "Walter Pendergast is not, so far as anyone can establish, related to the Pendergasts of the chapel. He has been trying to prove it either way for nine years.",
                "\"That is the honest position,\" he says. \"Same county, same spelling, same decade, and not one document connecting them. It would be easy to assume. I am not going to.\"",
            ]),
            ("Research Tips: Same Name, Different Man", [
                "Two men of the same name in the same county is the ordinary case, not the exception. Before merging them, find a moment when both are documented and see whether they can be the same person.",
                "Land records are useful here because a man cannot sell the same parcel twice, and tax lists because he cannot be assessed in two townships in one year.",
            ]),
            ("Library Corner", [
                "New this quarter: the Kilbride County probate abstracts, 1867-1920, in three volumes, and a donated set of the Ravensworth genealogy compiled privately in 1958.",
                "A caution on the Ravensworth volume: it is undocumented throughout and contains at least two demonstrable errors in the fourth generation. It is useful as a set of leads and worthless as a source.",
            ]),
            ("Annual Meeting and Election", [
                "The annual meeting convenes November 12 at 2:00 p.m. in the Harrow Street reading room. Candidates for the Board are listed on page 6.",
                "Members unable to attend may vote by absentee ballot, available from the secretary through November 5.",
            ]),
            ("Upcoming Programs", [
                "October 8 — Probate Records and What They Actually Prove, with Judge Marian Kilbride, retired.",
                "November 12 — Annual meeting and election.",
                "December 10 — Members' show and tell. Bring one document and five minutes of explanation.",
            ]),
        ],
    },
    {
        "year": 2024, "season": "winter", "label": "Winter 2024", "volume": 42, "issue": 1,
        "lead": "Thorne",
        "articles": [
            ("President's Message", [
                "My thanks to the membership for the confidence expressed at the annual meeting, and to Cecily Thorne, who ran a close and gracious race.",
                "The society enters its forty-second year in the healthiest condition I have known it: solvent, busy, and short only of shelf space.",
            ]),
            ("Member Spotlight: Cecily Thorne", [
                "Cecily Thorne indexes what nobody else will touch. Her current project is the Fairhaven city directory series, 1878 to 1931, entered household by household.",
                "The value is not the directories themselves, which survive in good order, but the ability to search them together. A family that moves four times in twelve years becomes visible as one thread instead of four disconnected entries.",
            ]),
            ("The Ravensworth Correction", [
                "Following the note in our Fall issue, three members independently examined the 1958 Ravensworth genealogy and have documented seven errors, not two.",
                "The most consequential is a conflation of two men named Josiah Ravensworth, one of Kilbride County and one of Marchetti County, whose descendants have been mixed together for sixty-six years and repeated in at least four published works since.",
                "A corrected chart is available at the reference desk. Members using the 1958 volume are urged to consult it first.",
            ]),
            ("Research Tips: Citing as You Go", [
                "The citation you do not write today is the one you cannot reconstruct in five years. Record the repository, the collection, the volume, the page, and the date you looked.",
                "Nobody has ever regretted an over-full citation. A great many people have re-driven ninety miles to recover an under-full one.",
            ]),
            ("Winter Workshop Series", [
                "Four Saturday workshops run January through March, each limited to twelve participants so that everyone gets time at a reader.",
                "Topics: land records, immigration and naturalisation, newspapers beyond the obituary, and organising what you already have.",
            ]),
            ("New Members", [
                "The society welcomes: Ingrid Lindqvist, Oscar Marchetti, Faith Abernathy, Julian Vasquez, Nkechi Okonkwo, and Bartholomew Kilbride.",
            ]),
        ],
    },
    {
        "year": 2024, "season": "spring", "label": "Spring 2024", "volume": 42, "issue": 2,
        "lead": "Okonkwo",
        "articles": [
            ("President's Message", [
                "The winter workshops filled within a week of announcement, and we have added a fifth in April. The appetite for structured instruction is clearly larger than we assumed.",
            ]),
            ("Member Spotlight: Thomas Okonkwo", [
                "Thomas Okonkwo teaches the newspaper workshop, and his central point surprises people every time: the obituary is usually the least informative item about a person in the paper.",
                "\"The obituary tells you who survived him and where the funeral was,\" he says. \"The court notice tells you he was sued in 1889 and by whom. The advertisement tells you what he did for a living. Those are the ones that break a wall.\"",
            ]),
            ("Digitisation Project Begins", [
                "With a grant from the Fairhaven Community Foundation, the society has begun digitising the Whitmore papers, starting with the two account books.",
                "The account books cover 1871 to 1889 and record, among much else, the names of forty-one people employed seasonally on the Whitmore farm — several of whom appear in no census under that surname and were almost certainly boarders.",
            ]),
            ("Research Tips: Boarders, Servants, and the Household", [
                "A census household is not a family. The people at the bottom of the entry, listed after the children, are frequently relatives whose connection the enumerator did not record.",
                "Check them. A boarder with the wife's maiden surname is not a coincidence.",
            ]),
            ("Cemetery Preservation Update", [
                "Cranberry Marsh has been fully transcribed and the results are searchable in the members' area. Pendergast Chapel is complete except for the unmarked area, which will remain undisturbed.",
                "Next season the survey moves to the Abernathy family plot on the Kilbride County line, at the invitation of the current owner.",
            ]),
            ("Upcoming Programs", [
                "April 13 — Fifth winter workshop, by demand: Organising What You Already Have.",
                "May 11 — Land Platting for the Terrified, with Sam Thorne.",
                "June 8 — Annual picnic and surname exchange.",
            ]),
        ],
    },
    {
        "year": 2024, "season": "summer", "label": "Summer 2024", "volume": 42, "issue": 3,
        "lead": "Lindqvist",
        "articles": [
            ("President's Message", [
                "The reading room recorded its busiest quarter on record, and for the first time we turned people away on a Tuesday. The Board is considering a second weekly session.",
            ]),
            ("Member Spotlight: Beatrice Lindqvist", [
                "Beatrice Lindqvist arrived in Fairhaven in 1974 and began researching her husband's family because, as she puts it, nobody else was going to.",
                "Fifty years on she has assembled the most complete account of Scandinavian settlement in the county that exists anywhere, largely from church books she taught herself to read.",
                "\"The language was the easy part,\" she says. \"The handwriting took four years.\"",
            ]),
            ("Church Books and What They Hold", [
                "Scandinavian parish registers record more than births, marriages, and deaths. The moving-in and moving-out books track a family's arrival and departure from a parish, often with the exact destination.",
                "For a family that appears in Fairhaven in 1881 with no prior American record, the parish exit book may name the port, the ship, and the year.",
            ]),
            ("Whitmore Account Books Online", [
                "The digitised account books are now searchable. Forty-one seasonal employees have been indexed, of whom eleven have been matched to census households and three to marriage records naming the Whitmore farm as residence.",
                "One name, entered simply as \"R.V., harvest, 1871,\" may connect to the initialled stone at Cranberry Marsh. The society is careful to note this is a hypothesis and nothing more.",
            ]),
            ("Research Tips: When Two Sources Disagree", [
                "Prefer the source created nearest the event by someone with reason to know. A death certificate's birth date comes from a grieving informant decades later; a baptismal register comes from the week itself.",
                "Where a conflict cannot be resolved, record both and say so. An honest unresolved conflict is a finding.",
            ]),
            ("New Members", [
                "The society welcomes: Solveig Lindqvist, Emeka Okonkwo, Harriet Calloway, Dominic Marchetti, Theodora Ashbury, and Lucas Vasquez.",
            ]),
        ],
    },
    {
        "year": 2024, "season": "fall", "label": "Fall 2024", "volume": 42, "issue": 4,
        "lead": "Marchetti",
        "articles": [
            ("President's Message", [
                "A second weekly research session begins in October, on Thursday evenings. My thanks to the six volunteers who agreed to staff it before being asked twice.",
            ]),
            ("Member Spotlight: Delia Marchetti", [
                "Delia Marchetti's family owned the dry goods store whose storefront dated the Whitmore album. She has spent four years assembling the store's history from the surviving ledgers.",
                "The ledgers name customers, and customers are the whole town. For a county with patchy census coverage in 1890, a merchant's accounts receivable is very nearly a substitute.",
            ]),
            ("The 1890 Problem", [
                "The near-total loss of the 1890 federal census leaves a twenty-year gap that swallows families whole. Substitutes exist and are underused.",
                "Consider: state censuses, city directories, tax lists, voter registrations, church membership rolls, fraternal lodge records, and — as the Marchetti ledgers show — commercial accounts.",
                "None of them is a census. Together they are frequently enough.",
            ]),
            ("Abernathy Plot Survey Complete", [
                "The Abernathy family plot yielded 46 stones across four generations, in unusually good condition owing to the shelter of the treeline.",
                "The earliest is Ruth Abernathy, died 1852. A stone for her husband was expected and is not present; the probate file suggests he died in Ohio.",
            ]),
            ("Research Tips: Follow the Witnesses", [
                "The witnesses on a deed, the bondsman on a marriage, the neighbours who post a probate bond — these people are rarely strangers. They are brothers-in-law, cousins, and former neighbours from the previous county.",
                "When a line goes cold, research the witnesses. They frequently lead back to the family you lost.",
            ]),
            ("Annual Meeting", [
                "The annual meeting convenes November 10 at 2:00 p.m. Reports from the treasurer and the accessions committee will be presented.",
            ]),
        ],
    },
    {
        "year": 2025, "season": "winter", "label": "Winter 2025", "volume": 43, "issue": 1,
        "lead": "Vasquez",
        "articles": [
            ("President's Message", [
                "Forty-three years. The society is larger, busier, and better equipped than at any point in my membership, and the reason is not money. It is that a great many people do a small amount of work reliably.",
            ]),
            ("Member Spotlight: Dr. Helena Vasquez", [
                "Dr. Vasquez has taught the damaged-documents workshop three times and consulted on the illegible Cranberry Marsh stones.",
                "Her advice on faded ink is characteristically blunt: photograph it properly before you touch it, try raking light, try a colour filter, and stop there. Chemical treatments belong to conservators, and most documents that were ruined were ruined by someone helping.",
            ]),
            ("The R.V. Stone", [
                "Two years after it was recorded, the initialled stone at Cranberry Marsh remains unidentified, and the society considers that the correct outcome for now.",
                "The Whitmore account book entry for \"R.V., harvest, 1871\" is suggestive and proves nothing. No burial register survives. No probate names a matching individual. The stone reads R.V. and 1871, and that is the whole of the evidence.",
                "It is recorded in the survey as unidentified, and it will stay that way until a document says otherwise.",
            ]),
            ("Winter Workshop Series", [
                "Five Saturdays, January through March. Land records, immigration, newspapers, organisation, and a new session on evaluating published genealogies — prompted, inevitably, by the Ravensworth volume.",
            ]),
            ("Research Tips: Evaluating a Published Genealogy", [
                "Ask three questions. Does it cite sources? Do the citations lead to records that exist? Where it states a relationship, does the cited record actually establish that relationship, or merely permit it?",
                "A book that fails the first question is a set of leads. A book that fails the third is a hazard, because its errors are repeated with the confidence of print.",
            ]),
            ("New Members", [
                "The society welcomes: Marisol Vasquez, Peter Kilbride, Anneke Lindqvist, Chidi Okonkwo, Verity Thorne, and Augustus Pendergast.",
            ]),
        ],
    },
    {
        "year": 2025, "season": "spring", "label": "Spring 2025", "volume": 43, "issue": 2,
        "lead": "Kilbride",
        "articles": [
            ("President's Message", [
                "The workshop on published genealogies drew the largest attendance of any session we have run, which tells us something about what the membership is wrestling with.",
            ]),
            ("Member Spotlight: Marian Kilbride", [
                "Judge Kilbride spent thirty-one years on the county bench and now spends her Thursdays explaining probate files to people who find them impenetrable.",
                "\"An estate file is a story told by accountants,\" she says. \"The inventory tells you how he lived. The claims tell you who he owed. The distribution tells you who was left and how well they got on.\"",
            ]),
            ("Probate Beyond the Will", [
                "Most estates have no will. Those files are often richer, because an intestate estate must document the heirs to distribute the property.",
                "Guardianship papers name minor children and their ages. Dower assignments name the widow. Petitions for sale name every heir including those who had moved away, with their places of residence.",
            ]),
            ("Digitisation: Phase Two", [
                "With phase one complete, the society has begun on the Whitmore correspondence — some 1,400 items, of which roughly a third are legible without magnification.",
                "Volunteers are needed for transcription. Training is provided and the work can be done from home.",
            ]),
            ("Research Tips: The Widow's Trail", [
                "A widow who remarries vanishes under a new surname, taking the children's household with her. Look for the guardianship: a remarriage frequently triggers a court appointment to protect the minors' inheritance from the new husband.",
                "That single document ties the old surname to the new one and is often the only thing that does.",
            ]),
            ("Upcoming Programs", [
                "April 12 — Transcription clinic for the Whitmore correspondence project.",
                "May 10 — Reading Land: Metes, Bounds and the Public Land Survey.",
                "June 14 — Annual picnic and surname exchange at Cranberry Marsh Park.",
            ]),
        ],
    },
    {
        "year": 2025, "season": "summer", "label": "Summer 2025", "volume": 43, "issue": 3,
        "lead": "Abernathy",
        "articles": [
            ("President's Message", [
                "Transcription of the Whitmore correspondence is a third complete after four months, which is faster than the committee projected and slower than the volunteers had hoped.",
            ]),
            ("Member Spotlight: Joan Abernathy", [
                "Joan Abernathy has transcribed 312 of the Whitmore letters, more than the next three volunteers combined, and insists this reflects available time rather than diligence.",
                "The letters, she reports, are largely about weather, prices, and illness, in that order. \"Which is the point,\" she says. \"You learn what a year was actually like. The dramatic ones are rare and you notice them.\"",
            ]),
            ("A Letter Worth the Project", [
                "Among the correspondence is a letter dated March 1878 from a cousin in Ohio, acknowledging receipt of a photograph and naming nine people in it.",
                "The society believes, with reasonable confidence, that this is the fourth plate of the Whitmore album — the storefront photograph. Seven of the nine names match identifications made independently from census evidence in 2023.",
                "It is the first time in the project that two entirely separate lines of evidence have confirmed one another.",
            ]),
            ("Research Tips: Reading Correspondence for Structure", [
                "Do not read a letter collection front to back looking for names. Read it for the shape of the network: who writes to whom, how often, and who is mentioned without explanation.",
                "The relative nobody explains is the one everybody knew. That is usually a sibling.",
            ]),
            ("Cemetery Preservation", [
                "Summer work focuses on maintenance rather than survey: resetting fallen stones at Cranberry Marsh, with guidance from a conservator and no adhesives of any kind.",
            ]),
            ("New Members", [
                "The society welcomes: Constance Abernathy, Rafael Vasquez, Ingeborg Lindqvist, Obinna Okonkwo, Silas Whitmore, and Prudence Calloway.",
            ]),
        ],
    },
    {
        "year": 2025, "season": "fall", "label": "Fall 2025", "volume": 43, "issue": 4,
        "lead": "Whitmore",
        "articles": [
            ("President's Message", [
                "The Whitmore project has changed how this society works. Three years ago we held four boxes nobody had opened. We now hold a searchable archive that has answered questions for researchers in six states.",
            ]),
            ("Member Spotlight: Silas Whitmore", [
                "Silas Whitmore joined in March, having learned of the society when a search for his great-great-grandmother returned the account books.",
                "\"I had been looking for eleven years,\" he says. \"She was in a farm ledger the whole time, under a name I would never have searched, because she was working there before she married.\"",
            ]),
            ("The Correspondence Complete", [
                "Transcription finished in September, eleven months after it began. 1,412 items, 1,109 of them legible in whole or part, transcribed by 34 volunteers.",
                "The index names 2,847 individuals. Of these, 611 do not appear in any census index for the county.",
            ]),
            ("Research Tips: Women Before Marriage", [
                "A woman's records before marriage are filed under a surname you may not know, which is the central difficulty of tracing any female line.",
                "Employment records, school registers, church membership admissions, and — as the Whitmore ledgers demonstrate — farm accounts capture women under their maiden names at exactly the age the census is least helpful.",
            ]),
            ("Annual Meeting and Election", [
                "The annual meeting convenes November 9 at 2:00 p.m. Three Board seats are contested. Candidate statements appear on page 6.",
            ]),
            ("Upcoming Programs", [
                "October 11 — What the Whitmore Project Taught Us, a panel with the transcription volunteers.",
                "November 9 — Annual meeting and election.",
                "December 13 — Members' show and tell.",
            ]),
        ],
    },
    {
        "year": 2026, "season": "winter", "label": "Winter 2026", "volume": 44, "issue": 1,
        "lead": "Ravensworth",
        "articles": [
            ("President's Message", [
                "This is my last message as president. Eight years is long enough, and the society is in better hands than mine going forward.",
                "What I will say is this: everything of value we accomplished was done by volunteers who were not asked twice. Look after them.",
            ]),
            ("Member Spotlight: Martin Ravensworth", [
                "Martin Ravensworth is the great-grandson of the man who compiled the 1958 genealogy this newsletter has criticised twice.",
                "He joined the correction committee voluntarily. \"He did the best anyone could in 1958 with no photocopier and no index,\" he says. \"He would have wanted it fixed. What he would not have wanted is people repeating it.\"",
                "The corrected Ravensworth chart, with full citations, is now the society's most requested finding aid.",
            ]),
            ("Looking Back at Forty-Four Years", [
                "The society was founded in 1982 by eleven people in a church basement. It now holds 571 members, 4,300 volumes, three transcribed manuscript collections, and four completed cemetery surveys.",
                "Every one of those things was optional. Nobody had to do any of it.",
            ]),
            ("Research Tips: Leaving a Trail for the Next Person", [
                "Your research will outlive your ability to explain it. Write for the stranger who inherits the boxes.",
                "Label photographs on the reverse in pencil, with names and the year. Keep a single index of what you hold and where it came from. State your uncertainties in writing — the next researcher cannot infer your doubts from a chart that shows none.",
            ]),
            ("Winter Workshop Series", [
                "Five Saturdays, January through March, with a new closing session: Preparing Your Research for Donation.",
            ]),
            ("New Members", [
                "The society welcomes: Imogen Ravensworth, Tobias Kilbride, Adaora Okonkwo, Elsa Lindqvist, Matteo Marchetti, and Wilhelmina Ashbury.",
            ]),
        ],
    },
]


def wrap(text, font, size, width, pdf):
    """Greedy wrap on measured width — reportlab has no paragraph flow here."""
    words, lines, line = text.split(), [], ""
    for w in words:
        trial = (line + " " + w).strip()
        if pdf.stringWidth(trial, font, size) <= width:
            line = trial
        else:
            if line:
                lines.append(line)
            line = w
    if line:
        lines.append(line)
    return lines


def draw_masthead(pdf, issue):
    y = PAGE_H - MARGIN
    pdf.setFont("Times-Bold", 30)
    pdf.drawCentredString(PAGE_W / 2, y - 22, MASTHEAD)
    pdf.setFont("Times-Italic", 12)
    pdf.drawCentredString(PAGE_W / 2, y - 42, SOCIETY)
    pdf.setFont("Helvetica", 8.5)
    pdf.drawCentredString(PAGE_W / 2, y - 58, ADDRESS)
    pdf.drawCentredString(PAGE_W / 2, y - 70, CONTACT)
    pdf.setLineWidth(1.4)
    pdf.line(MARGIN, y - 80, PAGE_W - MARGIN, y - 80)
    pdf.setFont("Helvetica-Bold", 10)
    pdf.drawString(MARGIN, y - 94, issue["label"])
    pdf.drawRightString(
        PAGE_W - MARGIN,
        y - 94,
        "Volume %d, Number %d" % (issue["volume"], issue["issue"]),
    )
    pdf.setLineWidth(0.5)
    pdf.line(MARGIN, y - 100, PAGE_W - MARGIN, y - 100)
    return y - 126


def draw_contents(pdf, issue, y, pages):
    """The printed contents table the plugin parses back out of the text."""
    pdf.setFont("Times-Bold", 14)
    pdf.drawCentredString(PAGE_W / 2, y, "Inside this Issue:")
    y -= 22

    entries = [(title, pages.get(title, 1)) for title, _ in blocks_for(issue)]
    half = (len(entries) + 1) // 2
    left, right = entries[:half], entries[half:]

    col_l, col_r = MARGIN + 6, MARGIN + (BODY_W / 2) + 24
    num_l = MARGIN + (BODY_W / 2) - 34
    num_r = PAGE_W - MARGIN - 6

    size = 9.0

    def fit(title, avail):
        """
        Trim a title so it cannot run into its own page number.

        WHY: the plugin reads this table back by looking for a gap of two or
        more spaces before the number. A title that reaches the number leaves
        one space or none, and the entry is parsed as a mangled fragment with
        the page digit welded to the last word.
        """
        if pdf.stringWidth(title, "Helvetica", size) <= avail:
            return title
        ell = "…"
        cut = title
        while cut and pdf.stringWidth(cut + ell, "Helvetica", size) > avail:
            cut = cut[:-1]
        return cut.rstrip(" ,:;-") + ell

    gap = 22          # kept clear between the longest title and the number
    avail_l = (num_l - col_l) - gap
    avail_r = (num_r - col_r) - gap

    pdf.setFont("Helvetica", size)
    for i in range(half):
        row_y = y - (i * 14)
        title, page = left[i]
        pdf.drawString(col_l, row_y, fit(title, avail_l))
        pdf.drawRightString(num_l, row_y, str(page))
        if i < len(right):
            title, page = right[i]
            pdf.drawString(col_r, row_y, fit(title, avail_r))
            pdf.drawRightString(num_r, row_y, str(page))

    return y - (half * 14) - 20


def draw_footer(pdf, issue, page_no):
    pdf.setFont("Helvetica", 8)
    pdf.drawString(MARGIN, 0.55 * inch, "%s — %s" % (MASTHEAD, issue["label"]))
    pdf.drawRightString(PAGE_W - MARGIN, 0.55 * inch, "Page %d" % page_no)


BODY_SIZE = 11.0
LEADING = 15.0


# Officers change over time, as they do in a real society. WHY it matters here:
# identical standing pages in all twelve issues made every surname match every
# issue, so a demo search returned the whole archive whatever you typed.
OFFICER_SLATES = [
    (2023, [
        "President: Eleanor Ashbury.  Vice-President: Desmond Calloway.  Secretary: Beatrice Lindqvist.  Treasurer: Walter Pendergast.",
        "Directors-at-Large: Cecily Thorne, Thomas Okonkwo, Delia Marchetti, Joan Abernathy.",
        "Committee chairs — Library: Priya Raman.  Cemetery: Sam Thorne.  Programs: Marisol Vasquez.  Publications: Martin Ravensworth.",
    ]),
    (2024, [
        "President: Eleanor Ashbury.  Vice-President: Cecily Thorne.  Secretary: Beatrice Lindqvist.  Treasurer: Walter Pendergast.",
        "Directors-at-Large: Thomas Okonkwo, Delia Marchetti, Joan Abernathy, Harriet Calloway.",
        "Committee chairs — Library: Priya Raman.  Cemetery: Sam Thorne.  Programs: Dominic Marchetti.  Publications: Grace Abernathy.",
    ]),
    (2025, [
        "President: Eleanor Ashbury.  Vice-President: Cecily Thorne.  Secretary: Solveig Lindqvist.  Treasurer: Augustus Pendergast.",
        "Directors-at-Large: Thomas Okonkwo, Marisol Vasquez, Joan Abernathy, Peter Kilbride.",
        "Committee chairs — Library: Verity Thorne.  Cemetery: Chidi Okonkwo.  Programs: Anneke Lindqvist.  Publications: Martin Ravensworth.",
    ]),
    (2026, [
        "President: Cecily Thorne.  Vice-President: Thomas Okonkwo.  Secretary: Solveig Lindqvist.  Treasurer: Augustus Pendergast.",
        "Directors-at-Large: Marisol Vasquez, Peter Kilbride, Imogen Ravensworth, Matteo Marchetti.",
        "Committee chairs — Library: Verity Thorne.  Cemetery: Chidi Okonkwo.  Programs: Adaora Okonkwo.  Publications: Wilhelmina Ashbury.",
    ]),
]


def officers_for(year):
    slate = OFFICER_SLATES[0][1]
    for y, s in OFFICER_SLATES:
        if year >= y:
            slate = s
    return slate


def back_matter(issue):
    """Standing sections every issue carries, as a real society newsletter does."""
    year = issue["year"]
    return [
        ("Society Officers", officers_for(year) + [
            "Officers may be reached through the society office during reading room hours, or by post at the Harrow Street address.",
        ]),
        ("Library Hours and Directions", [
            "The reading room is open Tuesday 10:00 a.m. to 8:00 p.m., Thursday 4:00 p.m. to 8:00 p.m., and the first Saturday of each month 10:00 a.m. to 4:00 p.m.",
            "The library is closed on public holidays and for two weeks at the end of December. Members may request access at other times by arrangement with the librarian.",
            "Harrow Street runs north from the courthouse square. Parking is available behind the building and on the street after 5:00 p.m. The rear entrance is step-free.",
            "Visitors are always welcome and are not asked to join. There is no charge to use the collection, and the Tuesday volunteers will happily start you off.",
        ]),
        ("Membership Information", [
            "Annual dues are $30 for an individual, $45 for a household, and $20 for students and those over 75. Life membership is $500.",
            "Dues run from January and may be paid at the reading room, by post, or through the society website. Members joining after October are credited through the following year.",
            "Membership includes this newsletter, borrowing privileges, access to the members' area of the website, and reduced rates on workshops.",
            "The society is a registered non-profit corporation. Donations of money, books, and family papers are gratefully received and are tax deductible to the extent the law allows.",
        ]),
        ("Calendar", [
            "Reading room: Tuesdays and Thursdays, plus first Saturdays. Board meets the second Monday of each month at 6:30 p.m.; members may attend.",
            "DNA Interest Group: first Thursday, 6:00 p.m.  Beginners' table: every Tuesday, 6:00 p.m., no booking required.",
            "Transcription volunteers meet informally on Thursday evenings. New volunteers are trained on the spot and may work from home thereafter.",
            "Submissions for the next issue are due six weeks before publication and should be sent to the publications chair. Queries from members are printed free of charge.",
        ]),
    ]


def blocks_for(issue):
    return list(issue["articles"]) + back_matter(issue)


def lay_out(pdf, issue, pages, draw):
    """
    Walk the whole issue once.

    WHY this runs twice rather than estimating: the contents block prints a page
    number against every article, and an estimated number is a number that is
    sometimes wrong. The first pass draws nothing and only records where each
    heading actually landed; the second pass draws with those numbers in hand.
    """
    page_no = 1
    if draw:
        y = draw_masthead(pdf, issue)
        y = draw_contents(pdf, issue, y, pages)
    else:
        # Same vertical cost as the masthead and contents, without drawing them.
        rows = (len(blocks_for(issue)) + 1) // 2
        y = PAGE_H - MARGIN - 126 - 22 - (rows * 14) - 20

    def new_page():
        nonlocal page_no, y
        if draw:
            draw_footer(pdf, issue, page_no)
            pdf.showPage()
        page_no += 1
        y = PAGE_H - MARGIN - 20

    for idx, (title, paras) in enumerate(blocks_for(issue)):
        # Never strand a heading alone at the foot of a page.
        if y - (LEADING * 3) < 1.0 * inch and idx > 0:
            new_page()

        pages.setdefault(title, page_no)
        if not draw:
            pages[title] = page_no

        if draw:
            pdf.setFont("Times-Bold", 13.5)
            pdf.drawString(MARGIN, y, title)
        y -= 19

        for para in paras:
            for line in wrap(para, "Times-Roman", BODY_SIZE, BODY_W, pdf):
                if y < 1.0 * inch:
                    new_page()
                if draw:
                    pdf.setFont("Times-Roman", BODY_SIZE)
                    pdf.drawString(MARGIN, y, line)
                y -= LEADING
            y -= 7
        y -= 10

    if draw:
        draw_footer(pdf, issue, page_no)
    return page_no


def build_issue(issue, out_dir):
    stem = "kc-%d-%s" % (issue["year"], issue["season"])
    pdf_path = out_dir / (stem + ".pdf")

    # Pass one: measure. The canvas is needed for string widths but never saved.
    pages = {}
    measure = canvas.Canvas(str(out_dir / (stem + ".measure.tmp")), pagesize=letter)
    lay_out(measure, issue, pages, draw=False)

    # Pass two: draw, with real page numbers in the contents table.
    pdf = canvas.Canvas(str(pdf_path), pagesize=letter)
    pdf.setTitle("%s — %s" % (MASTHEAD, issue["label"]))
    pdf.setAuthor(SOCIETY)
    total = lay_out(pdf, issue, pages, draw=True)
    pdf.save()
    (out_dir / (stem + ".measure.tmp")).unlink(missing_ok=True)

    # Cover JPG from page one, matching what attach-newsletters.php expects.
    cover_path = out_dir / (stem + "-cover.jpg")
    subprocess.run(
        ["gs", "-q", "-dNOPAUSE", "-dBATCH", "-dSAFER", "-sDEVICE=jpeg",
         "-dJPEGQ=88", "-r150", "-dFirstPage=1", "-dLastPage=1",
         "-sOutputFile=%s" % cover_path, str(pdf_path)],
        check=True,
    )
    return pdf_path, cover_path


def main():
    out_dir = Path(sys.argv[1] if len(sys.argv) > 1 else ".")
    out_dir.mkdir(parents=True, exist_ok=True)
    for issue in ISSUES:
        pdf_path, cover_path = build_issue(issue, out_dir)
        print("%-28s %7d bytes   cover %6d bytes"
              % (pdf_path.name, pdf_path.stat().st_size, cover_path.stat().st_size))
    print("\n%d issues written to %s" % (len(ISSUES), out_dir))


if __name__ == "__main__":
    main()
