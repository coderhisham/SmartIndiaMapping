// Admin Panel JS

document.addEventListener('DOMContentLoaded', function() {
    // Setup event listeners
    setupEventListeners();
});

function setupEventListeners() {
    // View data button
    const viewDataBtn = document.getElementById('view-data');
    if (viewDataBtn) {
        viewDataBtn.addEventListener('click', loadDataTable);
    }
    
    // Resource type select
    const resourceTypeSelect = document.getElementById('view-resource-type');
    if (resourceTypeSelect) {
        resourceTypeSelect.addEventListener('change', function() {
            const resourceType = this.value;
            if (resourceType) {
                loadRegionsForResourceType(resourceType);
            }
        });
    }
}

function loadRegionsForResourceType(resourceType) {
    fetch(`php/get_regions.php?resource_type=${resourceType}`)
        .then(response => response.json())
        .then(data => {
            const regionSelect = document.getElementById('view-region');
            
            // Clear existing options except the first one
            while (regionSelect.options.length > 1) {
                regionSelect.remove(1);
            }
            
            // Add options for each region
            data.forEach(region => {
                const option = document.createElement('option');
                option.value = region;
                option.textContent = region;
                regionSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading regions:', error);
        });
}

function loadDataTable() {
    const resourceType = document.getElementById('view-resource-type').value;
    const region = document.getElementById('view-region').value;
    
    if (!resourceType) {
        alert('Please select a resource type');
        return;
    }
    
    // Build URL with optional region parameter
    let url = `php/get_resources.php?type=${resourceType}`;
    if (region) {
        url += `&region=${encodeURIComponent(region)}`;
    }
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            updateDataTable(data, resourceType);
        })
        .catch(error => {
            console.error('Error loading data:', error);
        });
}

function updateDataTable(data, resourceType) {
    const tableBody = document.getElementById('data-table-body');
    tableBody.innerHTML = '';
    
    if (data.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="5" class="py-4 px-4 text-center text-gray-500">No data found</td>`;
        tableBody.appendChild(row);
        return;
    }
    
    data.forEach(item => {
        const row = document.createElement('tr');
        
        // Get appropriate fields based on resource type
        const name = item.name || 'Unnamed';
        const region = item.state || 'N/A';
        let type = 'N/A';
        
        switch(resourceType) {
            case 'schools':
                type = item.level || 'School';
                break;
            case 'hospitals':
                type = item.hospital_type || 'Hospital';
                break;
            case 'water':
                type = item.source_type || 'Water Source';
                break;
            case 'electricity':
                type = item.power_type || 'Power Source';
                break;
        }
        
        row.innerHTML = `
            <td class="py-2 px-4">${item.id || '-'}</td>
            <td class="py-2 px-4">${name}</td>
            <td class="py-2 px-4">${region}</td>
            <td class="py-2 px-4">${type}</td>
            <td class="py-2 px-4">
                <button class="edit-btn text-blue-600 hover:text-blue-800 mr-2" data-id="${item.id}" data-type="${resourceType}">Edit</button>
                <button class="delete-btn text-red-600 hover:text-red-800" data-id="${item.id}" data-type="${resourceType}">Delete</button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
    
    // Add event listeners to edit and delete buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            openEditModal(id, type);
        });
    });
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const type = this.dataset.type;
            if (confirm('Are you sure you want to delete this item?')) {
                deleteResource(id, type);
            }
        });
    });
}

function openEditModal(id, type) {
    // This function would open a modal with a form to edit the resource
    // For simplicity, we'll just show an alert
    alert(`Edit feature for ${type} ID ${id} would open here`);
    
    // In a full implementation, you would:
    // 1. Fetch the resource details using the ID
    // 2. Open a modal with a form populated with the resource details
    // 3. Submit the form to update the resource
}

function deleteResource(id, type) {
    fetch(`php/delete_resource.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&type=${type}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload the data table
            loadDataTable();
            // Also refresh dashboard data
            loadDashboardData();
        } else {
            alert('Error deleting resource: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error deleting resource:', error);
        alert('Error deleting resource');
    });
}

// Function to parse URL parameters
function getUrlParam(param) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(param);
}

// Check for success/error messages from URL params
if (getUrlParam('upload') === 'success') {
    document.getElementById('upload-success').classList.remove('hidden');
} else if (getUrlParam('upload') === 'error') {
    document.getElementById('upload-error').classList.remove('hidden');
    document.getElementById('upload-error').textContent = 'Error uploading data: ' + (getUrlParam('message') || 'Unknown error');
} 