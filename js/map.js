// Map initialization
let map;
let stateLayer;
let resourceMarkers = {
    schools: [],
    hospitals: [],
    water: [],
    electricity: []
};
let currentFilters = [];
let resourceData = {};
let activeRegion = null;

// Charts
let resourceChart;
let regionChart;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    initMap();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load initial data
    loadInitialData();
});

function initMap() {
    // Initialize Leaflet map
    map = L.map('map-container').setView([20.5937, 78.9629], 5);
    
    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 18
    }).addTo(map);
    
    // Load India GeoJSON
    loadIndiaGeoJSON();
}

function loadIndiaGeoJSON() {
    fetch('assets/india-states.geojson')
        .then(response => {
            if (!response.ok) {
                // If the file doesn't exist, display an error and use a sample GeoJSON
                console.error('GeoJSON file not found. Using a sample outline of India.');
                // Sample simplified GeoJSON of India's outline
                return {
                    type: "FeatureCollection",
                    features: [{
                        type: "Feature",
                        properties: { STATE: "India" },
                        geometry: {
                            type: "Polygon",
                            coordinates: [
                                [
                                    [77.0, 8.4], [97.0, 8.4], [97.0, 37.2], [77.0, 37.2], [77.0, 8.4]
                                ]
                            ]
                        }
                    }]
                };
            }
            return response.json();
        })
        .then(data => {
            stateLayer = L.geoJSON(data, {
                style: {
                    fillColor: '#3b82f6',
                    weight: 1,
                    opacity: 1,
                    color: 'white',
                    fillOpacity: 0.3
                },
                onEachFeature: function(feature, layer) {
                    // Add click event to states
                    layer.on({
                        click: function(e) {
                            resetStateStyles();
                            activeRegion = feature.properties.STATE;
                            
                            // Highlight the selected state
                            e.target.setStyle({
                                fillColor: '#1a56db',
                                fillOpacity: 0.6
                            });
                            
                            // Load data for this state
                            loadRegionData(feature.properties.STATE);
                            
                            // Update select dropdown
                            document.getElementById('region-select').value = feature.properties.STATE;
                            
                            // Show region information
                            showRegionInfo(feature.properties.STATE);
                        },
                        mouseover: function(e) {
                            if (feature.properties.STATE !== activeRegion) {
                                e.target.setStyle({
                                    fillOpacity: 0.5
                                });
                            }
                        },
                        mouseout: function(e) {
                            if (feature.properties.STATE !== activeRegion) {
                                stateLayer.resetStyle(e.target);
                            }
                        }
                    });
                    
                    // Add a tooltip
                    layer.bindTooltip(feature.properties.STATE, {
                        permanent: false,
                        direction: 'center',
                        className: 'state-tooltip'
                    });
                }
            }).addTo(map);
            
            // Fit bounds to the state layer
            map.fitBounds(stateLayer.getBounds());
            
            // Populate region select dropdown
            populateRegionSelect(data.features);
        })
        .catch(error => {
            console.error('Error loading GeoJSON:', error);
        });
}

function resetStateStyles() {
    stateLayer.eachLayer(function(layer) {
        stateLayer.resetStyle(layer);
    });
    activeRegion = null;
}

function setupEventListeners() {
    // Filter checkboxes
    document.querySelectorAll('.resource-filter').forEach(checkbox => {
        checkbox.addEventListener('change', updateFilters);
    });
    
    // Apply filters button
    document.getElementById('apply-filters').addEventListener('click', applyFilters);
    
    // Region select
    document.getElementById('region-select').addEventListener('change', function() {
        const selectedRegion = this.value;
        
        if (selectedRegion === '') {
            // Reset view if "All Regions" is selected
            resetStateStyles();
            map.fitBounds(stateLayer.getBounds());
            hideRegionInfo();
            clearMarkers();
            loadAllResources();
        } else {
            // Find and click the corresponding state on the map
            stateLayer.eachLayer(function(layer) {
                if (layer.feature.properties.STATE === selectedRegion) {
                    layer.fire('click');
                }
            });
        }
    });
}

function updateFilters() {
    currentFilters = [];
    document.querySelectorAll('.resource-filter:checked').forEach(checkbox => {
        currentFilters.push(checkbox.dataset.type);
    });
}

function applyFilters() {
    // Clear existing markers
    clearMarkers();
    
    // If no filters selected, show all resources
    if (currentFilters.length === 0) {
        loadAllResources();
        return;
    }
    
    // Otherwise, load selected resource types
    if (activeRegion) {
        // If a region is selected, only load for that region
        currentFilters.forEach(type => {
            loadResourcesByType(type, activeRegion);
        });
    } else {
        // Load for all regions
        currentFilters.forEach(type => {
            loadResourcesByType(type);
        });
    }
    
    // Update charts
    updateCharts();
}

function loadInitialData() {
    // Load all resource types
    loadAllResources();
    
    // Initialize charts
    initCharts();
}

function loadAllResources() {
    // Load all resource types for all regions
    loadResourcesByType('schools');
    loadResourcesByType('hospitals');
    loadResourcesByType('water');
    loadResourcesByType('electricity');
}

function loadResourcesByType(type, region = null) {
    // Build URL with optional region parameter
    let url = `php/get_resources.php?type=${type}`;
    if (region) {
        url += `&region=${encodeURIComponent(region)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Store data for charts
            resourceData[type] = data;
            
            // Create markers for each resource
            data.forEach(resource => {
                createMarker(resource, type);
            });
            
            // Update charts
            updateCharts();
        })
        .catch(error => {
            console.error(`Error loading ${type} data:`, error);
        });
}

function createMarker(resource, type) {
    // Check if coordinates are valid
    if (!resource.latitude || !resource.longitude) {
        console.warn(`Invalid coordinates for resource:`, resource);
        return;
    }
    
    // Create marker
    const marker = L.circleMarker([resource.latitude, resource.longitude], {
        radius: 6,
        fillColor: getMarkerColor(type),
        color: '#fff',
        weight: 1,
        opacity: 1,
        fillOpacity: 0.8
    });
    
    // Add popup
    marker.bindPopup(createPopupContent(resource, type));
    
    // Add to map
    marker.addTo(map);
    
    // Store marker reference
    resourceMarkers[type].push(marker);
}

function getMarkerColor(type) {
    switch(type) {
        case 'schools': return '#2563eb';
        case 'hospitals': return '#dc2626';
        case 'water': return '#0891b2';
        case 'electricity': return '#facc15';
        default: return '#1a56db';
    }
}

function createPopupContent(resource, type) {
    let content = `<div class="popup-header">${resource.name}</div>`;
    content += `<div class="popup-content">`;
    content += `<div class="item"><span class="label">Type:</span> <span class="value">${type.charAt(0).toUpperCase() + type.slice(1)}</span></div>`;
    content += `<div class="item"><span class="label">Region:</span> <span class="value">${resource.state || 'N/A'}</span></div>`;
    
    // Add type-specific information
    switch(type) {
        case 'schools':
            content += `<div class="item"><span class="label">Education Level:</span> <span class="value">${resource.level || 'N/A'}</span></div>`;
            content += `<div class="item"><span class="label">Students:</span> <span class="value">${resource.students || 'N/A'}</span></div>`;
            break;
        case 'hospitals':
            content += `<div class="item"><span class="label">Type:</span> <span class="value">${resource.hospital_type || 'N/A'}</span></div>`;
            content += `<div class="item"><span class="label">Beds:</span> <span class="value">${resource.beds || 'N/A'}</span></div>`;
            break;
        case 'water':
            content += `<div class="item"><span class="label">Source:</span> <span class="value">${resource.source_type || 'N/A'}</span></div>`;
            content += `<div class="item"><span class="label">Capacity:</span> <span class="value">${resource.capacity || 'N/A'}</span></div>`;
            break;
        case 'electricity':
            content += `<div class="item"><span class="label">Type:</span> <span class="value">${resource.power_type || 'N/A'}</span></div>`;
            content += `<div class="item"><span class="label">Capacity:</span> <span class="value">${resource.capacity || 'N/A'}</span></div>`;
            break;
    }
    
    content += `</div>`;
    return content;
}

function clearMarkers() {
    // Remove all markers by type
    for (const type in resourceMarkers) {
        resourceMarkers[type].forEach(marker => {
            map.removeLayer(marker);
        });
        resourceMarkers[type] = [];
    }
}

function loadRegionData(region) {
    // Clear existing markers
    clearMarkers();
    
    // Load resources for the selected region
    loadResourcesByType('schools', region);
    loadResourcesByType('hospitals', region);
    loadResourcesByType('water', region);
    loadResourcesByType('electricity', region);
}

function showRegionInfo(region) {
    const regionInfo = document.getElementById('region-info');
    regionInfo.classList.remove('hidden');
    
    document.getElementById('region-name').textContent = region;
    
    // Load region details from the server
    fetch(`php/get_region_info.php?region=${encodeURIComponent(region)}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('region-population').textContent = `Population: ${data.population.toLocaleString() || 'N/A'}`;
            
            // Update resource counts
            const resourceCounts = document.getElementById('resource-counts');
            resourceCounts.innerHTML = '';
            
            ['schools', 'hospitals', 'water', 'electricity'].forEach(type => {
                const count = data[type] || 0;
                const div = document.createElement('div');
                div.className = 'flex justify-between items-center text-sm';
                div.innerHTML = `
                    <span>${type.charAt(0).toUpperCase() + type.slice(1)}:</span>
                    <span class="font-medium">${count}</span>
                `;
                resourceCounts.appendChild(div);
            });
        })
        .catch(error => {
            console.error('Error loading region info:', error);
        });
}

function hideRegionInfo() {
    const regionInfo = document.getElementById('region-info');
    regionInfo.classList.add('hidden');
}

function populateRegionSelect(features) {
    const select = document.getElementById('region-select');
    
    // Clear existing options except the first one
    while (select.options.length > 1) {
        select.remove(1);
    }
    
    // Add options for each state
    features.forEach(feature => {
        const option = document.createElement('option');
        option.value = feature.properties.STATE;
        option.textContent = feature.properties.STATE;
        select.appendChild(option);
    });
}

function initCharts() {
    // Resource Distribution Chart
    const resourceCtx = document.getElementById('resourceChart').getContext('2d');
    resourceChart = new Chart(resourceCtx, {
        type: 'bar',
        data: {
            labels: ['Schools', 'Hospitals', 'Water', 'Electricity'],
            datasets: [{
                label: 'Number of Resources',
                data: [0, 0, 0, 0],
                backgroundColor: [
                    '#2563eb',
                    '#dc2626',
                    '#0891b2',
                    '#facc15'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Region Comparison Chart (initially empty)
    const regionCtx = document.getElementById('regionChart').getContext('2d');
    regionChart = new Chart(regionCtx, {
        type: 'pie',
        data: {
            labels: [],
            datasets: [{
                label: 'Resources by Region',
                data: [],
                backgroundColor: [
                    '#2563eb',
                    '#4f46e5',
                    '#7c3aed',
                    '#9333ea',
                    '#c026d3',
                    '#db2777',
                    '#e11d48',
                    '#ef4444',
                    '#f97316'
                ],
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
}

function updateCharts() {
    // Update resource distribution chart
    const resourceCounts = [
        resourceData.schools ? resourceData.schools.length : 0,
        resourceData.hospitals ? resourceData.hospitals.length : 0,
        resourceData.water ? resourceData.water.length : 0,
        resourceData.electricity ? resourceData.electricity.length : 0
    ];
    
    resourceChart.data.datasets[0].data = resourceCounts;
    resourceChart.update();
    
    // Update region comparison chart if we have data
    if (activeRegion) {
        // For the active region, show distribution by resource type
        regionChart.data.labels = ['Schools', 'Hospitals', 'Water', 'Electricity'];
        regionChart.data.datasets[0].data = resourceCounts;
    } else {
        // When no region is active, show top 5 regions by total resources
        updateRegionComparisonChart();
    }
}

function updateRegionComparisonChart() {
    // Collect all regions
    let regionData = {};
    
    // Count resources by region
    for (const type in resourceData) {
        if (resourceData[type]) {
            resourceData[type].forEach(resource => {
                const region = resource.state || 'Unknown';
                if (!regionData[region]) {
                    regionData[region] = 0;
                }
                regionData[region]++;
            });
        }
    }
    
    // Sort regions by resource count
    const sortedRegions = Object.entries(regionData)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 5); // Take top 5
    
    // Update chart
    regionChart.data.labels = sortedRegions.map(item => item[0]);
    regionChart.data.datasets[0].data = sortedRegions.map(item => item[1]);
    regionChart.update();
} 