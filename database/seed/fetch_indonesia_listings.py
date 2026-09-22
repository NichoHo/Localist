"""Builds database/seed/listings.csv from real Indonesian businesses.

Source: Foursquare Open Source Places (Apache 2.0), 2025-02-06 release, mirrored
publicly (no account/token needed) at https://source.coop/fused/fsq-os-places.
That dataset has no opening-hours or description fields, so this script doesn't
fabricate any — those stay empty until a real owner claims the listing and fills
them in via the portal. That's the honest tradeoff of using real data.

Requires: pip install duckdb

Usage:
    python database/seed/fetch_indonesia_listings.py
"""
import csv
import math
import re
import sys
import time

import duckdb

RAW_PARQUET = "database/seed/raw/fsq_indonesia_local_business.parquet"
OUT_CSV = "database/seed/listings.csv"

# Curated cities: (name, region/province, timezone, lat, lng). Spans all three
# Indonesian time zones (WIB/WITA/WIT) on purpose, since Business::isOpenNow()
# needs a real per-city timezone, not one hardcoded clock.
CITIES = [
    ("Jakarta", "DKI Jakarta", "Asia/Jakarta", -6.2088, 106.8456),
    ("Surabaya", "Jawa Timur", "Asia/Jakarta", -7.2575, 112.7521),
    ("Bandung", "Jawa Barat", "Asia/Jakarta", -6.9175, 107.6191),
    ("Bekasi", "Jawa Barat", "Asia/Jakarta", -6.2383, 106.9756),
    ("Depok", "Jawa Barat", "Asia/Jakarta", -6.4025, 106.7942),
    ("Bogor", "Jawa Barat", "Asia/Jakarta", -6.5971, 106.8060),
    ("Tangerang", "Banten", "Asia/Jakarta", -6.1783, 106.6319),
    ("South Tangerang", "Banten", "Asia/Jakarta", -6.2884, 106.7180),
    ("Semarang", "Jawa Tengah", "Asia/Jakarta", -6.9667, 110.4167),
    ("Surakarta", "Jawa Tengah", "Asia/Jakarta", -7.5755, 110.8243),
    ("Yogyakarta", "DI Yogyakarta", "Asia/Jakarta", -7.7956, 110.3695),
    ("Malang", "Jawa Timur", "Asia/Jakarta", -7.9666, 112.6326),
    ("Palembang", "Sumatera Selatan", "Asia/Jakarta", -2.9761, 104.7754),
    ("Medan", "Sumatera Utara", "Asia/Jakarta", 3.5952, 98.6722),
    ("Padang", "Sumatera Barat", "Asia/Jakarta", -0.9471, 100.4172),
    ("Pekanbaru", "Riau", "Asia/Jakarta", 0.5333, 101.4500),
    ("Batam", "Kepulauan Riau", "Asia/Jakarta", 1.0456, 104.0305),
    ("Jambi", "Jambi", "Asia/Jakarta", -1.6101, 103.6131),
    ("Bandar Lampung", "Lampung", "Asia/Jakarta", -5.4292, 105.2610),
    ("Banda Aceh", "Aceh", "Asia/Jakarta", 5.5483, 95.3238),
    ("Pontianak", "Kalimantan Barat", "Asia/Jakarta", -0.0263, 109.3425),
    ("Denpasar", "Bali", "Asia/Makassar", -8.6705, 115.2126),
    ("Makassar", "Sulawesi Selatan", "Asia/Makassar", -5.1477, 119.4327),
    ("Balikpapan", "Kalimantan Timur", "Asia/Makassar", -1.2379, 116.8529),
    ("Samarinda", "Kalimantan Timur", "Asia/Makassar", -0.5022, 117.1536),
    ("Banjarmasin", "Kalimantan Selatan", "Asia/Makassar", -3.3186, 114.5944),
    ("Manado", "Sulawesi Utara", "Asia/Makassar", 1.4748, 124.8421),
    ("Mataram", "Nusa Tenggara Barat", "Asia/Makassar", -8.5833, 116.1167),
    ("Jayapura", "Papua", "Asia/Jayapura", -2.5337, 140.7181),
    ("Ambon", "Maluku", "Asia/Jayapura", -3.6954, 128.1814),
    ("Sorong", "Papua Barat Daya", "Asia/Jayapura", -0.8762, 131.2558),
]
CITY_RADIUS_KM = 25  # rows farther than this from every curated city are dropped

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
