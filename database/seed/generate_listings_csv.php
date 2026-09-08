<?php
// Generates database/seed/listings.csv with ~5,000 synthetic Malaysian local-service listings.
// Synthetic data, clearly labeled. Lat/lng = city center + jitter (no geocoding API needed for seed).

$categories = [
    'Plumbers', 'Electricians', 'Cleaners', 'Tutors', 'Movers', 'Landscapers',
    'Painters', 'Locksmiths', 'Pest Control', 'Handyman', 'Roofers', 'Aircon Servicing',
];

// name => [region, lat, lng]
$cities = [
    'Kuala Lumpur'     => ['Federal Territory', 3.1390, 101.6869],
    'Petaling Jaya'    => ['Selangor', 3.1073, 101.6067],
    'Shah Alam'        => ['Selangor', 3.0733, 101.5185],
    'Subang Jaya'      => ['Selangor', 3.0567, 101.5851],
    'Klang'            => ['Selangor', 3.0449, 101.4456],
    'Ampang'           => ['Selangor', 3.1500, 101.7666],
    'Cheras'           => ['Kuala Lumpur', 3.0879, 101.7382],
    'Puchong'          => ['Selangor', 3.0250, 101.6167],
    'Kajang'           => ['Selangor', 2.9935, 101.7874],
    'Rawang'           => ['Selangor', 3.3213, 101.5767],
    'George Town'      => ['Penang', 5.4141, 100.3288],
    'Butterworth'      => ['Penang', 5.3992, 100.3639],
    'Bukit Mertajam'   => ['Penang', 5.3631, 100.4667],
    'Ipoh'             => ['Perak', 4.5975, 101.0901],
    'Taiping'          => ['Perak', 4.8500, 100.7333],
    'Johor Bahru'      => ['Johor', 1.4927, 103.7414],
    'Iskandar Puteri'  => ['Johor', 1.4234, 103.6317],
    'Batu Pahat'       => ['Johor', 1.8548, 102.9325],
    'Muar'             => ['Johor', 2.0442, 102.5689],
    'Malacca City'     => ['Malacca', 2.1896, 102.2501],
    'Seremban'         => ['Negeri Sembilan', 2.7297, 101.9381],
    'Kuantan'          => ['Pahang', 3.8077, 103.3260],
    'Kota Bharu'       => ['Kelantan', 6.1254, 102.2381],
    'Kuala Terengganu' => ['Terengganu', 5.3302, 103.1408],
    'Alor Setar'       => ['Kedah', 6.1264, 100.3673],
    'Sungai Petani'    => ['Kedah', 5.6497, 100.4877],
    'Kangar'           => ['Perlis', 6.4414, 100.1986],
    'Kuching'          => ['Sarawak', 1.5533, 110.3592],
    'Miri'             => ['Sarawak', 4.3995, 113.9914],
    'Sibu'             => ['Sarawak', 2.2870, 111.8305],
    'Kota Kinabalu'    => ['Sabah', 5.9804, 116.0735],
    'Sandakan'         => ['Sabah', 5.8394, 118.1172],
    'Tawau'            => ['Sabah', 4.2448, 117.8912],
    'Putrajaya'        => ['Federal Territory', 2.9264, 101.6964],
    'Cyberjaya'        => ['Selangor', 2.9189, 101.6520],
];

$prefixes = ['Ace', 'Prime', 'Metro', 'City', 'Rapid', 'Trusty', 'Golden', 'First Choice', 'Reliable', 'Pro', 'Elite', 'Budget', 'Express', 'Harmony', 'Sunrise', 'Skyline', 'Evergreen', 'United', 'Superb', 'Mega'];
$suffixesByCat = [
    'Plumbers'         => ['Plumbing', 'Plumbing Works', 'Pipe Services', 'Plumbing & Piping'],
    'Electricians'     => ['Electrical', 'Electric Works', 'Wiring Services', 'Electrical Solutions'],
    'Cleaners'         => ['Cleaning', 'Cleaning Services', 'Home Cleaning', 'Maid Services'],
    'Tutors'           => ['Tuition Centre', 'Learning Hub', 'Academy', 'Home Tuition'],
    'Movers'           => ['Movers', 'Moving Services', 'Relocation', 'Lorry Transport'],
    'Landscapers'      => ['Landscaping', 'Garden Services', 'Landscape Works', 'Greenscapes'],
    'Painters'         => ['Painting', 'Painting Works', 'Paint Services', 'Coating Specialists'],
    'Locksmiths'       => ['Locksmith', 'Lock & Key', 'Key Services', 'Security Locks'],
    'Pest Control'     => ['Pest Control', 'Pest Solutions', 'Exterminators', 'Pest Management'],
    'Handyman'         => ['Handyman', 'Home Repairs', 'Fix-It Services', 'Maintenance Works'],
    'Roofers'          => ['Roofing', 'Roof Repairs', 'Roofing Specialists', 'Roof Works'],
    'Aircon Servicing' => ['Aircond Services', 'Air Cond Specialist', 'Cooling Services', 'Aircon Care'],
];
$streets = ['Jalan Merdeka', 'Jalan Besar', 'Jalan Dato Onn', 'Jalan Sultan', 'Jalan Bunga Raya', 'Jalan Industri', 'Jalan Damai', 'Jalan Mawar', 'Jalan Melati', 'Jalan Cempaka', 'Jalan Anggerik', 'Jalan Seri Setia', 'Jalan Perdana', 'Jalan Aman', 'Jalan Bahagia'];

// Opening-hours templates. Each is a full week; 'trade' pool is emergency-lean
// (plumbers, electricians, locksmiths, aircon), 'shop' is project/office-lean.
$days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
$hoursTemplates = [
    'trade' => [ // 8am-7pm weekdays/Sat, Sunday emergency-only (kept "closed" — no walk-ins)
        'mon' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
        'tue' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
        'wed' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
        'thu' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
        'fri' => ['open' => '08:00', 'close' => '19:00', 'closed' => false],
        'sat' => ['open' => '08:00', 'close' => '16:00', 'closed' => false],
        'sun' => ['open' => '00:00', 'close' => '00:00', 'closed' => true],
    ],
    'extended' => [ // daily, open late (aircon servicing, cleaners in demand areas)
        'mon' => ['open' => '08:00', 'close' => '21:00', 'closed' => false],
        'tue' => ['open' => '08:00', 'close' => '21:00', 'closed' => false],
        'wed' => ['open' => '08:00', 'close' => '21:00', 'closed' => false],
        'thu' => ['open' => '08:00', 'close' => '21:00', 'closed' => false],
        'fri' => ['open' => '08:00', 'close' => '21:00', 'closed' => false],
        'sat' => ['open' => '08:00', 'close' => '21:00', 'closed' => false],
        'sun' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
    ],
    'emergency' => [ // 24 hour call-out (locksmiths, a slice of plumbers/electricians)
        'mon' => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
        'tue' => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
        'wed' => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
        'thu' => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
        'fri' => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
        'sat' => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
        'sun' => ['open' => '00:00', 'close' => '23:59', 'closed' => false],
    ],
    'shop' => [ // 9am-6pm weekdays/Sat, closed Sunday (roofers, painters, landscapers)
        'mon' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
        'tue' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
        'wed' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
        'thu' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
        'fri' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
        'sat' => ['open' => '09:00', 'close' => '13:00', 'closed' => false],
        'sun' => ['open' => '00:00', 'close' => '00:00', 'closed' => true],
    ],
    'tuition' => [ // afternoon/evening, closed Monday (tutors)
        'mon' => ['open' => '00:00', 'close' => '00:00', 'closed' => true],
        'tue' => ['open' => '14:00', 'close' => '21:00', 'closed' => false],
        'wed' => ['open' => '14:00', 'close' => '21:00', 'closed' => false],
        'thu' => ['open' => '14:00', 'close' => '21:00', 'closed' => false],
        'fri' => ['open' => '14:00', 'close' => '21:00', 'closed' => false],
        'sat' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
        'sun' => ['open' => '09:00', 'close' => '18:00', 'closed' => false],
    ],
];
// category => [template => weight]
$hoursByCategory = [
    'Plumbers'         => ['trade' => 55, 'emergency' => 35, 'extended' => 10],
    'Electricians'     => ['trade' => 60, 'emergency' => 30, 'extended' => 10],
    'Cleaners'         => ['shop' => 40, 'extended' => 60],
    'Tutors'           => ['tuition' => 100],
    'Movers'           => ['trade' => 70, 'extended' => 30],
    'Landscapers'      => ['shop' => 100],
    'Painters'         => ['shop' => 100],
    'Locksmiths'       => ['emergency' => 70, 'trade' => 30],
    'Pest Control'     => ['trade' => 50, 'extended' => 50],
    'Handyman'         => ['trade' => 60, 'shop' => 40],
    'Roofers'          => ['shop' => 100],
    'Aircon Servicing' => ['extended' => 65, 'trade' => 35],
];

function weightedPick(array $weights): string
{
    $roll = mt_rand(1, array_sum($weights));
    foreach ($weights as $key => $w) {
        if ($roll <= $w) {
            return $key;
        }
        $roll -= $w;
    }

    return array_key_first($weights);
}

function jitterHours(array $template, array $days): array
{
    $out = [];
    foreach ($days as $day) {
        $slot = $template[$day];
        if ($slot['closed']) {
            $out[$day] = $slot;
            continue;
        }
        // +/- 30 min jitter on open/close so businesses in the same template don't look identical.
        $jitter = fn ($time) => $time === '00:00' || $time === '23:59'
            ? $time
            : date('H:i', strtotime($time) + mt_rand(-30, 30) * 60);
        $out[$day] = ['open' => $jitter($slot['open']), 'close' => $jitter($slot['close']), 'closed' => false];
    }

    return $out;
}

mt_srand(42); // deterministic output

$out = fopen($argv[1] ?? 'listings.csv', 'w');
fputcsv($out, ['name', 'category', 'city', 'region', 'address', 'phone', 'website', 'email', 'description', 'hours', 'lat', 'lng']);

$target = 5000;
$rows = 0;
$used = [];
$cityNames = array_keys($cities);

while ($rows < $target) {
    $cat = $categories[mt_rand(0, count($categories) - 1)];
    $city = $cityNames[mt_rand(0, count($cityNames) - 1)];
    $prefix = $prefixes[mt_rand(0, count($prefixes) - 1)];
    $suffix = $suffixesByCat[$cat][mt_rand(0, count($suffixesByCat[$cat]) - 1)];
    $name = "$prefix $suffix $city";
    if (isset($used[$name])) {
        $name = "$prefix $suffix $city " . chr(65 + mt_rand(0, 25)); // disambiguate
        if (isset($used[$name])) continue;
    }
    $used[$name] = true;

    [$region, $clat, $clng] = $cities[$city];
    $lat = round($clat + (mt_rand(-300, 300) / 10000), 6);
    $lng = round($clng + (mt_rand(-300, 300) / 10000), 6);
    $street = $streets[mt_rand(0, count($streets) - 1)];
    $address = mt_rand(1, 250) . ", $street, " . $city;
    $phone = '01' . mt_rand(0, 9) . '-' . mt_rand(200, 999) . ' ' . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $domainSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $prefix . str_replace(' ', '', $suffix)));
    $website = mt_rand(0, 2) ? "https://www.$domainSlug.example.my" : '';
    $email = "hello@$domainSlug.example.my";
    $desc = "$name provides professional " . strtolower($cat === 'Handyman' ? 'handyman services' : $cat) .
        " in $city, $region. Trusted by local homeowners and businesses. Contact us for a free quote. (Synthetic demo listing.)";
    $template = weightedPick($hoursByCategory[$cat]);
    $hours = json_encode(jitterHours($hoursTemplates[$template], $days));

    fputcsv($out, [$name, $cat, $city, $region, $address, $phone, $website, $email, $desc, $hours, $lat, $lng]);
    $rows++;
}
fclose($out);
echo "Wrote $rows rows\n";
