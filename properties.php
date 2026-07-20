<?php
require_once __DIR__ . '/includes/functions.php';
$user = current_user();

$cities = [];
$res = db()->query("SELECT DISTINCT city FROM properties WHERE city != '' AND status = 'available' ORDER BY city");
while ($row = $res->fetch_assoc()) {
    $cities[] = $row['city'];
}

$pageTitle = 'Properties';
include __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Browse Properties</h1>
        <p>Find your perfect rental from our verified listings</p>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="filters-bar">
            <form id="filterForm" class="filter-form">
                <div class="filter-group">
                    <input type="text" name="search" id="fSearch" placeholder="Search...">
                </div>
                <div class="filter-group">
                    <select name="type" id="fType">
                        <option value="">All Types</option>
                        <option value="apartment">Apartment</option>
                        <option value="house">House</option>
                        <option value="room">Room</option>
                        <option value="studio">Studio</option>
                        <option value="villa">Villa</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="city" id="fCity">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $c): ?>
                            <option value="<?= e($c) ?>"><?= e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <input type="number" name="min_price" id="fMin" placeholder="Min Price">
                </div>
                <div class="filter-group">
                    <input type="number" name="max_price" id="fMax" placeholder="Max Price">
                </div>
                <button type="button" class="btn btn-outline" id="resetBtn"><i class="fas fa-redo"></i> Reset</button>
            </form>
        </div>

        <div class="results-info">
            <span id="resultsCount">Loading...</span>
        </div>

        <div class="property-grid" id="propertiesGrid">
            <div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading properties...</div>
        </div>

        <div class="section-cta" id="loadMoreCta" style="display:none;">
            <button class="btn btn-primary" id="loadMoreBtn" onclick="loadMore()">
                <i class="fas fa-chevron-down"></i> Load More Properties
            </button>
        </div>
    </div>
</section>

<script>
let propOffset = 0;
let filterTimer = null;

function getFilters() {
    return {
        search: document.getElementById('fSearch').value.trim(),
        type: document.getElementById('fType').value,
        city: document.getElementById('fCity').value,
        min_price: document.getElementById('fMin').value,
        max_price: document.getElementById('fMax').value
    };
}

function fetchProperties(reset) {
    if (reset) {
        propOffset = 0;
        document.getElementById('propertiesGrid').innerHTML = '<div class="loading-state"><i class="fas fa-spinner fa-spin"></i> Loading properties...</div>';
    }
    const f = getFilters();
    const params = new URLSearchParams({
        offset: propOffset,
        limit: 6,
        search: f.search,
        type: f.type,
        city: f.city,
        min_price: f.min_price,
        max_price: f.max_price
    });

    fetch(SITE_URL + '/api/load-properties.php?' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (reset) {
                document.getElementById('propertiesGrid').innerHTML = data.html || '<div class="empty-state"><i class="fas fa-search"></i><h3>No properties found</h3><p>Try adjusting your filters.</p></div>';
            } else {
                document.getElementById('propertiesGrid').insertAdjacentHTML('beforeend', data.html);
            }
            propOffset += 6;
            document.getElementById('resultsCount').textContent = data.total + ' propert' + (data.total === 1 ? 'y' : 'ies') + ' found';
            document.getElementById('loadMoreCta').style.display = data.has_more ? 'block' : 'none';
        })
        .catch(() => {
            document.getElementById('propertiesGrid').innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle"></i><h3>Error loading properties</h3></div>';
        });
}

function loadMore() {
    fetchProperties(false);
}

function liveFilter() {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => fetchProperties(true), 300);
}

['fSearch','fType','fCity','fMin','fMax'].forEach(id => {
    const el = document.getElementById(id);
    el.addEventListener('input', liveFilter);
    el.addEventListener('change', liveFilter);
});

document.getElementById('resetBtn').addEventListener('click', function() {
    document.getElementById('fSearch').value = '';
    document.getElementById('fType').value = '';
    document.getElementById('fCity').value = '';
    document.getElementById('fMin').value = '';
    document.getElementById('fMax').value = '';
    fetchProperties(true);
});

fetchProperties(true);
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
