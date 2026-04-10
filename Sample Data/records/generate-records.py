#!/usr/bin/env python3
"""
Generate sample genealogical record CSVs for SAGHS (San Antonio Genealogical & Historical Society).

All data is fictional but historically plausible for San Antonio / Bexar County, Texas.
Names reflect the actual ethnic diversity of the region: Spanish/Mexican, German, Anglo,
African American, Polish/Czech, and others.

Historical constraints enforced:
- Given names match surname ethnicity (with some intermarriage crossover)
- Newspaper publication dates respected (La Prensa 1913-1963, etc.)
- Military units matched to correct wars
- Polish settlers only appear after 1854 (Panna Maria founding)
- Fort Sam Houston references only after 1876
- Church denominations matched to ethnic groups
- Land grant types matched to correct eras
- Census race codes matched to surname ethnicity

Output: one CSV per record type, ready to import into the SocietyPress Records module
via the "one-step create" CSV import (headers become field names automatically).
"""

import csv
import random
import os

random.seed(42)  # reproducible

OUT_DIR = os.path.dirname(os.path.abspath(__file__))

# ---------------------------------------------------------------------------
# Name pools — organized by ethnicity for pairing
# ---------------------------------------------------------------------------

ETHNIC_GROUPS = {
    "spanish": {
        "surnames": [
            "Garcia", "Rodriguez", "Martinez", "Navarro", "de la Garza", "Seguin",
            "Ruiz", "Flores", "Hernandez", "Perez", "Salazar", "Gonzalez",
            "Veramendi", "Cassiano", "Leal", "Trevino", "Losoya", "Esparza",
            "Zambrano", "Arocha", "Menchaca", "Bustillo", "Camarena", "Delgado",
            "Montalvo", "Ximenes", "Barrera", "Cantu", "Villarreal", "Garza",
            "Gutierrez", "Sanchez", "Morales", "Reyes", "Vela", "Luna",
            "Calderon", "Fuentes", "Ibarra", "Padilla", "Soto", "Trujillo",
        ],
        "male_given": [
            "Juan", "Jose", "Pedro", "Miguel", "Francisco", "Antonio", "Manuel",
            "Carlos", "Luis", "Rafael", "Andres", "Alejandro", "Guadalupe",
            "Ramon", "Ignacio", "Tomas", "Felipe", "Domingo", "Santiago",
            "Esteban", "Lorenzo", "Vicente", "Pablo", "Fernando", "Cristobal",
        ],
        "female_given": [
            "Maria", "Josefa", "Guadalupe", "Juana", "Petra", "Dolores",
            "Rosa", "Elena", "Teresa", "Luz", "Carmen", "Francisca",
            "Antonia", "Concepcion", "Soledad", "Esperanza", "Trinidad",
            "Margarita", "Isabel", "Catalina", "Manuela", "Leonor",
        ],
        "race": "M",  # Mexican/Hispanic on census
        "earliest": 1718,  # San Antonio founding
    },
    "german": {
        "surnames": [
            "Menger", "Steves", "Guenther", "Altgelt", "Elmendorf", "Braunig",
            "Kampmann", "Koehler", "Groos", "Heusinger", "Toepperwein", "Kalteyer",
            "Thielepape", "Wurzbach", "Beckmann", "Fink", "Herff", "Scheele",
            "Pfeiffer", "Boerner", "Dittmar", "Eiband", "Schuetze", "Wulff",
            "Nagel", "Mueller", "Richter", "Schneider", "Fischer", "Weber",
        ],
        "male_given": [
            "Friedrich", "Wilhelm", "Heinrich", "Karl", "Otto", "Ernst",
            "Herman", "Albert", "Frederick", "August", "Ludwig", "Gustav",
            "Franz", "Max", "Rudolf", "Theodor", "Eduard", "Conrad",
            "Adolph", "Emil", "Hugo", "Leopold", "Bernhard", "Gottfried",
        ],
        "female_given": [
            "Frieda", "Wilhelmina", "Hedwig", "Gertrud", "Auguste",
            "Emma", "Anna", "Clara", "Bertha", "Ida", "Mathilde",
            "Elise", "Dorothea", "Johanna", "Pauline", "Amalie",
            "Helene", "Luise", "Margarethe", "Ottilie", "Rosalie",
        ],
        "race": "W",
        "earliest": 1844,  # German immigration wave to Texas
    },
    "anglo": {
        "surnames": [
            "Maverick", "Twohig", "Dignowity", "French", "Frost", "Brackenridge",
            "King", "Smith", "Jones", "Walker", "Thompson", "Clark",
            "Wilson", "Taylor", "Moore", "Hall", "White",
            "Allen", "Young", "Wright", "Mitchell", "Carter", "Roberts",
            "Phillips", "Campbell", "Parker", "Edwards", "Stewart", "Morris",
        ],
        "male_given": [
            "James", "John", "William", "Robert", "Charles", "George", "Joseph",
            "Thomas", "Henry", "Edward", "Samuel", "Benjamin", "David", "Daniel",
            "Arthur", "Frank", "Walter", "Richard", "Harold", "Leonard",
            "Clarence", "Raymond", "Eugene", "Chester", "Earl",
        ],
        "female_given": [
            "Mary", "Elizabeth", "Sarah", "Margaret", "Catherine",
            "Martha", "Alice", "Florence", "Ruth", "Helen",
            "Dorothy", "Mildred", "Frances", "Virginia", "Louise",
            "Ethel", "Bessie", "Minnie", "Pearl", "Nellie",
            "Mattie", "Fannie", "Lena", "Viola", "Lillie",
        ],
        "race": "W",
        "earliest": 1820,
    },
    "black": {
        "surnames": [
            "Anderson", "Washington", "Jackson", "Brown", "Davis", "Walker",
            "Harris", "Robinson", "Green", "Lewis", "Scott", "Baker",
            "Turner", "Coleman", "Reed", "Bell", "Patterson",
            "Freeman", "Henderson", "Brooks", "Price", "Howard", "Sanders",
        ],
        "male_given": [
            "James", "John", "William", "George", "Henry", "Charles",
            "Augustus", "Elijah", "Abraham", "Moses", "Isaiah", "Solomon",
            "Samuel", "Joseph", "Robert", "Homer", "Luther", "Rufus",
            "Floyd", "Chester", "Booker", "Emmett", "Leon", "Willis",
        ],
        "female_given": [
            "Harriet", "Mary", "Sarah", "Martha", "Mattie", "Fannie",
            "Viola", "Lillie", "Pearl", "Bessie", "Minnie", "Nellie",
            "Ruth", "Ethel", "Mildred", "Hattie", "Lottie", "Rosie",
            "Ella", "Willie Mae", "Beulah", "Josephine",
        ],
        "race": "B",
        "earliest": 1790,  # enslaved people present from Spanish era
    },
    "polish": {
        "surnames": [
            "Moczygemba", "Lyssy", "Pawelek", "Dziuk", "Bednarz", "Gawlik",
            "Kotara", "Jarzombek", "Urbanski", "Wiatrek", "Polasek", "Kozlowski",
            "Szymanski", "Nowak", "Kowalski", "Jankowski",
        ],
        "male_given": [
            "Stanislaus", "Kazimierz", "Wojciech", "Tadeusz", "Jan",
            "Jozef", "Antoni", "Franciszek", "Tomasz", "Piotr",
            "Stefan", "Wladyslaw", "Michal", "Andrzej", "Pawel",
        ],
        "female_given": [
            "Stella", "Bronislawa", "Wanda", "Jadwiga", "Anna",
            "Maria", "Katarzyna", "Zofia", "Helena", "Agnieszka",
            "Rozalia", "Franciszka", "Marianna", "Tekla", "Ewa",
        ],
        "race": "W",
        "earliest": 1854,  # Panna Maria founding
    },
}

# Build lookup: surname -> ethnicity
SURNAME_TO_ETHNIC = {}
for eth, data in ETHNIC_GROUPS.items():
    for s in data["surnames"]:
        SURNAME_TO_ETHNIC[s] = eth

ALL_SURNAMES = []
for data in ETHNIC_GROUPS.values():
    ALL_SURNAMES.extend(data["surnames"])

def random_person(year=None):
    """Return (surname, given_name, sex, ethnicity) with matched names.
    If year is given, exclude ethnic groups not yet present."""
    groups = list(ETHNIC_GROUPS.keys())
    if year:
        groups = [g for g in groups if ETHNIC_GROUPS[g]["earliest"] <= year]
    if not groups:
        groups = ["spanish"]  # fallback

    # Weight toward Spanish pre-1844, then more diverse
    if year and year < 1844:
        weights = [10 if g == "spanish" else (3 if g in ("anglo", "black") else 1) for g in groups]
    else:
        weights = [1] * len(groups)

    eth = random.choices(groups, weights=weights)[0]
    data = ETHNIC_GROUPS[eth]
    sex = random.choice(["M", "F"])
    surname = random.choice(data["surnames"])
    given = random.choice(data["male_given"] if sex == "M" else data["female_given"])
    return surname, given, sex, eth

def random_male_person(year=None):
    """Return (surname, given_name, ethnicity) for a male."""
    groups = list(ETHNIC_GROUPS.keys())
    if year:
        groups = [g for g in groups if ETHNIC_GROUPS[g]["earliest"] <= year]
    if not groups:
        groups = ["spanish"]
    eth = random.choice(groups)
    data = ETHNIC_GROUPS[eth]
    return random.choice(data["surnames"]), random.choice(data["male_given"]), eth

def random_female_person(year=None):
    """Return (surname, given_name, ethnicity) for a female."""
    groups = list(ETHNIC_GROUPS.keys())
    if year:
        groups = [g for g in groups if ETHNIC_GROUPS[g]["earliest"] <= year]
    if not groups:
        groups = ["spanish"]
    eth = random.choice(groups)
    data = ETHNIC_GROUPS[eth]
    return random.choice(data["surnames"]), random.choice(data["female_given"]), eth

def race_for_ethnicity(eth):
    return ETHNIC_GROUPS[eth]["race"]

def male_given_for(eth):
    return random.choice(ETHNIC_GROUPS[eth]["male_given"])

def female_given_for(eth):
    return random.choice(ETHNIC_GROUPS[eth]["female_given"])

# ---------------------------------------------------------------------------
# San Antonio location data
# ---------------------------------------------------------------------------

CEMETERIES = [
    "San Fernando Cemetery No. 1", "San Fernando Cemetery No. 2",
    "City Cemetery No. 1", "City Cemetery No. 4",
    "Confederate Cemetery", "Fort Sam Houston National Cemetery",
    "Mission Burial Ground", "Odd Fellows Rest Cemetery",
    "St. Mary's Cemetery", "Masonic Cemetery",
    "San Jose Burial Ground", "Alamo Masonic Cemetery",
    "Sunset Memorial Park", "Mission Park Cemetery",
    "Holy Cross Cemetery", "St. John's Lutheran Cemetery",
]

# Churches matched to denominations for ethnic pairing
CHURCHES_CATHOLIC = [
    "San Fernando Cathedral", "St. Mary's Catholic Church",
    "Immaculate Conception of Mary", "St. Joseph's Catholic Church",
    "St. Michael's Catholic Church", "Mission Concepcion",
    "Mission San Jose", "Mission Espada", "Mission San Juan Capistrano",
]
CHURCHES_CATHOLIC_POLISH = ["Panna Maria Catholic Church", "St. Michael's Catholic Church"]
CHURCHES_PROTESTANT_GERMAN = ["St. John's Lutheran Church", "German Evangelical Church"]
CHURCHES_PROTESTANT_ANGLO = [
    "St. Mark's Episcopal Church", "First Presbyterian Church",
    "Travis Park United Methodist Church", "First Baptist Church",
    "Madison Square Presbyterian", "Alamo Methodist Church",
    "St. Paul's Episcopal Church",
]
CHURCHES_BLACK = [
    "Mt. Zion Baptist Church", "Second Baptist Church",
    "Bethel A.M.E. Church",
]
ALL_CHURCHES = (CHURCHES_CATHOLIC + CHURCHES_CATHOLIC_POLISH +
                CHURCHES_PROTESTANT_GERMAN + CHURCHES_PROTESTANT_ANGLO +
                CHURCHES_BLACK)

def church_for_ethnicity(eth):
    if eth == "spanish":
        return random.choice(CHURCHES_CATHOLIC)
    elif eth == "german":
        return random.choice(CHURCHES_CATHOLIC + CHURCHES_PROTESTANT_GERMAN)
    elif eth == "polish":
        return random.choice(CHURCHES_CATHOLIC_POLISH + CHURCHES_CATHOLIC[:3])
    elif eth == "black":
        return random.choice(CHURCHES_BLACK)
    else:  # anglo
        return random.choice(CHURCHES_PROTESTANT_ANGLO + CHURCHES_CATHOLIC[:2])

# Newspapers with actual publication date ranges
NEWSPAPER_RANGES = [
    ("San Antonio Express", 1865, 1984),
    ("San Antonio Light", 1881, 1993),
    ("San Antonio Daily Express", 1860, 1878),
    ("San Antonio Ledger", 1851, 1858),
    ("Freie Presse fuer Texas", 1865, 1897),
    ("San Antonio Herald", 1855, 1878),
    ("San Antonio Zeitung", 1853, 1914),
    ("San Antonio Gazette", 1848, 1856),
    ("The Southern Messenger", 1891, 1954),
    ("La Prensa", 1913, 1963),
]

def newspaper_for_year(year, eth=None):
    """Pick a newspaper that was actually publishing in the given year."""
    available = [(n, s, e) for n, s, e in NEWSPAPER_RANGES if s <= year <= e]
    if not available:
        available = [("San Antonio Express", 1865, 1984)]  # fallback
    # Ethnic preference
    if eth == "german":
        german_papers = [x for x in available if x[0] in ("Freie Presse fuer Texas", "San Antonio Zeitung")]
        if german_papers and random.random() < 0.4:
            return random.choice(german_papers)[0]
    if eth == "spanish":
        spanish_papers = [x for x in available if x[0] == "La Prensa"]
        if spanish_papers and random.random() < 0.3:
            return random.choice(spanish_papers)[0]
    return random.choice(available)[0]

OCCUPATIONS = [
    "Farmer", "Laborer", "Merchant", "Carpenter", "Blacksmith",
    "Rancher", "Teacher", "Clerk", "Lawyer", "Doctor",
    "Minister", "Soldier", "Teamster", "Mason", "Butcher",
    "Baker", "Tailor", "Shoemaker", "Printer", "Wheelwright",
    "Stock Raiser", "Domestic Servant", "Washerwoman", "Seamstress",
    "None", "At Home", "Cook", "Bartender", "Saloon Keeper",
    "Grocer", "Druggist", "Saddler", "Tinner", "Plasterer",
    "Painter", "Barber", "Brewer", "Hotel Keeper", "Stage Driver",
    "Freighter", "Surveyor", "Deputy Sheriff", "Scout",
]

BIRTHPLACES = {
    "spanish": ["Texas", "Mexico", "Coahuila", "Nuevo Leon", "Tamaulipas", "Chihuahua", "Canary Islands", "Spain"],
    "german": ["Germany", "Prussia", "Bavaria", "Saxony", "Wuerttemberg", "Hanover", "Baden", "Texas"],
    "anglo": ["Texas", "Virginia", "Tennessee", "Kentucky", "Georgia", "Alabama", "Missouri",
              "Mississippi", "North Carolina", "South Carolina", "Ohio", "New York", "Pennsylvania",
              "Ireland", "England", "Illinois", "Indiana", "Louisiana", "Arkansas", "Connecticut"],
    "black": ["Texas", "Virginia", "Alabama", "Georgia", "Mississippi", "Louisiana",
              "Tennessee", "North Carolina", "South Carolina", "Kentucky", "Missouri"],
    "polish": ["Poland", "Silesia", "Prussia", "Texas"],
}

BEXAR_LOCATIONS = [
    "San Antonio", "Bexar County", "Helotes", "Leon Springs",
    "Losoya", "Elmendorf", "Von Ormy", "Somerset",
    "Converse", "Kirby", "Windcrest", "Alamo Heights",
    "Bracken", "Schertz", "Cibolo", "Garden Ridge",
]

# Military units matched to wars
UNITS_BY_WAR = {
    "Texas Revolution": [
        "Bexar Guards", "New Orleans Greys", "Texian Army",
        "Gonzales Ranging Company", "Texas Volunteers",
    ],
    "Mexican-American War": [
        "1st Texas Mounted Rifles", "2nd Texas Mounted Rifles",
        "8th U.S. Infantry", "Texas Rangers", "Hays' Rangers",
    ],
    "Civil War (CSA)": [
        "Terry's Texas Rangers", "32nd Texas Cavalry", "Sibley's Brigade",
        "6th Texas Infantry", "3rd Texas Cavalry", "Hood's Texas Brigade",
        "4th Texas Infantry", "5th Texas Infantry", "Bexar Guards",
        "Alamo Rifles", "Waul's Texas Legion", "Duff's Partisan Rangers",
        "33rd Texas Cavalry", "Frontier Regiment",
    ],
    "Civil War (USA)": [
        "1st Texas Cavalry (USA)", "2nd Texas Cavalry (USA)",
        "Texas State Troops (Union)",
    ],
    "Indian Wars": [
        "Buffalo Soldiers, 24th Infantry", "Buffalo Soldiers, 25th Infantry",
        "Buffalo Soldiers, 9th Cavalry", "Buffalo Soldiers, 10th Cavalry",
        "4th U.S. Cavalry", "Frontier Battalion, Texas Rangers",
        "8th U.S. Cavalry", "19th Infantry Regiment",
    ],
    "Spanish-American War": [
        "Rough Riders, 1st U.S. Volunteer Cavalry",
        "23rd Infantry Regiment", "19th Infantry Regiment",
    ],
    "World War I": [
        "36th Infantry Division", "90th Infantry Division",
        "359th Infantry Regiment", "141st Infantry Regiment",
    ],
    "World War II": [
        "36th Infantry Division", "90th Infantry Division",
        "2nd Infantry Division", "1st Cavalry Division",
        "442nd Regimental Combat Team",
    ],
}

MILITARY_RANKS = [
    "Private", "Private", "Private", "Private",  # weighted toward enlisted
    "Corporal", "Corporal", "Sergeant", "Sergeant",
    "First Sergeant", "Lieutenant", "Captain", "Major",
    "Colonel", "Quartermaster Sergeant",
]

MILITARY_WARS = [
    "Texas Revolution", "Mexican-American War", "Civil War (CSA)",
    "Civil War (USA)", "Indian Wars", "Spanish-American War",
    "World War I", "World War II",
]

COUNTRIES_OF_ORIGIN = {
    "german": ["Germany", "Prussia", "Bavaria", "Wuerttemberg", "Saxony", "Hanover", "Baden", "Austria"],
    "spanish": ["Mexico", "Spain", "Canary Islands"],
    "polish": ["Poland", "Silesia", "Prussia"],
    "anglo": ["Ireland", "England", "Scotland", "Wales"],
    "black": [],  # typically not immigrants in this era
}

# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def random_date(start_year, end_year):
    y = random.randint(start_year, end_year)
    m = random.randint(1, 12)
    d = random.randint(1, 28)
    return f"{m:02d}/{d:02d}/{y}"

def write_csv(filename, headers, rows):
    path = os.path.join(OUT_DIR, filename)
    with open(path, "w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        writer.writerow(headers)
        writer.writerows(rows)
    print(f"  {filename}: {len(rows)} records")

# ---------------------------------------------------------------------------
# 1. Cemetery / Burial Index
# ---------------------------------------------------------------------------

def gen_cemetery():
    rows = []
    for _ in range(200):
        birth_year = random.randint(1790, 1960)
        surname, given, sex, eth = random_person(year=birth_year)
        age_at_death = random.randint(0, 95)
        death_year = birth_year + age_at_death
        if death_year > 2000:
            death_year = random.randint(birth_year + 1, 2000)
        actual_age = death_year - birth_year

        cemetery = random.choice(CEMETERIES)
        # Fort Sam Houston National Cemetery only after 1876
        if death_year < 1876 and cemetery == "Fort Sam Houston National Cemetery":
            cemetery = random.choice(["San Fernando Cemetery No. 1", "City Cemetery No. 1",
                                       "Mission Burial Ground", "Masonic Cemetery"])
        # Confederate Cemetery primarily for Civil War era
        if cemetery == "Confederate Cemetery" and death_year < 1861:
            cemetery = random.choice(["City Cemetery No. 1", "San Fernando Cemetery No. 1"])

        section = random.choice(["A", "B", "C", "D", "E", "F", "G", "H",
                                  "Old Section", "New Section", "Military",
                                  "Priests'", "Sisters'", "Children's"])
        if actual_age < 5:
            section = random.choice(["Children's", "A", "B", "C"])

        lot = str(random.randint(1, 500))

        # Notes that are consistent with the record
        notes = ""
        r = random.random()
        if r < 0.3:
            notes = ""  # no notes
        elif actual_age < 2:
            notes = "Infant"
        elif actual_age < 10:
            notes = f"Child, age {actual_age}"
        elif r < 0.4:
            notes = "Headstone broken"
        elif r < 0.45:
            notes = "Inscription partially illegible"
        elif r < 0.5:
            notes = "Shared marker with spouse"
        elif r < 0.55:
            notes = "Fieldstone marker, no inscription"
        elif r < 0.6 and death_year >= 1865 and death_year <= 1920:
            notes = "Confederate veteran"
        elif r < 0.65:
            notes = "Iron fence enclosure"
        elif r < 0.7 and sex == "M":
            notes = f"Husband of {female_given_for(eth)}"
        elif r < 0.75 and sex == "F":
            notes = f"Wife of {male_given_for(eth)}"
        elif r < 0.8:
            notes = f"Born in {random.choice(BIRTHPLACES.get(eth, ['Texas']))}"
        elif r < 0.85 and eth in ("german", "polish"):
            notes = f"Native of {random.choice(COUNTRIES_OF_ORIGIN.get(eth, ['Germany']))}"
        elif r < 0.9 and death_year >= 1900 and death_year < 1950 and sex == "M":
            notes = f"Spanish-American War veteran" if death_year < 1930 else "World War I veteran"

        rows.append([
            surname, given,
            random_date(birth_year, birth_year),
            random_date(death_year, death_year),
            cemetery,
            "San Antonio, Bexar County, Texas",
            section,
            lot,
            notes,
        ])
    write_csv("cemetery-burial-index.csv",
              ["Surname", "Given Name", "Birth Date", "Death Date",
               "Cemetery", "Location", "Section", "Lot", "Notes"],
              rows)

# ---------------------------------------------------------------------------
# 2. Census Transcriptions — 1850, 1860, 1870, 1880, 1900, 1910
# ---------------------------------------------------------------------------

def gen_census():
    rows = []
    census_years = [1850, 1860, 1870, 1880, 1900, 1910]
    for _ in range(250):
        year = random.choice(census_years)
        surname, given, sex, eth = random_person(year=year)
        age = random.randint(1, 80)
        race = race_for_ethnicity(eth)
        # 1850 census: free schedule didn't enumerate slaves by name
        # but free Black people were listed
        household = str(random.randint(1, 800))
        page = str(random.randint(1, 60))
        occ = random.choice(OCCUPATIONS) if age >= 12 else "None"
        if sex == "F" and random.random() < 0.5 and age >= 14:
            occ = random.choice(["At Home", "Domestic Servant", "Washerwoman",
                                  "Seamstress", "Teacher", "Cook", "None"])
        bp = random.choice(BIRTHPLACES.get(eth, ["Texas"]))
        # Children more likely born in Texas
        if age < 20 and random.random() < 0.6:
            bp = "Texas"
        rows.append([
            surname, given, str(age), sex, race, str(year),
            household, page, occ, bp,
            "Bexar", "Texas",
        ])
    write_csv("census-transcriptions.csv",
              ["Surname", "Given Name", "Age", "Sex", "Race", "Year",
               "Household", "Page", "Occupation", "Birthplace",
               "County", "State"],
              rows)

# ---------------------------------------------------------------------------
# 3. Church Records
# ---------------------------------------------------------------------------

def gen_church():
    rows = []
    event_types = ["Baptism", "Marriage", "Burial", "Confirmation",
                   "First Communion", "Death"]
    for _ in range(175):
        year = random.randint(1731, 1940)
        surname, given, sex, eth = random_person(year=year)
        event = random.choice(event_types)
        # First Communion and Confirmation are Catholic
        if event in ("First Communion", "Confirmation") and eth == "black":
            event = random.choice(["Baptism", "Burial", "Marriage"])
        church = church_for_ethnicity(eth)
        # Mission churches only active through ~1824, then sporadically
        if "Mission" in church and year > 1830 and random.random() < 0.7:
            church = church_for_ethnicity(eth)

        if "Mission" in church:
            loc = f"{church}, Bexar County, Texas"
        elif "Panna Maria" in church:
            loc = "Panna Maria, Karnes County, Texas"
        else:
            loc = "San Antonio, Texas"

        date = random_date(year, year)
        notes = ""
        r = random.random()
        if r < 0.3:
            notes = ""
        elif r < 0.4:
            notes = f"Parents: {male_given_for(eth)} and {female_given_for(eth)} {surname}"
        elif r < 0.5:
            sp_s, sp_g, _, sp_eth = random_person(year=year)
            notes = f"Sponsor: {sp_g} {sp_s}"
        elif r < 0.55 and year < 1850:
            notes = "Recorded in Latin"
        elif r < 0.6:
            notes = "Entry partially illegible"
        elif r < 0.65 and eth == "spanish" and year < 1836:
            notes = "Marginal note in Spanish"
        elif r < 0.7 and event == "Baptism":
            gp1_s, gp1_g, _, _ = random_person(year=year)
            gp2_s, gp2_g, _, _ = random_person(year=year)
            notes = f"Godparents: {gp1_g} {gp1_s} and {gp2_g} {gp2_s}"
        elif r < 0.75 and event == "Baptism":
            notes = "Adult convert"

        rows.append([surname, given, event, church, loc, date, notes])
    write_csv("church-records.csv",
              ["Surname", "Given Name", "Event Type", "Church",
               "Location", "Date", "Notes"],
              rows)

# ---------------------------------------------------------------------------
# 4. Obituary Index
# ---------------------------------------------------------------------------

def gen_obituary():
    rows = []
    for _ in range(200):
        death_year = random.randint(1870, 1970)
        surname, given, sex, eth = random_person(year=death_year)
        newspaper = newspaper_for_year(death_year, eth)
        pub_date = random_date(death_year, death_year)
        page = str(random.randint(1, 16))
        age = random.randint(18, 95)
        bp = random.choice(BIRTHPLACES.get(eth, ["Texas"]))

        notes_options = [
            f"Age {age}",
            f"Age {age}; survived by {'wife' if sex == 'M' else 'husband'} and {random.randint(2, 10)} children",
            f"Age {age}; born in {bp}",
            f"Age {age}; resident of San Antonio for {random.randint(5, 50)} years",
            f"Age {age}; member of {church_for_ethnicity(eth)}",
            f"Age {age}; buried at {random.choice(CEMETERIES)}",
            f"Age {age}; long illness",
        ]
        if death_year < 1910 and age > 60:
            notes_options.append(f"Pioneer settler; age {age}")
        if death_year >= 1865 and death_year <= 1930 and sex == "M":
            notes_options.append(f"Age {age}; Civil War veteran")
        if sex == "M" and random.random() < 0.15:
            notes_options.append(f"Age {age}; prominent merchant")

        rows.append([
            surname, given,
            random_date(death_year, death_year),
            newspaper, pub_date, page,
            random.choice(notes_options),
        ])
    write_csv("obituary-index.csv",
              ["Surname", "Given Name", "Death Date", "Newspaper",
               "Publication Date", "Page", "Notes"],
              rows)

# ---------------------------------------------------------------------------
# 5. Marriage Records
# ---------------------------------------------------------------------------

def gen_marriage():
    rows = []
    for _ in range(200):
        year = random.randint(1837, 1950)
        g_surname, g_given, g_eth = random_male_person(year)
        b_surname, b_given, b_eth = random_female_person(year)

        date = random_date(year, year)
        loc = random.choice(["San Antonio", "Bexar County"] + BEXAR_LOCATIONS[:6])

        # Officiants appropriate to the couple
        if g_eth == "spanish" or b_eth == "spanish":
            officiants = [
                "Rev. J. M. Odin", "Rev. Claude Jaillet", "Rev. P. F. Parisot",
                "Father Francis Bouchu", "Father Miguel de Arcos",
                "Judge Bryan Callaghan", "Judge Nelson Davis",
            ]
        elif g_eth == "german":
            officiants = [
                "Rev. Heinrich Schroeder", "Rev. William Newell",
                "Rev. John McCullough", "Judge J. R. Sweet",
                "Rev. P. F. Parisot",
            ]
        elif g_eth == "black":
            officiants = [
                "Rev. S. A. Hudson", "Rev. Walter Richardson",
                "Judge Nelson Davis", "Justice R. M. Turner",
            ]
        else:
            officiants = [
                "Rev. John McCullough", "Rev. William Newell",
                "Judge Nelson Davis", "Judge J. R. Sweet",
                "Justice A. B. Norton", "Rev. Walter Richardson",
                "Judge Thomas Devine",
            ]
        officiant = random.choice(officiants)
        book = random.choice(["A", "B", "C", "D", "E", "F", "G", "H",
                               "1", "2", "3", "4", "5", "6", "7", "8"])
        page = str(random.randint(1, 500))
        rows.append([
            g_surname, g_given, b_surname, b_given,
            date, loc, officiant, book, page,
        ])
    write_csv("marriage-records.csv",
              ["Groom Surname", "Groom Given Name",
               "Bride Surname", "Bride Given Name",
               "Date", "Location", "Officiant", "Book", "Page"],
              rows)

# ---------------------------------------------------------------------------
# 6. Vital Records (Birth & Death)
# ---------------------------------------------------------------------------

def gen_vital():
    rows = []
    for _ in range(200):
        event = random.choice(["Birth", "Birth", "Birth", "Death", "Death"])
        if event == "Birth":
            year = random.randint(1870, 1940)
        else:
            year = random.randint(1870, 1960)
        surname, given, sex, eth = random_person(year=year)

        if event == "Birth":
            notes_options = [
                "", "", "",
                f"Father: {male_given_for(eth)} {surname}",
                f"Mother: {female_given_for(eth)} {random.choice(ETHNIC_GROUPS[eth]['surnames'])}",
                f"Parents: {male_given_for(eth)} {surname} and {female_given_for(eth)} {random.choice(ETHNIC_GROUPS[eth]['surnames'])}",
                "Midwife attended",
                f"Born at home, {random.choice(BEXAR_LOCATIONS)}",
                "Twin",
            ]
        else:
            causes_early = ["Consumption", "Pneumonia", "Cholera", "Malaria",
                            "Yellow fever", "Typhoid fever", "Tuberculosis",
                            "Drowned", "Gunshot wound", "Accident"]
            causes_later = ["Heart disease", "Pneumonia", "Influenza",
                            "Tuberculosis", "Cancer", "Accident", "Old age",
                            "Stroke", "Kidney disease"]
            causes = causes_early if year < 1900 else causes_later
            if year < 1880:
                causes.append("Killed by Indians")
            age = random.randint(1, 90)
            notes_options = [
                "", "", "",
                f"Cause: {random.choice(causes)}",
                f"Age {age}",
                f"Buried at {random.choice(CEMETERIES)}",
                f"Attending physician: Dr. {random.choice(ETHNIC_GROUPS[random.choice(list(ETHNIC_GROUPS.keys()))]['surnames'])}",
            ]

        date = random_date(year, year)
        loc = random.choice(BEXAR_LOCATIONS)
        book = str(random.randint(1, 30))
        page = str(random.randint(1, 500))
        rows.append([
            surname, given, event, loc, date, book, page,
            random.choice(notes_options),
        ])
    write_csv("vital-records.csv",
              ["Surname", "Given Name", "Event Type", "Location",
               "Date", "Book", "Page", "Notes"],
              rows)

# ---------------------------------------------------------------------------
# 7. Military Records
# ---------------------------------------------------------------------------

def gen_military():
    rows = []
    for _ in range(150):
        war = random.choice(MILITARY_WARS)

        if war == "Texas Revolution":
            enlist_year = random.randint(1835, 1836)
        elif war == "Mexican-American War":
            enlist_year = random.randint(1846, 1848)
        elif war.startswith("Civil War"):
            enlist_year = random.randint(1861, 1865)
        elif war == "Indian Wars":
            enlist_year = random.randint(1866, 1890)
        elif war == "Spanish-American War":
            enlist_year = 1898
        elif war == "World War I":
            enlist_year = random.randint(1917, 1918)
        else:  # WW2
            enlist_year = random.randint(1941, 1945)

        surname, given, sex, eth = random_person(year=enlist_year)
        # Military was overwhelmingly male
        if sex == "F":
            surname, given, eth = random_male_person(enlist_year)

        unit = random.choice(UNITS_BY_WAR[war])
        # Buffalo Soldiers: Black soldiers
        if "Buffalo" in unit and eth != "black":
            # Re-pick a Black soldier
            data = ETHNIC_GROUPS["black"]
            surname = random.choice(data["surnames"])
            given = random.choice(data["male_given"])
            eth = "black"
        # Civil War USA: mostly Black soldiers from Texas or Unionists
        if war == "Civil War (USA)" and eth not in ("black", "anglo", "german"):
            data = ETHNIC_GROUPS[random.choice(["black", "anglo", "german"])]
            surname = random.choice(data["surnames"])
            given = random.choice(data["male_given"])

        rank = random.choice(MILITARY_RANKS)

        # Notes
        enlist_loc = random.choice(["San Antonio", "Bexar County"])
        if enlist_year >= 1876:
            enlist_loc = random.choice(["San Antonio", "Bexar County", "Fort Sam Houston"])

        discharge_loc = random.choice(["San Antonio", "Galveston", "Houston"])
        if enlist_year >= 1876:
            discharge_loc = random.choice(["San Antonio", "Fort Sam Houston", "Galveston"])

        burial_options = [random.choice(CEMETERIES)]
        if war.startswith("Civil War (CSA)"):
            burial_options.append("Confederate Cemetery")
        if enlist_year >= 1876:
            burial_options.append("Fort Sam Houston National Cemetery")

        notes_pool = [
            "", "", "",
            "Wounded in action",
            "Killed in action",
            "Died of disease",
            f"Mustered out at {discharge_loc}",
            f"Enlisted at {enlist_loc}",
            "Prisoner of war",
            f"Discharged at {discharge_loc}",
            f"Buried at {random.choice(burial_options)}",
            "Pension application on file",
        ]
        if war == "Texas Revolution":
            notes_pool.extend(["Present at Battle of the Alamo",
                               "Fought at San Jacinto",
                               "Present at Siege of Bexar"])

        residence = random.choice(BEXAR_LOCATIONS[:4])

        rows.append([
            surname, given, rank, unit, war,
            random_date(enlist_year, enlist_year), residence,
            random.choice(notes_pool),
        ])
    write_csv("military-records.csv",
              ["Surname", "Given Name", "Rank", "Unit", "War/Conflict",
               "Enlistment Date", "Residence", "Notes"],
              rows)

# ---------------------------------------------------------------------------
# 8. Land / Deed Records
# ---------------------------------------------------------------------------

def gen_land():
    rows = []
    surveys_rural = [
        "Survey 42, Abstract 123", "Survey 18, Abstract 67",
        "Porcion 15, Arroyo de Medio", "Labor 8, Mission San Jose",
        "Section 12, Block 3", "Survey 59, Abstract 202",
        "Labor 4, Mission Espada", "Porcion 22, Rio San Antonio",
        "Suerte 3, San Fernando de Bexar", "Survey 7, Abstract 45",
        "Survey 31, Abstract 178", "Labor 12, Mission Concepcion",
    ]
    surveys_urban = [
        "Lot 5, Block 12, Original Town", "NCB 137",
        "Lot 7, Alamo Addition", "Lot 14, Block 8, Dignowity Addition",
        "Lot 3, Block 6, Maverick Addition", "Lot 9, Block 2, Tobin Hill",
        "Lot 11, Block 5, Government Hill", "Lot 2, Block 3, Lavaca",
        "NCB 265", "Lot 8, Block 1, King William",
    ]

    for _ in range(150):
        year = random.randint(1731, 1920)

        # Determine deed type based on era
        if year < 1821:
            deed = "Spanish Land Grant"
        elif year < 1836:
            deed = random.choice(["Mexican Land Grant", "Deed of Trust"])
        elif year < 1846:
            deed = random.choice(["Republic of Texas Patent", "Warranty Deed", "Gift Deed"])
        else:
            deed = random.choice(["Warranty Deed", "Quit Claim Deed",
                                   "Deed of Trust", "Sheriff's Deed", "Tax Deed",
                                   "Partition Deed", "Gift Deed"])

        grantor_s, grantor_g, _, g_eth = random_person(year=year)
        grantee_s, grantee_g, _, _ = random_person(year=year)

        # Spanish/Mexican grants should have Spanish grantors
        if deed in ("Spanish Land Grant", "Mexican Land Grant") and g_eth != "spanish":
            g_data = ETHNIC_GROUPS["spanish"]
            grantor_s = random.choice(g_data["surnames"])
            grantor_g = random.choice(g_data["male_given"])

        # Acreage and survey description should be consistent
        if deed in ("Spanish Land Grant", "Mexican Land Grant", "Republic of Texas Patent"):
            # Rural grants
            acres = random.choice(["1 league", "1 labor", "1 sitio",
                                    "640 acres", "320 acres", "160 acres",
                                    "80 acres", "40 acres"])
            survey = random.choice(surveys_rural)
        elif year > 1870 and random.random() < 0.5:
            # Urban lots
            acres = random.choice(["Town lot", "City lot", "1/4 acre", "1/2 acre", "1 acre"])
            survey = random.choice(surveys_urban)
        else:
            acres = random.choice(["5 acres", "10 acres", "40 acres", "80 acres",
                                    "160 acres", "320 acres", "2 acres"])
            survey = random.choice(surveys_rural)

        # Consideration appropriate to deed type
        if deed == "Gift Deed":
            consideration = "Love and affection"
        elif deed in ("Spanish Land Grant", "Mexican Land Grant", "Republic of Texas Patent"):
            consideration = random.choice(["Royal grant", "Government patent",
                                            "$1.00 and other consideration", "Headright"])
        elif deed == "Tax Deed":
            consideration = f"${random.randint(5, 500)}.00"
        elif deed == "Sheriff's Deed":
            consideration = f"${random.randint(50, 2000)}.00"
        else:
            if year < 1870:
                consideration = f"${random.choice([1, 5, 10, 25, 50, 100, 200, 500])}.00"
            else:
                consideration = f"${random.choice([50, 100, 200, 500, 1000, 2500, 5000]):,}.00"

        volume = str(random.randint(1, 200))
        page = str(random.randint(1, 700))

        rows.append([
            f"{grantor_g} {grantor_s}", f"{grantee_g} {grantee_s}",
            deed, random_date(year, year), acres, survey,
            "Bexar County, Texas", volume, page, consideration,
        ])
    write_csv("land-deed-records.csv",
              ["Grantor", "Grantee", "Deed Type", "Date",
               "Acreage", "Survey/Description", "County",
               "Volume", "Page", "Consideration"],
              rows)

# ---------------------------------------------------------------------------
# 9. Probate / Estate Records
# ---------------------------------------------------------------------------

def gen_probate():
    rows = []
    doc_types = [
        "Last Will and Testament", "Letters of Administration",
        "Inventory and Appraisement", "Final Account",
        "Guardianship", "Partition of Estate",
        "Order of Sale", "Annual Return",
        "Letters Testamentary", "Application for Probate",
    ]
    for _ in range(120):
        year = random.randint(1837, 1950)
        surname, given, sex, eth = random_person(year=year)
        doc = random.choice(doc_types)
        case_no = str(random.randint(100, 9999))
        # Estate values scaled to era
        if year < 1870:
            val = f"${random.randint(50, 30000):,}"
        elif year < 1920:
            val = f"${random.randint(200, 50000):,}"
        else:
            val = f"${random.randint(500, 100000):,}"

        executor_s, executor_g, _, _ = random_person(year=year)

        notes_options = ["", "", ""]
        notes_options.append(f"Heirs: {random.randint(2, 12)} named")
        if year < 1865:
            notes_options.append("Includes slave property")
        notes_options.extend([
            f"Real estate in {random.choice(BEXAR_LOCATIONS)}",
            "Estate insolvent",
            "Contested by heirs",
            f"Executor: {executor_g} {executor_s}",
            "Includes livestock and farm equipment",
            "Minor children named",
        ])
        if eth in ("german", "anglo") and random.random() < 0.2:
            notes_options.append("Includes mercantile inventory")

        notes = random.choice(notes_options)
        rows.append([
            surname, given, doc, random_date(year, year),
            case_no, val, "Bexar County, Texas",
            random.choice(["Probate Book " + str(random.randint(1, 50)),
                           "Minutes Book " + random.choice(["A","B","C","D","E","F"])]),
            str(random.randint(1, 500)),
            notes,
        ])
    write_csv("probate-estate-records.csv",
              ["Surname", "Given Name", "Document Type", "Date",
               "Case Number", "Estate Value", "County",
               "Book", "Page", "Notes"],
              rows)

# ---------------------------------------------------------------------------
# 10. Immigration / Naturalization
# ---------------------------------------------------------------------------

def gen_immigration():
    rows = []
    ports_sea = [
        "Galveston", "Indianola", "New Orleans", "New York",
        "Baltimore", "Philadelphia", "Boston", "Port Lavaca",
    ]
    ports_border = ["Laredo", "Eagle Pass", "El Paso", "Brownsville"]

    # Only generate for ethnic groups that actually immigrated
    immigrant_groups = ["german", "spanish", "polish", "anglo"]  # not "black" (mostly not immigrants)

    for _ in range(150):
        eth = random.choice(immigrant_groups)
        data = ETHNIC_GROUPS[eth]
        year = random.randint(max(1840, data["earliest"]), 1930)
        sex = random.choice(["M", "F"])
        surname = random.choice(data["surnames"])
        given = random.choice(data["male_given"] if sex == "M" else data["female_given"])

        country = random.choice(COUNTRIES_OF_ORIGIN.get(eth, ["Unknown"]))

        if eth == "spanish" and country in ("Mexico",):
            port = random.choice(ports_border)
            doc_type = random.choice(["Border Crossing Record",
                                       "Declaration of Intention",
                                       "Petition for Naturalization"])
        else:
            port = random.choice(ports_sea)
            # Indianola destroyed by hurricane in 1886
            if year > 1886 and port == "Indianola":
                port = random.choice(["Galveston", "New Orleans", "New York"])
            doc_type = random.choice([
                "Declaration of Intention", "Petition for Naturalization",
                "Certificate of Naturalization", "Ship Manifest",
                "Passenger List",
            ])

        court = random.choice([
            "Bexar County District Court", "U.S. District Court, Western Texas",
            "Bexar County Court",
        ])
        # Ship manifests don't have court entries
        if doc_type in ("Ship Manifest", "Passenger List", "Border Crossing Record"):
            court = ""

        volume = str(random.randint(1, 30))
        page = str(random.randint(1, 500))
        rows.append([
            surname, given, country, port,
            random_date(year, year), doc_type, court,
            volume, page,
        ])
    write_csv("immigration-naturalization.csv",
              ["Surname", "Given Name", "Country of Origin",
               "Port of Entry", "Date", "Document Type",
               "Court", "Volume", "Page"],
              rows)

# ---------------------------------------------------------------------------
# 11. Newspaper Abstracts (non-obituary items)
# ---------------------------------------------------------------------------

def gen_newspaper():
    rows = []
    categories = [
        "Marriage announcement", "Birth announcement", "Anniversary",
        "Arrival notice", "Departure notice", "Business opening",
        "Legal notice", "Sheriff's sale", "Runaway notice",
        "Estate notice", "Missing person", "Crime report",
        "Fire report", "Society meeting", "Church dedication",
        "School graduation", "Election results", "Court proceedings",
        "Military mustering", "Indian depredation", "Livestock brand",
        "Land sale", "Auction notice", "Dissolution of partnership",
    ]
    # Some categories only make sense in certain eras
    early_only = {"Runaway notice", "Indian depredation", "Military mustering", "Livestock brand"}

    for _ in range(175):
        year = random.randint(1848, 1940)
        surname, given, sex, eth = random_person(year=year)
        newspaper = newspaper_for_year(year, eth)

        cat = random.choice(categories)
        # Runaway notices only pre-1865, Indian depredations mostly pre-1880
        if cat == "Runaway notice" and year > 1865:
            cat = random.choice(["Legal notice", "Sheriff's sale", "Crime report"])
        if cat == "Indian depredation" and year > 1880:
            cat = random.choice(["Crime report", "Fire report", "Court proceedings"])
        if cat == "Military mustering" and year > 1900:
            cat = random.choice(["Election results", "Society meeting"])

        page = str(random.randint(1, 12))
        col = str(random.randint(1, 6))

        spouse_given = random.choice(ETHNIC_GROUPS[eth]["female_given"] if sex == "M"
                                     else ETHNIC_GROUPS[eth]["male_given"])
        spouse_s, _, _, _ = random_person(year=year)

        streets = ["Commerce", "Houston", "Alamo", "Main", "Soledad",
                    "Flores", "Dolorosa", "Market", "Navarro", "St. Mary's"]

        abstracts = {
            "Marriage announcement": f"{given} {surname} married {spouse_given} {spouse_s} at {church_for_ethnicity(eth)}",
            "Birth announcement": f"Born to {'Mr.' if sex == 'M' else 'Mrs.'} {given} {surname}, a {'son' if random.random() < 0.5 else 'daughter'}",
            "Anniversary": f"Mr. and Mrs. {surname} celebrated {random.choice(['25th', '50th', '10th', '40th'])} wedding anniversary",
            "Arrival notice": f"{given} {surname} arrived from {random.choice(BIRTHPLACES.get(eth, ['Texas']))}",
            "Business opening": f"{given} {surname} opened a new {random.choice(['mercantile', 'dry goods store', 'saloon', 'hotel', 'blacksmith shop', 'livery stable', 'brewery', 'bakery'])} on {random.choice(streets)} Street",
            "Legal notice": f"Notice to creditors of the estate of {given} {surname}, deceased",
            "Sheriff's sale": f"Sale of property of {given} {surname} by order of the sheriff",
            "Runaway notice": (lambda g=random.random() < 0.5: f"Ran away from {given} {surname}: {'a negro man named ' + random.choice(['Sam', 'Jim', 'Ben', 'Tom', 'Caesar', 'Harry', 'Peter', 'Moses']) if g else 'a negro woman named ' + random.choice(['Mary', 'Jane', 'Lucy', 'Hannah', 'Dinah', 'Silvy', 'Patience', 'Rose'])}")(),
            "Crime report": f"{given} {surname} {'arrested for' if random.random() < 0.5 else 'victim of'} {random.choice(['theft', 'assault', 'disorderly conduct', 'robbery', 'trespass'])}",
            "Society meeting": f"{given} {surname} elected {random.choice(['president', 'secretary', 'treasurer'])} of {random.choice(['the Turnverein', 'the Casino Club', 'the Odd Fellows Lodge', 'the Masonic Lodge', 'the San Antonio Rifle Club', 'the German-English School']) if eth == 'german' else random.choice(['the Odd Fellows Lodge', 'the Masonic Lodge', 'the Knights of Pythias', 'the Woodmen of the World'])}",
            "Fire report": f"Fire at the property of {given} {surname} on {random.choice(streets)} Street",
            "Church dedication": f"Dedication of {church_for_ethnicity(eth)}; {given} {surname} among attendees",
            "Indian depredation": f"Indians raided the ranch of {given} {surname} near {random.choice(['Helotes', 'Leon Springs', 'Losoya', 'Medina River'])}",
            "Land sale": f"Sale of {random.choice(['40', '80', '160', '320'])} acres by {given} {surname} in Bexar County",
        }
        abstract = abstracts.get(cat, f"{cat} mentioning {given} {surname}")

        rows.append([
            surname, given, newspaper,
            random_date(year, year), page, col, cat, abstract,
        ])
    write_csv("newspaper-abstracts.csv",
              ["Surname", "Given Name", "Newspaper",
               "Date", "Page", "Column", "Category", "Abstract"],
              rows)

# ---------------------------------------------------------------------------
# 12. Tax Lists
# ---------------------------------------------------------------------------

def gen_tax():
    rows = []
    for _ in range(150):
        year = random.randint(1837, 1910)
        surname, given, sex, eth = random_person(year=year)

        # Property holdings — scale to era and ethnicity
        acres = random.choice([0, 0, 0, 10, 40, 80, 160, 320, 640,
                                1, 2, 5, 20, 50, 100, 200, 500])
        town_lots = random.randint(0, 5)
        horses = random.randint(0, 20)
        cattle = random.randint(0, 500)
        if year < 1865 and eth not in ("black",):
            # Slave ownership — not all whites owned slaves, most didn't
            slaves = random.choices([0, 0, 0, 0, 0, 0, 0, 1, 2, 3, 5, 8, 12],
                                    weights=[30, 20, 10, 8, 5, 3, 2, 5, 5, 4, 3, 3, 2])[0]
        else:
            slaves = 0

        # Assessed value scaled to era
        if year < 1870:
            assessed = random.randint(50, 30000)
        else:
            assessed = random.randint(100, 50000)
        tax_paid = round(assessed * random.uniform(0.005, 0.02), 2)

        precinct = random.choice(["Precinct 1", "Precinct 2", "Precinct 3",
                                   "Precinct 4", "San Antonio",
                                   "Helotes", "Leon Springs", "Losoya"])

        row = [
            surname, given, str(year), precinct,
            str(acres) if acres > 0 else "",
            str(town_lots) if town_lots > 0 else "",
            str(horses) if horses > 0 else "",
            str(cattle) if cattle > 0 else "",
        ]
        if year < 1865:
            row.append(str(slaves) if slaves > 0 else "")
        else:
            row.append("")
        row.extend([
            f"${assessed:,}",
            f"${tax_paid:,.2f}",
        ])
        rows.append(row)
    write_csv("tax-lists.csv",
              ["Surname", "Given Name", "Year", "Precinct",
               "Acres", "Town Lots", "Horses", "Cattle", "Slaves",
               "Assessed Value", "Tax Amount"],
              rows)

# ---------------------------------------------------------------------------
# Run all generators
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    print("Generating SAGHS sample records for San Antonio / Bexar County...\n")
    gen_cemetery()
    gen_census()
    gen_church()
    gen_obituary()
    gen_marriage()
    gen_vital()
    gen_military()
    gen_land()
    gen_probate()
    gen_immigration()
    gen_newspaper()
    gen_tax()
    print(f"\nDone. Files written to: {OUT_DIR}")
