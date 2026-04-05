#!/usr/bin/env python3
"""
Generate a realistic EasyNetSites/Blue Crab Software CSV export for testing
the SocietyPress member import.

Target: ~580 members from the North Dakota area.
- Mix of individuals, families, organizations, students, lifetime, patron
- Some expiring soon, some lapsed, some new, some in leadership
"""

import csv
import random
import sys
from datetime import date, timedelta

random.seed(42)  # Reproducible results

# ============================================================================
# DATA POOLS
# ============================================================================

FIRST_NAMES_M = [
    "James", "John", "Robert", "Michael", "David", "William", "Richard", "Thomas",
    "Charles", "Daniel", "Matthew", "Anthony", "Mark", "Donald", "Steven", "Andrew",
    "Paul", "Joshua", "Kenneth", "Kevin", "Brian", "George", "Timothy", "Ronald",
    "Edward", "Jason", "Jeffrey", "Ryan", "Jacob", "Gary", "Nicholas", "Eric",
    "Jonathan", "Stephen", "Larry", "Justin", "Scott", "Brandon", "Benjamin",
    "Samuel", "Raymond", "Gregory", "Frank", "Patrick", "Jack", "Dennis",
    "Jerry", "Alexander", "Tyler", "Henry", "Douglas", "Peter", "Adam", "Nathan",
    "Zachary", "Walter", "Harold", "Gerald", "Carl", "Arthur", "Lawrence",
    "Dylan", "Jesse", "Jordan", "Bryan", "Billy", "Bruce", "Ralph", "Roy",
    "Eugene", "Russell", "Louis", "Philip", "Randy", "Howard", "Vincent",
    "Liam", "Noah", "Elijah", "Logan", "Mason", "Oliver", "Ethan", "Lucas",
    "Aiden", "Caleb", "Owen", "Wyatt", "Luke", "Julian", "Levi", "Isaac",
    "Gabriel", "Lincoln", "Jaxon", "Nolan", "Hunter", "Connor", "Colton",
]

FIRST_NAMES_F = [
    "Mary", "Patricia", "Jennifer", "Linda", "Barbara", "Elizabeth", "Susan",
    "Jessica", "Sarah", "Karen", "Lisa", "Nancy", "Betty", "Margaret", "Sandra",
    "Ashley", "Dorothy", "Kimberly", "Emily", "Donna", "Michelle", "Carol",
    "Amanda", "Melissa", "Deborah", "Stephanie", "Rebecca", "Sharon", "Laura",
    "Cynthia", "Kathleen", "Amy", "Angela", "Shirley", "Anna", "Brenda",
    "Pamela", "Emma", "Nicole", "Helen", "Samantha", "Katherine", "Christine",
    "Debra", "Rachel", "Carolyn", "Janet", "Catherine", "Maria", "Heather",
    "Diane", "Ruth", "Julie", "Olivia", "Joyce", "Virginia", "Victoria",
    "Kelly", "Lauren", "Christina", "Joan", "Evelyn", "Judith", "Andrea",
    "Hannah", "Megan", "Cheryl", "Jacqueline", "Martha", "Gloria", "Teresa",
    "Ann", "Sara", "Madison", "Frances", "Kathryn", "Janice", "Jean", "Abigail",
    "Sophia", "Isabella", "Mia", "Charlotte", "Amelia", "Harper", "Ella",
    "Avery", "Scarlett", "Grace", "Lily", "Chloe", "Zoey", "Riley", "Layla",
]

# Norwegian, German, and English surnames common in North Dakota
LAST_NAMES = [
    "Anderson", "Berg", "Berger", "Benson", "Carlson", "Christenson", "Dahlgren",
    "Erickson", "Fischer", "Gunderson", "Gustafson", "Hagen", "Halvorson",
    "Hanson", "Hansen", "Haugen", "Hedstrom", "Helgeson", "Iverson", "Jacobson",
    "Jensen", "Johnson", "Knutson", "Larson", "Lee", "Lindgren", "Lund",
    "Meyer", "Miller", "Moen", "Nelson", "Nygaard", "Olson", "Pedersen",
    "Peterson", "Rasmussen", "Reistad", "Sandberg", "Sather", "Schmidt",
    "Schneider", "Schultz", "Solberg", "Sorensen", "Stenberg", "Strand",
    "Svenson", "Thompson", "Thorson", "Wagner", "Weber", "Williams",
    "Wolff", "Zimmerman", "Bakken", "Braaten", "Dahl", "Engen", "Falk",
    "Fossum", "Gilbertson", "Grandahl", "Holm", "Hovland", "Keller",
    "Kraft", "Langdon", "Morken", "Nordstrom", "Oberg", "Paulson",
    "Quale", "Ringness", "Severson", "Torgerson", "Ulrich", "Vold",
    "Wold", "Aanderud", "Bjerke", "Domogalla", "Ehrenberg", "Froelich",
    "Graber", "Huber", "Krause", "Lehmann", "Muller", "Pfeiffer",
    "Richter", "Schaefer", "Vogt", "Weiss", "Brunsdale", "Stricklin",
]

# North Dakota cities and zip codes
ND_CITIES = [
    ("Bismarck", "58501"), ("Bismarck", "58503"), ("Bismarck", "58504"),
    ("Fargo", "58102"), ("Fargo", "58103"), ("Fargo", "58104"),
    ("Grand Forks", "58201"), ("Grand Forks", "58203"),
    ("Minot", "58701"), ("Minot", "58703"),
    ("West Fargo", "58078"), ("Williston", "58801"), ("Dickinson", "58601"),
    ("Mandan", "58554"), ("Jamestown", "58401"), ("Wahpeton", "58075"),
    ("Devils Lake", "58301"), ("Valley City", "58072"),
    ("Watford City", "58854"), ("Grafton", "58237"),
    ("Beulah", "58523"), ("Rugby", "58368"), ("Bottineau", "58318"),
    ("Cavalier", "58220"), ("Carrington", "58421"), ("Hillsboro", "58045"),
    ("Lisbon", "58054"), ("Oakes", "58474"), ("Park River", "58270"),
    ("Langdon", "58249"), ("Crosby", "58730"), ("Hettinger", "58639"),
    ("Ellendale", "58436"), ("Garrison", "58540"), ("Harvey", "58341"),
    ("New Salem", "58563"), ("Hazen", "58545"), ("Linton", "58552"),
    ("Wishek", "58495"), ("Kenmare", "58746"), ("Stanley", "58784"),
    ("Tioga", "58852"), ("Bowman", "58623"), ("Beach", "58621"),
    ("Walhalla", "58282"), ("Northwood", "58267"), ("Mayville", "58257"),
    ("Cooperstown", "58425"), ("Enderlin", "58027"), ("Medora", "58645"),
    ("Washburn", "58577"), ("Steele", "58482"), ("Ashley", "58413"),
]

ND_STREETS = [
    "Main St", "Broadway", "Front St", "Oak Ave", "Elm St", "1st Ave N",
    "2nd Ave S", "3rd St NW", "4th Ave NE", "5th St S", "6th Ave N",
    "University Dr", "State St", "River Rd", "Prairie Dr", "Sunset Blvd",
    "Heritage Ln", "Pioneer Way", "Dakota Ave", "Meadow Ln", "Birch St",
    "Maple Ave", "Cedar Ct", "Willow Dr", "Country Club Rd", "Airport Rd",
    "Division St", "Central Ave", "Washington St", "Lincoln Ave",
    "Park Ave", "Church St", "School Rd", "Railway Ave", "Industrial Blvd",
    "Hillside Dr", "Valley View Rd", "Lakeview Dr", "Rosewood Ln",
]

# Organizations in ND
ORG_NAMES = [
    "Bismarck Public Library", "State Historical Society of North Dakota",
    "North Dakota Heritage Center", "Fargo Public Library",
    "Grand Forks County Historical Society", "Minot Public Library",
    "Red River Valley Genealogical Society", "Cass County Historical Society",
    "Pembina County Historical Museum", "Barnes County Historical Society",
    "Stutsman County Memorial Museum", "Fort Abraham Lincoln Foundation",
    "Bonanzaville USA", "Ward County Historical Society",
    "Williams County Historical Society", "Burleigh County Historical Society",
    "Morton County Historical Society", "McLean County Historical Society",
    "Richland County Historical Society", "Traill County Historical Society",
    "Griggs County Historical Society", "LaMoure County Historical Society",
    "Ransom County Historical Museum", "Walsh County Historical Society",
    "Bottineau County Historical Society", "Rolette County Historical Society",
    "Dunn County Historical Society", "Stark County Historical Society",
    "Mercer County Historical Society", "Dakota Territory Museum",
]

TIERS = [
    ("Individual", 0.60),
    ("Family", 0.20),
    ("Student", 0.05),
    ("Lifetime", 0.04),
    ("Patron", 0.06),
    ("Organization", 0.05),
]

SKILLS = [
    "German translation", "Norwegian translation", "Swedish translation",
    "Cemetery transcription", "Courthouse research", "DNA analysis",
    "Digital scanning", "Newspaper research", "Military records",
    "Land records", "Church records", "Immigration records",
    "Census research", "Oral history interviews", "Photography",
    "Genealogy software", "Website design", "Database management",
    "Writing", "Public speaking", "Teaching", "Bookkeeping",
    "Grant writing", "Event planning", "Social media",
]

INTERESTS = [
    "Norwegian immigration", "German-Russian heritage", "Homesteading era",
    "Native American history", "Railroad history", "Military history",
    "Scandinavian genealogy", "DNA genealogy", "Civil War records",
    "Pioneer women", "One-room schoolhouses", "Frontier medicine",
    "County histories", "Church records", "Cemetery preservation",
    "Immigration patterns", "Farm families", "Town histories",
    "Fur trade era", "Red River Valley history", "Bonanza farming",
]

LEADERSHIP_ROLES = [
    "Board President", "Vice President", "Secretary", "Treasurer",
    "Board Member", "Past President", "Newsletter Editor",
    "Library Chair", "Programs Chair", "Membership Chair",
    "Publicity Chair", "Webmaster", "Records Committee",
    "Cemetery Committee", "Education Committee", "Fundraising Committee",
    "Volunteer Coordinator", "Social Media Manager", "Archivist",
    "Research Committee Chair",
]

PREFIXES = ["Mr.", "Mrs.", "Ms.", "Dr.", "Rev."]
SUFFIXES = ["Jr.", "Sr.", "III", "Ph.D.", "MD"]

EMAIL_DOMAINS = [
    "gmail.com", "yahoo.com", "hotmail.com", "outlook.com", "aol.com",
    "icloud.com", "msn.com", "live.com", "mail.com", "protonmail.com",
    "midco.net", "bektel.com", "gondtc.com", "westriv.com", "restel.com",
    "ndsupernet.com", "drtel.net", "polarcomm.com",
]

TODAY = date(2026, 4, 5)


def random_phone():
    """Generate a 701-xxx-xxxx phone number (North Dakota area code)."""
    return f"701-{random.randint(200, 999)}-{random.randint(1000, 9999)}"


def random_date_between(start, end):
    """Random date between start and end (inclusive)."""
    delta = (end - start).days
    return start + timedelta(days=random.randint(0, delta))


def format_date(d):
    """MM/DD/YYYY format for ENS CSV."""
    return d.strftime("%m/%d/%Y")


def generate_email(first, last, domain=None):
    """Generate a plausible email address."""
    if not domain:
        domain = random.choice(EMAIL_DOMAINS)
    patterns = [
        f"{first.lower()}.{last.lower()}@{domain}",
        f"{first[0].lower()}{last.lower()}@{domain}",
        f"{first.lower()}{last[0].lower()}@{domain}",
        f"{first.lower()}_{last.lower()}@{domain}",
        f"{last.lower()}{first[0].lower()}@{domain}",
    ]
    return random.choice(patterns)


def pick_tier():
    """Weighted random tier selection."""
    r = random.random()
    cumulative = 0
    for name, weight in TIERS:
        cumulative += weight
        if r < cumulative:
            return name
    return "Individual"


def generate_members():
    members = []
    used_emails = set()
    leadership_assigned = 0
    member_id = 0

    # ---- Organizations (30) ----
    for org_name in ORG_NAMES:
        member_id += 1
        city, zip_code = random.choice(ND_CITIES)
        join_date = random_date_between(date(2015, 1, 1), date(2024, 12, 31))

        # Most active, a few lapsed
        if random.random() < 0.85:
            exp_date = random_date_between(date(2026, 7, 1), date(2027, 6, 30))
            active = "Yes"
        else:
            exp_date = random_date_between(date(2025, 6, 1), date(2026, 2, 28))
            active = "No"

        email = f"info@{org_name.lower().replace(' ', '').replace('&', '')[:20]}.org"
        if email in used_emails:
            email = f"contact@{org_name.lower().replace(' ', '')[:15]}.org"
        used_emails.add(email)

        members.append({
            "First Name": "",
            "Last Name": "",
            "Name Prefix": "",
            "Name Suffix": "",
            "File Name": org_name,
            "Email": email,
            "Telephone": random_phone(),
            "Cell Phone": "",
            "Address 1": f"{random.randint(100, 9999)} {random.choice(ND_STREETS)}",
            "Address 2": random.choice(["", "", "", "Suite 200", "PO Box " + str(random.randint(100, 999))]),
            "City": city,
            "State / Province": "ND",
            "Postal Code": zip_code,
            "Country": "US",
            "Member Active": active,
            "Member Join Date": format_date(join_date),
            "Expiration Date": format_date(exp_date),
            "Membership Plan": "Organization",
            "Membership Type": "Organization",
            "Your Skills": "",
            "Your Interests": "",
            "Volunteering?": "No",
            "Administrative Notes": "",
            "Joint Member": "",
            "Email of Joint": "",
            "Mbr. List - Show Name": "Yes",
            "Mbr. List - Show Address": "Yes",
            "Mbr. List - Show Phone": "Yes",
            "Mbr. List - Show Email": "Yes",
            "Birth Year": "",
            "Gender": "",
        })

    # ---- Individuals (~550) ----
    target = 570
    while len(members) < target:
        member_id += 1
        is_female = random.random() < 0.55  # slightly more women in genealogy
        first = random.choice(FIRST_NAMES_F if is_female else FIRST_NAMES_M)
        last = random.choice(LAST_NAMES)
        city, zip_code = random.choice(ND_CITIES)
        tier = pick_tier()
        if tier == "Organization":
            tier = "Individual"  # orgs handled above

        # Generate unique email
        email = generate_email(first, last)
        attempts = 0
        while email in used_emails and attempts < 10:
            email = generate_email(first, last, random.choice(EMAIL_DOMAINS))
            attempts += 1
        if email in used_emails:
            email = f"{first.lower()}{member_id}@{random.choice(EMAIL_DOMAINS)}"
        used_emails.add(email)

        # ---- Status distribution ----
        # Decide what category this member falls into
        roll = random.random()

        if roll < 0.04:
            # NEW — joined within last 30 days (~22 members)
            join_date = random_date_between(TODAY - timedelta(days=30), TODAY)
            exp_date = date(join_date.year + 1, join_date.month, min(join_date.day, 28))
            active = "Yes"
        elif roll < 0.18:
            # LAPSED — expired before today (~80 members)
            exp_date = random_date_between(date(2025, 3, 1), TODAY - timedelta(days=1))
            join_date = random_date_between(date(2014, 1, 1), date(2024, 6, 30))
            active = "No"
        elif roll < 0.24:
            # EXPIRING SOON — within next 30 days (~35 members)
            exp_date = random_date_between(TODAY, TODAY + timedelta(days=30))
            join_date = random_date_between(date(2015, 1, 1), date(2025, 4, 30))
            active = "Yes"
        else:
            # ACTIVE — expiration well in the future
            join_date = random_date_between(date(2012, 1, 1), date(2025, 12, 31))
            exp_date = random_date_between(date(2026, 6, 1), date(2027, 12, 31))
            active = "Yes"

        # Lifetime members: no expiration
        if tier == "Lifetime":
            exp_date = date(2099, 12, 31)
            active = "Yes"
            join_date = random_date_between(date(2010, 1, 1), date(2024, 12, 31))

        # Prefix/suffix (occasional)
        prefix = random.choice(PREFIXES) if random.random() < 0.08 else ""
        suffix = random.choice(SUFFIXES) if random.random() < 0.03 else ""

        # Skills and interests (maybe 30% have them)
        skills = ", ".join(random.sample(SKILLS, k=random.randint(1, 3))) if random.random() < 0.30 else ""
        interests = ", ".join(random.sample(INTERESTS, k=random.randint(1, 3))) if random.random() < 0.35 else ""

        # Volunteering
        volunteering = "Yes" if random.random() < 0.20 else "No"

        # Leadership roles (first ~25 active members)
        admin_notes = ""
        if leadership_assigned < len(LEADERSHIP_ROLES) and active == "Yes" and random.random() < 0.06:
            admin_notes = LEADERSHIP_ROLES[leadership_assigned]
            leadership_assigned += 1
            volunteering = "Yes"

        # Family memberships get joint member info
        joint_name = ""
        joint_email = ""
        if tier == "Family":
            spouse_first = random.choice(FIRST_NAMES_M if is_female else FIRST_NAMES_F)
            joint_name = f"{spouse_first} {last}"
            joint_email = generate_email(spouse_first, last)

        # Birth year (range for realism)
        if tier == "Student":
            birth_year = str(random.randint(1998, 2006))
        else:
            birth_year = str(random.randint(1940, 2000)) if random.random() < 0.6 else ""

        # Directory preferences
        show_name = "Yes"
        show_addr = "Yes" if random.random() < 0.7 else "No"
        show_phone = "Yes" if random.random() < 0.5 else "No"
        show_email = "Yes" if random.random() < 0.8 else "No"

        members.append({
            "First Name": first,
            "Last Name": last,
            "Name Prefix": prefix,
            "Name Suffix": suffix,
            "File Name": "",
            "Email": email,
            "Telephone": random_phone() if random.random() < 0.7 else "",
            "Cell Phone": random_phone() if random.random() < 0.5 else "",
            "Address 1": f"{random.randint(100, 9999)} {random.choice(ND_STREETS)}",
            "Address 2": random.choice(["", "", "", "", "Apt " + str(random.randint(1, 20)), "Unit " + random.choice("ABCD")]),
            "City": city,
            "State / Province": "ND",
            "Postal Code": zip_code,
            "Country": "US",
            "Member Active": active,
            "Member Join Date": format_date(join_date),
            "Expiration Date": format_date(exp_date),
            "Membership Plan": tier,
            "Membership Type": "Individual",
            "Your Skills": skills,
            "Your Interests": interests,
            "Volunteering?": volunteering,
            "Administrative Notes": admin_notes,
            "Joint Member": joint_name,
            "Email of Joint": joint_email,
            "Mbr. List - Show Name": show_name,
            "Mbr. List - Show Address": show_addr,
            "Mbr. List - Show Phone": show_phone,
            "Mbr. List - Show Email": show_email,
            "Birth Year": birth_year,
            "Gender": "Female" if is_female else "Male",
        })

    # Shuffle so orgs aren't all at the top
    random.shuffle(members)
    return members


def main():
    members = generate_members()

    fieldnames = [
        "First Name", "Last Name", "Name Prefix", "Name Suffix", "File Name",
        "Email", "Telephone", "Cell Phone",
        "Address 1", "Address 2", "City", "State / Province", "Postal Code", "Country",
        "Member Active", "Member Join Date", "Expiration Date",
        "Membership Plan", "Membership Type",
        "Your Skills", "Your Interests", "Volunteering?",
        "Administrative Notes",
        "Joint Member", "Email of Joint",
        "Mbr. List - Show Name", "Mbr. List - Show Address",
        "Mbr. List - Show Phone", "Mbr. List - Show Email",
        "Birth Year", "Gender",
    ]

    output = sys.argv[1] if len(sys.argv) > 1 else "kindred-nd-genealogical-society-members.csv"

    with open(output, "w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(members)

    # Stats
    total = len(members)
    orgs = sum(1 for m in members if m["Membership Type"] == "Organization")
    active = sum(1 for m in members if m["Member Active"] == "Yes")
    lapsed = sum(1 for m in members if m["Member Active"] == "No")
    new_30 = sum(1 for m in members if m["Member Active"] == "Yes" and m["Member Join Date"]
                 and (TODAY - timedelta(days=30)) <= date(
                     int(m["Member Join Date"].split("/")[2]),
                     int(m["Member Join Date"].split("/")[0]),
                     int(m["Member Join Date"].split("/")[1])
                 ) <= TODAY)
    lifetime = sum(1 for m in members if m["Membership Plan"] == "Lifetime")
    leaders = sum(1 for m in members if m["Administrative Notes"])

    print(f"Generated {total} members → {output}")
    print(f"  Organizations: {orgs}")
    print(f"  Active: {active}")
    print(f"  Lapsed: {lapsed}")
    print(f"  New (last 30 days): {new_30}")
    print(f"  Lifetime: {lifetime}")
    print(f"  Leadership roles: {leaders}")
    print(f"  Tiers: ", end="")
    tier_counts = {}
    for m in members:
        t = m["Membership Plan"]
        tier_counts[t] = tier_counts.get(t, 0) + 1
    print(", ".join(f"{k}: {v}" for k, v in sorted(tier_counts.items())))


if __name__ == "__main__":
    main()
