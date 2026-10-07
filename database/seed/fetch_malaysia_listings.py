"""Builds database/seed/listings.csv from real Malaysian businesses.

Source: Foursquare Open Source Places (Apache 2.0), 2025-02-06 release, mirrored
publicly (no account/token needed) at https://source.coop/fused/fsq-os-places.
That dataset has no opening-hours or description fields, so this script doesn't
fabricate any — those stay empty until a real owner claims the listing and fills
them in via the portal. That's the honest tradeoff of using real data.

Requires: pip install duckdb

Usage:
    python database/seed/fetch_malaysia_listings.py
"""
import csv
import math
import re
import sys
import time

import duckdb

RAW_PARQUET = "database/seed/raw/fsq_malaysia_local_business.parquet"
OUT_CSV = "database/seed/listings.csv"

# Curated cities: (name, state, timezone, lat, lng). All of Malaysia (peninsula,
# Sabah, Sarawak) is on Asia/Kuala_Lumpur (UTC+8), but the per-city timezone column
# stays because Business::isOpenNow() reads it.
TZ = "Asia/Kuala_Lumpur"
CITIES = [
    ("Kuala Lumpur", "Kuala Lumpur", TZ, 3.1390, 101.6869),
    ("Petaling Jaya", "Selangor", TZ, 3.1073, 101.6067),
    ("Shah Alam", "Selangor", TZ, 3.0738, 101.5183),
    ("Subang Jaya", "Selangor", TZ, 3.0565, 101.5851),
    ("Klang", "Selangor", TZ, 3.0449, 101.4456),
    ("Puchong", "Selangor", TZ, 3.0206, 101.6177),
    ("Kajang", "Selangor", TZ, 2.9927, 101.7909),
    ("Putrajaya", "Putrajaya", TZ, 2.9264, 101.6964),
    ("Cyberjaya", "Selangor", TZ, 2.9213, 101.6559),
    ("Seremban", "Negeri Sembilan", TZ, 2.7258, 101.9424),
    ("George Town", "Penang", TZ, 5.4141, 100.3288),
    ("Butterworth", "Penang", TZ, 5.3992, 100.3639),
    ("Ipoh", "Perak", TZ, 4.5975, 101.0901),
    ("Taiping", "Perak", TZ, 4.8500, 100.7333),
    ("Sungai Petani", "Kedah", TZ, 5.6470, 100.4877),
    ("Alor Setar", "Kedah", TZ, 6.1248, 100.3678),
    ("Johor Bahru", "Johor", TZ, 1.4927, 103.7414),
    ("Iskandar Puteri", "Johor", TZ, 1.4355, 103.6435),
    ("Batu Pahat", "Johor", TZ, 1.8548, 102.9325),
    ("Muar", "Johor", TZ, 2.0442, 102.5689),
    ("Melaka", "Melaka", TZ, 2.1896, 102.2501),
    ("Kuantan", "Pahang", TZ, 3.8077, 103.3260),
    ("Kota Bharu", "Kelantan", TZ, 6.1254, 102.2381),
    ("Kuala Terengganu", "Terengganu", TZ, 5.3302, 103.1408),
    ("Kangar", "Perlis", TZ, 6.4414, 100.1986),
    ("Kota Kinabalu", "Sabah", TZ, 5.9804, 116.0735),
    ("Sandakan", "Sabah", TZ, 5.8394, 118.1172),
    ("Tawau", "Sabah", TZ, 4.2498, 117.8871),
    ("Kuching", "Sarawak", TZ, 1.5535, 110.3593),
    ("Miri", "Sarawak", TZ, 4.3995, 113.9914),
    ("Sibu", "Sarawak", TZ, 2.2870, 111.8300),
    ("Bintulu", "Sarawak", TZ, 3.1700, 113.0360),
]
CITY_RADIUS_KM = 15  # rows farther than this from every curated city are dropped

# (slug, display name, keywords) in match priority order: narrower categories
# first so a multi-label row (e.g. a hotel's own restaurant) lands in the
# more specific bucket rather than the biggest one.
CATEGORIES = [
    ("dentists", "Dentists", ["dentist"]),
    ("clinics-doctors", "Clinics & Doctors", ["clinic", "doctor"]),
    ("auto-repair", "Auto Repair & Services", ["auto%repair", "motorcycle repair"]),
    ("salons-barbershops", "Salons & Barbershops", ["salon", "barber"]),
    ("gyms-fitness", "Gyms & Fitness", ["gym", "fitness"]),
    ("hotels", "Hotels & Stays", ["hotel"]),
    ("bakeries", "Bakeries", ["bakery"]),
    ("cafes-coffee", "Cafes & Coffee Shops", ["cafe", "coffee shop"]),
    ("clothing-fashion", "Clothing & Fashion", ["clothing store"]),
    ("restaurants", "Restaurants", ["restaurant"]),
]

MAX_PER_CITY_CATEGORY = 20  # caps total volume near the spec's ~5,000-listing target


def haversine_km(lat1, lng1, lat2, lng2):
    r = 6371
    p1, p2 = math.radians(lat1), math.radians(lat2)
    dp, dl = math.radians(lat2 - lat1), math.radians(lng2 - lng1)
    a = math.sin(dp / 2) ** 2 + math.cos(p1) * math.cos(p2) * math.sin(dl / 2) ** 2
    return 2 * r * math.asin(math.sqrt(a))


def nearest_city(lat, lng):
    best, best_d = None, CITY_RADIUS_KM
    for city in CITIES:
        d = haversine_km(lat, lng, city[3], city[4])
        if d < best_d:
            best, best_d = city, d
    return best


def match_category(labels):
    for slug, name, keywords in CATEGORIES:
        for kw in keywords:
            pattern = kw.replace("%", ".*")
            if re.search(pattern, labels, re.IGNORECASE):
                return slug, name
    return None, None


def main():
    t0 = time.time()
    con = duckdb.connect()
    con.execute("SET threads=16")
    con.execute("SET enable_progress_bar=false")

    rows = con.execute(f"""
        SELECT name, latitude, longitude, address, tel, website, email,
               array_to_string(fsq_category_labels, '|') AS labels
        FROM read_parquet('{RAW_PARQUET}')
        WHERE (tel IS NOT NULL OR website IS NOT NULL)
          AND name IS NOT NULL AND latitude IS NOT NULL AND longitude IS NOT NULL
    """).fetchall()
    print(f"{len(rows)} candidate rows with a phone or website, {round(time.time()-t0, 1)}s")

    cells = {}  # (city_name, category_slug) -> list of row dicts
    seen_names = set()
    for name, lat, lng, address, tel, website, email, labels in rows:
        cat_slug, cat_name = match_category(labels)
        if not cat_slug:
            continue
        city = nearest_city(lat, lng)
        if not city:
            continue
        city_name = city[0]
        key = (name.strip().lower(), city_name)
        if key in seen_names:
            continue
        seen_names.add(key)
        cells.setdefault((city_name, cat_slug), []).append({
            "name": name, "category": cat_name, "category_slug": cat_slug, "city": city_name, "region": city[1],
            "address": address or "", "phone": tel or "", "website": website or "",
            "email": email or "", "lat": lat, "lng": lng, "timezone": city[2],
        })

    out_rows = []
    for (city_name, cat_slug), items in cells.items():
        # Prefer rows with both phone and website when a cell needs trimming.
        items.sort(key=lambda r: (bool(r["phone"]) + bool(r["website"])), reverse=True)
        out_rows.extend(items[:MAX_PER_CITY_CATEGORY])

    header = ["name", "category", "category_slug", "city", "region", "address", "phone", "website", "email", "lat", "lng", "timezone"]
    with open(OUT_CSV, "w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=header)
        w.writeheader()
        for r in out_rows:
            w.writerow(r)

    print(f"Wrote {len(out_rows)} rows to {OUT_CSV} ({round(time.time()-t0, 1)}s total)")

    from collections import Counter
    by_city = Counter(r["city"] for r in out_rows)
    by_cat = Counter(r["category"] for r in out_rows)
    print("By city:", dict(sorted(by_city.items(), key=lambda x: -x[1])))
    print("By category:", dict(sorted(by_cat.items(), key=lambda x: -x[1])))


if __name__ == "__main__":
    main()
