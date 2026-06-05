<?php
/**
 * search.php
 * Multi-source academic conference search
 * Sources: Semantic Scholar + OpenAlex
 * Caching: file-based
 */

/* ===================== CONFIG ===================== */

define('CACHE_DIR', __DIR__ . '/cache');
define('CACHE_TTL', 3600); // 1 hour

if (!is_dir(CACHE_DIR)) {
    mkdir(CACHE_DIR, 0777, true);
}

/* ===================== MAIN API ===================== */

function searchConferences($query, $page = 1, $lr = null, $sort = null)
{
    $query = trim($query);
    if ($query === '') return [];

    $cacheKey  = md5($query . $page);
    $cacheFile = CACHE_DIR . "/$cacheKey.json";

    // ---- Cache hit ----
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_TTL) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    // ---- Fetch from sources ----
    $results = [];

    $results = array_merge(
        $results,
        semanticScholarSearch($query),
        openAlexSearch($query)
    );

    // ---- Deduplicate by link ----
    $results = deduplicateResults($results);

    // ---- Pagination ----
    $perPage = 10;
    $offset  = ($page - 1) * $perPage;
    $paged   = array_slice($results, $offset, $perPage);

    // ---- Cache store ----
    file_put_contents($cacheFile, json_encode($paged));

    return $paged;
}

/* ===================== SEMANTIC SCHOLAR ===================== */

function semanticScholarSearch($query)
{
    $url = "https://api.semanticscholar.org/graph/v1/paper/search?" . http_build_query([
        'query'  => "conference " . $query,
        'limit'  => 20,
        'fields' => 'title,abstract,venue,year,url'
    ]);

    $data = curlJson($url);
    if (empty($data['data'])) return [];

    $results = [];
    foreach ($data['data'] as $p) {
        if (empty($p['url'])) continue;

        $results[] = [
            'title'   => $p['title'] ?? '',
            'link'    => $p['url'],
            'snippet' => trim(($p['venue'] ?? '') . ' ' . ($p['year'] ?? '') . ' ' . ($p['abstract'] ?? '')),
            'image'   => 'placeholder-image.png'
        ];
    }

    return $results;
}

/* ===================== OPENALEX ===================== */

function openAlexSearch($query)
{
    $url = "https://api.openalex.org/works?" . http_build_query([
        'search' => "conference " . $query,
        'per-page' => 20
    ]);

    $data = curlJson($url);
    if (empty($data['results'])) return [];

    $results = [];
    foreach ($data['results'] as $w) {
        if (empty($w['id'])) continue;

        $results[] = [
            'title'   => $w['title'] ?? '',
            'link'    => $w['id'],
            'snippet' => trim(($w['host_venue']['display_name'] ?? '') . ' ' . ($w['publication_year'] ?? '')),
            'image'   => 'placeholder-image.png'
        ];
    }

    return $results;
}

/* ===================== HELPERS ===================== */

function curlJson($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: Academic-Conference-Search/1.0'
        ]
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        curl_close($ch);
        return [];
    }

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code !== 200) return [];

    return json_decode($response, true) ?: [];
}

function deduplicateResults($results)
{
    $seen = [];
    $unique = [];

    foreach ($results as $r) {
        if (empty($r['link'])) continue;
        if (isset($seen[$r['link']])) continue;

        $seen[$r['link']] = true;
        $unique[] = $r;
    }

    return $unique;
}
?>
